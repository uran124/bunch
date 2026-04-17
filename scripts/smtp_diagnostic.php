<?php
declare(strict_types=1);

/**
 * SMTP diagnostic script.
 * CLI-only helper to debug SMTP connection/auth/send issues.
 *
 * Example:
 * php scripts/smtp_diagnostic.php \
 *   --host=mail.bunchflowers.ru \
 *   --port=465 \
 *   --encryption=ssl \
 *   --username=hello@bunchflowers.ru \
 *   --password='secret' \
 *   --from=hello@bunchflowers.ru \
 *   --to=hello@bunchflowers.ru
 */

function usage(): void
{
    $msg = <<<TXT
Usage:
  php scripts/smtp_diagnostic.php --host=HOST --port=PORT --encryption=tls|ssl|none --from=EMAIL --to=EMAIL [options]

Required:
  --host               SMTP host, e.g. mail.bunchflowers.ru
  --port               SMTP port, e.g. 465 or 587
  --encryption         tls | ssl | none
  --from               Envelope/header from e-mail
  --to                 Recipient e-mail for test message

Optional:
  --username           SMTP login (if omitted, defaults to --from)
  --password           SMTP password
  --allow-self-signed  1/0 (default: 0)
  --helo               EHLO name (default: bunchflowers.ru)
  --timeout            Timeout seconds (default: 12)
TXT;

    echo $msg . PHP_EOL;
}

function out(string $line): void
{
    echo $line . PHP_EOL;
}

function readResponse($socket): array
{
    $response = '';
    while (!feof($socket)) {
        $line = fgets($socket, 1024);
        if ($line === false) {
            break;
        }

        $response .= $line;
        if (isset($line[3]) && $line[3] === ' ') {
            break;
        }
    }

    preg_match('/^(\d{3})/m', $response, $matches);
    $code = isset($matches[1]) ? (int) $matches[1] : 0;

    return [$code, trim($response)];
}

function smtpCommand($socket, string $command, array $expectedCodes): bool
{
    out('> ' . $command);
    fwrite($socket, $command . "\r\n");
    [$code, $response] = readResponse($socket);
    out('< ' . $response);

    return in_array($code, $expectedCodes, true);
}

function extractDomain(string $email): ?string
{
    $email = trim($email);
    $parts = explode('@', $email);
    if (count($parts) !== 2 || trim($parts[1]) === '') {
        return null;
    }

    return strtolower(trim($parts[1]));
}

$opts = getopt('', [
    'host:',
    'port:',
    'encryption:',
    'username::',
    'password::',
    'from:',
    'to:',
    'allow-self-signed::',
    'helo::',
    'timeout::',
]);

if (PHP_SAPI !== 'cli') {
    http_response_code(400);
    header('Content-Type: text/plain; charset=utf-8');
    echo "This script is CLI-only.\n";
    exit(1);
}

$host = trim((string) ($opts['host'] ?? ''));
$port = (int) ($opts['port'] ?? 0);
$encryption = strtolower(trim((string) ($opts['encryption'] ?? '')));
$from = trim((string) ($opts['from'] ?? ''));
$to = trim((string) ($opts['to'] ?? ''));
$username = trim((string) ($opts['username'] ?? ''));
$password = (string) ($opts['password'] ?? '');
$allowSelfSigned = ((string) ($opts['allow-self-signed'] ?? '0')) === '1';
$helo = trim((string) ($opts['helo'] ?? 'bunchflowers.ru'));
$timeout = (int) ($opts['timeout'] ?? 12);

if ($host === '' || $port <= 0 || !in_array($encryption, ['tls', 'ssl', 'none'], true) || $from === '' || $to === '') {
    usage();
    exit(2);
}

if (!filter_var($from, FILTER_VALIDATE_EMAIL) || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
    out('ERROR: --from and --to must be valid e-mail addresses.');
    exit(2);
}

if ($username === '') {
    $username = $from;
}

if (strpos($username, '@') === false) {
    $fromDomain = extractDomain($from);
    if ($fromDomain !== null && strtolower($username) === $fromDomain) {
        $username = $from;
    }
}

