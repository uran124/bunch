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
            $addresses = $addressModel->getByUserId((int) Auth::user()['id']);
        }

        $this->render('cart', [
            'items' => $items,
            'totals' => $totals,
            'accessories' => $accessoryProducts,
            'productAttributes' => $productAttributes,
            'addresses' => $addresses,
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
}
