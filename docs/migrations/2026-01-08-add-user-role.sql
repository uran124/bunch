-- Добавление роли пользователя для админ-панели
ALTER TABLE users
  ADD COLUMN role ENUM('admin', 'manager', 'florist', 'courier', 'customer') NOT NULL DEFAULT 'customer' AFTER is_active;

-- Пример проверки заполненности
SELECT id, phone, name, role FROM users LIMIT 10;
