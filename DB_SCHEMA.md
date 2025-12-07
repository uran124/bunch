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

  pin_hash VARCHAR(255) NOT NULL,          -- password_hash(pin)
  pin_updated_at DATETIME NULL,            -- когда PIN в последний раз меняли

  failed_pin_attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
  last_failed_pin_at DATETIME NULL,        -- защита от перебора PIN

  telegram_chat_id BIGINT UNSIGNED NULL,   -- идентификатор чата в Telegram
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
  comment   TEXT         NULL,             -- как найти подъезд, ориентиры и т.п.

  is_primary TINYINT(1) NOT NULL DEFAULT 0, -- основной адрес пользователя

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,

  INDEX idx_user_primary (user_id, is_primary)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

Примечания:
- На уровне приложения стоит гарантировать, что у пользователя **не более одного** адреса с `is_primary = 1`.
- В интерфейсе “основной адрес” — это запись с `is_primary = 1`.

---

## 4. Таблица `products`

Товары, которые можно купить через сайт.  
Базовый продукт — импортная красная роза премиум‑качества. В клиентских текстах **нельзя** хранить/выводить реальные страну/сорт.

```sql
CREATE TABLE products (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

  name        VARCHAR(150) NOT NULL,      -- название для клиента
  slug        VARCHAR(150) NOT NULL UNIQUE,
  description TEXT NULL,                  -- описание (без раскрытия страны/сорта)
  price       DECIMAL(10,2) NOT NULL,     -- текущая цена за единицу (например, 89.00)

  is_base     TINYINT(1) NOT NULL DEFAULT 0, -- базовый продукт (массовая красная роза)
  is_active   TINYINT(1) NOT NULL DEFAULT 1,

  sort_order  INT NOT NULL DEFAULT 0,

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

При наполнении:
- для базового продукта `is_base = 1`, `price = 89.00`;
- все тексты — в рамках ограничений (см. README_DEV.md).

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

## 6. Таблицы групп и рассылок

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
  price      DECIMAL(10,2) NOT NULL,            -- цена на момент добавления в корзину

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

  total_amount DECIMAL(10,2) NOT NULL,         -- итоговая сумма заказа
  status ENUM('new', 'confirmed', 'delivering', 'delivered', 'cancelled')
    NOT NULL DEFAULT 'new',

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
  price        DECIMAL(10,2) NOT NULL,         -- слепок цены за единицу на момент заказа

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

## 11. Индексы и производительность (минимальный набор)

- `users.phone` — UNIQUE.
- `user_addresses.user_id` + `is_primary`.
- `orders.user_id`, `orders.address_id`, `orders.status`, `orders.created_at`.
- `subscriptions.user_id`, `subscriptions.status`, `subscriptions.next_delivery_date`.

По мере роста нагрузки индексы можно расширять по фактическим запросам.

---

## 12. Инициализация и сиды

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
