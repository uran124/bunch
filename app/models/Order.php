<?php
// app/models/Order.php

class Order extends Model
{
    private const TELEGRAM_ADMIN_CHAT_ID = -1002055168794;
    private const TELEGRAM_ADMIN_THREAD_ORDERS = 1096;
    private array $statusPaymentMap = [
        'paid' => ['confirmed', 'assembled', 'delivering', 'delivered'],
        'pending' => ['new'],
        'refund' => ['cancelled'],
    ];

    public function getLatestActiveForUser(int $userId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM orders WHERE user_id = :user_id AND status IN ('new', 'confirmed', 'delivering') ORDER BY created_at DESC LIMIT 1"
        );
        $stmt->execute(['user_id' => $userId]);

        $order = $stmt->fetch();

        if (!$order) {
            return null;
        }

        return $this->buildOrderPayload($order);
    }

    public function findById(int $orderId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM orders WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $orderId]);

        $order = $stmt->fetch();

        return $order ?: null;
    }

    public function getActiveOrdersForUser(int $userId): array
    {
        return $this->getOrdersByStatuses($userId, ['new', 'confirmed', 'delivering']);
    }

    public function getCompletedOrdersForUser(int $userId, int $limit = 10, int $offset = 0): array
    {
        return $this->getOrdersByStatuses($userId, ['delivered', 'cancelled'], $limit, $offset);
    }

    public function getUserOrderDetail(int $orderId, int $userId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM orders WHERE id = :id AND user_id = :user_id LIMIT 1');
        $stmt->execute([
            'id' => $orderId,
            'user_id' => $userId,
        ]);

        $order = $stmt->fetch();

        if (!$order) {
            return null;
        }

        $items = $this->getItems($orderId);

        return $this->mapUserOrder($order, $items);
    }

    public function updateUserOrder(int $orderId, int $userId, array $data): bool
    {
        $allowedDeliveryTypes = ['pickup', 'delivery'];
        $deliveryType = in_array($data['delivery_type'] ?? 'pickup', $allowedDeliveryTypes, true)
            ? $data['delivery_type']
            : 'pickup';

        $scheduledDate = $this->normalizeDate($data['scheduled_date'] ?? null);
        $scheduledTime = $this->normalizeTime($data['scheduled_time'] ?? null);

        $addressText = $this->emptyToNull($data['address_text'] ?? null);
        if ($deliveryType !== 'delivery') {
            $addressText = null;
        }

        $stmt = $this->db->prepare(
            'UPDATE orders SET delivery_type = :delivery_type, scheduled_date = :scheduled_date, scheduled_time = :scheduled_time, address_text = :address_text, recipient_name = :recipient_name, recipient_phone = :recipient_phone, comment = :comment, updated_at = NOW() WHERE id = :id AND user_id = :user_id AND status = :status'
        );

        $stmt->execute([
            'id' => $orderId,
            'user_id' => $userId,
            'status' => 'new',
            'delivery_type' => $deliveryType,
            'scheduled_date' => $scheduledDate,
            'scheduled_time' => $scheduledTime,
            'address_text' => $addressText,
            'recipient_name' => $this->emptyToNull($data['recipient_name'] ?? null),
            'recipient_phone' => $this->emptyToNull($data['recipient_phone'] ?? null),
            'comment' => $this->emptyToNull($data['comment'] ?? null),
        ]);

        if ($stmt->rowCount() > 0) {
            return true;
        }

        $checkStmt = $this->db->prepare(
            'SELECT COUNT(*) FROM orders WHERE id = :id AND user_id = :user_id AND status = :status'
        );
        $checkStmt->execute([
            'id' => $orderId,
            'user_id' => $userId,
            'status' => 'new',
        ]);

        return (int) $checkStmt->fetchColumn() > 0;
    }

    public function markPaidForUser(int $orderId, int $userId): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE orders SET status = :status, updated_at = NOW() WHERE id = :id AND user_id = :user_id AND status = :current_status'
        );

        $stmt->execute([
            'id' => $orderId,
            'user_id' => $userId,
            'status' => 'confirmed',
            'current_status' => 'new',
        ]);

        if ($stmt->rowCount() <= 0) {
            return false;
        }

        $this->notifyAdminOrderPaid($orderId);
        $this->notifyUserOrderStatus($orderId, $userId, 'confirmed');

        return true;
    }

    public function markPaidById(int $orderId): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE orders SET status = :status, updated_at = NOW() WHERE id = :id AND status = :current_status'
        );

        $stmt->execute([
            'id' => $orderId,
            'status' => 'confirmed',
            'current_status' => 'new',
        ]);

        if ($stmt->rowCount() <= 0) {
            return false;
        }

        $order = $this->findById($orderId);
        $userId = $order['user_id'] ?? null;
        if ($userId) {
            $this->notifyUserOrderStatus($orderId, (int) $userId, 'confirmed');
        }

        $this->notifyAdminOrderPaid($orderId);

        return true;
    }

    public function countCompletedOrdersForUser(int $userId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM orders WHERE user_id = :user_id AND status IN ('delivered', 'cancelled')"
        );
        $stmt->execute(['user_id' => $userId]);

        return (int) $stmt->fetchColumn();
    }

    public function countOneTimeByStatus(string $status): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM orders WHERE status = :status AND delivery_type <> 'subscription'"
        );
        $stmt->execute(['status' => $status]);

        return (int) $stmt->fetchColumn();
    }

    public function getOnlinePaymentLink(int $orderId): ?string
    {
        $order = $this->findById($orderId);
        if (!$order) {
            return null;
        }

        $settings = new Setting();
        $paymentDefaults = $settings->getPaymentDefaults();
        $gateway = $settings->get(Setting::ONLINE_PAYMENT_GATEWAY, $paymentDefaults[Setting::ONLINE_PAYMENT_GATEWAY] ?? 'robokassa');

        if ($gateway !== 'robokassa') {
            return null;
        }

        return $this->buildRobokassaPaymentLink($order, $settings);
    }

    private function getOrdersByStatuses(int $userId, array $statuses, int $limit = 50, int $offset = 0): array
    {
        if (empty($statuses)) {
            return [];
        }

        $placeholders = [];
        foreach ($statuses as $index => $status) {
            $placeholders[] = ':status' . $index;
        }

        $sql = sprintf(
            'SELECT * FROM orders WHERE user_id = :user_id AND status IN (%s) ORDER BY created_at DESC LIMIT :limit OFFSET :offset',
            implode(', ', $placeholders)
        );

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        foreach ($statuses as $index => $status) {
            $stmt->bindValue(':status' . $index, $status, PDO::PARAM_STR);
        }

        $stmt->execute();

        $orders = $stmt->fetchAll();

        return array_map(function (array $order): array {
            return $this->buildOrderPayload($order);
        }, $orders);
    }

    public function getAdminOrders(?string $search = null, ?string $statusFilter = null, ?string $paymentFilter = null): array
    {
        $sql = "SELECT o.*, u.name AS user_name, u.phone AS user_phone, u.email AS user_email FROM orders o LEFT JOIN users u ON u.id = o.user_id WHERE o.delivery_type <> 'subscription'";
        $params = [];

        if ($statusFilter && $statusFilter !== 'all') {
            $sql .= ' AND o.status = :status';
            $params['status'] = $statusFilter;
        }

        if ($paymentFilter && $paymentFilter !== 'all') {
            $statuses = $this->mapPaymentFilterToStatuses($paymentFilter);

            if (!empty($statuses)) {
                $placeholders = [];
                foreach ($statuses as $index => $status) {
                    $key = ':payment_status_' . $index;
                    $placeholders[] = $key;
                    $params[$key] = $status;
                }

                $sql .= ' AND o.status IN (' . implode(', ', $placeholders) . ')';
            }
        }

        if ($search) {
            $sql .= ' AND (o.id LIKE :search OR o.recipient_name LIKE :search OR u.name LIKE :search OR u.phone LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        $sql .= ' ORDER BY o.created_at DESC LIMIT 100';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $orders = $stmt->fetchAll();

        return array_map(function (array $order): array {
            return $this->mapAdminOrder($order);
        }, $orders);
    }

    public function getAdminOrderDetail(int $orderId): ?array
    {
        $stmt = $this->db->prepare('SELECT o.*, u.name AS user_name, u.phone AS user_phone, u.email AS user_email FROM orders o LEFT JOIN users u ON u.id = o.user_id WHERE o.id = :id LIMIT 1');
        $stmt->execute(['id' => $orderId]);

        $order = $stmt->fetch();

        if (!$order || $order['delivery_type'] === 'subscription') {
            return null;
        }

        $items = $this->getItems($orderId);

        return $this->mapAdminOrder($order, $items);
    }

    public function updateAdminOrder(int $orderId, array $data): void
    {
        $allowedStatuses = ['new', 'confirmed', 'assembled', 'delivering', 'delivered', 'cancelled'];
        $allowedDeliveryTypes = ['pickup', 'delivery'];

        $status = in_array($data['status'] ?? 'new', $allowedStatuses, true) ? $data['status'] : 'new';
        $deliveryType = in_array($data['delivery_type'] ?? 'pickup', $allowedDeliveryTypes, true)
            ? $data['delivery_type']
            : 'pickup';

        $scheduledDate = $this->normalizeDate($data['scheduled_date'] ?? null);
        $scheduledTime = $this->normalizeTime($data['scheduled_time'] ?? null);

        $stmt = $this->db->prepare(
            'UPDATE orders SET status = :status, delivery_type = :delivery_type, scheduled_date = :scheduled_date, scheduled_time = :scheduled_time, address_text = :address_text, recipient_name = :recipient_name, recipient_phone = :recipient_phone, comment = :comment, updated_at = NOW() WHERE id = :id'
        );

        $stmt->execute([
            'id' => $orderId,
            'status' => $status,
            'delivery_type' => $deliveryType,
            'scheduled_date' => $scheduledDate,
            'scheduled_time' => $scheduledTime,
            'address_text' => $this->emptyToNull($data['address_text'] ?? null),
            'recipient_name' => $this->emptyToNull($data['recipient_name'] ?? null),
            'recipient_phone' => $this->emptyToNull($data['recipient_phone'] ?? null),
            'comment' => $this->emptyToNull($data['comment'] ?? null),
        ]);
    }

    public function deleteAdminOrder(int $orderId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM orders WHERE id = :id');
        $stmt->execute(['id' => $orderId]);

        return $stmt->rowCount() > 0;
    }

    private function getItems(int $orderId): array
    {
        $stmt = $this->db->prepare(
            'SELECT oi.*, p.photo_url, p.product_type, s.stems_per_pack FROM order_items oi LEFT JOIN products p ON p.id = oi.product_id LEFT JOIN supplies s ON s.id = p.supply_id WHERE oi.order_id = :order_id ORDER BY oi.id ASC'
        );
        $stmt->execute(['order_id' => $orderId]);

        return $stmt->fetchAll();
    }

    public function getItemsForOrder(int $orderId): array
    {
        return $this->getItems($orderId);
    }

    public function createFromCart(int $userId, array $cartItems, array $payload): int
    {
        if (empty($cartItems)) {
            throw new RuntimeException('Корзина пуста');
        }

        $deliveryType = ($payload['mode'] ?? '') === 'delivery' ? 'delivery' : 'pickup';
        $scheduledDate = $this->normalizeDate($payload['date'] ?? null);
        $scheduledTime = $this->normalizeTime($payload['time'] ?? null);
        $addressId = isset($payload['address_id']) ? (int) $payload['address_id'] : null;
        $addressText = trim((string) ($payload['address_text'] ?? ''));
        $deliveryPrice = isset($payload['delivery_price']) ? (int) floor((float) $payload['delivery_price']) : null;
        $zoneId = isset($payload['zone_id']) ? (int) $payload['zone_id'] : null;
        $deliveryPricingVersion = $this->emptyToNull($payload['delivery_pricing_version'] ?? null);
        $recipientName = trim((string) ($payload['recipient_name'] ?? ''));
        $recipientPhone = trim((string) ($payload['recipient_phone'] ?? ''));
        $comment = trim((string) ($payload['comment'] ?? ''));

        $totalAmount = 0;
        foreach ($cartItems as $item) {
            $totalAmount += (int) floor((float) ($item['line_total'] ?? 0));
        }

        if ($deliveryType === 'delivery' && $deliveryPrice !== null) {
            $totalAmount += $deliveryPrice;
        }


        $this->db->beginTransaction();

        try {
            $orderStmt = $this->db->prepare(
                'INSERT INTO orders (user_id, address_id, total_amount, status, delivery_type, delivery_price, zone_id, delivery_pricing_version, scheduled_date, scheduled_time, address_text, recipient_name, recipient_phone, comment) VALUES (:user_id, :address_id, :total_amount, :status, :delivery_type, :delivery_price, :zone_id, :delivery_pricing_version, :scheduled_date, :scheduled_time, :address_text, :recipient_name, :recipient_phone, :comment)'            );

            $orderStmt->execute([
                'user_id' => $userId,
                'address_id' => $deliveryType === 'delivery' ? $addressId : null,
                'total_amount' => $totalAmount,
                'status' => 'new',
                'delivery_type' => $deliveryType,
                'delivery_price' => $deliveryType === 'delivery' ? $deliveryPrice : null,
                'zone_id' => $deliveryType === 'delivery' ? $zoneId : null,
                'delivery_pricing_version' => $deliveryType === 'delivery' ? $deliveryPricingVersion : null,
                'scheduled_date' => $scheduledDate,
                'scheduled_time' => $scheduledTime,
                'address_text' => $deliveryType === 'delivery' ? ($addressText ?: null) : null,
                'recipient_name' => $deliveryType === 'delivery' ? ($recipientName ?: null) : null,
                'recipient_phone' => $deliveryType === 'delivery' ? ($recipientPhone ?: null) : null,
                'comment' => $comment ?: null,
            ]);

            $orderId = (int) $this->db->lastInsertId();

            $itemStmt = $this->db->prepare(
                'INSERT INTO order_items (order_id, product_id, product_name, qty, price) VALUES (:order_id, :product_id, :product_name, :qty, :price)'
            );
            $attrStmt = $this->db->prepare(
                'INSERT INTO order_item_attributes (order_item_id, attribute_id, attribute_value_id, applies_to, price_delta) VALUES (:order_item_id, :attribute_id, :attribute_value_id, :applies_to, :price_delta)'
            );

            foreach ($cartItems as $item) {
                $itemStmt->execute([
                    'order_id' => $orderId,
                    'product_id' => (int) $item['product_id'],
                    'product_name' => $item['name'] ?? 'Товар',
                    'qty' => (int) $item['qty'],
                    'price' => (int) floor((float) ($item['price_per_stem'] ?? 0)),
                ]);

                $orderItemId = (int) $this->db->lastInsertId();

                foreach ($item['attributes'] as $attr) {
                    $attrStmt->execute([
                        'order_item_id' => $orderItemId,
                        'attribute_id' => (int) ($attr['attribute_id'] ?? 0),
                        'attribute_value_id' => (int) ($attr['value_id'] ?? 0),
                        'applies_to' => $attr['applies_to'] ?? 'stem',
                        'price_delta' => (int) floor((float) ($attr['price_delta'] ?? 0)),
                    ]);
                }
            }

            $this->db->commit();

            $this->notifyAdminNewOrder($orderId, $cartItems, [
                'delivery_type' => $deliveryType,
                'scheduled_date' => $scheduledDate,
                'scheduled_time' => $scheduledTime,
                'address_text' => $addressText,
                'address' => $payload['address'] ?? null,
                'recipient_name' => $recipientName,
                'recipient_phone' => $recipientPhone,
                'total_amount' => $totalAmount,
                'status' => 'new',
            ]);
            $this->logFrontpadNewOrder($orderId, $cartItems, [
                'delivery_type' => $deliveryType,
                'scheduled_date' => $scheduledDate,
                'scheduled_time' => $scheduledTime,
                'total_amount' => $totalAmount,
            ]);

            return $orderId;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function normalizeDate(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $dt = DateTime::createFromFormat('Y-m-d', $value);
        return $dt instanceof DateTime ? $dt->format('Y-m-d') : null;
    }

    private function normalizeTime(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $dt = DateTime::createFromFormat('H:i', $value);
        return $dt instanceof DateTime ? $dt->format('H:i:s') : null;
    }

    private function buildRobokassaPaymentLink(array $order, Setting $settings): ?string
    {
        $robokassaDefaults = $settings->getRobokassaDefaults();
        $merchantLogin = trim((string) $settings->get(
            Setting::ROBOKASSA_MERCHANT_LOGIN,
            $robokassaDefaults[Setting::ROBOKASSA_MERCHANT_LOGIN] ?? ''
        ));
        $password1 = trim((string) $settings->get(
            Setting::ROBOKASSA_PASSWORD1,
            $robokassaDefaults[Setting::ROBOKASSA_PASSWORD1] ?? ''
        ));

        if ($merchantLogin === '' || $password1 === '') {
            return null;
        }

        $orderId = (int) ($order['id'] ?? 0);
        if ($orderId <= 0) {
            return null;
        }

        $amount = number_format((float) ($order['total_amount'] ?? 0), 2, '.', '');
        $description = 'Оплата заказа №' . str_pad((string) $orderId, 4, '0', STR_PAD_LEFT);
        $signatureAlgorithm = strtolower((string) $settings->get(
            Setting::ROBOKASSA_SIGNATURE_ALGORITHM,
            $robokassaDefaults[Setting::ROBOKASSA_SIGNATURE_ALGORITHM] ?? 'md5'
        ));
        $signatureAlgorithm = $signatureAlgorithm === 'sha256' ? 'sha256' : 'md5';
        $signaturePayload = $merchantLogin . ':' . $amount . ':' . $orderId . ':' . $password1;
        $extraParams = [];
        $customerEmail = trim((string) ($order['user_email'] ?? ''));
        if ($customerEmail !== '' && filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
            $extraParams['Shp_email'] = $customerEmail;
        }
        if ($extraParams !== []) {
            ksort($extraParams, SORT_STRING);
            $signaturePayload .= ':' . implode(':', array_map(
                static function (string $key, string $value): string {
                    return $key . '=' . $value;
                },
                array_keys($extraParams),
                array_values($extraParams)
            ));
        }
        $signature = strtoupper(hash($signatureAlgorithm, $signaturePayload));

        $query = [
            'MerchantLogin' => $merchantLogin,
            'OutSum' => $amount,
            'InvId' => $orderId,
            'Description' => $description,
            'SignatureValue' => $signature,
            'Culture' => 'ru',
            'Encoding' => 'utf-8',
        ];
        if ($extraParams !== []) {
            $query = array_merge($query, $extraParams);
        }

        $isTest = filter_var(
            $settings->get(Setting::ROBOKASSA_TEST_MODE, $robokassaDefaults[Setting::ROBOKASSA_TEST_MODE] ?? '0'),
            FILTER_VALIDATE_BOOLEAN
        );
        if ($isTest) {
            $query['IsTest'] = 1;
        }

        $successUrl = trim((string) $settings->get(
            Setting::ROBOKASSA_SUCCESS_URL,
            $robokassaDefaults[Setting::ROBOKASSA_SUCCESS_URL] ?? ''
        ));
        if ($successUrl !== '') {
            $query['SuccessURL'] = $successUrl;
        }

        $failUrl = trim((string) $settings->get(
            Setting::ROBOKASSA_FAIL_URL,
            $robokassaDefaults[Setting::ROBOKASSA_FAIL_URL] ?? ''
        ));
        if ($failUrl !== '') {
            $query['FailURL'] = $failUrl;
        }

        return 'https://auth.robokassa.ru/Merchant/Index.aspx?' . http_build_query($query);
    }

    private function mapAdminOrder(array $order, array $items = []): array
    {
        $scheduled = $this->formatDeliveryWindow($order['scheduled_date'] ?? null, $order['scheduled_time'] ?? null);
        $items = $items ?: $this->getItems((int) $order['id']);

        $itemsPayload = array_map(function (array $item): array {
            $qty = (int) $item['qty'];
            $price = (float) $item['price'];

            $title = $item['product_name'] ?? ($item['name'] ?? 'Товар');
            if (($item['product_type'] ?? null) === 'small_wholesale' && !empty($item['stems_per_pack'])) {
                $title .= ' (' . (int) $item['stems_per_pack'] . ' шт.)';
            }

            return [
                'title' => $title,
                'qty' => $qty,
                'unit' => $this->formatPrice($price),
                'total' => $this->formatPrice($price * $qty),
                'image' => $item['photo_url'] ?? '/assets/images/products/bouquet.svg',
            ];
        }, $items);

        return [
            'id' => (int) $order['id'],
            'number' => 'B-' . str_pad((string) $order['id'], 4, '0', STR_PAD_LEFT),
            'customer' => $order['user_name'] ?? $order['recipient_name'] ?? 'Без имени',
            'customerPhone' => $order['user_phone'] ?? $order['recipient_phone'] ?? '',
            'sum' => $this->formatPrice((float) $order['total_amount']),
            'status' => $order['status'],
            'statusLabel' => $this->mapOrderStatus($order['status']),
            'payment' => $this->mapPaymentStatus($order['status']),
            'delivery' => $scheduled,
            'deliveryType' => $this->mapDeliveryType($order['delivery_type'] ?? 'pickup'),
            'deliveryTypeValue' => $order['delivery_type'] ?? 'pickup',
            'scheduled_date' => $order['scheduled_date'] ?? null,
            'scheduled_time' => $order['scheduled_time'] ?? null,
            'scheduled_date_raw' => $order['scheduled_date'] ?? null,
            'scheduled_time_raw' => $order['scheduled_time'] ?? null,
            'address' => $order['address_text'] ?? null,
            'addressTextRaw' => $order['address_text'] ?? null,
            'comment' => $order['comment'] ?? null,
            'commentRaw' => $order['comment'] ?? null,
            'recipient_name' => $order['recipient_name'] ?? null,
            'recipient_phone' => $order['recipient_phone'] ?? null,
            'recipientNameRaw' => $order['recipient_name'] ?? null,
            'recipientPhoneRaw' => $order['recipient_phone'] ?? null,
            'updated_at' => $order['updated_at'] ?? $order['created_at'] ?? null,
            'deliveryPrice' => isset($order['delivery_price']) ? $this->formatPrice((float) $order['delivery_price']) : null,
            'items' => $itemsPayload,
        ];
    }

    private function mapUserOrder(array $order, array $items = []): array
    {
        $scheduled = $this->formatDeliveryWindow($order['scheduled_date'] ?? null, $order['scheduled_time'] ?? null);
        $items = $items ?: $this->getItems((int) $order['id']);

        $itemsPayload = array_map(function (array $item): array {
            $qty = (int) $item['qty'];
            $price = (float) $item['price'];

            return [
                'title' => $item['product_name'] ?? ($item['name'] ?? 'Товар'),
                'qty' => $qty,
                'unit' => $this->formatPrice($price),
                'total' => $this->formatPrice($price * $qty),
                'image' => $item['photo_url'] ?? '/assets/images/products/bouquet.svg',
            ];
        }, $items);

        $scheduledTimeRaw = $order['scheduled_time'] ?? null;
        if ($scheduledTimeRaw) {
            $scheduledTimeRaw = substr((string) $scheduledTimeRaw, 0, 5);
        }

        $address = null;
        if (!empty($order['address_id'])) {
            $addressStmt = $this->db->prepare('SELECT * FROM user_addresses WHERE id = :id LIMIT 1');
            $addressStmt->execute(['id' => $order['address_id']]);
            $addressRow = $addressStmt->fetch();
            $address = $addressRow ? UserAddress::formatAddress($addressRow) : null;
        } elseif (!empty($order['address_text'])) {
            $address = $order['address_text'];
        }

        return [
            'id' => (int) $order['id'],
            'number' => '№' . str_pad((string) $order['id'], 4, '0', STR_PAD_LEFT),
            'status' => $order['status'],
            'statusLabel' => $this->mapOrderStatus($order['status']),
            'createdAt' => $order['created_at'] ?? null,
            'total' => $this->formatPrice((float) $order['total_amount']),
            'deliveryType' => $this->mapDeliveryType($order['delivery_type'] ?? 'pickup'),
            'deliveryTypeValue' => $order['delivery_type'] ?? 'pickup',
            'scheduled' => $scheduled,
            'scheduled_date_raw' => $order['scheduled_date'] ?? null,
            'scheduled_time_raw' => $scheduledTimeRaw,
            'address' => $address,
            'addressTextRaw' => $order['address_text'] ?? null,
            'recipientNameRaw' => $order['recipient_name'] ?? null,
            'recipientPhoneRaw' => $order['recipient_phone'] ?? null,
            'commentRaw' => $order['comment'] ?? null,
            'items' => $itemsPayload,
        ];
    }

    private function formatPrice(float $amount): string
    {
        $rounded = (int) floor($amount);
        return number_format($rounded, 0, ',', ' ') . ' ₽';
    }

    private function formatDeliveryWindow(?string $date, ?string $time): string
    {
        $parts = [];

        if ($date) {
            try {
                $parts[] = (new DateTimeImmutable($date))->format('d.m');
            } catch (Throwable $e) {
                $parts[] = $date;
            }
        }

        if ($time) {
            $parts[] = substr($time, 0, 5);
        }

        return $parts ? implode(', ', $parts) : '—';
    }

    private function mapOrderStatus(string $status): string
    {
        return match ($status) {
            'new' => 'Новый',
            'confirmed' => 'Принят',
            'assembled' => 'Собран',
            'delivering' => 'В доставке',
            'delivered' => 'Доставлен',
            'cancelled' => 'Отменён',
            default => 'В обработке',
        };
    }

    private function mapDeliveryType(string $type): string
    {
        return match ($type) {
            'delivery' => 'Доставка',
            'subscription' => 'Подписка',
            default => 'Самовывоз',
        };
    }

    private function mapPaymentStatus(string $status): string
    {
        return match ($status) {
            'cancelled' => 'Возврат',
            'new' => 'Ожидает',
            default => 'Оплачен',
        };
    }

    private function mapPaymentFilterToStatuses(string $filter): array
    {
        return $this->statusPaymentMap[$filter] ?? [];
    }

    public function notifyUserOrderStatus(int $orderId, int $userId, string $status): void
    {
        $message = $this->buildUserStatusMessage($orderId, $status);
        if ($message === null) {
            return;
        }

        $chatId = $this->getUserTelegramChatId($userId);
        if (!$chatId) {
            return;
        }

        $this->sendTelegramMessage($chatId, $message);
    }

    private function emptyToNull(?string $value): ?string
    {
        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function notifyAdminNewOrder(int $orderId, array $cartItems, array $payload): void
    {
        $status = $payload['status'] ?? 'new';
        $scheduled = $this->formatSchedule($payload['scheduled_date'] ?? null, $payload['scheduled_time'] ?? null);
        $number = $this->formatOrderNumber($orderId);

        $lines = [];
        $lines[] = sprintf('Заказ %s %s (дата и время получения товара):', $number, $scheduled);

        foreach ($cartItems as $item) {
            $title = $item['name'] ?? 'Товар';
            $qty = (int) ($item['qty'] ?? 0);
            $lineTotal = (float) ($item['line_total'] ?? 0);
            $lines[] = sprintf('%s, %d шт., %s', $title, $qty, $this->formatPrice($lineTotal));
        }

        $total = (float) ($payload['total_amount'] ?? 0);
        $lines[] = 'Итого: ' . $this->formatPrice($total);

        if (($payload['delivery_type'] ?? 'pickup') === 'delivery') {
            $address = $this->buildAddressLine($payload['address_text'] ?? null, $payload['address'] ?? null);
            $recipientName = $payload['recipient_name'] ?? null;
            $recipientPhone = $payload['recipient_phone'] ?? null;
            $lines[] = sprintf('[%s, %s, %s]', $address, $recipientName ?: 'Имя не указано', $recipientPhone ?: 'Телефон не указан');
        } else {
            $lines[] = 'Самовывоз';
        }

        $lines[] = 'Статус: ' . $this->mapOrderStatus($status);

        $this->sendTelegramMessage(self::TELEGRAM_ADMIN_CHAT_ID, implode("\n", $lines), [
            'message_thread_id' => self::TELEGRAM_ADMIN_THREAD_ORDERS,
        ]);
    }

    private function logFrontpadNewOrder(int $orderId, array $cartItems, array $payload): void
    {
        $settings = new Setting();
        $defaults = $settings->getFrontpadDefaults();
        $secret = trim((string) $settings->get(
            Setting::FRONTPAD_SECRET,
            $defaults[Setting::FRONTPAD_SECRET] ?? ''
        ));
        $apiUrl = trim((string) $settings->get(
            Setting::FRONTPAD_API_URL,
            $defaults[Setting::FRONTPAD_API_URL] ?? ''
        ));

        $items = array_map(static function (array $item): array {
            return [
                'product_id' => (int) ($item['product_id'] ?? 0),
                'name' => $item['name'] ?? 'Товар',
                'qty' => (int) ($item['qty'] ?? 0),
                'line_total' => (float) ($item['line_total'] ?? 0),
            ];
        }, $cartItems);

        $logger = new Logger('frontpad.log');
        $logger->logEvent(
            $secret === '' || $apiUrl === '' ? 'frontpad.skip_missing_settings' : 'frontpad.order_created',
            [
                'order_id' => $orderId,
                'delivery_type' => $payload['delivery_type'] ?? null,
                'scheduled_date' => $payload['scheduled_date'] ?? null,
                'scheduled_time' => $payload['scheduled_time'] ?? null,
                'total_amount' => (float) ($payload['total_amount'] ?? 0),
                'item_count' => count($items),
                'items' => $items,
                'api_url' => $apiUrl !== '' ? $apiUrl : null,
                'configured' => $secret !== '' && $apiUrl !== '',
            ]
        );
    }

    private function notifyAdminOrderPaid(int $orderId): void
    {
        $message = sprintf('Заказ %s оплачен!', $this->formatOrderNumber($orderId));

        $this->sendTelegramMessage(self::TELEGRAM_ADMIN_CHAT_ID, $message, [
            'message_thread_id' => self::TELEGRAM_ADMIN_THREAD_ORDERS,
        ]);
    }

    private function buildUserStatusMessage(int $orderId, string $status): ?string
    {
        $number = $this->formatOrderNumber($orderId);

        return match ($status) {
            'confirmed' => "Ваш заказ {$number} принят!",
            'assembled' => "Ваш заказ {$number} собран.",
            'delivering' => "Ваш заказ {$number} передан на доставку.",
            'delivered' => "Ваш заказ {$number} вручен.",
            'cancelled' => "Ваш заказ {$number} отменен.",
            default => null,
        };
    }

    private function formatOrderNumber(int $orderId): string
    {
        return '№' . str_pad((string) $orderId, 4, '0', STR_PAD_LEFT);
    }

    private function formatSchedule(?string $date, ?string $time): string
    {
        $parts = [];

        if ($date) {
            try {
                $parts[] = (new DateTimeImmutable($date))->format('d.m');
            } catch (Throwable $e) {
                $parts[] = $date;
            }
        }

        if ($time) {
            $parts[] = substr($time, 0, 5);
        }

        return $parts ? implode(' ', $parts) : '—';
    }

    private function buildAddressLine(?string $addressText, ?array $addressDetails): string
    {
        $addressText = trim((string) $addressText);
        if ($addressText !== '') {
            return $addressText;
        }

        if (!$addressDetails) {
            return 'Адрес не указан';
        }

        $parts = [];
        foreach (['settlement', 'street', 'house', 'block', 'apartment'] as $key) {
            $value = trim((string) ($addressDetails[$key] ?? ''));
            if ($value !== '') {
                $parts[] = $value;
            }
        }

        return $parts ? implode(', ', $parts) : 'Адрес не указан';
    }

    private function getUserTelegramChatId(int $userId): ?int
    {
        $stmt = $this->db->prepare('SELECT telegram_chat_id FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $userId]);
        $chatId = $stmt->fetchColumn();

        return $chatId ? (int) $chatId : null;
    }

    private function sendTelegramMessage(int $chatId, string $text, array $options = []): void
    {
        $settings = new Setting();
        $defaults = $settings->getTelegramDefaults();
        $token = $settings->get(Setting::TG_BOT_TOKEN, $defaults[Setting::TG_BOT_TOKEN] ?? '');

        if ($token === '') {
            return;
        }

        $telegram = new Telegram($token);
        $telegram->sendMessage($chatId, $text, $options);
    }

    private function buildOrderPayload(array $order): array
    {
        $items = $this->getItems((int) $order['id']);

        $address = null;
        if (!empty($order['address_id'])) {
            $addressStmt = $this->db->prepare('SELECT * FROM user_addresses WHERE id = :id LIMIT 1');
            $addressStmt->execute(['id' => $order['address_id']]);
            $addressRow = $addressStmt->fetch();
            $address = $addressRow ? UserAddress::formatAddress($addressRow) : null;
        } elseif (!empty($order['address_text'])) {
            $address = $order['address_text'];
        }

        return [
            'id' => (int) $order['id'],
            'status' => $order['status'],
            'created_at' => $order['created_at'],
            'total_amount' => (float) $order['total_amount'],
            'delivery_type' => $order['delivery_type'] ?? ($order['address_id'] ? 'delivery' : 'pickup'),
            'scheduled_date' => $order['scheduled_date'] ?? null,
            'scheduled_time' => $order['scheduled_time'] ?? null,
            'address' => $address,
            'address_text' => $order['address_text'] ?? null,
            'recipient_name' => $order['recipient_name'] ?? null,
            'recipient_phone' => $order['recipient_phone'] ?? null,
            'comment' => $order['comment'] ?? null,
            'items' => $items,
        ];
    }
}
