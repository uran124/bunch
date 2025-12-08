<?php
// app/controllers/CartController.php

class CartController extends Controller
{
    public function index(): void
    {
        $mockItems = [
            [
                'name' => 'роза Rhodos 50см',
                'qty' => 25,
                'price' => 190,
                'image' => 'https://cdn.bunch.test/rhodos.jpg',
                'attributes' => [
                    ['label' => 'Ростовка', 'value' => '50 см', 'scope' => 'к стеблю'],
                    ['label' => 'Оформление', 'value' => 'Крафт + лента', 'scope' => 'к букету'],
                ],
            ],
            [
                'name' => 'эвкалипт Cinerea 40см',
                'qty' => 10,
                'price' => 120,
                'image' => 'https://cdn.bunch.test/eucalyptus.jpg',
                'attributes' => [
                    ['label' => 'Ростовка', 'value' => '40 см', 'scope' => 'к стеблю'],
                ],
            ],
        ];

        $this->render('cart', [
            'items' => $mockItems,
            'pageMeta' => [
                'title' => 'Корзина — Bunch flowers',
                'description' => 'Проверьте позиции перед оформлением заказа.',
                'headerTitle' => 'Bunch flowers',
                'headerSubtitle' => 'Корзина',
            ],
        ]);
    }
}
