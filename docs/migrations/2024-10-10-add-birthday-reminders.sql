-- Настройки напоминаний о днях рождения
ALTER TABLE users
  ADD COLUMN birthday_reminder_days TINYINT UNSIGNED NOT NULL DEFAULT 3 AFTER is_active,
  ADD COLUMN birthday_reminders JSON NULL AFTER birthday_reminder_days;

-- Пример проверки
SELECT id, birthday_reminder_days, birthday_reminders FROM users LIMIT 5;
