<?php
// app/controllers/AccountController.php

class AccountController extends Controller
{
    private User $userModel;
    private UserAddress $addressModel;
    private Order $orderModel;
    private Subscription $subscriptionModel;
    private NotificationSetting $notificationSettingModel;
    private Logger $logger;

    public function __construct()
    {
        $this->userModel = new User();
        $this->addressModel = new UserAddress();
        $this->orderModel = new Order();
        $this->subscriptionModel = new Subscription();
        $this->notificationSettingModel = new NotificationSetting();
        $this->logger = new Logger();
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
        $deliveryZoneModel = new DeliveryZone();

        $activeOrdersRaw = $this->orderModel->getActiveOrdersForUser($userId);
        $activeOrders = array_map([$this, 'mapOrderToView'], $activeOrdersRaw);
        $activeOrder = $activeOrders[0] ?? null;

        $activeSubscriptionsRaw = $this->subscriptionModel->getActiveListForUser($userId);
        $activeSubscriptions = $this->mapSubscriptionsToView($activeSubscriptionsRaw);
        $activeSubscription = $activeSubscriptions[0] ?? null;

        $notificationOptions = $this->getNotificationOptions();
        $this->notificationSettingModel->syncTypes($notificationOptions);
        $notificationSettings = $this->notificationSettingModel->getSettingsForUser($userId, $notificationOptions);

        $pageMeta = [
            'title' => 'Личный кабинет — Bunch flowers',
            'description' => 'Управляйте профилем, адресами, заказами и подписками.',
            'headerTitle' => 'Bunch flowers',
            'headerSubtitle' => 'Профиль',
        ];

        $lastLogin = $this->formatDateTime($userRow['updated_at'] ?? $userRow['created_at'] ?? null);

        $cart = new Cart();
        $cartShortcut = $this->buildCartShortcut($cart->getItems());
        $ordersLink = '/?page=orders';

        $deliveryZones = $deliveryZoneModel->getZones(true, true);
        $deliveryPricingVersion = $deliveryZoneModel->getPricingVersion();
        $testAddresses = $deliveryZoneModel->getTestAddresses();
        $dadataConfig = $this->getDadataSettings();

        $this->render('account', compact(
            'user',
            'addresses',
            'activeOrder',
            'activeSubscription',
            'notificationSettings',
            'pageMeta',
            'lastLogin',
            'activeOrders',
            'activeSubscriptions',
            'cartShortcut',
            'ordersLink',
            'notificationOptions',
            'deliveryZones',
            'deliveryPricingVersion',
            'dadataConfig',
            'testAddresses'
        ));
    }

