ALTER TABLE promo_items
    ADD COLUMN product_id INT UNSIGNED NULL AFTER id,
    ADD INDEX idx_promo_items_product (product_id),
    ADD CONSTRAINT fk_promo_items_product
        FOREIGN KEY (product_id) REFERENCES products(id)
        ON DELETE SET NULL;
