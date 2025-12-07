-- Обновление схемы для раздела «Каталог» (товары, акции, атрибуты, поставки, мелкий опт)
-- Применять после базовой схемы db_schema.sql

START TRANSACTION;

-- 1. Поставки
CREATE TABLE IF NOT EXISTS supplies (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  is_standing TINYINT(1) NOT NULL DEFAULT 0,
  delivery_date DATE NULL,
  sort VARCHAR(100) NULL,
  color VARCHAR(100) NULL,
  height VARCHAR(50) NULL,
  weight VARCHAR(50) NULL,
  country VARCHAR(100) NULL,
  packs_total INT UNSIGNED NOT NULL DEFAULT 0,
  packs_available INT UNSIGNED NOT NULL DEFAULT 0,
  pack_size INT UNSIGNED NOT NULL DEFAULT 0,
  small_wholesale_enabled TINYINT(1) NOT NULL DEFAULT 0,
  status ENUM('planned', 'arrived', 'closed', 'in_progress') NOT NULL DEFAULT 'planned',
  comment TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_delivery_date (delivery_date),
  INDEX idx_status (status),
  INDEX idx_standing (is_standing)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Товары: привязка к поставке и характеристики стебля
ALTER TABLE products
  ADD COLUMN supply_id INT UNSIGNED NULL AFTER id,
  ADD COLUMN main_photo VARCHAR(255) NULL AFTER price,
  ADD COLUMN color VARCHAR(100) NULL AFTER description,
  ADD COLUMN country VARCHAR(100) NULL AFTER color,
  ADD COLUMN stem_height VARCHAR(50) NULL AFTER country,
  ADD COLUMN stem_weight VARCHAR(50) NULL AFTER stem_height,
  ADD CONSTRAINT fk_products_supply FOREIGN KEY (supply_id) REFERENCES supplies(id) ON DELETE SET NULL;

-- 3. Акции: привязка к товару/поставке и цена акции
ALTER TABLE promos
  ADD COLUMN product_id INT UNSIGNED NULL AFTER promo_type,
  ADD COLUMN supply_id INT UNSIGNED NULL AFTER product_id,
  ADD COLUMN promo_price DECIMAL(10,2) NULL AFTER description,
  ADD COLUMN photo VARCHAR(255) NULL AFTER promo_price,
  ADD CONSTRAINT fk_promos_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
  ADD CONSTRAINT fk_promos_supply FOREIGN KEY (supply_id) REFERENCES supplies(id) ON DELETE SET NULL;

-- 4. Атрибуты и варианты
CREATE TABLE IF NOT EXISTS attributes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  type VARCHAR(50) NOT NULL DEFAULT 'selector',
  description TEXT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS attribute_values (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  attribute_id INT UNSIGNED NOT NULL,
  name VARCHAR(150) NOT NULL,
  price_delta DECIMAL(10,2) NOT NULL DEFAULT 0,
  photo VARCHAR(255) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_attribute_values_attribute FOREIGN KEY (attribute_id) REFERENCES attributes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS product_attribute_values (
  product_id INT UNSIGNED NOT NULL,
  attribute_value_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (product_id, attribute_value_id),
  CONSTRAINT fk_pav_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  CONSTRAINT fk_pav_attribute_value FOREIGN KEY (attribute_value_id) REFERENCES attribute_values(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Бронирование мелкого опта по поставкам
CREATE TABLE IF NOT EXISTS supply_pack_reservations (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  supply_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NULL,
  packs_reserved INT UNSIGNED NOT NULL,
  status ENUM('reserved', 'confirmed', 'paid', 'cancelled', 'shipped') NOT NULL DEFAULT 'reserved',
  reserved_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  comment VARCHAR(255) NULL,
  CONSTRAINT fk_pack_res_supply FOREIGN KEY (supply_id) REFERENCES supplies(id) ON DELETE CASCADE,
  CONSTRAINT fk_pack_res_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_supply_status (supply_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;
