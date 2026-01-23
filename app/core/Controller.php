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

    protected function isAdminUser(): bool
    {
        return $this->getCurrentUserRole() === 'admin';
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

        if (!array_key_exists('tulipBalance', $data)) {
            $tulipBalance = 0;
            if (class_exists('Auth') && Auth::check()) {
                $userId = Auth::userId();
                if ($userId) {
                    $userModel = new User();
                    $user = $userModel->findById($userId);
                    $tulipBalance = (int) ($user['tulip_balance'] ?? 0);
                }
            }
            $data['tulipBalance'] = $tulipBalance;
        }

        if (!array_key_exists('staticMenuPages', $data) || !array_key_exists('staticFooterPages', $data)) {
            $staticPageModel = class_exists('StaticPage') ? new StaticPage() : null;
            $data['staticMenuPages'] = $data['staticMenuPages'] ?? ($staticPageModel ? $staticPageModel->getActiveByPlacement('menu') : []);
            $data['staticFooterPages'] = $data['staticFooterPages'] ?? ($staticPageModel ? $staticPageModel->getActiveByPlacement('footer') : []);
        }

        View::render($view, $data, $layout);
    }
}
