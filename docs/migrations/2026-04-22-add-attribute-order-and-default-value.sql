BEGIN;

ALTER TABLE attributes  ADD COLUMN IF NOT EXISTS sort_order INT NOT NULL DEFAULT 0 AFTER is_active;

ALTER TABLE attribute_values
  ADD COLUMN IF NOT EXISTS is_default TINYINT(1) NOT NULL DEFAULT 0 AFTER is_active;

UPDATE attribute_values av
INNER JOIN (
  SELECT attribute_id, MIN(id) AS min_id
  FROM attribute_values
  GROUP BY attribute_id
) first_value ON first_value.attribute_id = av.attribute_id
SET av.is_default = CASE WHEN av.id = first_value.min_id THEN 1 ELSE 0 END;

COMMIT;
