<?php
// app/controllers/CartController.php

class CartController extends Controller
{
    public function index(): void
    {
        $cart = new Cart();
        $productModel = new Product();

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
            'deliveryZones' => $this->getDeliveryZones(),
            'deliveryPricingVersion' => '2024-06-01',
            'dadataConfig' => $this->getDadataSettings(),
            'testAddresses' => $this->getDeliveryTestAddresses(),
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
            http_response_code(401);
            echo json_encode(['error' => 'Требуется авторизация']);
            return;
        }

        $payload = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $cart = new Cart();
        $items = $cart->getItems();

        if (empty($items)) {
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
        $comment = trim((string) ($payload['comment'] ?? ''));

        $addressModel = new UserAddress();
        $userAddresses = $addressModel->getByUserId((int) $userId);

        $addressDetails = is_array($payload['address'] ?? null) ? $payload['address'] : [];

        if ($mode === 'delivery' && $addressId !== null) {
            $validAddressIds = array_map(static fn ($row) => (int) ($row['raw']['id'] ?? 0), $userAddresses);
            if ($addressId > 0 && !in_array($addressId, $validAddressIds, true)) {
                http_response_code(400);
                echo json_encode(['error' => 'Адрес не найден']);
                return;
            }

            if ($addressId > 0) {
                $addressModel->touchUsage((int) $userId, $addressId);
            }
        }

        if ($mode === 'delivery' && $addressId === null) {
            $hasAddressPayload = $addressText !== '' || array_filter($addressDetails);
            if (!$hasAddressPayload) {
                http_response_code(400);
                echo json_encode(['error' => 'Укажите адрес доставки']);
                return;
            }

            try {
                $addressPayload = array_merge($addressDetails, [
                    'address_text' => $addressText,
                    'recipient_name' => $recipient['name'] ?? null,
                    'recipient_phone' => $recipient['phone'] ?? null,
                    'zone_id' => $payload['zone_id'] ?? ($addressDetails['zone_id'] ?? null),
                    'zone_version' => $payload['zone_version'] ?? $payload['delivery_pricing_version'] ?? null,
                    'zone_calculated_at' => $payload['zone_calculated_at'] ?? null,
                    'last_delivery_price_hint' => $payload['delivery_price'] ?? null,
                    'location_source' => $addressDetails['location_source'] ?? ($payload['location_source'] ?? null),
                    'geo_quality' => $addressDetails['geo_quality'] ?? ($payload['geo_quality'] ?? null),
                    'lat' => $addressDetails['lat'] ?? null,
                    'lon' => $addressDetails['lon'] ?? null,
                ]);

                $addressId = $addressModel->createForUser((int) $userId, $addressPayload);
            } catch (Throwable $e) {
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
                'delivery_price' => $payload['delivery_price'] ?? null,
                'zone_id' => $payload['zone_id'] ?? null,
                'delivery_pricing_version' => $payload['delivery_pricing_version'] ?? null,
                'recipient_name' => $recipient['name'] ?? null,
                'recipient_phone' => $recipient['phone'] ?? null,
                'comment' => $comment,
            ]);

            $cart->clear();

            echo json_encode([
                'ok' => true,
                'order_id' => $orderId,
            ]);
        } catch (Throwable $e) {
            http_response_code(400);
            echo json_encode([
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function getDadataSettings(): array
    {
        return [
            'apiKey' => '6e4950476cc01a78b287788434dc1028eb3e86cf',
            'secretKey' => 'f2b84eb0e15b3c7b93c75ac50a8cd53b1a9defa1',
            'defaultDeliveryPrice' => 350,
        ];
    }

    private function getDeliveryZones(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'Центр',
                'price' => 290,
                'color' => '#f43f5e',
                'polygon' => [
                    [37.5995, 55.7620],
                    [37.6205, 55.7620],
                    [37.6210, 55.7470],
                    [37.6015, 55.7465],
                    [37.5995, 55.7620],
                ],
                'landmarks' => 'Тверская, Цветной бульвар, Патрики',
            ],
            [
                'id' => 2,
                'name' => 'Северо-восток',
                'price' => 390,
                'color' => '#06b6d4',
                'polygon' => [
                    [37.6220, 55.7660],
                    [37.6660, 55.7680],
                    [37.6690, 55.7500],
                    [37.6250, 55.7475],
                    [37.6220, 55.7660],
                ],
                'landmarks' => 'Бауманская, Семёновская, Сокольники',
            ],
            [
                'id' => 3,
                'name' => 'Юг',
                'price' => 490,
                'color' => '#a855f7',
                'polygon' => [
                    [37.6040, 55.7440],
                    [37.6570, 55.7440],
                    [37.6590, 55.7340],
                    [37.6050, 55.7340],
                    [37.6040, 55.7440],
                ],
                'landmarks' => 'Павелецкая, Шаболовка, Фрунзенская',
            ],
        ];
    }

    private function getDeliveryTestAddresses(): array
    {
        return [
            [
                'label' => 'Москва, ул. Тверская, 12',
                'match' => 'тверская 12',
                'coords' => [37.6047, 55.7586],
            ],
            [
                'label' => 'Москва, ул. Бауманская, 35',
                'match' => 'бауманская 35',
                'coords' => [37.6630, 55.7650],
            ],
            [
                'label' => 'Москва, ул. Шаболовка, 24',
                'match' => 'шаболовка 24',
                'coords' => [37.6115, 55.7325],
            ],
            [
                'label' => 'Москва, ул. Алексеева, 22',
                'match' => 'алексеева 22',
                'coords' => [37.6100, 55.7535],
            ],
        ];
    }
}
