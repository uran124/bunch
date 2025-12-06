<?php
// app/controllers/AdminController.php

class AdminController extends Controller
{
    public function index(): void
    {
        $sections = [
            [
                'title' => 'Заказы и платежи',
                'items' => [
                    ['label' => 'Заказы', 'description' => 'Список, статусы, печать чеков'],
                    ['label' => 'Платежи', 'description' => 'Онлайн-оплата, возвраты, фрод'],
                    ['label' => 'Доставка', 'description' => 'Маршруты, партнёры, SLA'],
                ],
            ],
            [
                'title' => 'Каталог и акции',
                'items' => [
                    ['label' => 'Товары', 'description' => 'Цены, наличие, сортировка'],
                    ['label' => 'Категории', 'description' => 'Группы, теги, фильтры'],
                    ['label' => 'Акции и аукционы', 'description' => 'Распродажи, промокоды, спецпредложения'],
                ],
            ],
            [
                'title' => 'Подписки и клиенты',
                'items' => [
                    ['label' => 'Подписки', 'description' => 'Графики, паузы, продление'],
                    ['label' => 'Пользователи', 'description' => 'Профили, PIN, роли'],
                    ['label' => 'Уведомления', 'description' => 'Telegram, SMS, e-mail триггеры'],
                ],
            ],
            [
                'title' => 'Контент и настройки',
                'items' => [
                    ['label' => 'Лендинг и статические страницы', 'description' => 'Баннеры, FAQ, SEO'],
                    ['label' => 'Служебные справочники', 'description' => 'Праздники, поводы, сценарии доставки'],
                    ['label' => 'Общие настройки', 'description' => 'Тарифы, таймзона, контакты, интеграции'],
                ],
            ],
        ];

        $this->render('admin', [
            'sections' => $sections,
        ]);
    }
}