    public function updateNotifications(): void
    {
        header('Content-Type: application/json');

        $userId = Auth::userId();
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['error' => 'Требуется вход в аккаунт']);
            return;
        }

        $payload = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $notifications = $payload['notifications'] ?? [];

        $options = $this->getNotificationOptions();
        $this->notificationSettingModel->syncTypes($options);

        $preferences = [];
        foreach ($options as $option) {
            $code = $option['code'];
            $locked = !empty($option['locked']);
            $default = (bool) ($option['default'] ?? true);
            $preferences[$code] = $locked ? true : (bool) ($notifications[$code] ?? $default);
        }

        $this->notificationSettingModel->updateSettingsForUser($userId, $preferences);

        echo json_encode(['ok' => true]);
    }

    public function calendar()
    {
        $userId = Auth::userId();
        $userRow = $userId ? $this->userModel->findById($userId) : null;

        if (!$userRow) {
            header('Location: /?page=login');
            exit;
        }

        $birthdayReminderDays = range(1, 7);
        $birthdayReminderLeadDays = (int) ($userRow['birthday_reminder_days'] ?? 3);
        $birthdayReminderLeadDays = max(1, min(7, $birthdayReminderLeadDays));
        $birthdayReminders = $this->prepareBirthdayReminders($userRow['birthday_reminders'] ?? null);

        $pageMeta = [
            'title' => 'Ваш календарь значимых дат — Bunch flowers',
            'description' => 'Управляйте напоминаниями о значимых датах.',
            'headerTitle' => 'Bunch flowers',
            'headerSubtitle' => 'Календарь',
        ];

        $this->render('account-calendar', compact(
            'birthdayReminderDays',
            'birthdayReminderLeadDays',
            'birthdayReminders',
            'pageMeta'
        ));
    }

    public function updatePin(): void
    {
        header('Content-Type: application/json');

        $userId = Auth::userId();
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['error' => 'Требуется вход в аккаунт']);
            return;
        }

        $payload = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $pin = $this->collectPinInput($payload, 'pin');
        $pinConfirm = $this->collectPinInput($payload, 'pin_confirm');

        if (!preg_match('/^\d{4}$/', $pin)) {
            http_response_code(422);
            echo json_encode(['error' => 'PIN должен состоять из 4 цифр.']);
            return;
        }

        if ($pin !== $pinConfirm) {
            http_response_code(422);
            echo json_encode(['error' => 'PIN и подтверждение не совпадают.']);
            return;
        }

        $pinHash = password_hash($pin, PASSWORD_DEFAULT);
        $this->userModel->updatePin($userId, $pinHash);
        $this->userModel->resetFailedAttempts($userId);
        $this->logger->logEvent('WEB_PIN_UPDATED', ['user_id' => $userId]);

        echo json_encode(['ok' => true]);
    }

    private function mapOrderToView(?array $order): ?array
    {
        if (!$order) {
            return null;
        }

        $firstItem = $order['items'][0] ?? null;
        $itemTotal = $firstItem ? (float) $firstItem['price'] * (int) $firstItem['qty'] : $order['total_amount'];

        return [
            'id' => $order['id'],
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
            'nextDelivery' => $this->formatDate($subscription['next_delivery_date'] ?? null),
        ];
    }

    private function mapSubscriptionsToView(array $subscriptions): array
    {
        return array_map(function (array $subscription): array {
            $base = $this->mapSubscriptionToView($subscription) ?? [];
            $base['id'] = $subscription['id'];
            return $base;
        }, $subscriptions);
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

    private function formatDate(?string $date): string
    {
        if (!$date) {
            return '—';
        }

        try {
            $dt = new DateTime($date);
            return $dt->format('d.m.Y');
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

    private function collectPinInput(array $payload, string $field): string
    {
        $raw = $payload[$field] ?? '';

        if (is_array($raw)) {
            $raw = implode('', $raw);
        }

        return preg_replace('/\D+/', '', (string) $raw) ?? '';
    }

    private function prepareBirthdayReminders(?string $raw): array
    {
        $items = [];
        if ($raw) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $items = $decoded;
            }
        }

        if (!$items) {
            $items = [
                [
                    'id' => 1,
                    'recipient' => 'Анна Смирнова',
                    'occasion' => 'День рождения',
                    'date' => '2024-04-12',
                ],
                [
                    'id' => 2,
                    'recipient' => 'Мария Иванова',
                    'occasion' => 'Юбилей',
                    'date' => '2024-05-03',
                ],
                [
                    'id' => 3,
                    'recipient' => 'Владимир К.',
                    'occasion' => 'День рождения',
                    'date' => '2024-06-19',
                ],
            ];
        }

        $normalized = [];
        foreach ($items as $index => $item) {
            $dateRaw = $item['date'] ?? null;
            $normalized[] = [
                'id' => $item['id'] ?? ($index + 1),
                'recipient' => $item['recipient'] ?? 'Получатель',
                'occasion' => $item['occasion'] ?? 'Повод',
                'date_raw' => $dateRaw ? (string) $dateRaw : '',
                'date' => $this->formatDate($dateRaw),
            ];
        }

        return $normalized;
    }

    private function getNotificationOptions(): array
    {
        return [
            [
                'code' => 'order_updates',
                'label' => 'Мои заказы',
                'description' => 'Статусы и изменения по текущим заказам.',
                'locked' => true,
                'default' => true,
                'channel' => 'push',
                'sort_order' => 10,
            ],
            [
                'code' => 'system_updates',
                'label' => 'Системные уведомления',
                'description' => 'Важные сообщения о безопасности и входах.',
                'locked' => true,
                'default' => true,
                'channel' => 'system',
                'sort_order' => 20,
            ],
            [
                'code' => 'promo_bouquets',
                'label' => 'Уведомить о букетах по акции',
                'description' => 'Скидки и подборки букетов недели.',
                'default' => true,
                'channel' => 'push',
                'sort_order' => 30,
            ],
            [
                'code' => 'auction_updates',
                'label' => 'Уведомить о новых аукционах',
                'description' => 'Свежие позиции и результаты торгов.',
                'default' => true,
                'channel' => 'push',
                'sort_order' => 40,
            ],
            [
                'code' => 'birthday_reminders',
                'label' => 'Напоминания о значимых днях',
                'description' => 'Подготовим идеи и напомним заранее.',
                'default' => true,
                'channel' => 'push',
                'sort_order' => 50,
                'link' => '/?page=account-calendar',
            ],
            [
                'code' => 'holiday_preorders',
                'label' => 'Предзаказы на праздники',
                'description' => 'Закрепим букет до пиковой нагрузки.',
                'default' => true,
                'channel' => 'push',
                'sort_order' => 60,
            ],
        ];
    }

    private function buildCartShortcut(array $items): ?array
    {
        if (!$items) {
            return null;
        }

        $first = $items[array_key_first($items)];
        $name = $first['name'] ?? 'Товар';
        $qty = (int) ($first['qty'] ?? 1);

        return [
            'title' => $name,
            'qty' => $qty,
            'count' => count($items),
        ];
    }
}
