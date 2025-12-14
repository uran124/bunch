<?php
// app/core/Controller.php

abstract class Controller
{
    protected function getDadataSettings(): array
    {
        return [
            'apiKey' => '6e4950476cc01a78b287788434dc1028eb3e86cf',
            'secretKey' => 'f2b84eb0e15b3c7b93c75ac50a8cd53b1a9defa1',
            'suggestions' => true,
            'geocoding' => true,
            'dailyLimit' => 1500,
            'requestsToday' => 240,
            'lastSync' => 'Сегодня, 09:20',
            'defaultDeliveryPrice' => 350,
        ];
    }

    protected function render(string $view, array $data = [], string $layout = 'layouts/main')
    {
        View::render($view, $data, $layout);
    }
}
