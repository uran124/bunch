<?php
// app/controllers/CartController.php

class CartController extends Controller
{
    private const ORS_ORIGIN = [
        'lat' => 56.054764,
        'lon' => 92.909267,
    ];

    public function index(): void
    {
        $cart = new Cart();
        if (Auth::check()) {
            $auctionModel = new AuctionLot();
            $pendingLots = $auctionModel->getPendingWinnerLots((int) Auth::userId());
            foreach ($pendingLots as $lot) {
                try {
                    $cart->addItem((int) $lot['product_id'], 1, []);
                    $auctionModel->markWinnerCartAdded((int) $lot['id']);
                } catch (Throwable $e) {
                    continue;
                }
            }
        }
        $productModel = new Product();
        $deliveryZoneModel = new DeliveryZone();

        $items = $cart->getItems();
        $totals = $cart->getTotals();
        $accessoryProducts = $productModel->getActiveByCategory('accessory');

        $productAttributes = [];
        foreach ($items as $item) {
            $productId = (int) ($item['product_id'] ?? 0);
            if ($productId && !isset($productAttributes[$productId])) {
                $productAttributes[$productId] = $productModel->getAttributesWithValues($productId);
            }
        }

        $addresses = [];
        if (Auth::check()) {
            $addressModel = new UserAddress();
            $userId = Auth::userId();
            if ($userId !== null) {
                $addresses = $addressModel->getByUserId($userId);
            }
        }

        $this->render('cart', [
            'items' => $items,
            'totals' => $totals,
            'accessories' => $accessoryProducts,
            'productAttributes' => $productAttributes,
            'addresses' => $addresses,
            'deliveryZones' => $deliveryZoneModel->getZones(true, true),
            'deliveryPricingVersion' => $deliveryZoneModel->getPricingVersion(),
            'deliveryPricingMode' => $this->getDeliveryPricingMode(),
            'deliveryDistanceRates' => (new DeliveryDistanceRate())->getAll(),
            'orsApiKey' => $this->getOpenRouteApiKey(),
            'orsOrigin' => self::ORS_ORIGIN,
            'dadataConfig' => $this->getDadataSettings(),
            'testAddresses' => $deliveryZoneModel->getTestAddresses(),
            'onlinePaymentEnabled' => $this->isOnlinePaymentEnabled(),
            'pageMeta' => [
                'title' => 'Корзина — Bunch flowers',
                'description' => 'Проверьте позиции перед оформлением заказа.',
                'headerTitle' => 'Bunch flowers',
                'headerSubtitle' => 'Корзина',
            ],
        ]);
    }

