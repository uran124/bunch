-- Bunch flowers — стартовая схема базы данных
-- ВНИМАНИЕ: перед применением на проде убедитесь, что не потеряете данные.

CREATE DATABASE IF NOT EXISTS `bunch`
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE `bunch`;

SET NAMES utf8mb4;
SET time_zone = '+07:00'; -- Asia/Krasnoyarsk

SET FOREIGN_KEY_CHECKS = 0;

-- Очистка существующих таблиц (если есть)
DROP TABLE IF EXISTS telegram_pin_logs;
DROP TABLE IF EXISTS user_notification_settings;
DROP TABLE IF EXISTS notification_types;
DROP TABLE IF EXISTS subscriptions;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS cart_items;
DROP TABLE IF EXISTS carts;
DROP TABLE IF EXISTS product_price_tiers;
DROP TABLE IF EXISTS product_attributes;
DROP TABLE IF EXISTS attribute_values;
DROP TABLE IF EXISTS attributes;
DROP TABLE IF EXISTS auction_events;
DROP TABLE IF EXISTS auction_bids;
DROP TABLE IF EXISTS auction_lots;
DROP TABLE IF EXISTS lottery_ticket_logs;
DROP TABLE IF EXISTS lottery_tickets;
DROP TABLE IF EXISTS lotteries;
DROP TABLE IF EXISTS user_addresses;
DROP TABLE IF EXISTS promos;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS broadcast_message_groups;
DROP TABLE IF EXISTS broadcast_messages;
DROP TABLE IF EXISTS broadcast_group_users;
DROP TABLE IF EXISTS broadcast_groups;
DROP TABLE IF EXISTS birthday_reminders;
DROP TABLE IF EXISTS users;

-- =========================
-- 1. Таблица пользователей
-- =========================

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

  phone VARCHAR(20) NOT NULL UNIQUE,       -- основной логин (телефон)
  name  VARCHAR(100) NULL,
  email VARCHAR(100) NULL,

  is_active TINYINT(1) NOT NULL DEFAULT 1, -- активен ли пользователь для операций и рассылок
  role ENUM('admin', 'manager', 'florist', 'courier', 'customer', 'wholesale') NOT NULL DEFAULT 'customer',
  birthday_reminder_days TINYINT UNSIGNED NOT NULL DEFAULT 3,
  birthday_reminders JSON NULL,

  pin_hash VARCHAR(255) NOT NULL,          -- password_hash(pin)
  pin_updated_at DATETIME NULL,            -- когда PIN в последний раз меняли

  failed_pin_attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
  last_failed_pin_at DATETIME NULL,        -- защита от перебора PIN

  telegram_chat_id BIGINT UNSIGNED NULL,   -- идентификатор чата в Telegram
  telegram_username VARCHAR(64) NULL,      -- username в Telegram (без @)

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- ==============================
-- 1.0. Таблица значимых дат
-- ==============================

