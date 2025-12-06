<?php
// app/core/Analytics.php

class Analytics
{
    private Logger $logger;

    public function __construct()
    {
        $this->logger = new Logger('analytics.log');
    }

    public function track(string $event, array $params = []): void
    {
        $this->logger->logEvent($event, $params);
    }
}
