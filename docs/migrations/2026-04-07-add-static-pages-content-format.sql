ALTER TABLE static_pages
  ADD COLUMN content_format VARCHAR(10) NOT NULL DEFAULT 'visual' AFTER content;
