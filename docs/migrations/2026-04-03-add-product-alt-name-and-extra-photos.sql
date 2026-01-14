ALTER TABLE products
  ADD COLUMN alt_name VARCHAR(150) NULL AFTER name,
  ADD COLUMN photo_url_secondary VARCHAR(255) NULL AFTER photo_url,
  ADD COLUMN photo_url_tertiary VARCHAR(255) NULL AFTER photo_url_secondary;
