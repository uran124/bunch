BEGIN;

SET @has_attributes_sort_order := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'attributes'
    AND COLUMN_NAME = 'sort_order'
);
SET @sql := IF(
  @has_attributes_sort_order = 0,
  'ALTER TABLE attributes ADD COLUMN sort_order INT NOT NULL DEFAULT 0 AFTER is_active',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_attribute_values_is_default := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'attribute_values'
    AND COLUMN_NAME = 'is_default'
);
SET @sql := IF(
  @has_attribute_values_is_default = 0,
  'ALTER TABLE attribute_values ADD COLUMN is_default TINYINT(1) NOT NULL DEFAULT 0 AFTER is_active',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

UPDATE attribute_values av
INNER JOIN (
  SELECT attribute_id, MIN(id) AS min_id
  FROM attribute_values
  GROUP BY attribute_id
) first_value ON first_value.attribute_id = av.attribute_id
SET av.is_default = CASE WHEN av.id = first_value.min_id THEN 1 ELSE 0 END;

COMMIT;
