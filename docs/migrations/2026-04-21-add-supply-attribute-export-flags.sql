ALTER TABLE supplies
  ADD COLUMN export_stem_height_attribute TINYINT(1) NOT NULL DEFAULT 0 AFTER stem_weight_g,
  ADD COLUMN export_stem_weight_attribute TINYINT(1) NOT NULL DEFAULT 0 AFTER export_stem_height_attribute;
