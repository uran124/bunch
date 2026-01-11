ALTER TABLE products
  MODIFY COLUMN product_type ENUM('regular', 'small_wholesale', 'lottery', 'promo', 'auction', 'wholesale_box') NOT NULL DEFAULT 'regular';

ALTER TABLE supplies
  ADD COLUMN boxes_total INT UNSIGNED NOT NULL DEFAULT 0 AFTER country,
  ADD COLUMN packs_per_box INT UNSIGNED NOT NULL DEFAULT 0 AFTER boxes_total,
  ADD COLUMN allow_box_order TINYINT(1) NOT NULL DEFAULT 0 AFTER allow_small_wholesale,
  ADD COLUMN has_box_card TINYINT(1) NOT NULL DEFAULT 0 AFTER has_wholesale_card;

UPDATE supplies
SET boxes_total = packs_total,
    packs_per_box = CASE WHEN packs_per_box = 0 THEN 1 ELSE packs_per_box END
WHERE boxes_total = 0 AND packs_total > 0;
