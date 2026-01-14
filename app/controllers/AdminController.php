<?php
// app/controllers/AdminController.php

class AdminController extends Controller
{
    private function logAdminError(string $context, Throwable $e): void
    {
        $logFile = __DIR__ . '/../../storage/logs/error.log';
        $timestamp = date('Y-m-d H:i:s');
        $message = sprintf(
            "[%s] %s: %s in %s:%d\n",
            $timestamp,
            $context,
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        );

        error_log($message, 3, $logFile);
    }

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
                    [
                        'label' => 'Онлайн оплата',
                        'description' => 'Платёжные шлюзы и возвраты',
                        'cta' => 'Настроить',
                        'href' => '/?page=admin-services-payment',
                    ],
                    ['label' => 'Веб-аналитика яндекс метрика', 'description' => 'События, цели и конверсии'],
                    ['label' => 'Подключение к ЦРМ', 'description' => 'Синхронизация контактов и сделок'],
                    [
                        'label' => 'DaData + зоны доставки',
                        'description' => 'Подсказки адресов, геокодинг и расчёт через turf.js',
                        'cta' => 'Настроить',
                        'href' => '/?page=admin-services-delivery',
                    ],
                    [
                        'label' => 'Телеграм бот',
                        'description' => 'Токен, username и секрет для вебхука',
                        'cta' => 'Настроить',
                        'href' => '/?page=admin-services-telegram',
                    ],
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

        $orderModel = new Order();
        $subscriptionModel = new Subscription();
        $supplyModel = new Supply();
        $promoItemModel = new PromoItem();

        $oneTimeNewCount = $orderModel->countOneTimeByStatus('new');
        $activeSubscriptions = $subscriptionModel->countActive();
        $activePromos = $promoItemModel->countActive();

        $deliveryWindow = $supplyModel->getNextDeliveryWindow();
        $currentSupply = $deliveryWindow['current_supply'] ?? null;
        $orderedStems = 0;
        if ($currentSupply) {
            $orderedStems = (int) ($currentSupply['packs_reserved'] ?? 0) * (int) ($currentSupply['stems_per_pack'] ?? 0);
        }

        $monitoring = [
            'one_time_new' => $oneTimeNewCount,
            'active_subscriptions' => $activeSubscriptions,
            'ordered_stems' => $orderedStems,
            'active_promos' => $activePromos,
            'current_supply_date' => $deliveryWindow['current_date'] ?? null,
            'next_supply_date' => $deliveryWindow['next_date'] ?? null,
        ];

