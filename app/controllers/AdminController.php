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
                    [
                        'label' => 'Пользователи',
                        'description' => 'Профили клиентов и статусы доступа',
                        'href' => '/?page=admin-users',
                    ],
                    [
                        'label' => 'Рассылки',
                        'description' => 'E-mail, push и SMS кампании через телеграм-бота',
                        'href' => '/?page=admin-broadcast',
                    ],
                    [
                        'label' => 'Уведомления',
                        'description' => 'Триггеры и шаблоны сообщений',
                    ],
                ],
            ],
            [
                'title' => 'Каталог',
                'items' => [
                    [
                        'label' => 'Товары',
                        'description' => 'Карточки товаров из поставок: характеристики, атрибуты, фото и цены',
                        'href' => '/?page=admin-products',
                    ],
                    [
                        'label' => 'Акции',
                        'description' => 'Спецпредложения и акционные товары без привязки к поставкам',
                        'href' => '/?page=admin-promos',
                    ],
                    [
                        'label' => 'Атрибуты',
                        'description' => 'Высота стебля, виды оформления и другие варианты с ценой и фото',
                        'href' => '/?page=admin-attributes',
                    ],
                    [
                        'label' => 'Поставки',
                        'description' => 'Еженедельные поставки: сорт, страна, пачки, даты и бронирование под мелкий опт',
                        'href' => '/?page=admin-supplies',
                    ],
                ],
            ],
            [
                'title' => 'Заказы',
                'items' => [
                    [
                        'label' => 'Товары',
                        'description' => 'Разовые покупки и статусы',
                        'href' => '/?page=admin-orders-one-time',
                    ],
                    [
                        'label' => 'Подписки',
                        'description' => 'Регулярные доставки, паузы и скидки по периодичности',
                        'cta' => 'Настройка подписок',
                        'href' => '/?page=admin-orders-subscriptions',
                    ],
                    [
                        'label' => 'Мелкий опт',
                        'description' => 'Групповые заказы и лимиты',
                        'href' => '/?page=admin-orders-wholesale',
                    ],
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
                    [
                        'label' => 'Статичный контент',
                        'description' => 'Блоки страниц и SEO-тексты',
                        'href' => '/?page=admin-content-static',
                    ],
                    [
                        'label' => 'Товары',
                        'description' => 'Фото, описания и атрибуты',
                        'href' => '/?page=admin-content-products',
                    ],
                    [
                        'label' => 'Разделы сайта',
                        'description' => 'Навигация и лендинги',
                        'href' => '/?page=admin-content-sections',
                    ],
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

    public function users(): void
    {
        $pageMeta = [
            'title' => 'Пользователи — админ-панель Bunch',
            'description' => 'Список клиентов, поиск по телефону и активности заказов.',
            'h1' => 'Пользователи',
            'headerTitle' => 'Bunch Admin',
            'headerSubtitle' => 'Контакты и группы для рассылок',
            'footerLeft' => 'Управление пользователями Bunch',
            'footerRight' => 'Фильтрация по дате последних заказов',
        ];

        $userModel = new User();
        $users = $userModel->getAdminList();

        $this->render('admin-users', [
            'pageMeta' => $pageMeta,
            'users' => $users,
        ]);
    }

    public function user(): void
    {
        $pageMeta = [
            'title' => 'Профиль пользователя — админ-панель Bunch',
            'description' => 'Карточка клиента, заказы, подписки и рассылки.',
            'headerTitle' => 'Bunch Admin',
            'headerSubtitle' => 'Карточка клиента',
        ];

        $userId = (int) ($_GET['id'] ?? 1);
        $users = $this->getUserFixtures();
        $user = $users[0] ?? null;

        foreach ($users as $candidate) {
            if ($candidate['id'] === $userId) {
                $user = $candidate;
                break;
            }
        }

        if (!$user) {
            http_response_code(404);
            echo 'Пользователь не найден';
            return;
        }

        $orders = $this->getUserOrders($userId);
        $perPage = 10;
        $currentPage = max(1, (int) ($_GET['p'] ?? 1));
        $totalPages = max(1, (int) ceil(count($orders) / $perPage));
        $currentPage = min($currentPage, $totalPages);
        $ordersPage = array_slice($orders, ($currentPage - 1) * $perPage, $perPage);

        $pageMeta['h1'] = 'Клиент: ' . $user['name'];
        $pageMeta['footerLeft'] = 'Последний заказ: ' . $user['lastOrder'];
        $pageMeta['footerRight'] = 'Статус: ' . ($user['active'] ? 'Активен' : 'Не активен');

        $this->render('admin-user', [
            'pageMeta' => $pageMeta,
            'user' => $user,
            'orders' => $ordersPage,
            'totalPages' => $totalPages,
            'currentPage' => $currentPage,
            'addresses' => [
                ['title' => 'Дом', 'address' => 'Красноярск, ул. Карла Маркса, 12', 'comment' => 'Домофон 24, 3 подъезд'],
                ['title' => 'Работа', 'address' => 'Красноярск, пр-т Мира, 47', 'comment' => 'Офис на 5 этаже'],
            ],
            'subscriptions' => [
                ['title' => 'Еженедельная доставка', 'status' => 'Активна', 'nextDelivery' => 'Каждый вторник', 'tier' => 'Лояльность: 7%'],
                ['title' => 'Корпоративная подписка', 'status' => 'Пауза', 'nextDelivery' => 'Возобновление с 12.06', 'tier' => 'Бонусы копятся'],
            ],
        ]);
    }

    public function groupCreate(): void
    {
        $pageMeta = [
            'title' => 'Создать группу рассылки — админ-панель Bunch',
            'description' => 'Соберите активных клиентов в группу для рассылки через телеграм-бота.',
            'h1' => 'Создать группу',
            'headerTitle' => 'Bunch Admin',
            'headerSubtitle' => 'Группы для рассылок',
            'footerLeft' => 'Рассылки идут через телеграм-бота',
            'footerRight' => 'Выберите клиентов и сохраните группу',
        ];

        $userModel = new User();
        $users = array_filter($userModel->getAdminList(), static function ($user) {
            return $user['active'] === true;
        });

        $this->render('admin-group-create', [
            'pageMeta' => $pageMeta,
            'groups' => $this->getGroupFixtures(),
            'users' => array_values($users),
        ]);
    }

    public function broadcasts(): void
    {
        $pageMeta = [
            'title' => 'Рассылки — админ-панель Bunch',
            'description' => 'Создавайте сообщения, выбирайте группы и планируйте отправку.',
            'h1' => 'Рассылки',
            'headerTitle' => 'Bunch Admin',
            'headerSubtitle' => 'Кампании через телеграм-бота',
            'footerLeft' => 'Рассылаем только согласившимся клиентам',
            'footerRight' => 'Планирование по местному времени',
        ];

        $groups = $this->getGroupFixtures();
        $messages = $this->getBroadcastMessages();

        $perPage = 20;
        $currentPage = max(1, (int) ($_GET['p'] ?? 1));
        $totalPages = max(1, (int) ceil(count($messages) / $perPage));
        $currentPage = min($currentPage, $totalPages);
        $messagesPage = array_slice($messages, ($currentPage - 1) * $perPage, $perPage);

        $this->render('admin-broadcast', [
            'pageMeta' => $pageMeta,
            'groups' => $groups,
            'messages' => $messagesPage,
            'totalPages' => $totalPages,
            'currentPage' => $currentPage,
        ]);
    }

    public function catalogProducts(): void
    {
        $pageMeta = [
            'title' => 'Товары каталога — админ-панель Bunch',
            'description' => 'Карточки товаров с привязкой к поставкам, атрибутами и ценами.',
            'h1' => 'Товары',
            'headerTitle' => 'Bunch Admin',
            'headerSubtitle' => 'Каталог · Поставки и атрибуты',
        ];

        $filters = [
            'active' => ['Все', 'Активные', 'Неактивные'],
            'supplies' => array_column($this->getSupplyFixtures(), 'title'),
        ];

        $this->render('admin-products', [
            'pageMeta' => $pageMeta,
            'products' => $this->getProductFixtures(),
            'filters' => $filters,
        ]);
    }

    public function catalogPromos(): void
    {
        $pageMeta = [
            'title' => 'Акции — админ-панель Bunch',
            'description' => 'Акционные товары без обязательной привязки к поставке.',
            'h1' => 'Акции',
            'headerTitle' => 'Bunch Admin',
            'headerSubtitle' => 'Каталог · Спецпредложения',
        ];

        $this->render('admin-promos', [
            'pageMeta' => $pageMeta,
            'promos' => $this->getPromoFixtures(),
        ]);
    }

    public function catalogAttributes(): void
    {
        $pageMeta = [
            'title' => 'Атрибуты — админ-панель Bunch',
            'description' => 'Варианты оформления и параметры, влияющие на цену и фото.',
            'h1' => 'Атрибуты',
            'headerTitle' => 'Bunch Admin',
            'headerSubtitle' => 'Каталог · Атрибуты и варианты',
        ];

        $attributes = $this->getAttributeFixtures();

        $this->render('admin-attributes', [
            'pageMeta' => $pageMeta,
            'attributes' => $attributes,
        ]);
    }

    public function catalogSupplies(): void
    {
        $pageMeta = [
            'title' => 'Поставки — админ-панель Bunch',
            'description' => 'Управление расписанием поставок, сортами и мелким оптом.',
            'h1' => 'Поставки',
            'headerTitle' => 'Bunch Admin',
            'headerSubtitle' => 'Каталог · Поставки и брони',
        ];

        $supplies = $this->getSupplyFixtures();

        $this->render('admin-supplies', [
            'pageMeta' => $pageMeta,
            'supplies' => $supplies,
            'reservations' => $this->getSupplyReservations(),
        ]);
    }

    public function ordersOneTime(): void
    {
        $pageMeta = [
            'title' => 'Заказы · разовые покупки — админ-панель Bunch',
            'description' => 'Контроль статусов разовых заказов, оплат и доставки.',
            'h1' => 'Заказы (разовые)',
            'headerTitle' => 'Bunch Admin',
            'headerSubtitle' => 'Заказы · Разовые покупки',
        ];

        $this->render('admin-orders-one-time', [
            'pageMeta' => $pageMeta,
            'orders' => $this->getOneTimeOrders(),
            'filters' => [
                'status' => ['Все', 'Новый', 'В доставке', 'Доставлен', 'Отменён'],
                'payment' => ['Все', 'Оплачен', 'Ожидает', 'Возврат'],
            ],
        ]);
    }

    public function ordersSubscriptions(): void
    {
        $pageMeta = [
            'title' => 'Подписки · настройки скидок — админ-панель Bunch',
            'description' => 'Периодичность, скидки по поставкам и статусы подписок.',
            'h1' => 'Подписки',
            'headerTitle' => 'Bunch Admin',
            'headerSubtitle' => 'Заказы · Подписки',
        ];

        $this->render('admin-orders-subscriptions', [
            'pageMeta' => $pageMeta,
            'subscriptions' => $this->getSubscriptionOrders(),
            'periods' => ['Еженедельно', 'Раз в 2 недели', 'Ежемесячно'],
            'discountTiers' => $this->getSubscriptionDiscounts(),
        ]);
    }

    public function ordersWholesale(): void
    {
        $pageMeta = [
            'title' => 'Мелкий опт — админ-панель Bunch',
            'description' => 'Групповые заказы, лимиты по поставкам и статусы оплат.',
            'h1' => 'Мелкий опт',
            'headerTitle' => 'Bunch Admin',
            'headerSubtitle' => 'Заказы · Мелкий опт',
        ];

        $this->render('admin-orders-wholesale', [
            'pageMeta' => $pageMeta,
            'wholesaleOrders' => $this->getWholesaleOrders(),
            'limits' => [
                'packsAvailable' => 180,
                'reserved' => 62,
                'pendingInvoices' => 5,
            ],
        ]);
    }

    public function contentStatic(): void
    {
        $pageMeta = [
            'title' => 'Статичный контент — админ-панель Bunch',
            'description' => 'Блоки страниц, SEO-тексты и ответы на часто задаваемые вопросы.',
            'h1' => 'Статичные страницы',
            'headerTitle' => 'Bunch Admin',
            'headerSubtitle' => 'Контент · Статичные страницы',
        ];

        $this->render('admin-content-static', [
            'pageMeta' => $pageMeta,
            'pages' => $this->getStaticContentPages(),
            'faqs' => $this->getStaticFaqBlocks(),
        ]);
    }

    public function contentProducts(): void
    {
        $pageMeta = [
            'title' => 'Контент товаров — админ-панель Bunch',
            'description' => 'Фото, описания и рекомендации для карточек товаров.',
            'h1' => 'Контент для товаров',
            'headerTitle' => 'Bunch Admin',
            'headerSubtitle' => 'Контент · Товарные карточки',
        ];

        $this->render('admin-content-products', [
            'pageMeta' => $pageMeta,
            'products' => $this->getProductContentPages(),
            'attachments' => [
                'photoPresets' => 18,
                'descriptionTemplates' => 9,
                'attributes' => 12,
            ],
        ]);
    }

    public function contentSections(): void
    {
        $pageMeta = [
            'title' => 'Разделы сайта — админ-панель Bunch',
            'description' => 'Навигация, лендинги и ярлыки для мобильного приложения.',
            'h1' => 'Разделы сайта',
            'headerTitle' => 'Bunch Admin',
            'headerSubtitle' => 'Контент · Структура сайта',
        ];

        $this->render('admin-content-sections', [
            'pageMeta' => $pageMeta,
            'sections' => $this->getSiteSections(),
            'landingBlocks' => $this->getLandingPages(),
        ]);
    }

    private function getUserFixtures(): array
    {
        return [
            ['id' => 1, 'name' => 'Анна Соколова', 'phone' => '+7 900 123-45-67', 'active' => true, 'lastOrder' => '2024-05-30'],
            ['id' => 2, 'name' => 'Илья Петров', 'phone' => '+7 913 555-12-12', 'active' => true, 'lastOrder' => '2024-05-28'],
            ['id' => 3, 'name' => 'Наталья Климова', 'phone' => '+7 964 888-00-44', 'active' => false, 'lastOrder' => '2024-04-15'],
            ['id' => 4, 'name' => 'Сергей Ефимов', 'phone' => '+7 902 777-22-33', 'active' => true, 'lastOrder' => '2024-05-10'],
            ['id' => 5, 'name' => 'Дарья Никитина', 'phone' => '+7 923 111-66-55', 'active' => true, 'lastOrder' => '2024-05-03'],
            ['id' => 6, 'name' => 'Роман Белоусов', 'phone' => '+7 908 444-77-99', 'active' => false, 'lastOrder' => '2024-03-20'],
            ['id' => 7, 'name' => 'Ольга Смирнова', 'phone' => '+7 950 200-10-20', 'active' => true, 'lastOrder' => '2024-05-27'],
            ['id' => 8, 'name' => 'Артем Якубов', 'phone' => '+7 923 333-90-01', 'active' => true, 'lastOrder' => '2024-05-18'],
            ['id' => 9, 'name' => 'Мария Кузнецова', 'phone' => '+7 901 222-77-10', 'active' => true, 'lastOrder' => '2024-05-06'],
            ['id' => 10, 'name' => 'Владимир Новиков', 'phone' => '+7 904 999-33-44', 'active' => true, 'lastOrder' => '2024-05-21'],
            ['id' => 11, 'name' => 'Полина Сергеева', 'phone' => '+7 902 010-22-33', 'active' => true, 'lastOrder' => '2024-05-13'],
            ['id' => 12, 'name' => 'Дмитрий Богданов', 'phone' => '+7 933 666-44-22', 'active' => false, 'lastOrder' => '2024-04-28'],
            ['id' => 13, 'name' => 'Алина Галкина', 'phone' => '+7 905 345-67-89', 'active' => true, 'lastOrder' => '2024-05-31'],
        ];
    }

    private function getGroupFixtures(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'VIP клиенты / TG',
                'members' => 34,
                'channels' => ['Телеграм', 'SMS'],
                'description' => 'Покупают чаще 1 раза в месяц',
            ],
            [
                'id' => 2,
                'name' => 'Корпоративные клиенты',
                'members' => 12,
                'channels' => ['Телеграм'],
                'description' => 'Отправляем предложения по букетам для офисов',
            ],
            [
                'id' => 3,
                'name' => 'Новые подписчики',
                'members' => 58,
                'channels' => ['Телеграм', 'Email'],
                'description' => 'Получают приветственные цепочки',
            ],
            [
                'id' => 4,
                'name' => 'Забывшие корзину',
                'members' => 19,
                'channels' => ['Телеграм'],
                'description' => 'Напоминания об оставленных товарах',
            ],
        ];
    }

    private function getBroadcastMessages(): array
    {
        return [
            [
                'id' => 101,
                'title' => 'Новая коллекция пионов',
                'groups' => ['VIP клиенты / TG', 'Новые подписчики'],
                'status' => 'scheduled',
                'sendAt' => '2024-06-15 10:00',
                'createdAt' => '2024-06-12 09:20',
                'recipients' => 82,
            ],
            [
                'id' => 102,
                'title' => 'Скидка 15% на корпоративные заказы',
                'groups' => ['Корпоративные клиенты'],
                'status' => 'sent',
                'sendAt' => '2024-06-10 14:00',
                'createdAt' => '2024-06-09 18:40',
                'recipients' => 12,
            ],
            [
                'id' => 103,
                'title' => 'Промокод на доставку',
                'groups' => ['Новые подписчики', 'Забывшие корзину'],
                'status' => 'sent',
                'sendAt' => '2024-06-08 11:30',
                'createdAt' => '2024-06-07 16:05',
                'recipients' => 70,
            ],
            [
                'id' => 104,
                'title' => 'Обновления по подпискам',
                'groups' => ['VIP клиенты / TG'],
                'status' => 'scheduled',
                'sendAt' => '2024-06-20 09:15',
                'createdAt' => '2024-06-12 12:10',
                'recipients' => 34,
            ],
            [
                'id' => 105,
                'title' => 'Распродажа ленточек',
                'groups' => ['Забывшие корзину'],
                'status' => 'sent',
                'sendAt' => '2024-06-05 17:45',
                'createdAt' => '2024-06-04 10:00',
                'recipients' => 19,
            ],
            [
                'id' => 106,
                'title' => 'Праздничные наборы',
                'groups' => ['VIP клиенты / TG', 'Корпоративные клиенты'],
                'status' => 'sent',
                'sendAt' => '2024-05-30 09:00',
                'createdAt' => '2024-05-28 13:00',
                'recipients' => 46,
            ],
            [
                'id' => 107,
                'title' => 'Проверяем адреса доставки',
                'groups' => ['Новые подписчики'],
                'status' => 'sent',
                'sendAt' => '2024-05-25 15:30',
                'createdAt' => '2024-05-24 17:45',
                'recipients' => 58,
            ],
            [
                'id' => 108,
                'title' => 'Активность перед праздниками',
                'groups' => ['VIP клиенты / TG', 'Корпоративные клиенты', 'Забывшие корзину'],
                'status' => 'sent',
                'sendAt' => '2024-05-20 08:30',
                'createdAt' => '2024-05-19 12:50',
                'recipients' => 65,
            ],
            [
                'id' => 109,
                'title' => 'Соберите букет в конструкторе',
                'groups' => ['Новые подписчики'],
                'status' => 'sent',
                'sendAt' => '2024-05-12 09:15',
                'createdAt' => '2024-05-11 11:00',
                'recipients' => 58,
            ],
            [
                'id' => 110,
                'title' => 'Двойные бонусы за отзывы',
                'groups' => ['VIP клиенты / TG'],
                'status' => 'sent',
                'sendAt' => '2024-05-05 18:00',
                'createdAt' => '2024-05-04 09:40',
                'recipients' => 34,
            ],
            [
                'id' => 111,
                'title' => 'Приветствие новой аудитории',
                'groups' => ['Новые подписчики'],
                'status' => 'sent',
                'sendAt' => '2024-04-28 13:00',
                'createdAt' => '2024-04-27 10:10',
                'recipients' => 62,
            ],
            [
                'id' => 112,
                'title' => 'Обновления по доставке в новые районы',
                'groups' => ['Забывшие корзину', 'VIP клиенты / TG'],
                'status' => 'sent',
                'sendAt' => '2024-04-20 16:20',
                'createdAt' => '2024-04-19 14:00',
                'recipients' => 45,
            ],
            [
                'id' => 113,
                'title' => 'Весенние коллекции готовы',
                'groups' => ['Корпоративные клиенты'],
                'status' => 'sent',
                'sendAt' => '2024-04-10 10:00',
                'createdAt' => '2024-04-08 09:45',
                'recipients' => 12,
            ],
            [
                'id' => 114,
                'title' => 'Напоминание об опте',
                'groups' => ['Корпоративные клиенты'],
                'status' => 'sent',
                'sendAt' => '2024-04-02 11:00',
                'createdAt' => '2024-04-01 15:10',
                'recipients' => 12,
            ],
            [
                'id' => 115,
                'title' => 'Праздничные открытки в подарок',
                'groups' => ['Новые подписчики', 'VIP клиенты / TG'],
                'status' => 'sent',
                'sendAt' => '2024-03-25 17:00',
                'createdAt' => '2024-03-24 12:00',
                'recipients' => 92,
            ],
            [
                'id' => 116,
                'title' => 'Важное об изменении условий доставки',
                'groups' => ['Забывшие корзину'],
                'status' => 'sent',
                'sendAt' => '2024-03-18 14:30',
                'createdAt' => '2024-03-17 10:50',
                'recipients' => 19,
            ],
            [
                'id' => 117,
                'title' => 'Комбо-наборы к 8 марта',
                'groups' => ['VIP клиенты / TG', 'Корпоративные клиенты'],
                'status' => 'sent',
                'sendAt' => '2024-03-05 09:00',
                'createdAt' => '2024-03-03 13:30',
                'recipients' => 52,
            ],
            [
                'id' => 118,
                'title' => 'Актуальные контакты для связи',
                'groups' => ['Новые подписчики'],
                'status' => 'sent',
                'sendAt' => '2024-02-27 11:30',
                'createdAt' => '2024-02-26 09:15',
                'recipients' => 60,
            ],
            [
                'id' => 119,
                'title' => 'Подборка идей для подарков',
                'groups' => ['VIP клиенты / TG'],
                'status' => 'sent',
                'sendAt' => '2024-02-18 10:00',
                'createdAt' => '2024-02-17 08:40',
                'recipients' => 34,
            ],
            [
                'id' => 120,
                'title' => 'Промокод на первую доставку',
                'groups' => ['Новые подписчики'],
                'status' => 'sent',
                'sendAt' => '2024-02-10 12:00',
                'createdAt' => '2024-02-09 10:30',
                'recipients' => 58,
            ],
            [
                'id' => 121,
                'title' => 'Подтверждаем подписку на рассылки',
                'groups' => ['Новые подписчики'],
                'status' => 'sent',
                'sendAt' => '2024-01-30 13:00',
                'createdAt' => '2024-01-29 09:50',
                'recipients' => 55,
            ],
            [
                'id' => 122,
                'title' => 'Итоги года и бонусы',
                'groups' => ['VIP клиенты / TG', 'Корпоративные клиенты'],
                'status' => 'sent',
                'sendAt' => '2024-01-15 10:00',
                'createdAt' => '2024-01-14 14:00',
                'recipients' => 50,
            ],
            [
                'id' => 123,
                'title' => 'Запускаем чат с флористом',
                'groups' => ['VIP клиенты / TG'],
                'status' => 'sent',
                'sendAt' => '2024-01-05 18:30',
                'createdAt' => '2024-01-04 11:10',
                'recipients' => 34,
            ],
        ];
    }

    private function getProductFixtures(): array
    {
        return [
            [
                'id' => 501,
                'name' => 'Роза Freedom',
                'supply' => 'Поставка: Эквадор · 18.06',
                'active' => true,
                'hasAttributes' => true,
                'price' => '189 ₽',
                'createdAt' => '2024-06-10',
                'updatedAt' => '2024-06-12',
                'color' => 'Красный',
                'height' => '50 см',
            ],
            [
                'id' => 502,
                'name' => 'Гвоздика Cappuccino',
                'supply' => 'Поставка: Колумбия · 20.06',
                'active' => true,
                'hasAttributes' => false,
                'price' => '129 ₽',
                'createdAt' => '2024-06-11',
                'updatedAt' => '2024-06-11',
                'color' => 'Капучино',
                'height' => '45 см',
            ],
            [
                'id' => 503,
                'name' => 'Пион Coral Charm',
                'supply' => 'Поставка: Голландия · 25.06',
                'active' => false,
                'hasAttributes' => true,
                'price' => '289 ₽',
                'createdAt' => '2024-06-05',
                'updatedAt' => '2024-06-09',
                'color' => 'Корал',
                'height' => '60 см',
            ],
            [
                'id' => 504,
                'name' => 'Эвкалипт Cinerea',
                'supply' => 'Поставка: Стендинг · еженедельно',
                'active' => true,
                'hasAttributes' => false,
                'price' => '59 ₽',
                'createdAt' => '2024-05-29',
                'updatedAt' => '2024-06-01',
                'color' => 'Серебристый',
                'height' => '40 см',
            ],
        ];
    }

    private function getPromoFixtures(): array
    {
        return [
            [
                'id' => 801,
                'title' => 'Флеш-распродажа 6 часов',
                'type' => 'sale',
                'price' => '75 ₽',
                'active' => true,
                'period' => '14.06 10:00 — 14.06 16:00',
                'product' => 'Роза Freedom',
            ],
            [
                'id' => 802,
                'title' => 'Аукцион на пион Coral Charm',
                'type' => 'auction',
                'price' => 'Старт 250 ₽',
                'active' => true,
                'period' => '15.06 12:00 — 15.06 20:00',
                'product' => 'Пион Coral Charm',
            ],
            [
                'id' => 803,
                'title' => 'Лотерея «Заберите стендинг»',
                'type' => 'lottery',
                'price' => '0 ₽',
                'active' => false,
                'period' => '01.06 — 07.06',
                'product' => null,
            ],
        ];
    }

    private function getAttributeFixtures(): array
    {
        return [
            [
                'id' => 301,
                'name' => 'Высота стебля',
                'type' => 'selector',
                'active' => true,
                'values' => [
                    ['name' => '40 см', 'priceDelta' => '+0 ₽', 'photo' => 'photo-40.jpg', 'active' => true],
                    ['name' => '50 см', 'priceDelta' => '+10 ₽', 'photo' => 'photo-50.jpg', 'active' => true],
                    ['name' => '60 см', 'priceDelta' => '+20 ₽', 'photo' => 'photo-60.jpg', 'active' => false],
                ],
            ],
            [
                'id' => 302,
                'name' => 'Вид оформления',
                'type' => 'toggle',
                'active' => true,
                'values' => [
                    ['name' => 'Без оформления', 'priceDelta' => '+0 ₽', 'photo' => 'plain.jpg', 'active' => true],
                    ['name' => 'В крафте', 'priceDelta' => '+30 ₽', 'photo' => 'kraft.jpg', 'active' => true],
                    ['name' => 'Подарочная упаковка', 'priceDelta' => '+70 ₽', 'photo' => 'gift.jpg', 'active' => true],
                ],
            ],
            [
                'id' => 303,
                'name' => 'Цвет ленты',
                'type' => 'color',
                'active' => false,
                'values' => [
                    ['name' => 'Бордовая', 'priceDelta' => '+0 ₽', 'photo' => 'ribbon-red.jpg', 'active' => true],
                    ['name' => 'Бежевая', 'priceDelta' => '+0 ₽', 'photo' => 'ribbon-beige.jpg', 'active' => true],
                ],
            ],
        ];
    }

    private function getSupplyFixtures(): array
    {
        return [
            [
                'id' => 201,
                'title' => 'Эквадор · Freedom',
                'date' => '2024-06-18',
                'sort' => 'Freedom',
                'color' => 'Красный',
                'height' => '50 см',
                'weight' => '45 г',
                'country' => 'Эквадор',
                'packsTotal' => 60,
                'packsAvailable' => 38,
                'packSize' => 25,
                'smallWholesale' => true,
                'isStanding' => false,
                'status' => 'Планируется',
            ],
            [
                'id' => 202,
                'title' => 'Колумбия · Cappuccino',
                'date' => '2024-06-20',
                'sort' => 'Cappuccino',
                'color' => 'Капучино',
                'height' => '45 см',
                'weight' => '38 г',
                'country' => 'Колумбия',
                'packsTotal' => 40,
                'packsAvailable' => 32,
                'packSize' => 20,
                'smallWholesale' => true,
                'isStanding' => false,
                'status' => 'Планируется',
            ],
            [
                'id' => 203,
                'title' => 'Стендинг · Эвкалипт',
                'date' => 'Еженедельно (вторник)',
                'sort' => 'Cinerea',
                'color' => 'Серебристый',
                'height' => '40 см',
                'weight' => '28 г',
                'country' => 'Россия',
                'packsTotal' => 80,
                'packsAvailable' => 62,
                'packSize' => 15,
                'smallWholesale' => true,
                'isStanding' => true,
                'status' => 'В работе',
            ],
        ];
    }

    private function getSupplyReservations(): array
    {
        return [
            ['supply' => 'Эквадор · Freedom', 'client' => 'ООО «Астра»', 'packs' => 10, 'status' => 'Забронировано', 'date' => '2024-06-12'],
            ['supply' => 'Эквадор · Freedom', 'client' => 'ИП Флора', 'packs' => 6, 'status' => 'Подтверждено', 'date' => '2024-06-13'],
            ['supply' => 'Колумбия · Cappuccino', 'client' => 'Салон «Лаванда»', 'packs' => 4, 'status' => 'Ожидает оплаты', 'date' => '2024-06-14'],
            ['supply' => 'Стендинг · Эвкалипт', 'client' => 'Retail 24', 'packs' => 8, 'status' => 'Отгружено', 'date' => '2024-06-10'],
        ];
    }

    private function getOneTimeOrders(): array
    {
        return [
            ['number' => 'B-3012', 'customer' => 'Анна Соколова', 'sum' => '2 350 ₽', 'status' => 'Новый', 'payment' => 'Ожидает', 'delivery' => '12.06, 14:00', 'channel' => 'Сайт'],
            ['number' => 'B-3011', 'customer' => 'ИП Флора', 'sum' => '7 480 ₽', 'status' => 'В доставке', 'payment' => 'Оплачен', 'delivery' => '12.06, 18:00', 'channel' => 'Корзина'],
            ['number' => 'B-3010', 'customer' => 'Ольга Смирнова', 'sum' => '1 980 ₽', 'status' => 'Доставлен', 'payment' => 'Оплачен', 'delivery' => '11.06, 16:00', 'channel' => 'Сайт'],
            ['number' => 'B-3009', 'customer' => 'Retail 24', 'sum' => '4 120 ₽', 'status' => 'Отменён', 'payment' => 'Возврат', 'delivery' => '—', 'channel' => 'Менеджер'],
            ['number' => 'B-3008', 'customer' => 'Артем Якубов', 'sum' => '3 550 ₽', 'status' => 'Доставлен', 'payment' => 'Оплачен', 'delivery' => '10.06, 13:00', 'channel' => 'Сайт'],
            ['number' => 'B-3007', 'customer' => 'ООО «Астра»', 'sum' => '12 400 ₽', 'status' => 'В доставке', 'payment' => 'Оплачен', 'delivery' => '12.06, 09:00', 'channel' => 'Менеджер'],
        ];
    }

    private function getSubscriptionOrders(): array
    {
        return [
            ['plan' => 'Еженедельно', 'customer' => 'Мария Кузнецова', 'nextDelivery' => '13.06, 11:00', 'discount' => '-5%', 'status' => 'Активна', 'sku' => 'Букет «Нежность»'],
            ['plan' => 'Раз в 2 недели', 'customer' => 'ООО «Астра»', 'nextDelivery' => '20.06, 10:00', 'discount' => '-7%', 'status' => 'Активна', 'sku' => 'Моно-букет «Эвкалипт»'],
            ['plan' => 'Ежемесячно', 'customer' => 'Илья Петров', 'nextDelivery' => '05.07, 15:00', 'discount' => '-10%', 'status' => 'Пауза', 'sku' => 'Букет «Сезонный»'],
            ['plan' => 'Еженедельно', 'customer' => 'Корп. клиент «Retail 24»', 'nextDelivery' => '15.06, 09:30', 'discount' => '-6%', 'status' => 'Активна', 'sku' => 'Композиция «Офис»'],
        ];
    }

    private function getSubscriptionDiscounts(): array
    {
        return [
            ['step' => '2-й букет', 'discount' => '-3%', 'comment' => 'Фиксированный % на вторую доставку'],
            ['step' => '3-й букет', 'discount' => '-5%', 'comment' => 'Рост скидки при удержании подписки'],
            ['step' => '4-й букет', 'discount' => '-7%', 'comment' => 'Дополнительный стимул без промокодов'],
            ['step' => '5-й и далее', 'discount' => '-10%', 'comment' => 'Максимальная скидка для долгих подписок'],
        ];
    }

    private function getWholesaleOrders(): array
    {
        return [
            ['client' => 'Retail 24', 'packs' => 24, 'sum' => '48 600 ₽', 'status' => 'Ожидает оплату', 'supply' => 'Эквадор · Freedom', 'date' => '12.06'],
            ['client' => 'ООО «Астра»', 'packs' => 16, 'sum' => '32 800 ₽', 'status' => 'Подтверждено', 'supply' => 'Колумбия · Cappuccino', 'date' => '14.06'],
            ['client' => 'Салон «Лаванда»', 'packs' => 8, 'sum' => '15 200 ₽', 'status' => 'Отгружено', 'supply' => 'Стендинг · Эвкалипт', 'date' => '10.06'],
            ['client' => 'ИП Флора', 'packs' => 12, 'sum' => '22 400 ₽', 'status' => 'Резерв', 'supply' => 'Эквадор · Freedom', 'date' => '13.06'],
            ['client' => 'ООО «Букет»', 'packs' => 20, 'sum' => '38 500 ₽', 'status' => 'В работе', 'supply' => 'Стендинг · Эвкалипт', 'date' => '15.06'],
        ];
    }

    private function getUserOrders(int $userId): array
    {
        $templateOrders = [
            ['number' => 'A-2109', 'date' => '2024-05-31', 'sum' => '2 350 ₽', 'status' => 'Доставлен'],
            ['number' => 'A-2108', 'date' => '2024-05-24', 'sum' => '4 120 ₽', 'status' => 'Доставлен'],
            ['number' => 'A-2107', 'date' => '2024-05-17', 'sum' => '1 980 ₽', 'status' => 'Доставлен'],
            ['number' => 'A-2106', 'date' => '2024-05-10', 'sum' => '3 550 ₽', 'status' => 'Доставлен'],
            ['number' => 'A-2105', 'date' => '2024-05-03', 'sum' => '2 740 ₽', 'status' => 'Доставлен'],
            ['number' => 'A-2104', 'date' => '2024-04-26', 'sum' => '1 450 ₽', 'status' => 'Доставлен'],
            ['number' => 'A-2103', 'date' => '2024-04-19', 'sum' => '3 980 ₽', 'status' => 'Доставлен'],
            ['number' => 'A-2102', 'date' => '2024-04-12', 'sum' => '2 120 ₽', 'status' => 'Доставлен'],
            ['number' => 'A-2101', 'date' => '2024-04-05', 'sum' => '4 430 ₽', 'status' => 'Доставлен'],
            ['number' => 'A-2100', 'date' => '2024-03-29', 'sum' => '2 990 ₽', 'status' => 'Доставлен'],
            ['number' => 'A-2099', 'date' => '2024-03-22', 'sum' => '1 870 ₽', 'status' => 'Отменён'],
            ['number' => 'A-2098', 'date' => '2024-03-15', 'sum' => '3 330 ₽', 'status' => 'Доставлен'],
            ['number' => 'A-2097', 'date' => '2024-03-08', 'sum' => '5 120 ₽', 'status' => 'Доставлен'],
            ['number' => 'A-2096', 'date' => '2024-03-01', 'sum' => '1 640 ₽', 'status' => 'Доставлен'],
            ['number' => 'A-2095', 'date' => '2024-02-22', 'sum' => '3 880 ₽', 'status' => 'Доставлен'],
        ];

        return array_map(static function ($order) use ($userId) {
            $order['customer_id'] = $userId;
            return $order;
        }, $templateOrders);
    }

    private function getStaticContentPages(): array
    {
        return [
            [
                'id' => 1,
                'title' => 'Главная страница',
                'slug' => '/',
                'blocks' => 9,
                'seo' => 'Тайтл и описание заполнены',
                'updatedAt' => '2024-05-31 18:20',
                'status' => 'Опубликовано',
            ],
            [
                'id' => 2,
                'title' => 'Доставка и оплата',
                'slug' => '/delivery',
                'blocks' => 6,
                'seo' => 'Мета-теги готовы',
                'updatedAt' => '2024-05-29 11:40',
                'status' => 'Черновик',
            ],
            [
                'id' => 3,
                'title' => 'FAQ',
                'slug' => '/faq',
                'blocks' => 12,
                'seo' => 'Добавить H1',
                'updatedAt' => '2024-05-25 09:10',
                'status' => 'Опубликовано',
            ],
            [
                'id' => 4,
                'title' => 'О сервисе',
                'slug' => '/about',
                'blocks' => 8,
                'seo' => 'Заполнить alt у фото',
                'updatedAt' => '2024-05-22 15:05',
                'status' => 'Опубликовано',
            ],
        ];
    }

    private function getStaticFaqBlocks(): array
    {
        return [
            ['question' => 'Как оформить доставку в день заказа?', 'status' => 'Опубликован', 'updatedAt' => '2024-05-30'],
            ['question' => 'Какие способы оплаты доступны?', 'status' => 'Опубликован', 'updatedAt' => '2024-05-28'],
            ['question' => 'Как работает подписка на цветы?', 'status' => 'Черновик', 'updatedAt' => '2024-05-26'],
            ['question' => 'Возвраты и отмены', 'status' => 'Опубликован', 'updatedAt' => '2024-05-20'],
        ];
    }

    private function getProductContentPages(): array
    {
        return [
            [
                'id' => 101,
                'name' => 'Freedom · Роза Эквадор',
                'photos' => 6,
                'seo' => 'Тайтл, H1 и описание заполнены',
                'updatedAt' => '2024-05-31',
                'owner' => 'Екатерина',
            ],
            [
                'id' => 102,
                'name' => 'Пион Королева Ночь',
                'photos' => 4,
                'seo' => 'Нужен alt у фото',
                'updatedAt' => '2024-05-29',
                'owner' => 'Мария',
            ],
            [
                'id' => 103,
                'name' => 'Эвкалипт николи',
                'photos' => 5,
                'seo' => 'Добавить блок «композиции»',
                'updatedAt' => '2024-05-27',
                'owner' => 'Сергей',
            ],
            [
                'id' => 104,
                'name' => 'Коробки с цветами',
                'photos' => 8,
                'seo' => 'Заполнен сниппет для рекламы',
                'updatedAt' => '2024-05-25',
                'owner' => 'Илья',
            ],
        ];
    }

    private function getSiteSections(): array
    {
        return [
            [
                'title' => 'Главная навигация',
                'items' => ['Каталог', 'Подписка', 'Акции', 'Мелкий опт'],
                'status' => 'Активно',
                'updatedAt' => '2024-06-01 10:00',
            ],
            [
                'title' => 'Футер',
                'items' => ['О сервисе', 'FAQ', 'Доставка', 'Контакты'],
                'status' => 'Активно',
                'updatedAt' => '2024-05-29 14:20',
            ],
            [
                'title' => 'Быстрые ссылки в приложении',
                'items' => ['Повторить заказ', 'Рекомендации', 'Подарочные карты'],
                'status' => 'Скрыто',
                'updatedAt' => '2024-05-27 09:15',
            ],
        ];
    }

    private function getLandingPages(): array
    {
        return [
            ['title' => 'Свадебные букеты', 'slug' => '/wedding', 'traffic' => '12% конверсии', 'status' => 'Опубликован'],
            ['title' => 'Цветы к корпоративу', 'slug' => '/corporate', 'traffic' => '8% конверсии', 'status' => 'Опубликован'],
            ['title' => 'Подарочные сертификаты', 'slug' => '/gift-cards', 'traffic' => 'Тестируется', 'status' => 'Черновик'],
            ['title' => 'Еженедельные подборки', 'slug' => '/weekly-picks', 'traffic' => '5% конверсии', 'status' => 'Опубликован'],
        ];
    }
}