    public function add(): void
    {
        header('Content-Type: application/json');

        $payload = json_decode(file_get_contents('php://input'), true) ?: $_POST;

        $productId = (int) ($payload['product_id'] ?? 0);
        $qty = (int) ($payload['qty'] ?? 0);
        $attributes = $payload['attributes'] ?? [];
        if (!is_array($attributes)) {
            $attributes = [];
        }

        if ($productId <= 0 || $qty <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Некорректные данные для добавления товара']);
            return;
        }

        try {
            $cart = new Cart();
            $item = $cart->addItem($productId, $qty, $attributes);
            $totals = $cart->getTotals();

            echo json_encode([
                'ok' => true,
                'item' => $item,
                'totals' => $totals,
            ]);
        } catch (Throwable $e) {
            http_response_code(400);
            echo json_encode([
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function update(): void
    {
        header('Content-Type: application/json');

        $payload = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $key = trim((string) ($payload['key'] ?? ''));
        $qty = (int) ($payload['qty'] ?? 0);
        $attributes = $payload['attributes'] ?? [];

        if ($key === '' || $qty < 1) {
            http_response_code(400);
            echo json_encode(['error' => 'Некорректные данные позиции']);
            return;
        }

        try {
            $cart = new Cart();
            $item = $cart->updateItem($key, $qty, is_array($attributes) ? $attributes : []);
            $totals = $cart->getTotals();

            echo json_encode([
                'ok' => true,
                'item' => $item,
                'totals' => $totals,
            ]);
        } catch (Throwable $e) {
            http_response_code(400);
            echo json_encode([
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function remove(): void
    {
        header('Content-Type: application/json');

        $payload = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $key = trim((string) ($payload['key'] ?? ''));

        if ($key === '') {
            http_response_code(400);
            echo json_encode(['error' => 'Позиция не найдена']);
            return;
        }

        try {
            $cart = new Cart();
            $cart->removeItem($key);
            $totals = $cart->getTotals();

            echo json_encode([
                'ok' => true,
                'totals' => $totals,
            ]);
        } catch (Throwable $e) {
            http_response_code(400);
            echo json_encode([
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function checkout(): void
    {
        header('Content-Type: application/json');

        if (!Auth::check()) {
            $this->logCheckoutError('Unauthorized checkout attempt');
            http_response_code(401);
            echo json_encode(['error' => 'Требуется авторизация']);
            return;
        }

        $payload = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $cart = new Cart();
        $items = $cart->getItems();

        if (empty($items)) {
            $this->logCheckoutError('Empty cart on checkout', ['user_id' => $userId]);
            http_response_code(400);
            echo json_encode(['error' => 'Корзина пуста']);
            return;
        }

        $userId = Auth::userId();
        $mode = $payload['mode'] ?? 'pickup';
        $date = $payload['date'] ?? null;
        $time = $payload['time'] ?? null;
        $addressId = isset($payload['address_id']) ? (int) $payload['address_id'] : null;
        $addressText = trim((string) ($payload['address_text'] ?? ''));
        
        $recipient = $payload['recipient'] ?? [];
        $recipientName = trim((string) ($recipient['name'] ?? ''));
        $recipientPhone = trim((string) ($recipient['phone'] ?? ''));
        $comment = trim((string) ($payload['comment'] ?? ''));
        $paymentMethod = trim((string) ($payload['payment_method'] ?? 'cash'));
        $allowedPaymentMethods = ['online', 'sbp', 'cash'];
        if (!in_array($paymentMethod, $allowedPaymentMethods, true)) {
            $paymentMethod = 'cash';
        }
        if ($paymentMethod === 'online' && !$this->isOnlinePaymentEnabled()) {
            $paymentMethod = 'sbp';
        }

        $addressModel = new UserAddress();
        $userAddresses = $addressModel->getByUserId((int) $userId);

        $addressDetails = is_array($payload['address'] ?? null) ? $payload['address'] : [];
        $deliveryPrice = $payload['delivery_price'] ?? null;
        $distanceKm = null;

        if ($mode === 'delivery' && $addressId !== null) {
            $validAddressIds = array_map(static fn ($row) => (int) ($row['raw']['id'] ?? 0), $userAddresses);
            if ($addressId > 0 && !in_array($addressId, $validAddressIds, true)) {
                $this->logCheckoutError('Address does not belong to user', [
                    'user_id' => $userId,
                    'address_id' => $addressId,
                ]);
                http_response_code(400);
                echo json_encode(['error' => 'Адрес не найден']);
                return;
            }

            if ($addressId > 0) {
                $addressModel->touchUsage((int) $userId, $addressId);
            }
        }

        if ($mode === 'delivery' && ($recipientName === '' || $recipientPhone === '')) {
            $userModel = new User();
            $userProfile = $userModel->findById((int) $userId) ?? [];
            if ($recipientName === '') {
                $recipientName = trim((string) ($userProfile['name'] ?? ''));
            }
            if ($recipientPhone === '') {
                $recipientPhone = trim((string) ($userProfile['phone'] ?? ''));
            }
        }

        if ($mode === 'delivery' && $this->getDeliveryPricingMode() === 'ors') {
            $pricingFallback = (int) ($this->getDadataSettings()['defaultDeliveryPrice'] ?? 0);
            $distanceRates = (new DeliveryDistanceRate())->getAll();
            $selectedAddress = $addressId ? $this->findAddressById($userAddresses, $addressId) : null;
            $distanceKm = $this->resolveDistanceKm($selectedAddress, $addressDetails, $payload);

            if ($distanceKm === null) {
                $lat = $this->extractCoordinate($selectedAddress, $addressDetails, $payload, 'lat');
                $lon = $this->extractCoordinate($selectedAddress, $addressDetails, $payload, 'lon');
                if ($lat !== null && $lon !== null) {
                    $distanceKm = $this->calculateDistanceKm($lon, $lat);
                }
            }

            if ($distanceKm !== null) {
                $deliveryPrice = $this->findDistancePrice($distanceKm, $distanceRates) ?? $pricingFallback;
            } else {
                $deliveryPrice = $pricingFallback;
            }

            if ($addressId && $distanceKm !== null && empty($selectedAddress['raw']['distance_km'])) {
                $addressModel->updateDistanceForUser((int) $userId, $addressId, $distanceKm);
            }
        }

        if ($mode === 'delivery' && $addressId === null) {
            $hasAddressPayload = $addressText !== '' || array_filter($addressDetails);
            if (!$hasAddressPayload) {
                $this->logCheckoutError('Delivery checkout without address payload', [
                    'user_id' => $userId,
                ]);
                http_response_code(400);
                echo json_encode(['error' => 'Укажите адрес доставки']);
                return;
            }

            $settlement = trim((string) ($addressDetails['settlement'] ?? ''));
            $street = trim((string) ($addressDetails['street'] ?? ''));
            $house = trim((string) ($addressDetails['house'] ?? ''));

            if ($street === '' || $house === '') {
                http_response_code(400);
                echo json_encode(['error' => 'Укажите улицу и номер дома']);
                return;
            }

            if ($recipientName === '' || $recipientPhone === '') {
                http_response_code(400);
                echo json_encode(['error' => 'Укажите имя и телефон получателя']);
                return;
            }

            try {
                $zoneCalculatedAt = $this->normalizeZoneCalculatedAt(
                    $payload['zone_calculated_at'] ?? ($addressDetails['zone_calculated_at'] ?? null)
                );
                $addressPayload = array_merge($addressDetails, [
                    'address_text' => $addressText,

                    'recipient_name' => $recipientName ?: null,
                    'recipient_phone' => $recipientPhone ?: null,
                    'zone_id' => $payload['zone_id'] ?? ($addressDetails['zone_id'] ?? null),
                    'zone_version' => $payload['zone_version'] ?? $payload['delivery_pricing_version'] ?? null,
                    'zone_calculated_at' => $zoneCalculatedAt,
                    'last_delivery_price_hint' => $deliveryPrice ?? null,
                    'location_source' => $addressDetails['location_source'] ?? ($payload['location_source'] ?? null),
                    'geo_quality' => $addressDetails['geo_quality'] ?? ($payload['geo_quality'] ?? null),
                    'lat' => $addressDetails['lat'] ?? null,
                    'lon' => $addressDetails['lon'] ?? null,
                    'distance_km' => $distanceKm ?? ($payload['distance_km'] ?? null),
                ]);

                $validLocationSources = ['dadata', 'manual_pin', 'other'];
                if (!empty($addressPayload['location_source']) && !in_array($addressPayload['location_source'], $validLocationSources, true)) {
                    $addressPayload['location_source'] = 'other';
                }

                $addressId = $addressModel->createForUser((int) $userId, $addressPayload);
            } catch (Throwable $e) {
                $this->logCheckoutError('Failed to save address during checkout', [
                    'user_id' => $userId,
                    'message' => $e->getMessage(),
                ]);
                http_response_code(400);
                echo json_encode(['error' => 'Не удалось сохранить адрес']);
                return;
            }
        }

        try {
            $orderModel = new Order();
            $orderId = $orderModel->createFromCart((int) $userId, $items, [
                'mode' => $mode,
                'date' => $date,
                'time' => $time,
                'address_id' => $addressId,
                'address_text' => $addressText,
                 
                'address' => $addressDetails,
                'delivery_price' => $deliveryPrice,
                'zone_id' => $payload['zone_id'] ?? null,
                'delivery_pricing_version' => $payload['delivery_pricing_version'] ?? null,
                'recipient_name' => $recipientName ?: null,
                'recipient_phone' => $recipientPhone ?: null,
                'comment' => $comment,
                'payment_method' => $paymentMethod,
            ]);

            $cart->clear();
            $paymentLink = null;
            if ($paymentMethod === 'online') {
                $paymentLink = $orderModel->getOnlinePaymentLink($orderId);
                if ($paymentLink === null) {
                    $paymentLink = '/order-payment?id=' . $orderId;
                }
            }

            echo json_encode([
                'ok' => true,
                'order_id' => $orderId,
                'payment_link' => $paymentLink,
            ]);
        } catch (Throwable $e) {
            $this->logCheckoutError('Checkout failed', [
                'user_id' => $userId,
                'message' => $e->getMessage(),
            ]);
            http_response_code(400);
            echo json_encode([
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function normalizeZoneCalculatedAt(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        try {
            $dt = new DateTimeImmutable($value);
        } catch (Throwable $e) {
            return null;
        }

        return $dt->format('Y-m-d H:i:s');
    }

    private function getDeliveryPricingMode(): string
    {
        $settings = new Setting();
        $defaults = $settings->getDeliveryDefaults();
        $mode = $settings->get(Setting::DELIVERY_PRICING_MODE, $defaults[Setting::DELIVERY_PRICING_MODE] ?? 'turf');

        return in_array($mode, ['turf', 'ors'], true) ? $mode : 'turf';
    }

    private function getOpenRouteApiKey(): string
    {
        $settings = new Setting();
        $defaults = $settings->getDeliveryDefaults();

        return (string) $settings->get(Setting::OPENROUTE_API_KEY, $defaults[Setting::OPENROUTE_API_KEY] ?? '');
    }

    private function findAddressById(array $addresses, int $addressId): ?array
    {
        foreach ($addresses as $address) {
            if (isset($address['raw']['id']) && (int) $address['raw']['id'] === $addressId) {
                return $address;
            }
        }
        return null;
    }

    private function extractCoordinate(?array $selectedAddress, array $addressDetails, array $payload, string $key): ?float
    {
        $value = $addressDetails[$key] ?? ($payload[$key] ?? null);
        if ($value === null && $selectedAddress) {
            $value = $selectedAddress['raw'][$key] ?? null;
        }
        if ($value === null || $value === '') {
            return null;
        }
        $number = (float) $value;
        return is_finite($number) && $number !== 0.0 ? $number : null;
    }

    private function resolveDistanceKm(?array $selectedAddress, array $addressDetails, array $payload): ?float
    {
        $distanceRaw = $addressDetails['distance_km'] ?? ($payload['distance_km'] ?? null);
        if ($distanceRaw === null && $selectedAddress) {
            $distanceRaw = $selectedAddress['raw']['distance_km'] ?? null;
        }
        if ($distanceRaw === null || $distanceRaw === '') {
            return null;
        }
        $distance = (float) $distanceRaw;
        if (!is_finite($distance) || $distance <= 0) {
            return null;
        }
        return round($distance, 2);
    }

    private function calculateDistanceKm(float $lon, float $lat): ?float
    {
        $apiKey = $this->getOpenRouteApiKey();
        if ($apiKey === '') {
            return null;
        }
        $client = new OpenRouteServiceClient($apiKey);
        return $client->getDistanceKm(
            [self::ORS_ORIGIN['lon'], self::ORS_ORIGIN['lat']],
            [$lon, $lat]
        );
    }

    private function findDistancePrice(float $distanceKm, array $rates): ?int
    {
        foreach ($rates as $rate) {
            $min = isset($rate['min_km']) ? (float) $rate['min_km'] : null;
            $max = isset($rate['max_km']) && $rate['max_km'] !== null ? (float) $rate['max_km'] : null;
            if ($min === null) {
                continue;
            }
            if ($distanceKm < $min) {
                continue;
            }
            if ($max !== null && $distanceKm > $max) {
                continue;
            }
            return (int) ($rate['price'] ?? 0);
        }
        return null;
    }

    private function logCheckoutError(string $message, array $context = []): void
    {
        $logDir = __DIR__ . '/../../storage/logs';
        if (!is_dir($logDir)) {
            return;
        }

        $payload = $context ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        $entry = sprintf("[%s] %s%s\n", date('c'), $message, $payload);

        file_put_contents($logDir . '/checkout.log', $entry, FILE_APPEND);
    }

    private function isOnlinePaymentEnabled(): bool
    {
        $settings = new Setting();
        $defaults = $settings->getPaymentDefaults();
        $raw = $settings->get(
            Setting::ONLINE_PAYMENT_ENABLED,
            $defaults[Setting::ONLINE_PAYMENT_ENABLED] ?? '1'
        );

        return filter_var($raw, FILTER_VALIDATE_BOOLEAN);
    }

}
