<?php
// app/controllers/AccountController.php

class AccountController extends Controller
{
    public function index()
    {
        $user = [
            'name' => 'Алексей',
            'phone' => '+7 (999) 123-45-67',
            'email' => null,
        ];

        $addresses = [
            [
                'label' => 'Дом',
                'address' => 'г. Красноярск, ул. Ленина, д. 10, кв. 45',
                'is_primary' => true,
            ],
            [
                'label' => 'Работа',
                'address' => 'г. Красноярск, пр-т Мира, д. 30, офис 12',
                'is_primary' => false,
            ],
            [
                'label' => 'Мама',
                'address' => 'г. Красноярск, ул. Карла Маркса, д. 5',
                'is_primary' => false,
            ],
        ];

        $activeOrder = [
            'number' => '№2486',
            'datetime' => '8 декабря, 18:30',
            'delivery_type' => 'delivery',
            'delivery_address' => 'г. Красноярск, ул. Ленина, д. 10, кв. 45',
            'item' => [
                'name' => 'Букет «Милана»',
                'qty' => 1,
                'price' => '4 890 ₽',
                'image' => '/assets/images/products/bouquet.svg',
            ],
        ];

        $activeSubscription = [
            'frequency' => 'Раз в неделю',
            'item' => 'Букет недели',
            'qty' => 1,
            'discount' => '10%',
            'total' => '3 990 ₽',
        ];

        $notificationSettings = [
            'order_updates' => true,
            'bonus_updates' => true,
            'promo_updates' => false,
            'holiday_reminders' => true,
            'system_updates' => true,
        ];

        $pageMeta = [
            'title' => 'Личный кабинет — Bunch flowers',
            'description' => 'Управляйте профилем, адресами, заказами и подписками.',
            'headerTitle' => 'Bunch flowers',
            'headerSubtitle' => 'Профиль',
        ];

        $this->render('account', compact(
            'user',
            'addresses',
            'activeOrder',
            'activeSubscription',
            'notificationSettings',
            'pageMeta'
        ));
    }
}
