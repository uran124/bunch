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
                    [
                        'label' => 'DaData + зоны доставки',
                        'description' => 'Подсказки адресов, геокодинг и расчёт через turf.js',
                        'cta' => 'Настроить',
                        'href' => '/?page=admin-services-delivery',
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

        $this->render('admin', [
            'sections' => $sections,
            'pageMeta' => $pageMeta,
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
        $supplies = $supplyModel->getAdminList();

        $attributeModel = new AttributeModel();
        $productModel = new Product();

        $supplyOptions = array_column($supplies, 'flower_name');

        $selectedSupplyId = isset($_GET['create_from_supply']) ? (int) $_GET['create_from_supply'] : null;

        $filters = [
            'active' => ['Все', 'Активные', 'Неактивные'],
            'supplies' => $supplyOptions,
        ];

        $this->render('admin-products', [
            'pageMeta' => $pageMeta,
            'products' => $productModel->getAdminList(),
            'filters' => $filters,
            'supplies' => $supplies,
            'attributes' => $attributeModel->getAllWithValues(),
            'editingProduct' => isset($_GET['edit_id']) ? $productModel->getWithRelations((int) $_GET['edit_id']) : null,
            'selectedSupplyId' => $selectedSupplyId,
            'message' => $_GET['status'] ?? null,
        ]);
    }

    public function saveProduct(): void
    {
        $productId = (int) ($_POST['product_id'] ?? 0);
        $supplyId = (int) ($_POST['supply_id'] ?? 0);
        $article = trim($_POST['article'] ?? '');
        $photoUrl = trim($_POST['photo_url'] ?? '');
        $price = (float) ($_POST['price'] ?? 0);
        $active = isset($_POST['is_active']) ? 1 : 0;

        $uploadedPhoto = $this->handlePhotoUpload('photo_file', 'product');
        if ($uploadedPhoto) {
            $photoUrl = $uploadedPhoto;
        }

        $tierQty = $_POST['tier_min_qty'] ?? [];
        $tierPrice = $_POST['tier_price'] ?? [];
        $attributeIds = array_filter(array_map('intval', $_POST['attribute_ids'] ?? []));

        $supplyModel = new Supply();
        $supply = $supplyModel->findById($supplyId);

        if (!$supply || $price <= 0) {
            header('Location: /?page=admin-products&status=error');
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
            'description' => $description,
            'price' => $price,
            'article' => $article !== '' ? $article : null,
            'photo_url' => $photoUrl !== '' ? $photoUrl : null,
            'stem_height_cm' => $supply['stem_height_cm'] ?? null,
            'stem_weight_g' => $supply['stem_weight_g'] ?? null,
            'country' => $supply['country'] ?? null,
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
            $priceValue = isset($tierPrice[$index]) ? (float) $tierPrice[$index] : 0;

            if ($minQty > 0 && $priceValue > 0) {
                $tiers[] = ['min_qty' => $minQty, 'price' => $priceValue];
            }
        }

        $productModel->setPriceTiers($productId, $tiers);
        $productModel->setAttributes($productId, $attributeIds);

        header('Location: /?page=admin-products&status=saved');
    }

    public function deleteProduct(): void
    {
        $productId = (int) ($_POST['product_id'] ?? 0);

        if ($productId <= 0) {
            header('Location: /?page=admin-products&status=error');
            return;
        }

        $productModel = new Product();
        $productModel->deleteProduct($productId);

        header('Location: /?page=admin-products&status=deleted');
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
        $priceDelta = (float) ($_POST['price_delta'] ?? 0);
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

        $payload = [
            'photo_url' => trim($_POST['photo_url'] ?? ''),
            'flower_name' => trim($_POST['flower_name'] ?? ''),
            'variety' => trim($_POST['variety'] ?? ''),
            'country' => trim($_POST['country'] ?? ''),
            'packs_total' => (int) ($_POST['packs_total'] ?? 0),
            'stems_per_pack' => (int) ($_POST['stems_per_pack'] ?? 0),
            'stem_height_cm' => (int) ($_POST['stem_height_cm'] ?? 0),
            'stem_weight_g' => (int) ($_POST['stem_weight_g'] ?? 0),
            'periodicity' => $_POST['periodicity'] === 'biweekly' ? 'biweekly' : 'weekly',
            'first_delivery_date' => $firstDelivery !== '' ? $firstDelivery : null,
            'planned_delivery_date' => $firstDelivery !== '' ? $firstDelivery : null,
            'actual_delivery_date' => $actualDelivery !== '' ? $actualDelivery : null,
            'allow_small_wholesale' => isset($_POST['allow_small_wholesale']) ? 1 : 0,
            'skip_date' => $skipDate !== '' ? $skipDate : null,
            'packs_reserved' => 0,
        ];

        $uploadedPhoto = $this->handlePhotoUpload('photo_file_standing', 'supply');
        if ($uploadedPhoto) {
            $payload['photo_url'] = $uploadedPhoto;
        }

        if ($payload['flower_name'] === '' || $payload['variety'] === '' || !$payload['packs_total'] || !$payload['stems_per_pack'] || empty($payload['first_delivery_date'])) {
            header('Location: /?page=admin-supply-standing&status=error');
            return;
        }

        $supplyModel = new Supply();
        $supplyModel->createStanding($payload);

        header('Location: /?page=admin-supplies&status=created');
    }

    public function createSingleSupply(): void
    {
        $plannedDelivery = trim($_POST['planned_delivery_date'] ?? '');
        $actualDelivery = trim($_POST['actual_delivery_date'] ?? '');

        $payload = [
            'photo_url' => trim($_POST['photo_url'] ?? ''),
            'flower_name' => trim($_POST['flower_name'] ?? ''),
            'variety' => trim($_POST['variety'] ?? ''),
            'country' => trim($_POST['country'] ?? ''),
            'packs_total' => (int) ($_POST['packs_total'] ?? 0),
            'stems_per_pack' => (int) ($_POST['stems_per_pack'] ?? 0),
            'stem_height_cm' => (int) ($_POST['stem_height_cm'] ?? 0),
            'stem_weight_g' => (int) ($_POST['stem_weight_g'] ?? 0),
            'planned_delivery_date' => $plannedDelivery !== '' ? $plannedDelivery : null,
            'actual_delivery_date' => $actualDelivery !== '' ? $actualDelivery : null,
            'allow_small_wholesale' => isset($_POST['allow_small_wholesale']) ? 1 : 0,
            'packs_reserved' => 0,
        ];

        $uploadedPhoto = $this->handlePhotoUpload('photo_file_single', 'supply');
        if ($uploadedPhoto) {
            $payload['photo_url'] = $uploadedPhoto;
        }

        if ($payload['flower_name'] === '' || $payload['variety'] === '' || !$payload['packs_total'] || !$payload['stems_per_pack'] || empty($payload['planned_delivery_date'])) {
            header('Location: /?page=admin-supply-single&status=error');
            return;
        }

        $supplyModel = new Supply();
        $supplyModel->createOneTime($payload);

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

        $common = [
            'photo_url' => $photoUrl,
            'flower_name' => trim($_POST['flower_name'] ?? ''),
            'variety' => trim($_POST['variety'] ?? ''),
            'country' => trim($_POST['country'] ?? ''),
            'packs_total' => (int) ($_POST['packs_total'] ?? 0),
            'stems_per_pack' => (int) ($_POST['stems_per_pack'] ?? 0),
            'stem_height_cm' => (int) ($_POST['stem_height_cm'] ?? 0),
            'stem_weight_g' => (int) ($_POST['stem_weight_g'] ?? 0),
            'allow_small_wholesale' => isset($_POST['allow_small_wholesale']) ? 1 : 0,
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

            if ($payload['flower_name'] === '' || $payload['variety'] === '' || !$payload['packs_total'] || !$payload['stems_per_pack'] || empty($payload['first_delivery_date'])) {
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

            if ($payload['flower_name'] === '' || $payload['variety'] === '' || !$payload['packs_total'] || !$payload['stems_per_pack'] || empty($payload['planned_delivery_date'])) {
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

        if ($supplyId <= 0 || !in_array($cardType, ['retail', 'wholesale'], true) || !in_array($activateRaw, ['0', '1'], true)) {
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

        $field = $cardType === 'retail' ? 'has_product_card' : 'has_wholesale_card';
        $supplyModel->setCardStatus($supplyId, $field, $activate ? 1 : 0);

        $status = $activate ? 'card-activated' : 'card-deactivated';
        header('Location: /?page=admin-supplies&status=' . $status . '#supply-' . $supplyId);
    }

    public function ordersOneTime(): void
    {
        $query = trim($_GET['q'] ?? '');
        $statusFilter = $_GET['status_filter'] ?? 'all';
        $paymentFilter = $_GET['payment_filter'] ?? 'all';
        $selectedId = isset($_GET['id']) ? (int) $_GET['id'] : null;

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
            'selectedOrder' => $selectedId ? $orderModel->getAdminOrderDetail($selectedId) : null,
            'query' => $query,
            'activeFilters' => [
                'status' => $statusFilter,
                'payment' => $paymentFilter,
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

        $query = ['page' => 'admin-orders-one-time', 'id' => $orderId, 'result' => 'updated'];

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

        $this->render('admin-services-delivery', [
            'pageMeta' => $pageMeta,
            'dadata' => $this->getDadataSettings(),
            'zones' => $this->getDeliveryZones(),
            'testAddresses' => $this->getDeliveryTestAddresses(),
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

    private function getDadataSettings(): array
    {
        return [
            'apiKey' => '6e4950476cc01a78b287788434dc1028eb3e86cf',
            'secretKey' => 'f2b84eb0e15b3c7b93c75ac50a8cd53b1a9defa1',
            'suggestions' => true,
            'geocoding' => true,
            'dailyLimit' => 1500,
            'requestsToday' => 240,
            'lastSync' => 'Сегодня, 09:20',
        ];
    }

    private function getDeliveryZones(): array
    {
        return [
            [
                'name' => 'Центр',
                'price' => 290,
                'color' => '#f43f5e',
                'polygon' => [
                    [37.5995, 55.7620],
                    [37.6205, 55.7620],
                    [37.6210, 55.7470],
                    [37.6015, 55.7465],
                    [37.5995, 55.7620],
                ],
                'landmarks' => 'Тверская, Цветной бульвар, Патрики',
            ],
            [
                'name' => 'Северо-восток',
                'price' => 390,
                'color' => '#06b6d4',
                'polygon' => [
                    [37.6220, 55.7660],
                    [37.6660, 55.7680],
                    [37.6690, 55.7500],
                    [37.6250, 55.7475],
                    [37.6220, 55.7660],
                ],
                'landmarks' => 'Бауманская, Семёновская, Сокольники',
            ],
            [
                'name' => 'Юг',
                'price' => 490,
                'color' => '#a855f7',
                'polygon' => [
                    [37.6040, 55.7440],
                    [37.6570, 55.7440],
                    [37.6590, 55.7340],
                    [37.6050, 55.7340],
                    [37.6040, 55.7440],
                ],
                'landmarks' => 'Павелецкая, Шаболовка, Фрунзенская',
            ],
        ];
    }

    private function getDeliveryTestAddresses(): array
    {
        return [
            [
                'label' => 'Москва, ул. Тверская, 12',
                'match' => 'тверская 12',
                'coords' => [37.6047, 55.7586],
            ],
            [
                'label' => 'Москва, ул. Бауманская, 35',
                'match' => 'бауманская 35',
                'coords' => [37.6630, 55.7650],
            ],
            [
                'label' => 'Москва, ул. Шаболовка, 24',
                'match' => 'шаболовка 24',
                'coords' => [37.6115, 55.7325],
            ],
        ];
    }
}
