-- Очистка данных адресов: разнести "улицу, дом" по разным полям.
-- Выполнять после бэкапа.

START TRANSACTION;

UPDATE user_addresses
SET
  house = TRIM(SUBSTRING_INDEX(street, ',', -1)),
  street = TRIM(SUBSTRING_INDEX(street, ',', 1))
WHERE
  (house IS NULL OR house = '')
  AND street LIKE '%,%';

COMMIT;
