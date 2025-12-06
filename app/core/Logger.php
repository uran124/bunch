<?php
// app/core/Logger.php

class Logger
{
    private string $logPath;

    public function __construct(string $fileName = 'app.log')
    {
        $this->logPath = __DIR__ . '/../../storage/logs/' . $fileName;
    }

    public function logEvent(string $event, array $context = []): void
    {
        $record = [
            'timestamp' => date('c'),
            'event' => $event,
            'context' => $context,
        ];

        $this->write(json_encode($record, JSON_UNESCAPED_UNICODE));
    }

    public function logRaw(string $message): void
    {
        $this->write($message);
    }

    private function write(string $message): void
    {
        $dir = dirname($this->logPath);

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents($this->logPath, $message . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}
