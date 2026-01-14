ALTER TABLE supplies
  ADD COLUMN bud_size_cm INT UNSIGNED NULL AFTER stem_weight_g,
  ADD COLUMN description TEXT NULL AFTER bud_size_cm;
