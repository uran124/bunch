-- Обновление цепочки статусов заказов и отделение статуса оплаты от статуса заказа.
-- payment_status: 0 — не оплачен, 1 — наличными при получении, 2 — оплачен картой на сайте.

ALTER TABLE orders
  ADD COLUMN payment_status TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER status,
  ADD COLUMN payment_method ENUM('cash', 'online', 'sbp') NOT NULL DEFAULT 'cash' AFTER payment_status;

ALTER TABLE orders
  MODIFY status ENUM('new', 'confirmed', 'assembled', 'delivering', 'delivered', 'completed', 'cancelled', 'returned') NOT NULL DEFAULT 'new';

UPDATE orders
SET status = 'completed'
WHERE status = 'delivered';

ALTER TABLE orders
  MODIFY status ENUM('new', 'confirmed', 'assembled', 'delivering', 'completed', 'cancelled', 'returned') NOT NULL DEFAULT 'new';

UPDATE orders
SET payment_status = CASE
    WHEN status IN ('confirmed', 'assembled', 'delivering', 'completed') THEN 2
    WHEN status IN ('cancelled', 'returned') THEN 0
    ELSE 0
  END,
  payment_method = CASE
    WHEN status IN ('confirmed', 'assembled', 'delivering', 'completed') THEN 'online'
    ELSE 'cash'
  END;

INSERT INTO settings (code, value)
VALUES
  ('order_enabled_statuses', 'new,confirmed,assembled,delivering,completed,cancelled,returned'),
  ('order_enabled_payment_methods', 'cash,online')
ON DUPLICATE KEY UPDATE value = VALUES(value);
