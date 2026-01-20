# Bunch flowers — схема базы данных

Этот файл описывает основные таблицы БД `bunch`, их поля и связи.  
SQL в примерах ориентирован на MySQL 8+ (InnoDB, `utf8mb4`).

> ⚠️ Важно: дамп в этом файле — стартовая схема. При изменениях структуры таблиц обязательно обновляйте `DB_SCHEMA.md`, чтобы новый разработчик видел актуальное состояние.

---

## 1. Общие настройки

Рекомендуемые дефолтные настройки при создании базы:

```sql
CREATE DATABASE IF NOT EXISTS bunch
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;
```

Все таблицы — InnoDB, `utf8mb4_unicode_ci`.

---

## 2. Таблица `users`

Хранит клиентов. Логин осуществляется по **телефону + PIN (4 цифры)**.  
PIN хранится в виде хэша (`password_hash()`), не в открытом виде.

```sql
CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

  phone VARCHAR(20) NOT NULL UNIQUE,       -- основной логин (формат: +7..., 8..., и т.п.)
  name  VARCHAR(100) NULL,
  email VARCHAR(100) NULL,

  is_active TINYINT(1) NOT NULL DEFAULT 1, -- флаг активности для CRM/рассылок
  role ENUM('admin', 'manager', 'florist', 'courier', 'customer', 'wholesale') NOT NULL DEFAULT 'customer',

  pin_hash VARCHAR(255) NOT NULL,          -- password_hash(pin)
  pin_updated_at DATETIME NULL,            -- когда PIN в последний раз меняли

  failed_pin_attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
  last_failed_pin_at DATETIME NULL,        -- защита от перебора PIN

  telegram_chat_id BIGINT NULL,            -- идентификатор чата в Telegram
  telegram_username VARCHAR(64) NULL,      -- username в Telegram (без @)

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

Рекомендации по логике:
- при успешном логине:
  - обнулять `failed_pin_attempts`;
  - очищать/обновлять `last_failed_pin_at`;
- при неуспешном логине:
  - увеличивать `failed_pin_attempts`;
  - при достижении порога (например, 5) — временно блокировать логин с данного IP/по данному пользователю.

---

## 2.1. Таблица `verification_codes`

Хранит одноразовые коды, которые выдаются ботом для регистрации и восстановления доступа.  
Запись действует ограниченное время, после использования отмечается как `is_used = 1`.

```sql
CREATE TABLE verification_codes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

  code VARCHAR(10) NOT NULL,
  purpose ENUM('register', 'recover') NOT NULL,

  chat_id BIGINT NOT NULL,
  phone VARCHAR(20) NULL,
  name VARCHAR(100) NULL,
  username VARCHAR(64) NULL,
  user_id INT UNSIGNED NULL,

  is_used TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  expires_at DATETIME NOT NULL,
  used_at DATETIME NULL,

  CONSTRAINT fk_verification_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE SET NULL,

  INDEX idx_code_purpose (code, purpose),
  INDEX idx_chat_id (chat_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 3. Таблица `user_addresses`

Один пользователь может иметь несколько адресов.  
На каждом адресе указаны данные **получателя** (это может быть сам клиент или другой человек).

```sql
CREATE TABLE user_addresses (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

  user_id INT UNSIGNED NOT NULL,
  CONSTRAINT fk_user_addresses_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE,

  is_active TINYINT(1) NOT NULL DEFAULT 1,
  is_archived TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,
  last_used_at DATETIME NULL,

  -- Адрес (структура + отображение)
  settlement VARCHAR(150) NULL,
  street VARCHAR(255) NULL,
  house VARCHAR(50) NULL,
  apartment VARCHAR(50) NULL,                 -- квартира / офис / компания
  address_text TEXT NULL,                     -- как показываем клиенту

  -- Гео для зон
  lat DECIMAL(10,7) NULL,
  lon DECIMAL(10,7) NULL,
  location_source ENUM('dadata', 'manual_pin', 'other') NULL,
  geo_quality VARCHAR(50) NULL,

  -- Зона доставки (кеш)
  zone_id INT UNSIGNED NULL,
  zone_calculated_at DATETIME NULL,
  zone_version VARCHAR(100) NULL,

  -- Получатель
  recipient_name  VARCHAR(100) NULL,
  recipient_phone VARCHAR(20)  NULL,

  -- Детали подъезда
  entrance VARCHAR(50) NULL,
  floor VARCHAR(50) NULL,
  intercom VARCHAR(50) NULL,
  delivery_comment TEXT NULL,

  -- Удобство для клиента
  label VARCHAR(50) NULL,                      -- "Дом", "Работа", "Для мамы" и др.
  is_default TINYINT(1) NOT NULL DEFAULT 0,

  -- Идентификаторы DaData
  fias_id VARCHAR(50) NULL,
  kladr_id VARCHAR(50) NULL,
  postal_code VARCHAR(20) NULL,
  region VARCHAR(150) NULL,
  city_district VARCHAR(150) NULL,

  last_delivery_price_hint INT NULL,

  INDEX idx_user_default (user_id, is_default),
  INDEX idx_user_archived (user_id, is_archived)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 3.1. Таблица `static_pages`

Статичные страницы для меню и футера.  
Используются для управляемых ссылок в клиентских разделах.

```sql
CREATE TABLE static_pages (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

  title VARCHAR(150) NOT NULL,
  slug VARCHAR(150) NOT NULL UNIQUE,
  content MEDIUMTEXT NULL,
  content_format VARCHAR(10) NOT NULL DEFAULT 'visual',

  show_in_footer TINYINT(1) NOT NULL DEFAULT 1,
  show_in_menu TINYINT(1) NOT NULL DEFAULT 1,
  footer_column TINYINT(1) NOT NULL DEFAULT 1,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,

  INDEX idx_static_pages_active (is_active, show_in_footer, show_in_menu),
  INDEX idx_static_pages_sort (sort_order, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

Примечания:
- На уровне приложения стоит гарантировать, что у пользователя **не более одного** адреса с `is_default = 1`.
- В интерфейсе “основной адрес” — это запись с `is_default = 1`.

---

## 4. Таблица `products`

Товары, которые можно купить через сайт.  
Базовый продукт — импортная красная роза премиум‑качества. В клиентских текстах **нельзя** хранить/выводить реальные страну/сорт.

```sql
CREATE TABLE products (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

  name        VARCHAR(150) NOT NULL,      -- название для клиента
  alt_name    VARCHAR(150) NULL,          -- альтернативное название для карточки
  slug        VARCHAR(150) NOT NULL UNIQUE,
  description TEXT NULL,                  -- описание (без раскрытия страны/сорта)
  price       INT NOT NULL,     -- текущая цена за единицу (например, 89)
  photo_url   VARCHAR(255) NULL,          -- основное фото товара
  photo_url_secondary VARCHAR(255) NULL,  -- дополнительное фото товара
  photo_url_tertiary  VARCHAR(255) NULL,  -- дополнительное фото товара

  category    ENUM('main', 'wholesale', 'accessory') NOT NULL DEFAULT 'main', -- витрина, опт или сопутствующие товары
  product_type ENUM('regular', 'small_wholesale', 'lottery', 'promo', 'auction', 'wholesale_box') NOT NULL DEFAULT 'regular',

  is_base     TINYINT(1) NOT NULL DEFAULT 0, -- базовый продукт (массовая красная роза)
  is_active   TINYINT(1) NOT NULL DEFAULT 1,
  status      ENUM('active', 'deleted') NOT NULL DEFAULT 'active',

  sort_order  INT NOT NULL DEFAULT 0,

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

При наполнении:
- для базового продукта `is_base = 1`, `price = 89`;
- все тексты — в рамках ограничений (см. README_DEV.md).
- сопутствующие товары помечаем `category = 'accessory'` (например, шарики, открытки, подарочные коробки).
- оптовые предзаказы — `category = 'wholesale'`.

Для существующих баз:
```sql
ALTER TABLE products
  ADD COLUMN status ENUM('active', 'deleted') NOT NULL DEFAULT 'active' AFTER is_active;
```

Миграция для добавления `alt_name`, `photo_url_secondary`, `photo_url_tertiary` находится в `docs/migrations/2026-04-03-add-product-alt-name-and-extra-photos.sql`.

Типы товара (`product_type`):
- `regular` — обычный товар на витрине;
- `small_wholesale` — мелкий опт (продажа пачками);
- `lottery` — розыгрыш букета (товар + билеты);
- `promo` — букет по акции;
- `auction` — товар аукцион;
- `wholesale_box` — опт (кратно коробкам).

---

## 5. Таблица `promos`

Акции, аукционы, распродажи остатков и спец‑предложения.

```sql
CREATE TABLE promos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

  title       VARCHAR(150) NOT NULL,
  slug        VARCHAR(150) NOT NULL UNIQUE,
  description TEXT NULL,                    -- текст акции (без раскрытия страны/сорта)
  promo_type  ENUM('sale', 'auction', 'lottery', 'other') NOT NULL DEFAULT 'sale',

  start_at    DATETIME NULL,
  end_at      DATETIME NULL,

  is_active   TINYINT(1) NOT NULL DEFAULT 1,

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

Для распродажи остатков базовой розы:
- цена в рамках акции должна быть **не ниже ~75 ₽** за розу (контролируется бизнес‑логикой).

---

## 5.1. Таблица `promo_items`

Разовые промо‑товары, не привязанные к поставкам. Количество может быть `NULL` — тогда отображается как 1 шт, а дата окончания указывается только при необходимости.

```sql
CREATE TABLE promo_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

  product_id INT UNSIGNED NULL,
  CONSTRAINT fk_promo_items_product
    FOREIGN KEY (product_id) REFERENCES products(id)
    ON DELETE SET NULL,

  title       VARCHAR(150) NOT NULL,
  slug        VARCHAR(150) NOT NULL UNIQUE,
  description TEXT NULL,

  price       INT NOT NULL DEFAULT 0,
  quantity    INT UNSIGNED NULL,
  ends_at     DATETIME NULL,

  label     VARCHAR(60) NULL,
  photo_url TEXT NULL,

  is_active TINYINT(1) NOT NULL DEFAULT 1,

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,

  INDEX idx_promo_items_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 5.2. Таблица `promo_categories`

Категории раздела «Акции» и их активность на витрине.

```sql
CREATE TABLE promo_categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(40) NOT NULL UNIQUE,
  title VARCHAR(120) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 6. Лотереи

Лотерея — товар `product_type = 'lottery'` + фиксированный пул билетов.

```sql
CREATE TABLE lotteries (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id INT UNSIGNED NOT NULL,

  prize_description TEXT NULL,
  ticket_price INT NOT NULL DEFAULT 0,
  tickets_total INT UNSIGNED NOT NULL,
  draw_at DATETIME NULL,
  status ENUM('active', 'sold_out', 'finished') NOT NULL DEFAULT 'active',
  winner_ticket_id INT UNSIGNED NULL,

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,

  CONSTRAINT fk_lotteries_product
    FOREIGN KEY (product_id) REFERENCES products(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Билеты

```sql
CREATE TABLE lottery_tickets (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  lottery_id INT UNSIGNED NOT NULL,
  ticket_number INT UNSIGNED NOT NULL,
  status ENUM('free', 'reserved', 'paid') NOT NULL DEFAULT 'free',
  user_id INT UNSIGNED NULL,
  phone_last4 VARCHAR(4) NULL,
  reserved_at DATETIME NULL,
  paid_at DATETIME NULL,

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  CONSTRAINT fk_lottery_tickets_lottery
    FOREIGN KEY (lottery_id) REFERENCES lotteries(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_lottery_tickets_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE SET NULL,

  UNIQUE KEY uniq_ticket_number (lottery_id, ticket_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Логи билетов

```sql
CREATE TABLE lottery_ticket_logs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ticket_id INT UNSIGNED NOT NULL,
  action ENUM('created', 'reserved', 'paid', 'released') NOT NULL,
  user_id INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  CONSTRAINT fk_lottery_ticket_logs_ticket
    FOREIGN KEY (ticket_id) REFERENCES lottery_tickets(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_lottery_ticket_logs_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 7. Аукционы

```sql
CREATE TABLE auction_lots (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

  title VARCHAR(150) NOT NULL,
  description TEXT NULL,
  image VARCHAR(255) NULL,
  store_price INT NOT NULL DEFAULT 0,
  start_price INT NOT NULL DEFAULT 1,
  bid_step INT NOT NULL DEFAULT 1,
  blitz_price INT NULL,
  starts_at DATETIME NULL,
  ends_at DATETIME NULL,
  original_ends_at DATETIME NULL,
  status ENUM('draft', 'active', 'finished', 'cancelled') NOT NULL DEFAULT 'draft',
  winner_user_id INT UNSIGNED NULL,
  winning_bid_id INT UNSIGNED NULL,

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,

  CONSTRAINT fk_auction_lot_winner
    FOREIGN KEY (winner_user_id) REFERENCES users(id)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Ставки

```sql
CREATE TABLE auction_bids (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  lot_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  amount INT NOT NULL,
  status ENUM('active', 'cancelled') NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  cancelled_at DATETIME NULL,
  cancel_reason VARCHAR(255) NULL,

  CONSTRAINT fk_auction_bids_lot
    FOREIGN KEY (lot_id) REFERENCES auction_lots(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_auction_bids_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Аудит событий

```sql
CREATE TABLE auction_events (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  lot_id INT UNSIGNED NOT NULL,
  bid_id INT UNSIGNED NULL,
  user_id INT UNSIGNED NULL,
  action ENUM('bid_created', 'bid_cancelled', 'blitz', 'finished') NOT NULL,
  note VARCHAR(255) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  CONSTRAINT fk_auction_events_lot
    FOREIGN KEY (lot_id) REFERENCES auction_lots(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_auction_events_bid
    FOREIGN KEY (bid_id) REFERENCES auction_bids(id)
    ON DELETE SET NULL,
  CONSTRAINT fk_auction_events_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 8. Таблицы групп и рассылок

Группы пользователей для выбора получателей и истории рассылок.

```sql
CREATE TABLE broadcast_groups (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

  name VARCHAR(150) NOT NULL,
  description TEXT NULL,
  is_system TINYINT(1) NOT NULL DEFAULT 0,

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,

  UNIQUE KEY uk_broadcast_groups_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE broadcast_group_users (
  group_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  added_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  CONSTRAINT fk_broadcast_group_users_group
    FOREIGN KEY (group_id) REFERENCES broadcast_groups(id)
    ON DELETE CASCADE,

  CONSTRAINT fk_broadcast_group_users_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE,

  PRIMARY KEY (group_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE broadcast_messages (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

  body TEXT NOT NULL,
  send_at DATETIME NULL,
  status ENUM('scheduled', 'sent') NOT NULL DEFAULT 'scheduled',

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE broadcast_message_groups (
  broadcast_id INT UNSIGNED NOT NULL,
  group_id INT UNSIGNED NOT NULL,

  CONSTRAINT fk_broadcast_message_groups_broadcast
    FOREIGN KEY (broadcast_id) REFERENCES broadcast_messages(id)
    ON DELETE CASCADE,

  CONSTRAINT fk_broadcast_message_groups_group
    FOREIGN KEY (group_id) REFERENCES broadcast_groups(id)
    ON DELETE CASCADE,

  PRIMARY KEY (broadcast_id, group_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO broadcast_groups (id, name, description, is_system, created_at, updated_at)
VALUES (1, 'Всем', 'Все активные пользователи', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE
  name = VALUES(name),
  description = VALUES(description),
  is_system = VALUES(is_system);

ALTER TABLE broadcast_groups AUTO_INCREMENT = 2;
```

---

## 7. Таблицы корзины (если храним в БД)

Для начала можно хранить корзину в сессии.  
Если нужна сохранённая корзина (например, между устройствами) — можно использовать таблицы ниже.

```sql
CREATE TABLE carts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

  user_id INT UNSIGNED NULL,                    -- NULL = гость (по желанию)
  session_id VARCHAR(64) NULL,                  -- ID сессии (если нужно)
  CONSTRAINT fk_carts_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE SET NULL,

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE cart_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

  cart_id    INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  qty        INT UNSIGNED NOT NULL DEFAULT 1,
  price      INT NOT NULL,            -- цена на момент добавления в корзину

  CONSTRAINT fk_cart_items_cart
    FOREIGN KEY (cart_id) REFERENCES carts(id)
    ON DELETE CASCADE,

  CONSTRAINT fk_cart_items_product
    FOREIGN KEY (product_id) REFERENCES products(id)
    ON DELETE RESTRICT,

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE cart_item_attributes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

  cart_item_id INT UNSIGNED NOT NULL,
  CONSTRAINT fk_cart_item_attributes_item
    FOREIGN KEY (cart_item_id) REFERENCES cart_items(id)
    ON DELETE CASCADE,

  attribute_id INT UNSIGNED NOT NULL,
  attribute_value_id INT UNSIGNED NOT NULL,
  applies_to ENUM('stem', 'bouquet') NOT NULL DEFAULT 'stem',
  price_delta INT NOT NULL DEFAULT 0,

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 8. Таблицы заказов

Заказ = слепок корзины + выбранный адрес/получатель на момент оформления.

```sql
CREATE TABLE orders (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

  user_id INT UNSIGNED NULL,
  CONSTRAINT fk_orders_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE SET NULL,

  address_id INT UNSIGNED NULL,
  CONSTRAINT fk_orders_address
    FOREIGN KEY (address_id) REFERENCES user_addresses(id)
    ON DELETE SET NULL,

  total_amount INT NOT NULL,         -- итоговая сумма заказа
  status ENUM('new', 'confirmed', 'assembled', 'delivering', 'delivered', 'cancelled')
    NOT NULL DEFAULT 'new',

  delivery_type ENUM('pickup', 'delivery', 'subscription') NOT NULL DEFAULT 'pickup',
  delivery_price INT NULL,
  zone_id INT UNSIGNED NULL,
  delivery_pricing_version VARCHAR(100) NULL,
  scheduled_date DATE NULL,
  scheduled_time TIME NULL,
  address_text TEXT NULL,                      -- слепок адреса, если не привязан к user_addresses
  address_unit VARCHAR(100) NULL,
  recipient_name VARCHAR(100) NULL,
  recipient_phone VARCHAR(20) NULL,
  subscription_interval INT UNSIGNED NULL,     -- интервал в днях для подписки

  comment TEXT NULL,                           -- комментарий клиента к заказу

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE order_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

  order_id  INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,

  product_name VARCHAR(150) NOT NULL,          -- слепок названия на момент заказа
  qty          INT UNSIGNED NOT NULL,
  price        INT NOT NULL,         -- слепок цены за единицу на момент заказа

  CONSTRAINT fk_order_items_order
    FOREIGN KEY (order_id) REFERENCES orders(id)
    ON DELETE CASCADE,

  CONSTRAINT fk_order_items_product
    FOREIGN KEY (product_id) REFERENCES products(id)
    ON DELETE RESTRICT,

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE order_item_attributes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

  order_item_id INT UNSIGNED NOT NULL,
  CONSTRAINT fk_order_item_attributes_item
    FOREIGN KEY (order_item_id) REFERENCES order_items(id)
    ON DELETE CASCADE,

  attribute_id INT UNSIGNED NOT NULL,
  attribute_value_id INT UNSIGNED NOT NULL,
  applies_to ENUM('stem', 'bouquet') NOT NULL DEFAULT 'stem',
  price_delta INT NOT NULL DEFAULT 0,

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

Важно:
- `product_name` и `price` дублируются в `order_items`, чтобы изменения каталога не ломали историю заказов.

---

## 9. Таблица подписок `subscriptions`

Подписка на регулярную доставку букетов.

```sql
CREATE TABLE subscriptions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

  user_id INT UNSIGNED NOT NULL,
  CONSTRAINT fk_subscriptions_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE,

  address_id INT UNSIGNED NOT NULL,
  CONSTRAINT fk_subscriptions_address
    FOREIGN KEY (address_id) REFERENCES user_addresses(id)
    ON DELETE RESTRICT,

  product_id INT UNSIGNED NOT NULL,            -- что именно везём (обычно базовая роза)
  CONSTRAINT fk_subscriptions_product
    FOREIGN KEY (product_id) REFERENCES products(id)
    ON DELETE RESTRICT,

  qty INT UNSIGNED NOT NULL,                   -- сколько роз / единиц за доставку

  plan ENUM('weekly', 'biweekly', 'monthly') NOT NULL, -- частота доставок
  start_date DATE NOT NULL,
  next_delivery_date DATE NOT NULL,            -- ближайшая запланированная доставка

  status ENUM('active', 'paused', 'cancelled') NOT NULL DEFAULT 'active',

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

Приложение (cron/скрипт) раз в день:
- ищет подписки с `status = 'active'` и `next_delivery_date <= CURDATE()`,
- создаёт заказы на основе подписки,
- пересчитывает `next_delivery_date` согласно `plan`.

---

## 10. Таблица для связки с Telegram (опционально)

Если нужно хранить логи выдачи PIN, можно завести отдельную таблицу:

```sql
CREATE TABLE telegram_pin_logs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

  user_id INT UNSIGNED NOT NULL,
  CONSTRAINT fk_telegram_pin_logs_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE,

  action ENUM('register', 'reset', 'change') NOT NULL,
  sent_pin_last4 CHAR(4) NULL,              -- можно хранить только последние 4 цифры для аудита,
                                            -- если бизнес это ок, либо NULL ради безопасности
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 11. Таблица `supplies`

Расписание поставок для стендинга и разовых закупок. Для стендинга `periodicity` — `weekly` или `biweekly`, а ближайшая дата поставки рассчитывается от `first_delivery_date` (при наличии `skip_date` текущая поставка пропускается). Для разовой поставки `periodicity = 'single'`, дата берётся из `planned_delivery_date`.

```sql
CREATE TABLE supplies (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

  is_standing TINYINT(1) NOT NULL DEFAULT 0,                   -- 1 — стендинг, 0 — разовая поставка
  photo_url VARCHAR(255) NULL,
  flower_name VARCHAR(120) NOT NULL,
  variety VARCHAR(120) NOT NULL,
  country VARCHAR(80) NULL,

  boxes_total INT UNSIGNED NOT NULL DEFAULT 0,
  packs_per_box INT UNSIGNED NOT NULL DEFAULT 0,
  packs_total INT UNSIGNED NOT NULL,
  packs_reserved INT UNSIGNED NOT NULL DEFAULT 0,              -- подписки, предзаказы и мелкий опт
  stems_per_pack INT UNSIGNED NOT NULL,
  stem_height_cm INT UNSIGNED NULL,
  stem_weight_g INT UNSIGNED NULL,
  bud_size_cm INT UNSIGNED NULL,
  description TEXT NULL,

  periodicity ENUM('weekly', 'biweekly', 'single') NOT NULL DEFAULT 'single',
  first_delivery_date DATE NULL,
  planned_delivery_date DATE NULL,
  actual_delivery_date DATE NULL,
  allow_small_wholesale TINYINT(1) NOT NULL DEFAULT 0,
  allow_box_order TINYINT(1) NOT NULL DEFAULT 0,
  skip_date DATE NULL,

  has_product_card TINYINT(1) NOT NULL DEFAULT 0,              -- создана карточка товара
  has_wholesale_card TINYINT(1) NOT NULL DEFAULT 0,            -- создана карточка мелкого опта
  has_box_card TINYINT(1) NOT NULL DEFAULT 0,                  -- создана карточка коробок

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

Пример сидов:

```sql
INSERT INTO supplies (
  is_standing, photo_url, flower_name, variety, country,
  boxes_total, packs_per_box, packs_total, packs_reserved, stems_per_pack, stem_height_cm, stem_weight_g, bud_size_cm, description,
  periodicity, first_delivery_date, planned_delivery_date, actual_delivery_date,
  allow_small_wholesale, allow_box_order, skip_date, has_product_card, has_wholesale_card, has_box_card
) VALUES
  (1, 'https://cdn.bunch.test/rhodos.jpg', 'Роза', 'Rhodos', 'Эквадор',
   12, 5, 60, 10, 25, 50, 45, 6, 'Классический крупный бутон с плотными лепестками.', 'weekly', '2024-12-05', '2024-12-05', NULL,
   1, 1, NULL, 1, 1, 1),
  (1, 'https://cdn.bunch.test/eucalyptus.jpg', 'Эвкалипт', 'Cinerea', 'Россия',
   16, 5, 80, 14, 15, 40, 28, NULL, 'Ароматный эвкалипт для оформления букетов.', 'biweekly', '2024-12-10', '2024-12-10', '2024-12-24',
   1, 0, '2024-12-31', 0, 1, 0),
  (0, 'https://cdn.bunch.test/chrysanthemum.jpg', 'Хризантема', 'Altaj', 'Колумбия',
   8, 4, 32, 4, 10, 32, 18, 5, 'Плотный бутон, ровная окраска.', 'single', '2024-12-09', '2024-12-09', NULL,
   1, 0, NULL, 0, 0, 0);
```

---

## 12. Индексы и производительность (минимальный набор)

- `users.phone` — UNIQUE.
- `user_addresses.user_id` + `is_default`.
- `orders.user_id`, `orders.address_id`, `orders.status`, `orders.created_at`.
- `subscriptions.user_id`, `subscriptions.status`, `subscriptions.next_delivery_date`.

По мере роста нагрузки индексы можно расширять по фактическим запросам.

---

## 13. Инициализация и сиды

Для быстрого запуска проекта полезно иметь:

1. Дамп структуры (`db_schema.sql`) на основе этого файла.
2. Seed‑скрипт:
   - базовый товар (импортная красная роза премиум‑качества, 89 ₽, `is_base = 1`);
   - тестовый пользователь;
   - один‑два тестовых адреса;
   - тестовая подписка;
   - пара тестовых акций (`promos`).

При любых изменениях структур таблиц:
- сначала меняем схемы в миграциях/скриптах;
- затем синхронизируем описание здесь (`DB_SCHEMA.md`).

### Зоны доставки

```sql
CREATE TABLE delivery_pricing_meta (
  id TINYINT UNSIGNED NOT NULL DEFAULT 1 PRIMARY KEY,
  shop VARCHAR(100) NOT NULL DEFAULT 'bunch',
  version INT UNSIGNED NOT NULL DEFAULT 1,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE delivery_zones (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  price INT NOT NULL DEFAULT 0,
  priority INT NOT NULL DEFAULT 0,
  color VARCHAR(20) NOT NULL DEFAULT '#f43f5e',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  polygon JSON NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

- `delivery_pricing_meta.version` — увеличивается при каждом сохранении зон.
- `delivery_zones.polygon` — массив точек `[lon, lat]` для turf/Leaflet.
