<?php
// app/core/Controller.php

abstract class Controller
{
    protected function getCurrentUserRole(): string
    {
        if (!class_exists('Auth') || !Auth::check()) {
            return 'customer';
        }

        $userId = Auth::userId();
        if (!$userId) {
            return 'customer';
        }

        $userModel = new User();
        $user = $userModel->findById($userId);

        return $user['role'] ?? 'customer';
    }

    protected function isWholesaleUser(): bool
    {
        return $this->getCurrentUserRole() === 'wholesale';
    }

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
        if (!array_key_exists('currentUserRole', $data)) {
            $data['currentUserRole'] = $this->getCurrentUserRole();
        }

        if (!array_key_exists('isWholesaleUser', $data)) {
            $data['isWholesaleUser'] = $data['currentUserRole'] === 'wholesale';
        }

        View::render($view, $data, $layout);
    }
}
