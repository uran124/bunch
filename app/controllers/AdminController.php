<?php
// app/controllers/AdminController.php

class AdminController extends Controller
{
    public function index(): void
    {
        $sections = [
            [
                'title' => 'Пользователи',
                'items' => [
                    ['label' => 'Пользователи', 'description' => 'Профили клиентов и статусы доступа'],
                    ['label' => 'Рассылки', 'description' => 'E-mail, push и SMS кампании'],
                    ['label' => 'Уведомления', 'description' => 'Триггеры и шаблоны сообщений'],
                ],
            ],
            [
                'title' => 'Каталог',
                'items' => [
                    ['label' => 'Товары', 'description' => 'Карточки, цены и наличие'],
                    ['label' => 'Варианты оформления', 'description' => 'Упаковка, ленты, открытки'],
                    ['label' => 'Мелкий опт', 'description' => 'Пакеты поштучной продажи'],
                    ['label' => 'Поставки', 'description' => 'Планирование и приёмка'],
                ],
            ],
            [
                'title' => 'Подписки',
                'items' => [
                    ['label' => 'Периоды', 'description' => 'Сроки, расписания и продления'],
                    ['label' => 'Настройка скидок', 'description' => 'Уровни лояльности и акции'],
                ],
            ],
            [
                'title' => 'Заказы',
                'items' => [
                    ['label' => 'Товары', 'description' => 'Разовые покупки и статусы'],
                    ['label' => 'Подписки', 'description' => 'Регулярные доставки и паузы'],
                    ['label' => 'Мелкий опт', 'description' => 'Групповые заказы и лимиты'],
                ],
            ],
            [
                'title' => 'Настройка сервисов',
                'items' => [
                    ['label' => 'Онлайн оплата', 'description' => 'Платёжные шлюзы и возвраты'],
                    ['label' => 'Веб-аналитика яндекс метрика', 'description' => 'События, цели и конверсии'],
                    ['label' => 'Подключение к ЦРМ', 'description' => 'Синхронизация контактов и сделок'],
                ],
            ],
            [
                'title' => 'Контент',
                'items' => [
                    ['label' => 'Статичный контент', 'description' => 'Блоки страниц и SEO-тексты'],
                    ['label' => 'Товары', 'description' => 'Фото, описания и атрибуты'],
                    ['label' => 'Разделы сайта', 'description' => 'Навигация и лендинги'],
                ],
            ],
        ];

        $pageMeta = [
            'title' => 'Админ-панель Bunch flowers — управление сервисом',
            'description' => 'Контроль пользователей, каталога, подписок, заказов и интеграций сервиса Bunch flowers.',
            'h1' => 'Администрирование Bunch flowers',
            'headerTitle' => 'Bunch Admin',
            'headerSubtitle' => 'Консоль управления сервисом',
            'footerLeft' => '© ' . date('Y') . ' Bunch flowers · админ-панель',
            'footerRight' => 'Рабочая среда · Asia/Krasnoyarsk',
        ];

        $this->render('admin', [
            'sections' => $sections,
            'pageMeta' => $pageMeta,
        ]);
    }
}
