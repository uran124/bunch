-- Добавление роли оптового покупателя
ALTER TABLE users
  MODIFY COLUMN role ENUM('admin', 'manager', 'florist', 'courier', 'customer', 'wholesale') NOT NULL DEFAULT 'customer';

-- Пример проверки заполненности
SELECT id, phone, name, role FROM users LIMIT 10;
