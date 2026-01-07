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
