<?php
// app/core/Controller.php

abstract class Controller
{
    protected function getDadataSettings(): array
    {
        return [
            'apiKey' => DADATA_API_KEY,
            'secretKey' => DADATA_SECRET_KEY,
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
