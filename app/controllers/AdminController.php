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
                        'href' => '/?page=admin-group-create',
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

        $users = $this->getUserFixtures();

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

        $users = array_filter($this->getUserFixtures(), static function ($user) {
            return $user['active'] === true;
        });

        $this->render('admin-group-create', [
            'pageMeta' => $pageMeta,
            'users' => array_values($users),
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
}
