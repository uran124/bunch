-- Normalize price values to integers and switch columns to INT.

UPDATE user_addresses
SET last_delivery_price_hint = FLOOR(last_delivery_price_hint)
WHERE last_delivery_price_hint IS NOT NULL;

UPDATE products
SET price = FLOOR(price);

UPDATE promo_items
SET base_price = FLOOR(base_price),
    price = FLOOR(price);

UPDATE lotteries
SET ticket_price = FLOOR(ticket_price);

UPDATE auction_lots
SET store_price = FLOOR(store_price),
    start_price = FLOOR(start_price),
    bid_step = FLOOR(bid_step),
    blitz_price = CASE
        WHEN blitz_price IS NULL THEN NULL
        ELSE FLOOR(blitz_price)
    END;

UPDATE auction_bids
SET amount = FLOOR(amount);

UPDATE cart_items
SET price = FLOOR(price);

UPDATE cart_item_attributes
SET price_delta = FLOOR(price_delta);

UPDATE orders
SET total_amount = FLOOR(total_amount),
    delivery_price = CASE
        WHEN delivery_price IS NULL THEN NULL
        ELSE FLOOR(delivery_price)
    END;

UPDATE order_items
SET price = FLOOR(price);

UPDATE order_item_attributes
SET price_delta = FLOOR(price_delta);

UPDATE attribute_values
SET price_delta = FLOOR(price_delta);

UPDATE product_price_tiers
SET price = FLOOR(price);

UPDATE delivery_zones
SET price = FLOOR(price);

ALTER TABLE user_addresses
    MODIFY last_delivery_price_hint INT NULL;

ALTER TABLE products
    MODIFY price INT NOT NULL;

ALTER TABLE promo_items
    MODIFY base_price INT NOT NULL DEFAULT 0,
    MODIFY price INT NOT NULL DEFAULT 0;

ALTER TABLE lotteries
    MODIFY ticket_price INT NOT NULL DEFAULT 0;

ALTER TABLE auction_lots
    MODIFY store_price INT NOT NULL DEFAULT 0,
    MODIFY start_price INT NOT NULL DEFAULT 1,
    MODIFY bid_step INT NOT NULL DEFAULT 1,
    MODIFY blitz_price INT NULL;

ALTER TABLE auction_bids
    MODIFY amount INT NOT NULL;

ALTER TABLE cart_items
    MODIFY price INT NOT NULL;

ALTER TABLE cart_item_attributes
    MODIFY price_delta INT NOT NULL DEFAULT 0;

ALTER TABLE orders
    MODIFY total_amount INT NOT NULL,
    MODIFY delivery_price INT NULL;

ALTER TABLE order_items
    MODIFY price INT NOT NULL;

ALTER TABLE order_item_attributes
    MODIFY price_delta INT NOT NULL DEFAULT 0;

ALTER TABLE attribute_values
    MODIFY price_delta INT NOT NULL DEFAULT 0;

ALTER TABLE product_price_tiers
    MODIFY price INT NOT NULL;

ALTER TABLE delivery_zones
    MODIFY price INT NOT NULL DEFAULT 0;
