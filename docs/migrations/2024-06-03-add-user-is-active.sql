-- Добавление флага активности пользователя для CRM/рассылок
ALTER TABLE users
  ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER email;

-- Пример проверки заполненности
SELECT id, phone, name, is_active FROM users LIMIT 10;
