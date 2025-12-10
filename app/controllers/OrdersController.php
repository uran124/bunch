<?php
// app/controllers/OrdersController.php

class OrdersController extends Controller
{
    private Order $orderModel;
    private Subscription $subscriptionModel;

    public function __construct()
    {
        $this->orderModel = new Order();
        $this->subscriptionModel = new Subscription();
    }

    public function index()
    {
        $userId = Auth::userId();

        $historyLimit = 10;
        $activeSubscriptions = $this->subscriptionModel->getActiveListForUser($userId);
        $activeOrders = $this->orderModel->getActiveOrdersForUser($userId);
        $completedOrders = $this->orderModel->getCompletedOrdersForUser($userId, $historyLimit, 0);
        $historyTotal = $this->orderModel->countCompletedOrdersForUser($userId);

        $pageMeta = [
            'title' => 'Мои заказы — Bunch flowers',
            'description' => 'Список активных и выполненных заказов, подписки и статусы доставки.',
            'headerTitle' => 'Bunch flowers',
            'headerSubtitle' => 'История заказов',
        ];

        $this->render('orders', [
            'pageMeta' => $pageMeta,
            'activeSubscriptions' => array_map([$this, 'mapSubscription'], $activeSubscriptions),
            'activeOrders' => array_map([$this, 'mapOrder'], $activeOrders),
            'completedOrders' => array_map([$this, 'mapOrder'], $completedOrders),
            'historyLimit' => $historyLimit,
            'historyHasMore' => $historyTotal > count($completedOrders),
        ]);
    }

    public function history(): void
    {
        header('Content-Type: application/json');

        $userId = Auth::userId();
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['error' => 'Требуется авторизация']);
            return;
        }

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $limit = max(1, (int) ($_GET['limit'] ?? 10));
        $offset = ($page - 1) * $limit;

        $orders = $this->orderModel->getCompletedOrdersForUser($userId, $limit, $offset);
        $hasMore = count($orders) === $limit;

        echo json_encode([
            'ok' => true,
            'orders' => array_map([$this, 'mapOrder'], $orders),
            'hasMore' => $hasMore,
        ]);
    }

    private function mapOrder(array $order): array
    {
        $items = $order['items'] ?? [];
        $firstItem = $items[0] ?? null;

        $scheduledParts = [];
        if (!empty($order['scheduled_date'])) {
            $scheduledParts[] = $this->formatDate($order['scheduled_date']);
        }
        if (!empty($order['scheduled_time'])) {
            $scheduledParts[] = $order['scheduled_time'];
        }

        return [
            'id' => (int) $order['id'],
            'number' => '№' . str_pad((string) $order['id'], 4, '0', STR_PAD_LEFT),
            'status' => $order['status'],
            'statusLabel' => $this->mapOrderStatus($order['status']),
            'createdAt' => $this->formatDateTime($order['created_at']),
            'total' => $this->formatPrice((float) $order['total_amount']),
            'deliveryType' => $this->mapDeliveryType($order['delivery_type'] ?? 'pickup'),
            'scheduled' => implode(' · ', $scheduledParts),
            'address' => $order['address'] ?? null,
            'item' => $firstItem ? [
                'title' => $firstItem['product_name'] ?? ($firstItem['name'] ?? 'Товар'),
                'qty' => (int) $firstItem['qty'],
                'price' => $this->formatPrice((float) $firstItem['price'] * (int) $firstItem['qty']),
                'unit' => $this->formatPrice((float) $firstItem['price']),
                'image' => $firstItem['photo_url'] ?? '/assets/images/products/bouquet.svg',
            ] : null,
        ];
    }

    private function mapSubscription(array $subscription): array
    {
        return [
            'id' => (int) $subscription['id'],
            'title' => $subscription['product_name'],
            'plan' => $this->formatPlan($subscription['plan']),
            'nextDelivery' => $this->formatDate($subscription['next_delivery_date'] ?? null),
            'qty' => (int) $subscription['qty'],
            'total' => $this->formatPrice((float) $subscription['product_price'] * (int) $subscription['qty']),
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

    private function formatDate(?string $date): string
    {
        if (!$date) {
            return '';
        }

        try {
            $dt = new DateTime($date);
            return $dt->format('d.m.Y');
        } catch (Exception $e) {
            return '';
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
            'confirmed' => 'Принят',
            'assembled' => 'Собран',
            'delivering' => 'В доставке',
            'delivered' => 'Выполнен',
            'cancelled' => 'Отменен',
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
}
