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
DROP TABLE IF EXISTS user_addresses;
DROP TABLE IF EXISTS promos;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS broadcast_message_groups;
DROP TABLE IF EXISTS broadcast_messages;
DROP TABLE IF EXISTS broadcast_group_users;
DROP TABLE IF EXISTS broadcast_groups;
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

  label VARCHAR(50) NULL,                  -- "Дом", "Работа", "Для мамы" и др.

  recipient_name  VARCHAR(100) NULL,       -- ФИО получателя
  recipient_phone VARCHAR(20)  NULL,       -- телефон получателя

  city      VARCHAR(100) NOT NULL,
  street    VARCHAR(255) NOT NULL,
  house     VARCHAR(50)  NOT NULL,
  building  VARCHAR(50)  NULL,             -- корпус/строение
  apartment VARCHAR(50)  NULL,
  entrance  VARCHAR(20)  NULL,
  floor     VARCHAR(20)  NULL,
  door_code VARCHAR(20)  NULL,
  comment   TEXT         NULL,             -- ориентиры, комментарий к адресу

  is_primary TINYINT(1) NOT NULL DEFAULT 0, -- основной адрес пользователя

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,

  INDEX idx_user_primary (user_id, is_primary)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- =========================
-- 3. Таблица товаров
-- =========================

CREATE TABLE products (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

  name        VARCHAR(150) NOT NULL,      -- название для клиента
  slug        VARCHAR(150) NOT NULL UNIQUE,
  description TEXT NULL,                  -- описание (без раскрытия страны/сорта)
  price       DECIMAL(10,2) NOT NULL,     -- цена за единицу (например, 89.00)

  is_base     TINYINT(1) NOT NULL DEFAULT 0, -- базовый продукт (массовая роза)
  is_active   TINYINT(1) NOT NULL DEFAULT 1,

  sort_order  INT NOT NULL DEFAULT 0,

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP
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
  price      DECIMAL(10,2) NOT NULL,            -- цена на момент добавления

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

  total_amount DECIMAL(10,2) NOT NULL,         -- итоговая сумма
  status ENUM('new', 'confirmed', 'delivering', 'delivered', 'cancelled')
    NOT NULL DEFAULT 'new',

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
  price        DECIMAL(10,2) NOT NULL,         -- слепок цены за единицу

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

  packs_total INT UNSIGNED NOT NULL,
  packs_reserved INT UNSIGNED NOT NULL DEFAULT 0,              -- подписки, предзаказы и мелкий опт
  stems_per_pack INT UNSIGNED NOT NULL,
  stem_height_cm INT UNSIGNED NULL,
  stem_weight_g INT UNSIGNED NULL,

  periodicity ENUM('weekly', 'biweekly', 'single') NOT NULL DEFAULT 'single',
  first_delivery_date DATE NULL,
  planned_delivery_date DATE NULL,
  actual_delivery_date DATE NULL,
  allow_small_wholesale TINYINT(1) NOT NULL DEFAULT 0,
  skip_date DATE NULL,

  has_product_card TINYINT(1) NOT NULL DEFAULT 0,
  has_wholesale_card TINYINT(1) NOT NULL DEFAULT 0,

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

INSERT INTO supplies (
  is_standing, photo_url, flower_name, variety, country,
  packs_total, packs_reserved, stems_per_pack, stem_height_cm, stem_weight_g,
  periodicity, first_delivery_date, planned_delivery_date, actual_delivery_date,
  allow_small_wholesale, skip_date, has_product_card, has_wholesale_card
) VALUES
  (1, 'https://cdn.bunch.test/rhodos.jpg', 'Роза', 'Rhodos', 'Эквадор',
   60, 10, 25, 50, 45, 'weekly', '2024-12-05', '2024-12-05', NULL,
   1, NULL, 1, 1),
  (1, 'https://cdn.bunch.test/eucalyptus.jpg', 'Эвкалипт', 'Cinerea', 'Россия',
   80, 14, 15, 40, 28, 'biweekly', '2024-12-10', '2024-12-10', '2024-12-24',
   1, '2024-12-31', 0, 1),
  (0, 'https://cdn.bunch.test/chrysanthemum.jpg', 'Хризантема', 'Altaj', 'Колумбия',
   32, 4, 10, 32, 18, 'single', '2024-12-09', '2024-12-09', NULL,
   1, NULL, 0, 0);

SET FOREIGN_KEY_CHECKS = 1;