        $this->render('admin', [
            'sections' => $sections,
            'pageMeta' => $pageMeta,
            'monitoring' => $monitoring,
        ]);
    }

    public function users(): void
    {
        $pageMeta = [
            'title' => 'Пользователи — админ-панель Bunch',
            'description' => 'Список клиентов и быстрая проверка активности.',
            'h1' => 'Пользователи',
            'headerTitle' => 'Bunch Admin',
            'headerSubtitle' => 'Контакты и статусы',
            'footerLeft' => 'Управление пользователями Bunch',
            'footerRight' => 'Изменение активности без перезагрузки',
        ];

        $userModel = new User();
        $users = $userModel->getAdminList();

        $this->render('admin-users', [
            'pageMeta' => $pageMeta,
            'users' => $users,
        ]);
    }

    public function toggleUserActive(): void
    {
        header('Content-Type: application/json');

        $payload = json_decode(file_get_contents('php://input'), true);
        $userId = isset($payload['userId']) ? (int) $payload['userId'] : 0;
        $active = isset($payload['active']) ? (bool) $payload['active'] : null;

        if ($userId <= 0 || $active === null) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Некорректные данные']);
            return;
        }

        $userModel = new User();
        $user = $userModel->findById($userId);

        if (!$user) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Пользователь не найден']);
            return;
        }

        $userModel->setActive($userId, $active);

        echo json_encode(['success' => true]);
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
        $userModel = new User();
        $userRecord = $userModel->findById($userId);
        $user = null;
        $roleOptions = [
            'admin' => 'Администратор',
            'manager' => 'Менеджер',
            'florist' => 'Флорист',
            'courier' => 'Курьер',
            'customer' => 'Покупатель',
            'wholesale' => 'Оптовый покупатель',
        ];
        $statusLabels = [
            'new' => 'Новый',
            'confirmed' => 'Подтверждён',
            'assembled' => 'Собран',
            'delivering' => 'В доставке',
            'delivered' => 'Доставлен',
            'cancelled' => 'Отменён',
        ];

        if ($userRecord) {
            $orderModel = new Order();
            $latestActive = $orderModel->getLatestActiveForUser($userId);
            $latestCompleted = $orderModel->getCompletedOrdersForUser($userId, 1, 0);
            $recentOrders = array_filter(array_merge(
                $latestActive ? [$latestActive] : [],
                $latestCompleted
            ));
            $lastOrderDate = '';
            $lastOrderStatus = 'Нет заказов';

            if (!empty($recentOrders)) {
                usort($recentOrders, static function (array $left, array $right): int {
                    return strcmp($right['created_at'], $left['created_at']);
                });
                $lastOrderDate = (new DateTime($recentOrders[0]['created_at']))->format('Y-m-d');
                $lastOrderStatus = $statusLabels[$recentOrders[0]['status']] ?? 'В обработке';
            }

            $role = $userRecord['role'] ?? 'customer';
            if (!array_key_exists($role, $roleOptions)) {
                $role = 'customer';
            }

            $user = [
                'id' => (int) $userRecord['id'],
                'name' => $userRecord['name'] ?: 'Без имени',
                'phone' => $userRecord['phone'] ?? '',
                'active' => (bool) ($userRecord['is_active'] ?? true),
                'lastOrder' => $lastOrderDate ?: 'Нет заказов',
                'lastOrderStatus' => $lastOrderStatus,
                'role' => $role,
                'roleLabel' => $roleOptions[$role],
            ];
        }

        if (!$user) {
            http_response_code(404);
            echo 'Пользователь не найден';
            return;
        }

        $orderModel = new Order();
        $activeOrdersRaw = $orderModel->getActiveOrdersForUser($userId);
        $activeOrders = array_map(static function (array $order) use ($statusLabels): array {
            $createdAt = new DateTime($order['created_at']);
            $scheduledParts = [];
            if (!empty($order['scheduled_date'])) {
                $scheduledParts[] = (new DateTime($order['scheduled_date']))->format('d.m.Y');
            }
            if (!empty($order['scheduled_time'])) {
                $scheduledParts[] = substr($order['scheduled_time'], 0, 5);
            }
            $scheduledLabel = $scheduledParts ? implode(', ', $scheduledParts) : 'Время уточняется';

            return [
                'id' => (int) $order['id'],
                'number' => '#' . (int) $order['id'],
                'date' => $createdAt->format('d.m.Y'),
                'sum' => number_format((int) floor((float) $order['total_amount']), 0, '.', ' ') . ' ₽',
                'status' => $statusLabels[$order['status']] ?? 'В обработке',
                'schedule' => $scheduledLabel,
            ];
        }, $activeOrdersRaw);

        $perPage = 10;
        $currentPage = max(1, (int) ($_GET['p'] ?? 1));
        $completedCount = $orderModel->countCompletedOrdersForUser($userId);
        $totalPages = max(1, (int) ceil($completedCount / $perPage));
        $currentPage = min($currentPage, $totalPages);
        $completedOrdersRaw = $orderModel->getCompletedOrdersForUser(
            $userId,
            $perPage,
            ($currentPage - 1) * $perPage
        );
        $ordersPage = array_map(static function (array $order) use ($statusLabels): array {
            $createdAt = new DateTime($order['created_at']);

            return [
                'number' => '#' . (int) $order['id'],
                'date' => $createdAt->format('d.m.Y'),
                'sum' => number_format((int) floor((float) $order['total_amount']), 0, '.', ' ') . ' ₽',
                'status' => $statusLabels[$order['status']] ?? 'В обработке',
            ];
        }, $completedOrdersRaw);

        $addressModel = new UserAddress();
        $addresses = array_map(static function (array $address): array {
            $raw = $address['raw'] ?? [];
            $commentParts = [];
            if (!empty($raw['recipient_name'])) {
                $commentParts[] = 'Получатель: ' . $raw['recipient_name'];
            }
            if (!empty($raw['recipient_phone'])) {
                $commentParts[] = 'Тел.: ' . $raw['recipient_phone'];
            }
            if (!empty($raw['delivery_comment'])) {
                $commentParts[] = $raw['delivery_comment'];
            }

            return [
                'title' => $address['label'],
                'address' => $address['address'],
                'comment' => $commentParts ? implode(' · ', $commentParts) : 'Комментариев нет',
                'is_primary' => (bool) ($address['is_primary'] ?? false),
            ];
        }, $addressModel->getByUserId($userId));

        $subscriptionModel = new Subscription();
        $activeSubscriptionsRaw = $subscriptionModel->getActiveListForUser($userId);
        $activeSubscriptions = array_map(static function (array $subscription): array {
            $nextDate = $subscription['next_delivery_date']
                ? (new DateTime($subscription['next_delivery_date']))->format('d.m.Y')
                : 'Не задано';
            $planLabel = match ($subscription['plan']) {
                'weekly' => 'Еженедельно',
                'biweekly' => 'Раз в 2 недели',
                'monthly' => 'Ежемесячно',
                default => 'Гибкий график',
            };

            return [
                'title' => $subscription['product_name'] . ' × ' . $subscription['qty'],
                'status' => 'Активна',
                'nextDelivery' => $nextDate,
                'tier' => $planLabel,
                'price' => number_format((int) floor((float) $subscription['product_price']), 0, '.', ' ') . ' ₽',
            ];
        }, $activeSubscriptionsRaw);

        $pageMeta['h1'] = 'Клиент: ' . $user['name'];
        $pageMeta['footerLeft'] = 'Последний заказ: ' . $user['lastOrder'];
        $pageMeta['footerRight'] = 'Статус: ' . ($user['active'] ? 'Активен' : 'Не активен');

        $message = $_GET['message'] ?? null;

        $this->render('admin-user', [
            'pageMeta' => $pageMeta,
            'user' => $user,
            'roleOptions' => $roleOptions,
            'roleMessage' => $message === 'role-updated'
                ? 'Роль пользователя обновлена.'
                : ($message === 'role-error' ? 'Не удалось обновить роль.' : null),
            'roleMessageTone' => $message === 'role-error' ? 'text-rose-600' : 'text-emerald-600',
            'activeOrders' => $activeOrders,
            'orders' => $ordersPage,
            'totalPages' => $totalPages,
            'currentPage' => $currentPage,
            'addresses' => $addresses,
            'subscriptions' => $activeSubscriptions,
        ]);
    }

    public function updateUserRole(): void
    {
        $userId = (int) ($_POST['user_id'] ?? 0);
        $role = trim((string) ($_POST['role'] ?? ''));
        $roleOptions = ['admin', 'manager', 'florist', 'courier', 'customer', 'wholesale'];

        if ($userId <= 0 || !in_array($role, $roleOptions, true)) {
            header('Location: /?page=admin-user&id=' . $userId . '&message=role-error');
            return;
        }

        $userModel = new User();
        $user = $userModel->findById($userId);

        if (!$user) {
            http_response_code(404);
            echo 'Пользователь не найден';
            return;
        }

        $userModel->setRole($userId, $role);

        header('Location: /?page=admin-user&id=' . $userId . '&message=role-updated');
    }

    public function groupCreate(): void
    {
        $groupModel = new BroadcastGroup();
        $groupModel->ensureSystemGroup();

        $pageMeta = [
            'title' => 'Создать группу рассылки — админ-панель Bunch',
            'description' => 'Соберите активных клиентов в группу для рассылки.',
            'h1' => 'Группы для рассылки',
            'headerTitle' => 'Bunch Admin',
            'headerSubtitle' => 'Управление группами',
            'footerLeft' => 'Рассылки идут через телеграм-бота',
            'footerRight' => 'Выберите клиентов и сохраните группу',
        ];

        $userModel = new User();
        $users = array_filter($userModel->getAdminList(), static function ($user) {
            return $user['active'] === true;
        });

        $selectedGroupId = isset($_GET['group']) ? (int) $_GET['group'] : null;

        $this->render('admin-group-create', [
            'pageMeta' => $pageMeta,
            'groups' => $groupModel->editableWithCounts(),
            'users' => array_values($users),
            'memberships' => $groupModel->membershipMap(),
            'selectedGroupId' => $selectedGroupId,
            'message' => $_GET['message'] ?? null,
        ]);
    }

    public function saveGroup(): void
    {
        $groupModel = new BroadcastGroup();
        $groupModel->ensureSystemGroup();

        $groupId = isset($_POST['group_id']) && $_POST['group_id'] !== '' ? (int) $_POST['group_id'] : null;
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $userIds = array_map('intval', $_POST['users'] ?? []);

        if ($groupId !== null) {
            $existing = $groupModel->find($groupId);

            if (!$existing || $existing['is_system']) {
                header('Location: /?page=admin-group-create&message=not-found');
                return;
            }

            $finalName = $name !== '' ? $name : 'Группа ' . $groupId;
            $groupModel->update($groupId, $finalName, $description);
            $groupModel->syncMembers($groupId, $userIds);
            $targetId = $groupId;
        } else {
            $initialName = $name !== '' ? $name : 'Группа';
            $newGroupId = $groupModel->create($initialName, $description, false);
            $finalName = $name !== '' ? $name : 'Группа ' . $newGroupId;
            if ($finalName !== $initialName) {
                $groupModel->update($newGroupId, $finalName, $description);
            }
            $groupModel->syncMembers($newGroupId, $userIds);
            $targetId = $newGroupId;
        }

        header('Location: /?page=admin-group-create&group=' . $targetId . '&message=saved');
    }

    public function broadcasts(): void
    {
        $groupModel = new BroadcastGroup();
        $groupModel->ensureSystemGroup();

        $pageMeta = [
            'title' => 'Рассылки — админ-панель Bunch',
            'description' => 'Создавайте сообщения, выбирайте группы и планируйте отправку.',
            'h1' => 'Рассылки',
            'headerTitle' => 'Bunch Admin',
            'headerSubtitle' => 'Кампании через телеграм-бота',
            'footerLeft' => 'Рассылаем только согласившимся клиентам',
            'footerRight' => 'Планирование по местному времени',
        ];

        $groups = $groupModel->allWithCounts();

        $perPage = 20;
        $currentPage = max(1, (int) ($_GET['p'] ?? 1));

        $broadcastModel = new BroadcastMessage();
        $pagination = $broadcastModel->paginate($currentPage, $perPage);

        $this->render('admin-broadcast', [
            'pageMeta' => $pageMeta,
            'groups' => $groups,
            'messages' => $pagination['messages'],
            'totalPages' => $pagination['totalPages'],
            'currentPage' => $pagination['currentPage'],
            'message' => $_GET['message'] ?? null,
        ]);
    }

    public function createBroadcast(): void
    {
        $groupModel = new BroadcastGroup();
        $systemGroup = $groupModel->ensureSystemGroup();

        $body = trim($_POST['body'] ?? '');
        $groupIds = array_map('intval', $_POST['groups'] ?? []);
        if (empty($groupIds)) {
            $groupIds = [$systemGroup['id']];
        }

        $date = trim($_POST['send_date'] ?? '');
        $time = trim($_POST['send_time'] ?? '');

        $sendAt = null;
        if ($date !== '') {
            $sendAtString = $date . ' ' . ($time !== '' ? $time : '00:00:00');
            $sendAt = new DateTimeImmutable($sendAtString);
        }

        if ($body === '') {
            header('Location: /?page=admin-broadcast&message=body-required');
            return;
        }

        $broadcastModel = new BroadcastMessage();
        $messageId = $broadcastModel->create($body, $sendAt, $groupIds);

        $shouldSendNow = $sendAt === null || $sendAt <= new DateTimeImmutable();
        if ($shouldSendNow) {
            $broadcastModel->sendNow($messageId, $body, $groupIds);
        }

        header('Location: /?page=admin-broadcast&message=created');
    }

    public function catalogProducts(): void
    {
        $pageMeta = [
            'title' => 'Товары — админ-панель Bunch',
            'description' => 'Карточки товаров с привязкой к поставкам, атрибутами и ценами.',
            'h1' => 'Товары',
            'headerTitle' => 'Bunch Admin',
            'headerSubtitle' => 'Каталог · Поставки и атрибуты',
        ];

        $supplyModel = new Supply();
        $productModel = new Product();

        $supplyOptions = array_column($supplyModel->getAdminList(), 'flower_name');

        $filters = [
            'active' => ['Все', 'Активные', 'Неактивные'],
            'supplies' => $supplyOptions,
        ];

        $blockedRelations = null;
        $blockedProductId = isset($_GET['product_id']) ? (int) $_GET['product_id'] : null;
        $showDeleted = isset($_GET['show_deleted']) && $_GET['show_deleted'] === '1';

        if (($_GET['status'] ?? null) === 'delete-blocked' && $blockedProductId) {
            $blockedRelations = $productModel->getBlockingRelations($blockedProductId);
        }

        $this->render('admin-products', [
            'pageMeta' => $pageMeta,
            'products' => $productModel->getAdminList($showDeleted, ['promo', 'auction', 'lottery']),
            'filters' => $filters,
            'message' => $_GET['status'] ?? null,
            'blockedRelations' => $blockedRelations,
            'showDeleted' => $showDeleted,
        ]);
    }

    public function productForm(): void
    {
        $productModel = new Product();
        $editingProduct = isset($_GET['edit_id']) ? $productModel->getWithRelations((int) $_GET['edit_id']) : null;
        $productRelations = null;
        if ($editingProduct) {
            $productRelations = $productModel->getBlockingRelations((int) $editingProduct['id']);
        }

        $pageMeta = [
            'title' => ($editingProduct ? 'Редактирование товара' : 'Новый товар') . ' — админ-панель Bunch',
            'description' => 'Создание и редактирование карточек товара.',
            'h1' => $editingProduct ? 'Редактирование товара' : 'Новый товар',
            'headerTitle' => 'Bunch Admin',
            'headerSubtitle' => 'Каталог · Товары',
        ];

        $supplyModel = new Supply();
        $attributeModel = new AttributeModel();

        $selectedSupplyId = isset($_GET['create_from_supply']) ? (int) $_GET['create_from_supply'] : null;
        if (!$selectedSupplyId && isset($_GET['supply_id'])) {
            $selectedSupplyId = (int) $_GET['supply_id'];
        }

        $this->render('admin-product-form', [
            'pageMeta' => $pageMeta,
            'supplies' => $supplyModel->getAdminList(),
            'attributes' => $attributeModel->getAllWithValues(),
            'editingProduct' => $editingProduct,
            'productRelations' => $productRelations,
            'selectedSupplyId' => $selectedSupplyId,
            'message' => $_GET['status'] ?? null,
        ]);
    }

    public function saveProduct(): void
    {
        $productId = (int) ($_POST['product_id'] ?? 0);
        $supplyId = (int) ($_POST['supply_id'] ?? 0);
        $article = trim($_POST['article'] ?? '');
        $altName = trim($_POST['alt_name'] ?? '');
        $photoUrl = trim($_POST['photo_url'] ?? '');
        $photoSecondaryUrl = trim($_POST['photo_url_secondary'] ?? '');
        $photoTertiaryUrl = trim($_POST['photo_url_tertiary'] ?? '');
        $deletePhoto = ($_POST['photo_delete'] ?? '') === '1';
        $deletePhotoSecondary = ($_POST['photo_delete_secondary'] ?? '') === '1';
        $deletePhotoTertiary = ($_POST['photo_delete_tertiary'] ?? '') === '1';
        $price = (int) floor((float) ($_POST['price'] ?? 0));
        $active = isset($_POST['is_active']) ? 1 : 0;
        $category = trim($_POST['category'] ?? 'main');
        $productType = trim($_POST['product_type'] ?? 'regular');
        $categoryOptions = ['main', 'wholesale', 'accessory'];
        if (!in_array($category, $categoryOptions, true)) {
            $category = 'main';
        }
        $productTypeOptions = ['regular', 'small_wholesale', 'wholesale_box'];
        if (!in_array($productType, $productTypeOptions, true)) {
            $productType = 'regular';
        }

        $uploadedPhoto = $this->handlePhotoUpload('photo_file', 'product');
        $uploadedSecondaryPhoto = $this->handlePhotoUpload('photo_file_secondary', 'product');
        $uploadedTertiaryPhoto = $this->handlePhotoUpload('photo_file_tertiary', 'product');
        if ($uploadedPhoto) {
            $photoUrl = $uploadedPhoto;
        } elseif ($deletePhoto) {
            $photoUrl = '';
        }
        if ($uploadedSecondaryPhoto) {
            $photoSecondaryUrl = $uploadedSecondaryPhoto;
        } elseif ($deletePhotoSecondary) {
            $photoSecondaryUrl = '';
        }
        if ($uploadedTertiaryPhoto) {
            $photoTertiaryUrl = $uploadedTertiaryPhoto;
        } elseif ($deletePhotoTertiary) {
            $photoTertiaryUrl = '';
        }

        $tierQty = $_POST['tier_min_qty'] ?? [];
        $tierPrice = $_POST['tier_price'] ?? [];
        $attributeIds = array_filter(array_map('intval', $_POST['attribute_ids'] ?? []));

        $supplyModel = new Supply();
        $supply = $supplyModel->findById($supplyId);

        if (!$supply || $price <= 0) {
            $redirect = '/?page=admin-product-form&status=error';
            if ($productId > 0) {
                $redirect .= '&edit_id=' . $productId;
            } elseif ($supplyId > 0) {
                $redirect .= '&supply_id=' . $supplyId;
            }
            header('Location: ' . $redirect);
            return;
        }

        $name = trim($supply['flower_name'] . ' ' . $supply['variety']);
        $description = sprintf(
            'Страна: %s. Высота стебля: %s см. Вес стебля: %s г.',
            $supply['country'] ?? '—',
            $supply['stem_height_cm'] ?? '—',
            $supply['stem_weight_g'] ?? '—'
        );

        $payload = [
            'supply_id' => $supplyId,
            'name' => $name,
            'alt_name' => $altName !== '' ? $altName : null,
            'description' => $description,
            'price' => $price,
            'article' => $article !== '' ? $article : null,
            'photo_url' => $photoUrl !== '' ? $photoUrl : null,
            'photo_url_secondary' => $photoSecondaryUrl !== '' ? $photoSecondaryUrl : null,
            'photo_url_tertiary' => $photoTertiaryUrl !== '' ? $photoTertiaryUrl : null,
            'stem_height_cm' => $supply['stem_height_cm'] ?? null,
            'stem_weight_g' => $supply['stem_weight_g'] ?? null,
            'country' => $supply['country'] ?? null,
            'category' => $category !== '' ? $category : 'main',
            'product_type' => $productType,
            'is_base' => 0,
            'is_active' => $active,
            'sort_order' => 0,
        ];

        $productModel = new Product();

        if ($productId > 0) {
            $productModel->updateProduct($productId, $payload);
        } else {
            $productId = $productModel->createFromSupply($payload);
        }

        $tiers = [];
        foreach ($tierQty as $index => $qty) {
            $minQty = (int) $qty;
            $priceValue = isset($tierPrice[$index]) ? (int) floor((float) $tierPrice[$index]) : 0;

            if ($minQty > 0 && $priceValue > 0) {
                $tiers[] = ['min_qty' => $minQty, 'price' => $priceValue];
            }
        }

        $productModel->setPriceTiers($productId, $tiers);
        $productModel->setAttributes($productId, $attributeIds);
        $this->syncSupplyCardStatus($supplyId, $productType, $active);

        header('Location: /?page=admin-product-form&status=saved&edit_id=' . $productId);
    }

    public function deleteProduct(): void
    {
        $productId = (int) ($_POST['product_id'] ?? 0);

        if ($productId <= 0) {
            header('Location: /?page=admin-products&status=error');
            return;
        }

        $productModel = new Product();
        if ($productModel->hasBlockingRelations($productId)) {
            header('Location: /?page=admin-products&status=delete-blocked&product_id=' . $productId);
            return;
        }

        try {
            $productModel->markDeleted($productId);
        } catch (\PDOException $exception) {
            header('Location: /?page=admin-products&status=delete-blocked&product_id=' . $productId);
            return;
        }

        header('Location: /?page=admin-products&status=deleted');
    }

    public function toggleProductActive(): void
    {
        $productId = (int) ($_POST['product_id'] ?? 0);

        if ($productId <= 0) {
            header('Location: /?page=admin-products&status=error');
            return;
        }

        $active = isset($_POST['is_active']) ? 1 : 0;

        $productModel = new Product();
        $productModel->setActive($productId, $active);
        $product = $productModel->getById($productId);
        if ($product && !empty($product['supply_id'])) {
            $this->syncSupplyCardStatus((int) $product['supply_id'], $product['product_type'] ?? 'regular', $active);
        }

        header('Location: /?page=admin-products');
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

        $auctionModel = new AuctionLot();
        $promoItemModel = new PromoItem();
        $lotteryModel = new Lottery();
        $settings = new Setting();
        $lotteryDefaults = $settings->getLotteryDefaults();

        $loadErrors = [];

        try {
            $auctions = $auctionModel->getAdminList();
        } catch (Throwable $e) {
            $auctions = [];
            $loadErrors[] = 'auctions';
            $this->logAdminError('Admin promos load error (auctions)', $e);
        }

        $activeLots = array_values(array_filter($auctions, static function (array $lot): bool {
            return ($lot['status'] ?? '') === 'active';
        }));
        $finishedLots = array_values(array_filter($auctions, static function (array $lot): bool {
            return ($lot['status'] ?? '') === 'finished';
        }));

        $this->render('admin-promos', [
            'pageMeta' => $pageMeta,
            'activeLots' => $activeLots,
            'finishedLots' => $finishedLots,
            'promoItems' => $promoItemModel->getAdminList(),
            'lotteries' => $lotteryModel->getAdminList(),
            'loadErrors' => $loadErrors,
            'message' => $_GET['status'] ?? null,
            'lotterySettings' => [
                'freeMonthlyLimit' => (int) $settings->get(
                    Setting::LOTTERY_FREE_MONTHLY_LIMIT,
                    $lotteryDefaults[Setting::LOTTERY_FREE_MONTHLY_LIMIT] ?? '0'
                ),
            ],
        ]);
    }

    public function auctionCreate(): void
    {
        $pageMeta = [
            'title' => 'Новый лот — админ-панель Bunch',
            'description' => 'Создание аукционного лота для акций.',
            'h1' => 'Новый лот',
            'headerTitle' => 'Bunch Admin',
            'headerSubtitle' => 'Акции · Новый лот',
        ];

        $this->render('admin-auction-create', [
            'pageMeta' => $pageMeta,
            'message' => $_GET['status'] ?? null,
        ]);
    }

    public function promoItemCreate(): void
    {
        $pageMeta = [
            'title' => 'Лимитированный товар — админ-панель Bunch',
            'description' => 'Создание товара по акции.',
            'h1' => 'Лимитированный товар',
            'headerTitle' => 'Bunch Admin',
            'headerSubtitle' => 'Акции · Лимитированный товар',
        ];

        $this->render('admin-promo-item-create', [
            'pageMeta' => $pageMeta,
            'message' => $_GET['status'] ?? null,
        ]);
    }

    public function promoItemEdit(): void
    {
        $promoId = (int) ($_GET['id'] ?? 0);
        if ($promoId <= 0) {
            header('Location: /?page=admin-promos&status=error');
            return;
        }

        $promoItemModel = new PromoItem();
        $promoItem = $promoItemModel->getById($promoId);
        if (!$promoItem) {
            header('Location: /?page=admin-promos&status=error');
            return;
        }

        $pageMeta = [
            'title' => 'Редактирование акции — админ-панель Bunch',
            'description' => 'Редактирование лимитированного товара.',
            'h1' => 'Редактирование акции',
            'headerTitle' => 'Bunch Admin',
            'headerSubtitle' => 'Акции · Редактирование',
        ];

        $this->render('admin-promo-item-edit', [
            'pageMeta' => $pageMeta,
            'promoItem' => $promoItem,
            'message' => $_GET['status'] ?? null,
        ]);
    }

    public function lotteryCreate(): void
    {
        $pageMeta = [
            'title' => 'Розыгрыш — админ-панель Bunch',
            'description' => 'Создание товара для розыгрыша.',
            'h1' => 'Розыгрыш',
            'headerTitle' => 'Bunch Admin',
            'headerSubtitle' => 'Акции · Розыгрыш',
        ];

        $settings = new Setting();
        $lotteryDefaults = $settings->getLotteryDefaults();

        $this->render('admin-lottery-create', [
            'pageMeta' => $pageMeta,
            'message' => $_GET['status'] ?? null,
            'lotterySettings' => [
                'freeMonthlyLimit' => (int) $settings->get(
                    Setting::LOTTERY_FREE_MONTHLY_LIMIT,
                    $lotteryDefaults[Setting::LOTTERY_FREE_MONTHLY_LIMIT] ?? '0'
                ),
            ],
        ]);
    }

    public function lotteryEdit(): void
    {
        $lotteryId = (int) ($_GET['id'] ?? 0);
        if ($lotteryId <= 0) {
            header('Location: /?page=admin-promos&status=error');
            return;
        }

        $lotteryModel = new Lottery();
        $lottery = $lotteryModel->getById($lotteryId);
        if (!$lottery) {
            header('Location: /?page=admin-promos&status=error');
            return;
        }

        $settings = new Setting();
        $lotteryDefaults = $settings->getLotteryDefaults();

        $pageMeta = [
            'title' => 'Редактирование розыгрыша — админ-панель Bunch',
            'description' => 'Редактирование розыгрыша.',
            'h1' => 'Редактирование розыгрыша',
            'headerTitle' => 'Bunch Admin',
            'headerSubtitle' => 'Акции · Редактирование',
        ];

        $this->render('admin-lottery-edit', [
            'pageMeta' => $pageMeta,
            'lottery' => $lottery,
            'message' => $_GET['status'] ?? null,
            'lotterySettings' => [
                'freeMonthlyLimit' => (int) $settings->get(
                    Setting::LOTTERY_FREE_MONTHLY_LIMIT,
                    $lotteryDefaults[Setting::LOTTERY_FREE_MONTHLY_LIMIT] ?? '0'
                ),
            ],
        ]);
    }

    public function auctionEdit(): void
    {
        $lotId = (int) ($_GET['id'] ?? 0);
        if ($lotId <= 0) {
            header('Location: /?page=admin-promos&status=error');
            return;
        }

        $auctionModel = new AuctionLot();
        $lot = $auctionModel->getAdminLotDetails($lotId);

        if (!$lot) {
            header('Location: /?page=admin-promos&status=error');
            return;
        }

        $pageMeta = [
            'title' => 'Редактирование лота — админ-панель Bunch',
            'description' => 'Редактирование аукционного лота.',
            'h1' => 'Редактирование лота',
            'headerTitle' => 'Bunch Admin',
            'headerSubtitle' => 'Акции · Редактирование',
        ];

        $this->render('admin-auction-edit', [
            'pageMeta' => $pageMeta,
            'lot' => $lot,
            'message' => $_GET['status'] ?? null,
        ]);
    }

    public function auctionView(): void
    {
        $lotId = (int) ($_GET['id'] ?? 0);
        if ($lotId <= 0) {
            header('Location: /?page=admin-promos&status=error');
            return;
        }

        $auctionModel = new AuctionLot();
        $lot = $auctionModel->getAdminLotDetails($lotId);

        if (!$lot) {
            header('Location: /?page=admin-promos&status=error');
            return;
        }

        $bidModel = new AuctionBid();
        $bids = $bidModel->getLotBids($lotId);

        $pageMeta = [
            'title' => 'Просмотр лота — админ-панель Bunch',
            'description' => 'Детали завершённого аукциона.',
            'h1' => 'Завершённый лот',
            'headerTitle' => 'Bunch Admin',
            'headerSubtitle' => 'Акции · История лота',
        ];

        $this->render('admin-auction-view', [
            'pageMeta' => $pageMeta,
            'lot' => $lot,
            'bids' => $bids,
        ]);
    }

    public function saveLottery(): void
    {
        $title = trim($_POST['title'] ?? '');
        $prize = trim($_POST['prize_description'] ?? '');
        $ticketPrice = (int) floor((float) ($_POST['ticket_price'] ?? 0));
        $ticketsTotal = (int) ($_POST['tickets_total'] ?? 0);
        $drawAt = trim($_POST['draw_at'] ?? '');
        $status = $_POST['status'] ?? 'active';
        $photo = trim($_POST['photo_url'] ?? '');

        if ($title === '' || $ticketsTotal <= 0) {
            header('Location: /?page=admin-promos&status=error');
            return;
        }

        $lotteryModel = new Lottery();

        try {
            $lotteryModel->createLottery([
                'title' => $title,
                'prize_description' => $prize !== '' ? $prize : null,
                'ticket_price' => $ticketPrice,
                'tickets_total' => $ticketsTotal,
                'draw_at' => $drawAt !== '' ? $drawAt : null,
                'status' => $status,
                'photo_url' => $photo !== '' ? $photo : null,
            ]);
        } catch (Throwable $e) {
            $this->logAdminError('Admin promos save lottery error', $e);
            header('Location: /?page=admin-promos&status=error');
            return;
        }

        header('Location: /?page=admin-promos&status=saved');
    }

    public function updateLottery(): void
    {
        $lotteryId = (int) ($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $prize = trim($_POST['prize_description'] ?? '');
        $ticketPrice = (int) floor((float) ($_POST['ticket_price'] ?? 0));
        $ticketsTotal = (int) ($_POST['tickets_total'] ?? 0);
        $drawAt = trim($_POST['draw_at'] ?? '');
        $status = $_POST['status'] ?? 'active';
        $photo = trim($_POST['photo_url'] ?? '');

        if ($lotteryId <= 0 || $title === '' || $ticketsTotal <= 0) {
            header('Location: /?page=admin-promos&status=error');
            return;
        }

        $lotteryModel = new Lottery();

        try {
            $lotteryModel->updateLottery($lotteryId, [
                'title' => $title,
                'prize_description' => $prize !== '' ? $prize : null,
                'ticket_price' => $ticketPrice,
                'tickets_total' => $ticketsTotal,
                'draw_at' => $drawAt !== '' ? $drawAt : null,
                'status' => $status,
                'photo_url' => $photo !== '' ? $photo : null,
            ]);
        } catch (Throwable $e) {
            $this->logAdminError('Admin promos update lottery error', $e);
            header('Location: /?page=admin-promos&status=error');
            return;
        }

        header('Location: /?page=admin-lottery-edit&id=' . $lotteryId . '&status=saved');
    }

    public function saveAuctionLot(): void
    {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $image = trim($_POST['image'] ?? '');
        $storePrice = (int) floor((float) ($_POST['store_price'] ?? 0));
        $startPrice = (int) floor((float) ($_POST['start_price'] ?? 1));
        $bidStep = (int) floor((float) ($_POST['bid_step'] ?? 1));
        $blitzPrice = trim($_POST['blitz_price'] ?? '');
        $startsAt = trim($_POST['starts_at'] ?? '');
        $endsAt = trim($_POST['ends_at'] ?? '');
        $status = $_POST['status'] ?? 'draft';

        if ($title === '' || $bidStep <= 0 || $startPrice <= 0) {
            header('Location: /?page=admin-promos&status=error');
            return;
        }

        $auctionModel = new AuctionLot();

        try {
            $auctionModel->createLot([
                'title' => $title,
                'description' => $description !== '' ? $description : null,
                'image' => $image !== '' ? $image : null,
                'store_price' => $storePrice,
                'start_price' => $startPrice,
                'bid_step' => $bidStep,
                'blitz_price' => $blitzPrice !== '' ? (int) floor((float) $blitzPrice) : null,
                'starts_at' => $startsAt !== '' ? $startsAt : null,
                'ends_at' => $endsAt !== '' ? $endsAt : null,
                'status' => $status,
            ]);
        } catch (Throwable $e) {
            $this->logAdminError('Admin promos save auction error', $e);
            header('Location: /?page=admin-promos&status=error');
            return;
        }

        header('Location: /?page=admin-promos&status=saved');
    }

    public function updateAuctionLot(): void
    {
        $lotId = (int) ($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $image = trim($_POST['image'] ?? '');
        $storePrice = (int) floor((float) ($_POST['store_price'] ?? 0));
        $startPrice = (int) floor((float) ($_POST['start_price'] ?? 1));
        $bidStep = (int) floor((float) ($_POST['bid_step'] ?? 1));
        $blitzPrice = trim($_POST['blitz_price'] ?? '');
        $startsAt = trim($_POST['starts_at'] ?? '');
        $endsAt = trim($_POST['ends_at'] ?? '');
        $status = $_POST['status'] ?? 'draft';

        if ($lotId <= 0 || $title === '' || $bidStep <= 0 || $startPrice <= 0) {
            header('Location: /?page=admin-promos&status=error');
            return;
        }

        $auctionModel = new AuctionLot();
        $existing = $auctionModel->getAdminLotDetails($lotId);
        if (!$existing) {
            header('Location: /?page=admin-promos&status=error');
            return;
        }

        try {
            $auctionModel->updateLot($lotId, [
                'title' => $title,
                'description' => $description !== '' ? $description : null,
                'image' => $image !== '' ? $image : null,
                'store_price' => $storePrice,
                'start_price' => $startPrice,
                'bid_step' => $bidStep,
                'blitz_price' => $blitzPrice !== '' ? (int) floor((float) $blitzPrice) : null,
                'starts_at' => $startsAt !== '' ? $startsAt : null,
                'ends_at' => $endsAt !== '' ? $endsAt : null,
                'original_ends_at' => $existing['original_ends_at'],
                'status' => $status,
            ]);
        } catch (Throwable $e) {
            $this->logAdminError('Admin promos update auction error', $e);
            header('Location: /?page=admin-promos&status=error');
            return;
        }

        header('Location: /?page=admin-auction-edit&id=' . $lotId . '&status=saved');
    }

    public function savePromoItem(): void
    {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $basePrice = (int) floor((float) ($_POST['base_price'] ?? 0));
        $price = (int) floor((float) ($_POST['price'] ?? 0));
        $quantityRaw = trim($_POST['quantity'] ?? '');
        $endsAt = trim($_POST['ends_at'] ?? '');
        $label = trim($_POST['label'] ?? '');
        $photoUrl = trim($_POST['photo_url'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($title === '' || $price <= 0 || $basePrice <= 0) {
            header('Location: /?page=admin-promos&status=error');
            return;
        }

        $quantity = $quantityRaw !== '' ? max(1, (int) $quantityRaw) : null;

        $promoItemModel = new PromoItem();
        $productModel = new Product();

        try {
            $productId = $productModel->createCustom([
                'name' => $title,
                'alt_name' => null,
                'description' => $description !== '' ? $description : null,
                'price' => $price,
                'article' => null,
                'photo_url' => $photoUrl !== '' ? $photoUrl : null,
                'photo_url_secondary' => null,
                'photo_url_tertiary' => null,
                'category' => 'main',
                'product_type' => 'promo',
                'is_base' => 0,
                'is_active' => $isActive,
                'sort_order' => 0,
            ]);

            $promoItemModel->create([
                'product_id' => $productId,
                'title' => $title,
                'description' => $description !== '' ? $description : null,
                'base_price' => $basePrice,
                'price' => $price,
                'quantity' => $quantity,
                'ends_at' => $endsAt !== '' ? $endsAt : null,
                'label' => $label !== '' ? $label : null,
                'photo_url' => $photoUrl !== '' ? $photoUrl : null,
                'is_active' => $isActive,
            ]);
        } catch (Throwable $e) {
            $this->logAdminError('Admin promos save promo item error', $e);
            header('Location: /?page=admin-promos&status=error');
            return;
        }

        header('Location: /?page=admin-promos&status=saved');
    }

    public function updatePromoItem(): void
    {
        $promoId = (int) ($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $basePrice = (int) floor((float) ($_POST['base_price'] ?? 0));
        $price = (int) floor((float) ($_POST['price'] ?? 0));
        $quantityRaw = trim($_POST['quantity'] ?? '');
        $endsAt = trim($_POST['ends_at'] ?? '');
        $label = trim($_POST['label'] ?? '');
        $photoUrl = trim($_POST['photo_url'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($promoId <= 0 || $title === '' || $price <= 0 || $basePrice <= 0) {
            header('Location: /?page=admin-promos&status=error');
            return;
        }

        $quantity = $quantityRaw !== '' ? max(1, (int) $quantityRaw) : null;

        $promoItemModel = new PromoItem();
        $productModel = new Product();
        $promoItem = $promoItemModel->getById($promoId);
        if (!$promoItem) {
            header('Location: /?page=admin-promos&status=error');
            return;
        }

        try {
            if (!empty($promoItem['product_id'])) {
                $productModel->updateCustom((int) $promoItem['product_id'], [
                    'name' => $title,
                    'alt_name' => null,
                    'description' => $description !== '' ? $description : null,
                    'price' => $price,
                    'photo_url' => $photoUrl !== '' ? $photoUrl : null,
                    'photo_url_secondary' => null,
                    'photo_url_tertiary' => null,
                    'category' => 'main',
                    'product_type' => 'promo',
                    'is_active' => $isActive,
                ]);
            }

            $promoItemModel->updateItem($promoId, [
                'title' => $title,
                'description' => $description !== '' ? $description : null,
                'base_price' => $basePrice,
                'price' => $price,
                'quantity' => $quantity,
                'ends_at' => $endsAt !== '' ? $endsAt : null,
                'label' => $label !== '' ? $label : null,
                'photo_url' => $photoUrl !== '' ? $photoUrl : null,
                'is_active' => $isActive,
            ]);
        } catch (Throwable $e) {
            $this->logAdminError('Admin promos update promo item error', $e);
            header('Location: /?page=admin-promos&status=error');
            return;
        }

        header('Location: /?page=admin-promo-item-edit&id=' . $promoId . '&status=saved');
    }

    public function savePromoCategories(): void
    {
        $categories = $_POST['categories'] ?? [];

        $promoCategoryModel = new PromoCategory();

        try {
            $statusMap = [];
            foreach ($categories as $code => $value) {
                $statusMap[$code] = 1;
            }
            $promoCategoryModel->updateStatuses($statusMap);
        } catch (Throwable $e) {
            $this->logAdminError('Admin promos save promo categories error', $e);
            header('Location: /?page=admin-promos&status=error');
            return;
        }

        header('Location: /?page=admin-promos&status=saved');
    }

    public function savePromoSettings(): void
    {
        $limitRaw = trim($_POST['free_lottery_monthly_limit'] ?? '');
        $limit = $limitRaw !== '' ? max(0, (int) $limitRaw) : 0;

        $settings = new Setting();
        $settings->set(Setting::LOTTERY_FREE_MONTHLY_LIMIT, (string) $limit);

        header('Location: /?page=admin-lottery-create&status=saved#promo-settings');
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

        $attributeModel = new AttributeModel();
        $attributes = $attributeModel->getAllWithValues();

        $this->render('admin-attributes', [
            'pageMeta' => $pageMeta,
            'attributes' => $attributes,
            'message' => $_GET['status'] ?? null,
        ]);
    }

    public function saveAttribute(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $type = $_POST['type'] ?? 'selector';
        $appliesTo = $_POST['applies_to'] ?? 'stem';
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($name === '') {
            header('Location: /?page=admin-attributes&status=error');
            return;
        }

        $attributeModel = new AttributeModel();
        $attributeModel->save([
            'id' => $id,
            'name' => $name,
            'description' => $description !== '' ? $description : null,
            'type' => $type,
            'applies_to' => $appliesTo === 'bouquet' ? 'bouquet' : 'stem',
            'is_active' => $isActive,
        ]);

        header('Location: /?page=admin-attributes&status=saved');
    }

    public function deleteAttribute(): void
    {
        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            header('Location: /?page=admin-attributes&status=error');
            return;
        }

        $attributeModel = new AttributeModel();
        $attributeModel->delete($id);

        header('Location: /?page=admin-attributes&status=deleted');
    }

    public function saveAttributeValue(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $attributeId = (int) ($_POST['attribute_id'] ?? 0);
        $value = trim($_POST['value'] ?? '');
        $priceDelta = (int) floor((float) ($_POST['price_delta'] ?? 0));
        $photoUrl = trim($_POST['photo_url'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);

        $uploadedPhoto = $this->handlePhotoUpload('photo_file', 'attribute');
        if ($uploadedPhoto) {
            $photoUrl = $uploadedPhoto;
        }

        if ($attributeId <= 0 || $value === '') {
            header('Location: /?page=admin-attributes&status=error');
            return;
        }

        $attributeModel = new AttributeModel();
        $attributeModel->saveValue([
            'id' => $id,
            'attribute_id' => $attributeId,
            'value' => $value,
            'price_delta' => $priceDelta,
            'photo_url' => $photoUrl !== '' ? $photoUrl : null,
            'is_active' => $isActive,
            'sort_order' => $sortOrder,
        ]);

        header('Location: /?page=admin-attributes&status=saved#attribute-' . $attributeId);
    }

    public function deleteAttributeValue(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $attributeId = (int) ($_POST['attribute_id'] ?? 0);

        if ($id <= 0) {
            header('Location: /?page=admin-attributes&status=error');
            return;
        }

        $attributeModel = new AttributeModel();
        $attributeModel->deleteValue($id);

        $anchor = $attributeId > 0 ? '#attribute-' . $attributeId : '';
        header('Location: /?page=admin-attributes&status=deleted' . $anchor);
    }

    private function handlePhotoUpload(string $fieldName, string $prefix): ?string
    {
        if (empty($_FILES[$fieldName])) {
            return null;
        }

        $uploader = new ImageUploader();

        return $uploader->upload($_FILES[$fieldName], $prefix);
    }

    public function catalogSupplies(): void
    {
        $pageMeta = [
            'title' => 'Поставки — админ-панель Bunch',
            'description' => 'Планирование поставок, создание карточек товаров и мелкого опта.',
            'h1' => 'Поставки',
            'headerTitle' => 'Bunch Admin',
            'headerSubtitle' => 'Каталог · Поставки и брони',
        ];

        $supplyModel = new Supply();
        $supplies = $supplyModel->getAdminList();

        $this->render('admin-supplies', [
            'pageMeta' => $pageMeta,
            'supplies' => $supplies,
            'message' => $_GET['status'] ?? null,
        ]);
    }

    public function supplyStandingForm(): void
    {
        $pageMeta = [
            'title' => 'Добавить стендинг — админ-панель Bunch',
            'description' => 'Создание стендинговых поставок с расчётом ближайшей даты.',
            'h1' => 'Добавить стендинг',
            'headerTitle' => 'Bunch Admin',
            'headerSubtitle' => 'Каталог · Поставки и брони',
        ];

        $this->render('admin-supply-standing', [
            'pageMeta' => $pageMeta,
            'message' => $_GET['status'] ?? null,
        ]);
    }

    public function supplySingleForm(): void
    {
        $pageMeta = [
            'title' => 'Разовая поставка — админ-панель Bunch',
            'description' => 'Создание единичных поставок с указанием дат и параметров.',
            'h1' => 'Разовая поставка',
            'headerTitle' => 'Bunch Admin',
            'headerSubtitle' => 'Каталог · Поставки и брони',
        ];

        $this->render('admin-supply-single', [
            'pageMeta' => $pageMeta,
            'message' => $_GET['status'] ?? null,
        ]);
    }

    public function editSupply(): void
    {
        $supplyId = (int) ($_GET['id'] ?? 0);

        if ($supplyId <= 0) {
            header('Location: /?page=admin-supplies&status=notfound');
            return;
        }

        $supplyModel = new Supply();
        $supply = $supplyModel->findById($supplyId);

        if (!$supply) {
            header('Location: /?page=admin-supplies&status=notfound');
            return;
        }

        $pageMeta = [
            'title' => 'Редактировать поставку — админ-панель Bunch',
            'description' => 'Обновление параметров и расписания поставки.',
            'h1' => 'Редактирование поставки',
            'headerTitle' => 'Bunch Admin',
            'headerSubtitle' => 'Каталог · Поставки и брони',
        ];

        $this->render('admin-supply-edit', [
            'pageMeta' => $pageMeta,
            'supply' => $supply,
            'message' => $_GET['status'] ?? null,
        ]);
    }

    public function createStandingSupply(): void
    {
        $firstDelivery = trim($_POST['first_delivery_date'] ?? '');
        $actualDelivery = trim($_POST['actual_delivery_date'] ?? '');
        $skipDate = trim($_POST['skip_date'] ?? '');
        $boxesTotal = (int) ($_POST['boxes_total'] ?? 0);
        $packsPerBox = (int) ($_POST['packs_per_box'] ?? 0);
        $packsTotal = $boxesTotal > 0 && $packsPerBox > 0 ? $boxesTotal * $packsPerBox : 0;
        $budSize = (int) ($_POST['bud_size_cm'] ?? 0);
        $description = trim($_POST['description'] ?? '');

        $payload = [
            'photo_url' => trim($_POST['photo_url'] ?? ''),
            'flower_name' => trim($_POST['flower_name'] ?? ''),
            'variety' => trim($_POST['variety'] ?? ''),
            'country' => trim($_POST['country'] ?? ''),
            'boxes_total' => $boxesTotal,
            'packs_per_box' => $packsPerBox,
            'packs_total' => $packsTotal,
            'stems_per_pack' => (int) ($_POST['stems_per_pack'] ?? 0),
            'stem_height_cm' => (int) ($_POST['stem_height_cm'] ?? 0),
            'stem_weight_g' => (int) ($_POST['stem_weight_g'] ?? 0),
            'bud_size_cm' => $budSize > 0 ? $budSize : null,
            'description' => $description !== '' ? $description : null,
            'periodicity' => $_POST['periodicity'] === 'biweekly' ? 'biweekly' : 'weekly',
            'first_delivery_date' => $firstDelivery !== '' ? $firstDelivery : null,
            'planned_delivery_date' => $firstDelivery !== '' ? $firstDelivery : null,
            'actual_delivery_date' => $actualDelivery !== '' ? $actualDelivery : null,
            'allow_small_wholesale' => isset($_POST['allow_small_wholesale']) ? 1 : 0,
            'allow_box_order' => isset($_POST['allow_box_order']) ? 1 : 0,
            'skip_date' => $skipDate !== '' ? $skipDate : null,
            'packs_reserved' => 0,
        ];

        $uploadedPhoto = $this->handlePhotoUpload('photo_file_standing', 'supply');
        if ($uploadedPhoto) {
            $payload['photo_url'] = $uploadedPhoto;
        }

        if ($payload['flower_name'] === '' || $payload['variety'] === '' || !$payload['boxes_total'] || !$payload['packs_per_box'] || !$payload['stems_per_pack'] || empty($payload['first_delivery_date'])) {
            header('Location: /?page=admin-supply-standing&status=error');
            return;
        }

        $supplyModel = new Supply();
        $supplyId = $supplyModel->createStanding($payload);
        $this->createSupplyProducts($supplyId);

        header('Location: /?page=admin-supplies&status=created');
    }

    public function createSingleSupply(): void
    {
        $plannedDelivery = trim($_POST['planned_delivery_date'] ?? '');
        $actualDelivery = trim($_POST['actual_delivery_date'] ?? '');
        $boxesTotal = (int) ($_POST['boxes_total'] ?? 0);
        $packsPerBox = (int) ($_POST['packs_per_box'] ?? 0);
        $packsTotal = $boxesTotal > 0 && $packsPerBox > 0 ? $boxesTotal * $packsPerBox : 0;
        $budSize = (int) ($_POST['bud_size_cm'] ?? 0);
        $description = trim($_POST['description'] ?? '');

        $payload = [
            'photo_url' => trim($_POST['photo_url'] ?? ''),
            'flower_name' => trim($_POST['flower_name'] ?? ''),
            'variety' => trim($_POST['variety'] ?? ''),
            'country' => trim($_POST['country'] ?? ''),
            'boxes_total' => $boxesTotal,
            'packs_per_box' => $packsPerBox,
            'packs_total' => $packsTotal,
            'stems_per_pack' => (int) ($_POST['stems_per_pack'] ?? 0),
            'stem_height_cm' => (int) ($_POST['stem_height_cm'] ?? 0),
            'stem_weight_g' => (int) ($_POST['stem_weight_g'] ?? 0),
            'bud_size_cm' => $budSize > 0 ? $budSize : null,
            'description' => $description !== '' ? $description : null,
            'planned_delivery_date' => $plannedDelivery !== '' ? $plannedDelivery : null,
            'actual_delivery_date' => $actualDelivery !== '' ? $actualDelivery : null,
            'allow_small_wholesale' => isset($_POST['allow_small_wholesale']) ? 1 : 0,
            'allow_box_order' => isset($_POST['allow_box_order']) ? 1 : 0,
            'packs_reserved' => 0,
        ];

        $uploadedPhoto = $this->handlePhotoUpload('photo_file_single', 'supply');
        if ($uploadedPhoto) {
            $payload['photo_url'] = $uploadedPhoto;
        }

        if ($payload['flower_name'] === '' || $payload['variety'] === '' || !$payload['boxes_total'] || !$payload['packs_per_box'] || !$payload['stems_per_pack'] || empty($payload['planned_delivery_date'])) {
            header('Location: /?page=admin-supply-single&status=error');
            return;
        }

        $supplyModel = new Supply();
        $supplyId = $supplyModel->createOneTime($payload);
        $this->createSupplyProducts($supplyId);

        header('Location: /?page=admin-supplies&status=created');
    }

    public function updateSupply(): void
    {
        $supplyId = (int) ($_POST['supply_id'] ?? 0);

        $supplyModel = new Supply();
        $existing = $supplyModel->findById($supplyId);

        if (!$existing) {
            header('Location: /?page=admin-supplies&status=notfound');
            return;
        }

        $photoUrl = trim($_POST['photo_url'] ?? '');
        $uploadedPhoto = $this->handlePhotoUpload('photo_file', 'supply');
        if ($uploadedPhoto) {
            $photoUrl = $uploadedPhoto;
        }

        $boxesTotal = (int) ($_POST['boxes_total'] ?? 0);
        $packsPerBox = (int) ($_POST['packs_per_box'] ?? 0);
        $packsTotal = $boxesTotal > 0 && $packsPerBox > 0 ? $boxesTotal * $packsPerBox : 0;
        $budSize = (int) ($_POST['bud_size_cm'] ?? 0);
        $description = trim($_POST['description'] ?? '');

        $common = [
            'photo_url' => $photoUrl,
            'flower_name' => trim($_POST['flower_name'] ?? ''),
            'variety' => trim($_POST['variety'] ?? ''),
            'country' => trim($_POST['country'] ?? ''),
            'boxes_total' => $boxesTotal,
            'packs_per_box' => $packsPerBox,
            'packs_total' => $packsTotal,
            'stems_per_pack' => (int) ($_POST['stems_per_pack'] ?? 0),
            'stem_height_cm' => (int) ($_POST['stem_height_cm'] ?? 0),
            'stem_weight_g' => (int) ($_POST['stem_weight_g'] ?? 0),
            'bud_size_cm' => $budSize > 0 ? $budSize : null,
            'description' => $description !== '' ? $description : null,
            'allow_small_wholesale' => isset($_POST['allow_small_wholesale']) ? 1 : 0,
            'allow_box_order' => isset($_POST['allow_box_order']) ? 1 : 0,
        ];

        if ($existing['is_standing']) {
            $firstDelivery = trim($_POST['first_delivery_date'] ?? '');
            $actualDelivery = trim($_POST['actual_delivery_date'] ?? '');
            $skipDate = trim($_POST['skip_date'] ?? '');
            $periodicity = $_POST['periodicity'] === 'biweekly' ? 'biweekly' : 'weekly';

            $payload = array_merge($common, [
                'periodicity' => $periodicity,
                'first_delivery_date' => $firstDelivery !== '' ? $firstDelivery : null,
                'planned_delivery_date' => $firstDelivery !== '' ? $firstDelivery : null,
                'actual_delivery_date' => $actualDelivery !== '' ? $actualDelivery : null,
                'skip_date' => $skipDate !== '' ? $skipDate : null,
            ]);

            if ($payload['flower_name'] === '' || $payload['variety'] === '' || !$payload['boxes_total'] || !$payload['packs_per_box'] || !$payload['stems_per_pack'] || empty($payload['first_delivery_date'])) {
                header('Location: /?page=admin-supply-edit&id=' . $supplyId . '&status=error');
                return;
            }

            $supplyModel->updateStanding($supplyId, $payload);
        } else {
            $plannedDelivery = trim($_POST['planned_delivery_date'] ?? '');
            $actualDelivery = trim($_POST['actual_delivery_date'] ?? '');

            $payload = array_merge($common, [
                'planned_delivery_date' => $plannedDelivery !== '' ? $plannedDelivery : null,
                'actual_delivery_date' => $actualDelivery !== '' ? $actualDelivery : null,
            ]);

            if ($payload['flower_name'] === '' || $payload['variety'] === '' || !$payload['boxes_total'] || !$payload['packs_per_box'] || !$payload['stems_per_pack'] || empty($payload['planned_delivery_date'])) {
                header('Location: /?page=admin-supply-edit&id=' . $supplyId . '&status=error');
                return;
            }

            $supplyModel->updateOneTime($supplyId, $payload);
        }

        header('Location: /?page=admin-supply-edit&id=' . $supplyId . '&status=saved');
    }

    public function toggleSupplyCard(): void
    {
        $supplyId = (int) ($_POST['supply_id'] ?? 0);
        $cardType = $_POST['card_type'] ?? '';
        $activateRaw = $_POST['activate'] ?? null;

        if ($supplyId <= 0 || !in_array($cardType, ['retail', 'wholesale', 'box'], true) || !in_array($activateRaw, ['0', '1'], true)) {
            header('Location: /?page=admin-supplies&status=error');
            return;
        }

        $activate = $activateRaw === '1';

        $supplyModel = new Supply();
        $supply = $supplyModel->findById($supplyId);

        if (!$supply) {
            header('Location: /?page=admin-supplies&status=notfound');
            return;
        }

        $field = match ($cardType) {
            'retail' => 'has_product_card',
            'wholesale' => 'has_wholesale_card',
            default => 'has_box_card',
        };
        $supplyModel->setCardStatus($supplyId, $field, $activate ? 1 : 0);
        $this->syncSupplyProductStatus($supplyId, $cardType, $activate);

        $status = $activate ? 'card-activated' : 'card-deactivated';
        header('Location: /?page=admin-supplies&status=' . $status . '#supply-' . $supplyId);
    }

    private function createSupplyProducts(int $supplyId): void
    {
        $supplyModel = new Supply();
        $supply = $supplyModel->findById($supplyId);
        if (!$supply) {
            return;
        }

        $name = trim($supply['flower_name'] . ' ' . $supply['variety']);
        $descriptionBase = sprintf(
            'Страна: %s. Высота стебля: %s см. Вес стебля: %s г. Размер бутона: %s см.',
            $supply['country'] ?? '—',
            $supply['stem_height_cm'] ?? '—',
            $supply['stem_weight_g'] ?? '—',
            $supply['bud_size_cm'] ?? '—'
        );
        $descriptionText = trim((string) ($supply['description'] ?? ''));
        $description = $descriptionText !== ''
            ? $descriptionText . ' ' . $descriptionBase
            : $descriptionBase;

        $productModel = new Product();
        $basePayload = [
            'supply_id' => $supplyId,
            'name' => $name,
            'description' => $description,
            'price' => 0,
            'article' => null,
            'photo_url' => $supply['photo_url'] ?? null,
            'stem_height_cm' => $supply['stem_height_cm'] ?? null,
            'stem_weight_g' => $supply['stem_weight_g'] ?? null,
            'country' => $supply['country'] ?? null,
            'is_base' => 0,
            'is_active' => 0,
            'sort_order' => 0,
        ];

        $definitions = [
            ['category' => 'main', 'product_type' => 'regular'],
            ['category' => 'main', 'product_type' => 'small_wholesale'],
            ['category' => 'wholesale', 'product_type' => 'wholesale_box'],
        ];

        $db = Database::getInstance();
        foreach ($definitions as $definition) {
            $stmt = $db->prepare('SELECT 1 FROM products WHERE supply_id = :supply_id AND product_type = :product_type LIMIT 1');
            $stmt->execute([
                'supply_id' => $supplyId,
                'product_type' => $definition['product_type'],
            ]);
            if ($stmt->fetchColumn()) {
                continue;
            }
            $productModel->createFromSupply($basePayload + $definition);
        }
    }

    private function syncSupplyProductStatus(int $supplyId, string $cardType, bool $activate): void
    {
        $productType = match ($cardType) {
            'retail' => 'regular',
            'wholesale' => 'small_wholesale',
            default => 'wholesale_box',
        };

        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT id FROM products WHERE supply_id = :supply_id AND product_type = :product_type LIMIT 1');
        $stmt->execute([
            'supply_id' => $supplyId,
            'product_type' => $productType,
        ]);
        $productId = $stmt->fetchColumn();
        if (!$productId) {
            $this->createSupplyProducts($supplyId);
            $stmt->execute([
                'supply_id' => $supplyId,
                'product_type' => $productType,
            ]);
            $productId = $stmt->fetchColumn();
        }
        if ($productId) {
            $productModel = new Product();
            $productModel->setActive((int) $productId, $activate ? 1 : 0);
        }
    }

    private function syncSupplyCardStatus(int $supplyId, string $productType, int $active): void
    {
        $field = match ($productType) {
            'regular' => 'has_product_card',
            'small_wholesale' => 'has_wholesale_card',
            'wholesale_box' => 'has_box_card',
            default => null,
        };

        if (!$field) {
            return;
        }

        $supplyModel = new Supply();
        $supplyModel->setCardStatus($supplyId, $field, $active ? 1 : 0);
    }

    public function ordersOneTime(): void
    {
        $query = trim($_GET['q'] ?? '');
        $statusFilter = $_GET['status_filter'] ?? 'all';
        $paymentFilter = $_GET['payment_filter'] ?? 'all';

        $pageMeta = [
            'title' => 'Заказы · разовые покупки — админ-панель Bunch',
            'description' => 'Контроль статусов разовых заказов, оплат и доставки.',
            'h1' => 'Заказы (разовые)',
            'headerTitle' => 'Bunch Admin',
            'headerSubtitle' => 'Заказы · Разовые покупки',
        ];

        $orderModel = new Order();

        $this->render('admin-orders-one-time', [
            'pageMeta' => $pageMeta,
            'orders' => $orderModel->getAdminOrders($query, $statusFilter, $paymentFilter),
            'filters' => [
                'status' => [
                    'all' => 'Все',
                    'new' => 'Новый',
                    'confirmed' => 'Принят',
                    'assembled' => 'Собран',
                    'delivering' => 'В доставке',
                    'delivered' => 'Доставлен',
                    'cancelled' => 'Отменён',
                ],
                'payment' => [
                    'all' => 'Все',
                    'paid' => 'Оплачен',
                    'pending' => 'Ожидает',
                    'refund' => 'Возврат',
                ],
            ],
            'query' => $query,
            'activeFilters' => [
                'status' => $statusFilter,
                'payment' => $paymentFilter,
            ],
            'message' => $_GET['result'] ?? null,
        ]);
    }

    public function orderOneTimeEdit(): void
    {
        $orderId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $query = trim($_GET['q'] ?? '');
        $statusFilter = $_GET['status_filter'] ?? 'all';
        $paymentFilter = $_GET['payment_filter'] ?? 'all';

        if ($orderId <= 0) {
            header('Location: /?page=admin-orders-one-time');
            return;
        }

        $orderModel = new Order();
        $order = $orderModel->getAdminOrderDetail($orderId);

        if (!$order) {
            header('Location: /?page=admin-orders-one-time');
            return;
        }

        $pageMeta = [
            'title' => 'Карточка заказа — разовые покупки · админ-панель Bunch',
            'description' => 'Редактирование статуса, доставки и контактных данных.',
            'h1' => 'Карточка заказа',
            'headerTitle' => 'Bunch Admin',
            'headerSubtitle' => 'Заказы · Разовые покупки',
        ];

        $this->render('admin-order-one-time-edit', [
            'pageMeta' => $pageMeta,
            'selectedOrder' => $order,
            'filters' => [
                'status' => [
                    'all' => 'Все',
                    'new' => 'Новый',
                    'confirmed' => 'Принят',
                    'assembled' => 'Собран',
                    'delivering' => 'В доставке',
                    'delivered' => 'Доставлен',
                    'cancelled' => 'Отменён',
                ],
            ],
            'returnQuery' => [
                'page' => 'admin-orders-one-time',
                'q' => $query,
                'status_filter' => $statusFilter,
                'payment_filter' => $paymentFilter,
            ],
            'message' => $_GET['result'] ?? null,
        ]);
    }

    public function updateOneTimeOrder(): void
    {
        $orderId = (int) ($_POST['order_id'] ?? 0);

        if ($orderId <= 0) {
            header('Location: /?page=admin-orders-one-time&status=error');
            return;
        }

        $orderModel = new Order();
        $order = $orderModel->findById($orderId);

        if (!$order || $order['delivery_type'] === 'subscription') {
            header('Location: /?page=admin-orders-one-time&status=error');
            return;
        }

        $payload = [
            'status' => $_POST['status'] ?? 'new',
            'delivery_type' => $_POST['delivery_type'] ?? ($order['delivery_type'] ?? 'pickup'),
            'scheduled_date' => trim($_POST['scheduled_date'] ?? ''),
            'scheduled_time' => trim($_POST['scheduled_time'] ?? ''),
            'recipient_name' => trim($_POST['recipient_name'] ?? ''),
            'recipient_phone' => trim($_POST['recipient_phone'] ?? ''),
            'address_text' => trim($_POST['address_text'] ?? ''),
            'comment' => trim($_POST['comment'] ?? ''),
        ];

        $orderModel->updateAdminOrder($orderId, $payload);

        $returnUrl = trim($_POST['return_url'] ?? '');
        $query = ['page' => 'admin-orders-one-time', 'id' => $orderId, 'result' => 'updated'];

        if ($returnUrl !== '') {
            parse_str($returnUrl, $returnParams);
            if (is_array($returnParams)) {
                $returnParams['result'] = 'updated';
                $query = $returnParams;
            }
        }

        header('Location: /?' . http_build_query($query));
    }

    public function deleteOneTimeOrder(): void
    {
        $orderId = (int) ($_POST['order_id'] ?? 0);

        if ($orderId <= 0) {
            header('Location: /?page=admin-orders-one-time&status=error');
            return;
        }

        $orderModel = new Order();
        $order = $orderModel->findById($orderId);

        if (!$order || $order['delivery_type'] === 'subscription') {
            header('Location: /?page=admin-orders-one-time&status=error');
            return;
        }

        $deleted = $orderModel->deleteAdminOrder($orderId);
        $returnUrl = trim($_POST['return_url'] ?? '');
        $query = ['page' => 'admin-orders-one-time', 'result' => $deleted ? 'deleted' : 'error'];

        if ($returnUrl !== '') {
            parse_str($returnUrl, $returnParams);
            if (is_array($returnParams)) {
                $returnParams['result'] = $deleted ? 'deleted' : 'error';
                $query = $returnParams;
            }
        }

        header('Location: /?' . http_build_query($query));
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

    public function serviceDelivery(): void
    {
        $pageMeta = [
            'title' => 'Настройка сервисов · доставка по зонам — админ-панель Bunch',
            'description' => 'DaData подсказки и геокодинг плюс расчёт доставки через полигоны turf.js.',
            'h1' => 'DaData + зоны доставки',
            'headerTitle' => 'Bunch Admin',
            'headerSubtitle' => 'Сервисы · Адреса и доставка',
        ];

        $zoneModel = new DeliveryZone();

        $this->render('admin-services-delivery', [
            'pageMeta' => $pageMeta,
            'dadata' => $this->getDadataSettings(),
            'zones' => $zoneModel->getZones(false, true),
            'deliveryPricingVersion' => $zoneModel->getPricingVersion(),
            'testAddresses' => $zoneModel->getTestAddresses(),
        ]);
    }

    public function serviceTelegram(): void
    {
        $pageMeta = [
            'title' => 'Настройка сервисов · Telegram бот — админ-панель Bunch',
            'description' => 'Храним токен бота, username и секрет для вебхука в базе данных.',
            'h1' => 'Телеграм бот',
            'headerTitle' => 'Bunch Admin',
            'headerSubtitle' => 'Сервисы · Телеграм бот',
        ];

        $settings = new Setting();
        $defaults = $settings->getTelegramDefaults();

        $this->render('admin-services-telegram', [
            'pageMeta' => $pageMeta,
            'status' => $_GET['status'] ?? null,
            'settings' => [
                'botToken' => $settings->get(Setting::TG_BOT_TOKEN, $defaults[Setting::TG_BOT_TOKEN] ?? ''),
                'botUsername' => $settings->get(Setting::TG_BOT_USERNAME, $defaults[Setting::TG_BOT_USERNAME] ?? ''),
                'webhookSecret' => $settings->get(Setting::TG_WEBHOOK_SECRET, $defaults[Setting::TG_WEBHOOK_SECRET] ?? ''),
            ],
        ]);
    }

    public function saveServiceTelegram(): void
    {
        $settings = new Setting();

        $botToken = trim($_POST['bot_token'] ?? '');
        $botUsername = trim($_POST['bot_username'] ?? '');
        $webhookSecret = trim($_POST['webhook_secret'] ?? '');

        $settings->set(Setting::TG_BOT_TOKEN, $botToken);
        $settings->set(Setting::TG_BOT_USERNAME, $botUsername);
        $settings->set(Setting::TG_WEBHOOK_SECRET, $webhookSecret);

        header('Location: /?page=admin-services-telegram&status=saved');
        exit;
    }

    public function serviceOnlinePayment(): void
    {
        $pageMeta = [
            'title' => 'Настройка сервисов · онлайн оплата — админ-панель Bunch',
            'description' => 'Подключение платёжных шлюзов, реквизиты и webhooks.',
            'h1' => 'Онлайн оплата',
            'headerTitle' => 'Bunch Admin',
            'headerSubtitle' => 'Сервисы · Платёжные шлюзы',
        ];

        $this->render('admin-services-payment', [
            'pageMeta' => $pageMeta,
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

        $staticPageModel = new StaticPage();
        $staticPages = $staticPageModel->getAdminList();
        $editPage = null;
        $editId = (int) ($_GET['edit_id'] ?? 0);

        if ($editId > 0) {
            $editPage = $staticPageModel->getById($editId);
            if (!$editPage) {
                header('Location: /?page=admin-content-static&status=notfound');
                exit;
            }
        }

        $this->render('admin-content-static', [
            'pageMeta' => $pageMeta,
            'staticPages' => $staticPages,
            'editPage' => $editPage,
        ]);
    }

    public function saveStaticPage(): void
    {
        $staticPageModel = new StaticPage();

        $id = (int) ($_POST['id'] ?? 0);
        $title = trim((string) ($_POST['title'] ?? ''));
        $slug = $this->normalizeStaticPageSlug((string) ($_POST['slug'] ?? ''));
        $content = trim((string) ($_POST['content'] ?? ''));
        $showInFooter = isset($_POST['show_in_footer']) ? 1 : 0;
        $showInMenu = isset($_POST['show_in_menu']) ? 1 : 0;
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);
        $footerColumn = (int) ($_POST['footer_column'] ?? 1);
        $footerColumn = in_array($footerColumn, [1, 2], true) ? $footerColumn : 1;

        if ($title === '' || $slug === '') {
            header('Location: /?page=admin-content-static&status=error');
            exit;
        }

        $payload = [
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'show_in_footer' => $showInFooter,
            'show_in_menu' => $showInMenu,
            'is_active' => $isActive,
            'sort_order' => $sortOrder,
            'footer_column' => $footerColumn,
        ];

        try {
            if ($id > 0) {
                $staticPageModel->update($id, $payload);
            } else {
                $staticPageModel->create($payload);
            }
        } catch (Throwable $e) {
            $this->logAdminError('Static page save error', $e);
            header('Location: /?page=admin-content-static&status=error');
            exit;
        }

        header('Location: /?page=admin-content-static&status=saved');
        exit;
    }

    public function toggleStaticPage(): void
    {
        $staticPageModel = new StaticPage();
        $id = (int) ($_POST['id'] ?? 0);
        $active = (int) ($_POST['active'] ?? 0);

        if ($id <= 0) {
            header('Location: /?page=admin-content-static&status=error');
            exit;
        }

        $staticPageModel->setActive($id, $active);
        header('Location: /?page=admin-content-static&status=updated');
        exit;
    }

    public function deleteStaticPage(): void
    {
        $staticPageModel = new StaticPage();
        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            header('Location: /?page=admin-content-static&status=error');
            exit;
        }

        $staticPageModel->delete($id);
        header('Location: /?page=admin-content-static&status=deleted');
        exit;
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

    private function normalizeStaticPageSlug(string $slug): string
    {
        $slug = trim($slug);
        $slug = trim($slug, '/');
        $slug = strtolower($slug);
        $slug = preg_replace('/[^a-z0-9-]+/u', '-', $slug) ?? '';
        return trim($slug, '-');
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
