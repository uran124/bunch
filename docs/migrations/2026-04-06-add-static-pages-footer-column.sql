ALTER TABLE static_pages
  ADD COLUMN footer_column TINYINT(1) NOT NULL DEFAULT 1 AFTER show_in_menu;

UPDATE static_pages
SET footer_column = 2
WHERE slug IN ('policy', 'consent', 'offer')
   OR title IN (
     'Политика обработки персональных данных',
     'Согласие на обработку персональных данных',
     'Пользовательское соглашение'
   );
