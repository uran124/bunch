<?php

use PHPUnit\Framework\TestCase;

final class LoggerTest extends TestCase
{
    private string $logFile;

    protected function setUp(): void
    {
        $this->logFile = __DIR__ . '/../storage/logs/phpunit-test.log';
        $this->cleanLog();
    }

    protected function tearDown(): void
    {
        $this->cleanLog();
    }

    private function cleanLog(): void
    {
        if (file_exists($this->logFile)) {
            unlink($this->logFile);
        }
    }

    public function testLogEventWritesJsonRecord(): void
    {
        $logger = new Logger('phpunit-test.log');
        $logger->logEvent('test_event', ['user' => 1]);

        $this->assertFileExists($this->logFile);
        $content = trim(file_get_contents($this->logFile));
        $data = json_decode($content, true);

        $this->assertSame('test_event', $data['event']);
        $this->assertSame(['user' => 1], $data['context']);
        $this->assertArrayHasKey('timestamp', $data);
    }

    public function testLogRawAppendsPlainMessage(): void
    {
        $logger = new Logger('phpunit-test.log');
        $logger->logRaw('first');
        $logger->logRaw('second');

        $lines = file($this->logFile, FILE_IGNORE_NEW_LINES);

        $this->assertSame(['first', 'second'], $lines);
    }
}
