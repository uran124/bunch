ALTER TABLE promo_items
    ADD COLUMN base_price DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER description;

UPDATE promo_items
SET base_price = price
WHERE base_price = 0;

ALTER TABLE auction_lots
    ADD COLUMN product_id INT UNSIGNED NULL AFTER id,
    ADD COLUMN winner_cart_added_at DATETIME NULL AFTER winning_bid_id,
    ADD INDEX idx_auction_lots_product (product_id),
    ADD CONSTRAINT fk_auction_lots_product
        FOREIGN KEY (product_id) REFERENCES products(id)
        ON DELETE SET NULL;
