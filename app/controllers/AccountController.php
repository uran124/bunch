<?php
// app/controllers/AccountController.php

class AccountController extends Controller
{
    private User $userModel;
    private UserAddress $addressModel;
    private Order $orderModel;
    private Subscription $subscriptionModel;
    private NotificationSetting $notificationSettingModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->addressModel = new UserAddress();
        $this->orderModel = new Order();
        $this->subscriptionModel = new Subscription();
        $this->notificationSettingModel = new NotificationSetting();
    }

    public function index()
    {
        $userId = Auth::userId();
        $userRow = $userId ? $this->userModel->findById($userId) : null;

        if (!$userRow) {
            header('Location: /?page=login');
            exit;
        }

        $user = [
            'name' => $userRow['name'] ?: 'Без имени',
            'phone' => $userRow['phone'],
            'email' => $userRow['email'],
        ];

        $addresses = $this->addressModel->getByUserId($userId);

        $activeOrderRow = $this->orderModel->getLatestActiveForUser($userId);
        $activeOrder = $this->mapOrderToView($activeOrderRow);

        $activeSubscriptionRow = $this->subscriptionModel->getActiveForUser($userId);
        $activeSubscription = $this->mapSubscriptionToView($activeSubscriptionRow);

        $notificationSettings = $this->notificationSettingModel->getSettingsForUser($userId);

        $pageMeta = [
            'title' => 'Личный кабинет — Bunch flowers',
            'description' => 'Управляйте профилем, адресами, заказами и подписками.',
            'headerTitle' => 'Bunch flowers',
            'headerSubtitle' => 'Профиль',
        ];

        $lastLogin = $this->formatDateTime($userRow['updated_at'] ?? $userRow['created_at'] ?? null);

        $this->render('account', compact(
            'user',
            'addresses',
            'activeOrder',
            'activeSubscription',
            'notificationSettings',
            'pageMeta',
            'lastLogin'
        ));
    }

    private function mapOrderToView(?array $order): ?array
    {
        if (!$order) {
            return null;
        }

        $firstItem = $order['items'][0] ?? null;
        $itemTotal = $firstItem ? (float) $firstItem['price'] * (int) $firstItem['qty'] : $order['total_amount'];

        return [
            'number' => '№' . str_pad((string) $order['id'], 4, '0', STR_PAD_LEFT),
            'datetime' => $this->formatDateTime($order['created_at']),
            'delivery_type' => $order['delivery_type'],
            'delivery_address' => $order['address'],
            'item' => [
                'name' => $firstItem['product_name'] ?? ($firstItem['name'] ?? 'Товар'),
                'qty' => $firstItem ? (int) $firstItem['qty'] : 1,
                'price' => $this->formatPrice($itemTotal),
                'unitPrice' => $firstItem ? $this->formatPrice((float) $firstItem['price']) : null,
                'image' => $firstItem['photo_url'] ?? '/assets/images/products/bouquet.svg',
            ],
            'total' => $this->formatPrice($order['total_amount']),
            'status' => $order['status'],
            'statusLabel' => $this->mapOrderStatus($order['status']),
        ];
    }

    private function mapSubscriptionToView(?array $subscription): ?array
    {
        if (!$subscription) {
            return null;
        }

        return [
            'frequency' => $this->formatPlan($subscription['plan']),
            'item' => $subscription['product_name'],
            'qty' => $subscription['qty'],
            'discount' => '—',
            'total' => $this->formatPrice($subscription['product_price'] * $subscription['qty']),
        ];
    }

    private function formatPrice(float $amount): string
    {
        return number_format($amount, 0, ',', ' ') . ' ₽';
    }

    private function formatDateTime(?string $dateTime): string
    {
        if (!$dateTime) {
            return '—';
        }

        try {
            $dt = new DateTime($dateTime);
            return $dt->format('d.m.Y, H:i');
        } catch (Exception $e) {
            return '—';
        }
    }

    private function formatPlan(string $plan): string
    {
        return match ($plan) {
            'weekly' => 'Раз в неделю',
            'biweekly' => 'Раз в 2 недели',
            'monthly' => 'Раз в месяц',
            default => 'Регулярно',
        };
    }

    private function mapOrderStatus(string $status): string
    {
        return match ($status) {
            'new' => 'Новый',
            'confirmed' => 'Подтвержден',
            'delivering' => 'В пути',
            'delivered' => 'Доставлен',
            'cancelled' => 'Отменен',
            default => 'В обработке',
        };
    }
}
