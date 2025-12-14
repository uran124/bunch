<?php
// app/core/Controller.php

abstract class Controller
{
    protected function getDadataSettings(): array
    {
        return [
            'apiKey' => 'd32d23e2087d406928a38947855d3179f03dcff2',
            'secretKey' => 'b11fccf3100a8666f0cb5382071f2e935c449df9',
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
