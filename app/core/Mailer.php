<?php
// app/core/Mailer.php

class Mailer
{
    private string $host;
    private int $port;
    private string $encryption;
    private string $username;
    private string $password;
    private string $fromEmail;
    private string $fromName;
    private bool $allowSelfSigned;
    private string $tlsPeerName;

    public function __construct(array $config)
    {
        $this->host = trim((string) ($config['host'] ?? ''));
        $this->port = (int) ($config['port'] ?? 0);
        $this->encryption = strtolower(trim((string) ($config['encryption'] ?? 'tls')));
        $this->username = trim((string) ($config['username'] ?? ''));
        $this->password = (string) ($config['password'] ?? '');
        $this->fromEmail = trim((string) ($config['from_email'] ?? ''));
        $this->fromName = trim((string) ($config['from_name'] ?? 'Bunch flowers'));
        $this->allowSelfSigned = filter_var($config['allow_self_signed'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $this->tlsPeerName = $this->resolveTlsPeerName();

        if ($this->username === '' && $this->fromEmail !== '') {
            $this->username = $this->fromEmail;
        }

        $emailDomain = $this->extractDomain($this->fromEmail);
        if (
            $this->username !== ''
            && strpos($this->username, '@') === false
            && $emailDomain !== null
            && mb_strtolower($this->username) === $emailDomain
        ) {
            $this->username = $this->fromEmail;
        }
    }

    public function send(string $toEmail, string $subject, string $body): bool
    {
        $toEmail = trim($toEmail);
        if ($toEmail === '' || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        if ($this->host === '' || $this->port <= 0 || $this->fromEmail === '') {
            return false;
        }

        $effectiveEncryption = $this->resolveEffectiveEncryption();
        $transportHost = $effectiveEncryption === 'ssl' ? 'ssl://' . $this->host : $this->host;
        $socketContext = stream_context_create([
            'ssl' => [
                'verify_peer' => !$this->allowSelfSigned,
                'verify_peer_name' => !$this->allowSelfSigned,
                'allow_self_signed' => $this->allowSelfSigned,
                'SNI_enabled' => true,
                'peer_name' => $this->tlsPeerName,
            ],
        ]);

        $socket = @stream_socket_client(
            $transportHost . ':' . $this->port,
            $errno,
            $errstr,
            10,
            STREAM_CLIENT_CONNECT,
            $socketContext
        );
        if (!$socket) {
            (new Logger('mail_errors.log'))->logRaw(date('c') . ' smtp_connect_error ' . $errno . ' ' . $errstr);
            return false;
        }

        stream_set_timeout($socket, 10);

        if (!$this->expect($socket, [220])) {
            fclose($socket);
            return false;
        }

        if (!$this->command($socket, 'EHLO bunchflowers.ru', [250])) {
            fclose($socket);
            return false;
        }

        if ($effectiveEncryption === 'tls') {
            if (!$this->command($socket, 'STARTTLS', [220])) {
                fclose($socket);
                return false;
            }

            if (!@stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                $opensslErrors = [];
                while ($error = openssl_error_string()) {
                    $opensslErrors[] = $error;
                }
                (new Logger('mail_errors.log'))->logRaw(
                    date('c') . ' smtp_starttls_failed ' . ($opensslErrors ? implode(' | ', $opensslErrors) : 'unknown_error')
                );
                fclose($socket);
                return false;
            }

            if (!$this->command($socket, 'EHLO bunchflowers.ru', [250])) {
                fclose($socket);
                return false;
            }
        }

        if ($this->username !== '') {
            if (!$this->command($socket, 'AUTH LOGIN', [334])) {
                fclose($socket);
                return false;
            }
            if (!$this->command($socket, base64_encode($this->username), [334])) {
                fclose($socket);
                return false;
            }
            if (!$this->command($socket, base64_encode($this->password), [235])) {
                fclose($socket);
                return false;
            }
        }

        if (!$this->command($socket, 'MAIL FROM:<' . $this->fromEmail . '>', [250])) {
            fclose($socket);
            return false;
        }
        if (!$this->command($socket, 'RCPT TO:<' . $toEmail . '>', [250, 251])) {
            fclose($socket);
            return false;
        }
        if (!$this->command($socket, 'DATA', [354])) {
            fclose($socket);
            return false;
        }

        $headers = [
            'Date: ' . date(DATE_RFC2822),
            'From: ' . $this->encodeHeader($this->fromName) . ' <' . $this->fromEmail . '>',
            'To: <' . $toEmail . '>',
            'Subject: ' . $this->encodeHeader($subject),
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
        ];

        $payload = implode("\r\n", $headers) . "\r\n\r\n" . str_replace("\n", "\r\n", $body) . "\r\n.\r\n";
        fwrite($socket, $payload);

        if (!$this->expect($socket, [250])) {
            fclose($socket);
            return false;
        }

        $this->command($socket, 'QUIT', [221]);
        fclose($socket);

        return true;
    }

    private function command($socket, string $command, array $codes): bool
    {
        fwrite($socket, $command . "\r\n");
        return $this->expect($socket, $codes);
    }

    private function expect($socket, array $codes): bool
    {
        $response = '';

        while (!feof($socket)) {
            $line = fgets($socket, 512);
            if ($line === false) {
                break;
            }

            $response .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }

        if ($response === '' || !preg_match('/^(\d{3})/m', $response, $matches)) {
            return false;
        }

        $code = (int) $matches[1];
        if (!in_array($code, $codes, true)) {
            (new Logger('mail_errors.log'))->logRaw(date('c') . ' smtp_unexpected_code ' . $code . ' ' . trim($response));
            return false;
        }

        return true;
    }

    private function encodeHeader(string $text): string
    {
        if ($text === '') {
            return '';
        }

        return '=?UTF-8?B?' . base64_encode($text) . '?=';
    }

    private function resolveEffectiveEncryption(): string
    {
        if ($this->encryption === 'ssl' && $this->port === 587) {
            (new Logger('mail_errors.log'))->logRaw(date('c') . ' smtp_encryption_autoswitch ssl_to_tls port_587');
            return 'tls';
        }

        if ($this->encryption === 'tls' && $this->port === 465) {
            (new Logger('mail_errors.log'))->logRaw(date('c') . ' smtp_encryption_autoswitch tls_to_ssl port_465');
            return 'ssl';
        }

        return $this->encryption;
    }

    private function resolveTlsPeerName(): string
    {
        if (filter_var($this->host, FILTER_VALIDATE_IP)) {
            $emailDomain = $this->extractDomain($this->fromEmail);
            if ($emailDomain !== null) {
                return $emailDomain;
            }
        }

        return $this->host;
    }

    private function extractDomain(string $email): ?string
    {
        $email = trim($email);
        $parts = explode('@', $email);
        if (count($parts) !== 2 || trim($parts[1]) === '') {
            return null;
        }

        return mb_strtolower(trim($parts[1]));
    }
}