CREATE TABLE birthday_reminders (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

  user_id INT UNSIGNED NOT NULL,
  CONSTRAINT fk_birthday_reminders_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE,

  recipient VARCHAR(100) NOT NULL,
  occasion VARCHAR(100) NOT NULL,
  reminder_date DATE NOT NULL,

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,

  INDEX idx_birthday_reminders_user_date (user_id, reminder_date)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- =============================
-- 1.1. Одноразовые коды для бота
-- =============================

CREATE TABLE verification_codes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

  code VARCHAR(10) NOT NULL,
  purpose ENUM('register', 'recover') NOT NULL,

  chat_id BIGINT UNSIGNED NOT NULL,
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
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- =============================
-- 2. Таблица адресов пользователя
-- =============================

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

  settlement VARCHAR(150) NULL,
  street VARCHAR(255) NULL,
  house VARCHAR(50) NULL,
  apartment VARCHAR(50) NULL,                 -- квартира / офис / компания
  address_text TEXT NULL,

  lat DECIMAL(10,7) NULL,
  lon DECIMAL(10,7) NULL,
  location_source ENUM('dadata', 'manual_pin', 'other') NULL,
  geo_quality VARCHAR(50) NULL,

  zone_id INT UNSIGNED NULL,
  zone_calculated_at DATETIME NULL,
  zone_version VARCHAR(100) NULL,

  recipient_name  VARCHAR(100) NULL,
  recipient_phone VARCHAR(20)  NULL,

  entrance VARCHAR(50) NULL,
  floor VARCHAR(50) NULL,
  intercom VARCHAR(50) NULL,
  delivery_comment TEXT NULL,

  label VARCHAR(50) NULL,
  is_default TINYINT(1) NOT NULL DEFAULT 0,

  fias_id VARCHAR(50) NULL,
  kladr_id VARCHAR(50) NULL,
  postal_code VARCHAR(20) NULL,
  region VARCHAR(150) NULL,
  city_district VARCHAR(150) NULL,

  last_delivery_price_hint INT NULL,

  INDEX idx_user_default (user_id, is_default),
  INDEX idx_user_archived (user_id, is_archived)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- =========================
-- 3. Таблица товаров
-- =========================

CREATE TABLE products (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

  supply_id INT UNSIGNED NULL,             -- привязка к поставке

  name        VARCHAR(150) NOT NULL,       -- название для клиента (из поставки)
  slug        VARCHAR(150) NOT NULL UNIQUE,
  description TEXT NULL,                   -- описание (без раскрытия страны/сорта)
  price       INT NOT NULL,      -- базовая цена за единицу
  article     VARCHAR(64) NULL,            -- артикул для склада/витрины
  photo_url   VARCHAR(255) NULL,           -- основное фото товара

  stem_height_cm INT UNSIGNED NULL,        -- ростовка из поставки
  stem_weight_g  INT UNSIGNED NULL,        -- вес стебля из поставки
  country       VARCHAR(80) NULL,          -- страна происхождения из поставки

  category    ENUM('main', 'wholesale', 'accessory') NOT NULL DEFAULT 'main', -- витрина, опт или сопутствующие товары
  product_type ENUM('regular', 'small_wholesale', 'lottery', 'promo', 'auction', 'wholesale_box') NOT NULL DEFAULT 'regular',

  is_base     TINYINT(1) NOT NULL DEFAULT 0, -- базовый продукт (массовая роза)
  is_active   TINYINT(1) NOT NULL DEFAULT 1,
  status      ENUM('active', 'deleted') NOT NULL DEFAULT 'active',

  sort_order  INT NOT NULL DEFAULT 0,

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,

  INDEX idx_products_supply (supply_id)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- =========================
-- 4. Таблица акций/промо
-- =========================

CREATE TABLE promos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

  title       VARCHAR(150) NOT NULL,
  slug        VARCHAR(150) NOT NULL UNIQUE,
  description TEXT NULL,
  promo_type  ENUM('sale', 'auction', 'lottery', 'other') NOT NULL DEFAULT 'sale',

  start_at    DATETIME NULL,
  end_at      DATETIME NULL,

  is_active   TINYINT(1) NOT NULL DEFAULT 1,

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- =========================
-- 4.1. Разовые промо-товары
-- =========================

CREATE TABLE promo_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

  product_id INT UNSIGNED NULL,

  title       VARCHAR(150) NOT NULL,
  slug        VARCHAR(150) NOT NULL UNIQUE,
  description TEXT NULL,

  base_price  INT NOT NULL DEFAULT 0,
  price       INT NOT NULL DEFAULT 0,
  quantity    INT UNSIGNED NULL,
  ends_at     DATETIME NULL,

  label     VARCHAR(60) NULL,
  photo_url TEXT NULL,

  is_active TINYINT(1) NOT NULL DEFAULT 1,

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,

  INDEX idx_promo_items_product (product_id),
  CONSTRAINT fk_promo_items_product
    FOREIGN KEY (product_id) REFERENCES products(id)
    ON DELETE SET NULL
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- =========================
-- 4.2. Категории акций
-- =========================

CREATE TABLE promo_categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(40) NOT NULL UNIQUE,
  title VARCHAR(120) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- =========================
-- 4.3. Лотереи
-- =========================

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
    ON DELETE CASCADE,

  INDEX idx_lottery_status (status)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

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

  UNIQUE KEY uniq_ticket_number (lottery_id, ticket_number),
  INDEX idx_lottery_ticket_status (lottery_id, status)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

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
    ON DELETE SET NULL,

  INDEX idx_ticket_log_ticket (ticket_id)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- =========================
-- 4.2. Аукционы
-- =========================

CREATE TABLE auction_lots (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

  product_id INT UNSIGNED NULL,

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
  winner_cart_added_at DATETIME NULL,

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,

  CONSTRAINT fk_auction_lot_winner
    FOREIGN KEY (winner_user_id) REFERENCES users(id)
    ON DELETE SET NULL,
  CONSTRAINT fk_auction_lot_product
    FOREIGN KEY (product_id) REFERENCES products(id)
    ON DELETE SET NULL,

  INDEX idx_auction_status (status),
  INDEX idx_auction_lots_product (product_id),
  INDEX idx_auction_time (starts_at, ends_at)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

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
    ON DELETE CASCADE,

  INDEX idx_auction_bid_lot (lot_id, status),
  INDEX idx_auction_bid_user (user_id)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

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
    ON DELETE SET NULL,

  INDEX idx_auction_event_lot (lot_id, action)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- =========================
-- 5. Таблицы групп рассылки
-- =========================

CREATE TABLE broadcast_groups (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

  name VARCHAR(150) NOT NULL,
  description TEXT NULL,
  is_system TINYINT(1) NOT NULL DEFAULT 0,

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,

  UNIQUE KEY uk_broadcast_groups_name (name)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

CREATE TABLE broadcast_messages (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

  body TEXT NOT NULL,
  send_at DATETIME NULL,
  status ENUM('scheduled', 'sent') NOT NULL DEFAULT 'scheduled',

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

INSERT INTO broadcast_groups (id, name, description, is_system, created_at, updated_at)
VALUES (1, 'Всем', 'Все активные пользователи', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE
  name = VALUES(name),
  description = VALUES(description),
  is_system = VALUES(is_system);

ALTER TABLE broadcast_groups AUTO_INCREMENT = 2;

-- =========================
-- 6. Таблицы корзины (опционально)
-- =========================

CREATE TABLE carts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

  user_id INT UNSIGNED NULL,                    -- NULL = гость
  session_id VARCHAR(64) NULL,                  -- ID сессии (если нужно)
  CONSTRAINT fk_carts_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE SET NULL,

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


CREATE TABLE cart_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

  cart_id    INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  qty        INT UNSIGNED NOT NULL DEFAULT 1,
  price      INT NOT NULL,            -- цена на момент добавления

  CONSTRAINT fk_cart_items_cart
    FOREIGN KEY (cart_id) REFERENCES carts(id)
    ON DELETE CASCADE,

  CONSTRAINT fk_cart_items_product
    FOREIGN KEY (product_id) REFERENCES products(id)
    ON DELETE RESTRICT,

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- =========================
-- 7. Таблицы заказов
-- =========================

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

  total_amount INT NOT NULL,         -- итоговая сумма
  status ENUM('new', 'confirmed', 'assembled', 'delivering', 'delivered', 'cancelled')
    NOT NULL DEFAULT 'new',

  delivery_type ENUM('pickup', 'delivery', 'subscription') NOT NULL DEFAULT 'pickup',
  delivery_price INT NULL,
  zone_id INT UNSIGNED NULL,
  delivery_pricing_version VARCHAR(100) NULL,
  scheduled_date DATE NULL,
  scheduled_time TIME NULL,
  address_text TEXT NULL,                      -- слепок адреса, если пользователь не авторизован
  address_unit VARCHAR(100) NULL,
  recipient_name VARCHAR(100) NULL,
  recipient_phone VARCHAR(20) NULL,
  subscription_interval INT UNSIGNED NULL,     -- интервал в днях для подписки

  comment TEXT NULL,                           -- комментарий клиента

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


CREATE TABLE order_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

  order_id   INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,

  product_name VARCHAR(150) NOT NULL,          -- слепок названия на момент заказа
  qty          INT UNSIGNED NOT NULL,
  price        INT NOT NULL,         -- слепок цены за единицу

  CONSTRAINT fk_order_items_order
    FOREIGN KEY (order_id) REFERENCES orders(id)
    ON DELETE CASCADE,

  CONSTRAINT fk_order_items_product
    FOREIGN KEY (product_id) REFERENCES products(id)
    ON DELETE RESTRICT,

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- =========================
-- 8. Таблица подписок
-- =========================

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

  product_id INT UNSIGNED NOT NULL,            -- что доставляем (обычно базовая роза)
  CONSTRAINT fk_subscriptions_product
    FOREIGN KEY (product_id) REFERENCES products(id)
    ON DELETE RESTRICT,

  qty INT UNSIGNED NOT NULL,                   -- количество за одну доставку

  plan ENUM('weekly', 'biweekly', 'monthly') NOT NULL,
  start_date DATE NOT NULL,
  next_delivery_date DATE NOT NULL,

  status ENUM('active', 'paused', 'cancelled') NOT NULL DEFAULT 'active',

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- =========================
-- 9. Настройки уведомлений и типы рассылок
-- =========================

CREATE TABLE notification_types (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

  code VARCHAR(64) NOT NULL UNIQUE,            -- системный код (order_updates, bonus_updates и т.д.)
  title VARCHAR(150) NOT NULL,
  description VARCHAR(255) NULL,

  sort_order INT NOT NULL DEFAULT 0,           -- сортировка в админке и в интерфейсе
  channel ENUM('push', 'sms', 'email', 'telegram', 'system') NOT NULL DEFAULT 'system',

  is_active TINYINT(1) NOT NULL DEFAULT 1,

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

INSERT INTO notification_types (code, title, description, sort_order, channel) VALUES
  ('order_updates',   'Уведомления о моих заказах',           'Статусы заказов, изменение времени доставки', 10, 'push'),
  ('bonus_updates',   'Начисление бонусных баллов',           'Баллы за покупки и их срок действия',         20, 'push'),
  ('promo_updates',   'Акционные товары',                     'Новые акции и спецпредложения',               30, 'push'),
  ('holiday_reminders','Напоминания о заказах к праздникам',   'Подборки к важным датам',                     40, 'email'),
  ('system_updates',  'Системные уведомления',                'Технические события и безопасность',          50, 'system');

CREATE TABLE user_notification_settings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

  user_id INT UNSIGNED NOT NULL,
  CONSTRAINT fk_user_notification_settings_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE,

  notification_type_id INT UNSIGNED NOT NULL,
  CONSTRAINT fk_user_notification_settings_type
    FOREIGN KEY (notification_type_id) REFERENCES notification_types(id)
    ON DELETE CASCADE,

  is_enabled TINYINT(1) NOT NULL DEFAULT 1,
  preferred_channel ENUM('push', 'sms', 'email', 'telegram', 'system') NOT NULL DEFAULT 'push',
  last_changed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  UNIQUE KEY uniq_user_notification (user_id, notification_type_id)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- =========================
-- 10. Логи работы с PIN в Telegram (опционально)
-- =========================

CREATE TABLE telegram_pin_logs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

  user_id INT UNSIGNED NOT NULL,
  CONSTRAINT fk_telegram_pin_logs_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE,

  action ENUM('register', 'reset', 'change') NOT NULL,
  sent_pin_last4 CHAR(4) NULL,              -- последние 4 цифры (опционально, можно хранить NULL)

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- =========================
-- 11. Поставки и расписание поставок
-- =========================

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

  has_product_card TINYINT(1) NOT NULL DEFAULT 0,
  has_wholesale_card TINYINT(1) NOT NULL DEFAULT 0,
  has_box_card TINYINT(1) NOT NULL DEFAULT 0,

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- =========================
-- 12. Атрибуты каталога и варианты
-- =========================

CREATE TABLE attributes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

  name VARCHAR(120) NOT NULL,
  description TEXT NULL,
  type ENUM('selector', 'toggle', 'color', 'text', 'number') NOT NULL DEFAULT 'selector',
  applies_to ENUM('stem', 'bouquet') NOT NULL DEFAULT 'stem',
  is_active TINYINT(1) NOT NULL DEFAULT 1,

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,

  UNIQUE KEY uk_attributes_name (name)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

CREATE TABLE attribute_values (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

  attribute_id INT UNSIGNED NOT NULL,
  CONSTRAINT fk_attribute_values_attribute
    FOREIGN KEY (attribute_id) REFERENCES attributes(id)
    ON DELETE CASCADE,

  value VARCHAR(150) NOT NULL,
  price_delta INT NOT NULL DEFAULT 0,
  photo_url VARCHAR(255) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,

  INDEX idx_attribute_values_attribute (attribute_id)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

CREATE TABLE product_attributes (
  product_id INT UNSIGNED NOT NULL,
  CONSTRAINT fk_product_attributes_product
    FOREIGN KEY (product_id) REFERENCES products(id)
    ON DELETE CASCADE,

  attribute_id INT UNSIGNED NOT NULL,
  CONSTRAINT fk_product_attributes_attribute
    FOREIGN KEY (attribute_id) REFERENCES attributes(id)
    ON DELETE CASCADE,

  PRIMARY KEY (product_id, attribute_id)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

CREATE TABLE product_price_tiers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

  product_id INT UNSIGNED NOT NULL,
  CONSTRAINT fk_product_price_tiers_product
    FOREIGN KEY (product_id) REFERENCES products(id)
    ON DELETE CASCADE,

  min_qty INT UNSIGNED NOT NULL DEFAULT 1,
  price INT NOT NULL,

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,

  INDEX idx_product_price_tiers_product (product_id)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

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

INSERT INTO attributes (name, description, type, applies_to, is_active) VALUES
  ('Высота стебля', 'Ростовка в сантиметрах', 'selector', 'stem', 1),
  ('Вид оформления', 'Обёртка и дополнительный декор', 'toggle', 'bouquet', 1),
  ('Цвет ленты', 'Дополнительный цветной акцент', 'color', 'bouquet', 0);

INSERT INTO attribute_values (attribute_id, value, price_delta, photo_url, is_active, sort_order) VALUES
  (1, '40 см', 0, 'https://cdn.bunch.test/stem-40.jpg', 1, 1),
  (1, '50 см', 10, 'https://cdn.bunch.test/stem-50.jpg', 1, 2),
  (1, '60 см', 20, 'https://cdn.bunch.test/stem-60.jpg', 0, 3),
  (2, 'Без оформления', 0, 'https://cdn.bunch.test/plain.jpg', 1, 1),
  (2, 'В крафте', 30, 'https://cdn.bunch.test/kraft.jpg', 1, 2),
  (2, 'Подарочная упаковка', 70, 'https://cdn.bunch.test/gift.jpg', 1, 3),
  (3, 'Бордовая лента', 0, 'https://cdn.bunch.test/ribbon-red.jpg', 1, 1),
  (3, 'Бежевая лента', 0, 'https://cdn.bunch.test/ribbon-beige.jpg', 1, 2);

INSERT INTO products (supply_id, name, slug, description, price, article, photo_url, stem_height_cm, stem_weight_g, country, category, is_base, is_active, sort_order)
VALUES
  (1, 'Роза Rhodos', 'roza-rhodos', 'Классическая роза из стендинга, идеально для срезки.', 89, 'RHD-001', 'https://cdn.bunch.test/rhodos-card.jpg', 50, 45, 'Эквадор', 'main', 0, 1, 10),
  (2, 'Эвкалипт Cinerea', 'evkalipt-cinerea', 'Ароматный эвкалипт для букетов и декора.', 55, 'EVC-010', 'https://cdn.bunch.test/eucalyptus-card.jpg', 40, 28, 'Россия', 'main', 0, 1, 20);

INSERT INTO product_attributes (product_id, attribute_id) VALUES
  (1, 1),
  (1, 2),
  (2, 2);

INSERT INTO product_price_tiers (product_id, min_qty, price) VALUES
  (1, 15, 82),
  (1, 25, 78),
  (2, 20, 48);

SET FOREIGN_KEY_CHECKS = 1;

-- Зоны доставки и версия тарифов
CREATE TABLE IF NOT EXISTS delivery_pricing_meta (
  id TINYINT UNSIGNED NOT NULL DEFAULT 1 PRIMARY KEY,
  shop VARCHAR(100) NOT NULL DEFAULT 'bunch',
  version INT UNSIGNED NOT NULL DEFAULT 1,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS delivery_zones (
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

CREATE TABLE IF NOT EXISTS settings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(120) NOT NULL,
  value TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_settings_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO settings (code, value)
VALUES
  ('telegram_bot_token', '8385667370:AAER94mzvJLtTtI1IWj2tHnQuen55xfrNsE'),
  ('telegram_bot_username', '@bunchflowersBot'),
  ('telegram_webhook_secret', 'bfb')
ON DUPLICATE KEY UPDATE value = VALUES(value);