$peerName = filter_var($host, FILTER_VALIDATE_IP) ? (extractDomain($from) ?? $host) : $host;
$context = stream_context_create([
    'ssl' => [
        'verify_peer' => !$allowSelfSigned,
        'verify_peer_name' => !$allowSelfSigned,
        'allow_self_signed' => $allowSelfSigned,
        'SNI_enabled' => true,
        'peer_name' => $peerName,
    ],
]);

$effectiveEncryption = $encryption;
if ($effectiveEncryption === 'ssl' && $port === 587) {
    $effectiveEncryption = 'tls';
    out('WARN: ssl + 587 detected, using STARTTLS (tls) for this run.');
} elseif ($effectiveEncryption === 'tls' && $port === 465) {
    $effectiveEncryption = 'ssl';
    out('WARN: tls + 465 detected, using implicit SSL for this run.');
}

$transportHost = $effectiveEncryption === 'ssl' ? 'ssl://' . $host : $host;
out('Connecting to ' . $transportHost . ':' . $port . ' ...');

$socket = @stream_socket_client(
    $transportHost . ':' . $port,
    $errno,
    $errstr,
    $timeout,
    STREAM_CLIENT_CONNECT,
    $context
);

if (!$socket) {
    out("ERROR: connect failed [{$errno}] {$errstr}");
    exit(3);
}

stream_set_timeout($socket, $timeout);

[$greetingCode, $greetingText] = readResponse($socket);
out('< ' . $greetingText);
if ($greetingCode !== 220) {
    out('ERROR: SMTP greeting code is not 220.');
    fclose($socket);
    exit(4);
}

if (!smtpCommand($socket, 'EHLO ' . $helo, [250])) {
    out('ERROR: EHLO failed.');
    fclose($socket);
    exit(5);
}

if ($effectiveEncryption === 'tls') {
    if (!smtpCommand($socket, 'STARTTLS', [220])) {
        out('ERROR: STARTTLS command failed.');
        fclose($socket);
        exit(6);
    }

    if (!@stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
        out('ERROR: TLS handshake failed.');
        while ($error = openssl_error_string()) {
            out('OpenSSL: ' . $error);
        }
        fclose($socket);
        exit(7);
    }

    if (!smtpCommand($socket, 'EHLO ' . $helo, [250])) {
        out('ERROR: EHLO after STARTTLS failed.');
        fclose($socket);
        exit(8);
    }
}

if ($username !== '') {
    if (!smtpCommand($socket, 'AUTH LOGIN', [334])) {
        out('ERROR: AUTH LOGIN not accepted by server.');
        fclose($socket);
        exit(9);
    }
    if (!smtpCommand($socket, base64_encode($username), [334])) {
        out('ERROR: SMTP username rejected.');
        fclose($socket);
        exit(10);
    }
    if (!smtpCommand($socket, base64_encode($password), [235])) {
        out('ERROR: SMTP password rejected.');
        fclose($socket);
        exit(11);
    }
}

if (!smtpCommand($socket, 'MAIL FROM:<' . $from . '>', [250])) {
    out('ERROR: MAIL FROM rejected.');
    fclose($socket);
    exit(12);
}

if (!smtpCommand($socket, 'RCPT TO:<' . $to . '>', [250, 251])) {
    out('ERROR: RCPT TO rejected.');
    fclose($socket);
    exit(13);
}

if (!smtpCommand($socket, 'DATA', [354])) {
    out('ERROR: DATA command rejected.');
    fclose($socket);
    exit(14);
}

$headers = [
    'Date: ' . date(DATE_RFC2822),
    'From: <' . $from . '>',
    'To: <' . $to . '>',
    'Subject: SMTP diagnostic test',
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
];
$body = "SMTP diagnostic from bunch project.\r\nTime: " . date('c') . "\r\n";
$payload = implode("\r\n", $headers) . "\r\n\r\n" . $body . "\r\n.\r\n";
fwrite($socket, $payload);

[$dataCode, $dataResponse] = readResponse($socket);
out('< ' . $dataResponse);
if ($dataCode !== 250) {
    out('ERROR: server did not accept message body.');
    fclose($socket);
    exit(15);
}

smtpCommand($socket, 'QUIT', [221]);
fclose($socket);

out('OK: test message accepted by SMTP server.');
exit(0);
