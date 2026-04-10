-- ============================================================
--  shop_db.sql
--  Database schema cho project PHP MVC
--  Tạo từ ERD diagram
--  Sử dụng: Import trực tiếp vào phpMyAdmin
-- ============================================================

CREATE DATABASE IF NOT EXISTS `shop_db`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `shop_db`;

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. USERS  (bảng cha cho Customer và Admin)
-- ============================================================
CREATE TABLE IF NOT EXISTS `users` (
  `user_id`    VARCHAR(36)  NOT NULL DEFAULT (UUID()),
  `full_name`  VARCHAR(100) NOT NULL,
  `email`      VARCHAR(100) NOT NULL,
  `phone`      VARCHAR(20)  DEFAULT NULL,
  `password`   VARCHAR(255) NOT NULL,
  `role`       ENUM('customer','admin') NOT NULL DEFAULT 'customer',
  `status`     ENUM('active','banned')  NOT NULL DEFAULT 'active',
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 2. CUSTOMERS
-- ============================================================
CREATE TABLE IF NOT EXISTS `customers` (
  `customer_id`         VARCHAR(36)  NOT NULL DEFAULT (UUID()),
  `user_id`             VARCHAR(36)  NOT NULL,
  `gender`              ENUM('male','female','other') NOT NULL DEFAULT 'other',
  `date_of_birth`       DATE         DEFAULT NULL,
  `loyalty_points`      INT          NOT NULL DEFAULT 0,
  `default_address_id`  VARCHAR(36)  DEFAULT NULL,
  PRIMARY KEY (`customer_id`),
  UNIQUE KEY `uq_customers_user` (`user_id`),
  CONSTRAINT `fk_customers_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 3. ADMINS
-- ============================================================
CREATE TABLE IF NOT EXISTS `admins` (
  `admin_id`         VARCHAR(36) NOT NULL DEFAULT (UUID()),
  `user_id`          VARCHAR(36) NOT NULL,
  `permission_level` INT         NOT NULL DEFAULT 1,
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `uq_admins_user` (`user_id`),
  CONSTRAINT `fk_admins_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 4. BRANDS
-- ============================================================
CREATE TABLE IF NOT EXISTS `brands` (
  `brand_id`    VARCHAR(36)  NOT NULL DEFAULT (UUID()),
  `brand_name`  VARCHAR(100) NOT NULL,
  `description` TEXT         DEFAULT NULL,
  `logo`        VARCHAR(255) DEFAULT NULL,
  `origin`      VARCHAR(100) DEFAULT NULL,
  `status`      ENUM('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`brand_id`),
  UNIQUE KEY `uq_brands_name` (`brand_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 5. PRODUCTS
-- ============================================================
CREATE TABLE IF NOT EXISTS `products` (
  `product_id`   VARCHAR(36)    NOT NULL DEFAULT (UUID()),
  `brand_id`     VARCHAR(36)    NOT NULL,
  `product_name` VARCHAR(200)   NOT NULL,
  `description`  TEXT           DEFAULT NULL,
  `base_price`   DECIMAL(12,2)  NOT NULL DEFAULT 0,
  `category`     VARCHAR(100)   DEFAULT NULL,
  `gender_type`  ENUM('male','female','unisex') NOT NULL DEFAULT 'unisex',
  `status`       ENUM('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`product_id`),
  KEY `idx_products_brand` (`brand_id`),
  KEY `idx_products_category` (`category`),
  CONSTRAINT `fk_products_brand`
    FOREIGN KEY (`brand_id`) REFERENCES `brands` (`brand_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 6. PRODUCT_IMAGES
-- ============================================================
CREATE TABLE IF NOT EXISTS `product_images` (
  `image_id`   VARCHAR(36)  NOT NULL DEFAULT (UUID()),
  `product_id` VARCHAR(36)  NOT NULL,
  `image_url`  VARCHAR(500) NOT NULL,
  `is_primary` TINYINT(1)   NOT NULL DEFAULT 0,
  PRIMARY KEY (`image_id`),
  KEY `idx_images_product` (`product_id`),
  CONSTRAINT `fk_images_product`
    FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 7. PRODUCT_VARIANTS
-- ============================================================
CREATE TABLE IF NOT EXISTS `product_variants` (
  `variant_id`       VARCHAR(36)   NOT NULL DEFAULT (UUID()),
  `product_id`       VARCHAR(36)   NOT NULL,
  `size`             VARCHAR(20)   NOT NULL,
  `color`            VARCHAR(50)   NOT NULL,
  `stock_quantity`   INT           NOT NULL DEFAULT 0,
  `price_adjustment` DECIMAL(12,2) NOT NULL DEFAULT 0,
  PRIMARY KEY (`variant_id`),
  KEY `idx_variants_product` (`product_id`),
  CONSTRAINT `fk_variants_product`
    FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 8. PROMOTIONS
-- ============================================================
CREATE TABLE IF NOT EXISTS `promotions` (
  `promotion_id`    VARCHAR(36)   NOT NULL DEFAULT (UUID()),
  `code`            VARCHAR(50)   NOT NULL,
  `promotion_type`  VARCHAR(100)  DEFAULT NULL,
  `discount_type`   ENUM('percent','fixed') NOT NULL DEFAULT 'percent',
  `discount_value`  DECIMAL(10,2) NOT NULL DEFAULT 0,
  `start_date`      DATE          NOT NULL,
  `end_date`        DATE          NOT NULL,
  `minimum_value`   DECIMAL(12,2) NOT NULL DEFAULT 0,
  `is_active`       TINYINT(1)    NOT NULL DEFAULT 1,
  PRIMARY KEY (`promotion_id`),
  UNIQUE KEY `uq_promotions_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 9. ADDRESSES
-- ============================================================
CREATE TABLE IF NOT EXISTS `addresses` (
  `address_id`     VARCHAR(36)  NOT NULL DEFAULT (UUID()),
  `customer_id`    VARCHAR(36)  NOT NULL,
  `receiver_name`  VARCHAR(100) NOT NULL,
  `phone`          VARCHAR(20)  NOT NULL,
  `street_address` VARCHAR(255) NOT NULL,
  `district`       VARCHAR(100) DEFAULT NULL,
  `city`           VARCHAR(100) NOT NULL,
  `zip_code`       VARCHAR(20)  DEFAULT NULL,
  PRIMARY KEY (`address_id`),
  KEY `idx_addresses_customer` (`customer_id`),
  CONSTRAINT `fk_addresses_customer`
    FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Thêm FK default_address cho customers (sau khi addresses được tạo)
ALTER TABLE `customers`
  ADD CONSTRAINT `fk_customers_default_addr`
    FOREIGN KEY (`default_address_id`) REFERENCES `addresses` (`address_id`)
    ON DELETE SET NULL;

-- ============================================================
-- 10. CARTS
-- ============================================================
CREATE TABLE IF NOT EXISTS `carts` (
  `cart_id`      VARCHAR(36)   NOT NULL DEFAULT (UUID()),
  `customer_id`  VARCHAR(36)   NOT NULL,
  `total_amount` DECIMAL(12,2) NOT NULL DEFAULT 0,
  PRIMARY KEY (`cart_id`),
  UNIQUE KEY `uq_carts_customer` (`customer_id`),
  CONSTRAINT `fk_carts_customer`
    FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 11. CART_ITEMS
-- ============================================================
CREATE TABLE IF NOT EXISTS `cart_items` (
  `cart_item_id` VARCHAR(36)   NOT NULL DEFAULT (UUID()),
  `cart_id`      VARCHAR(36)   NOT NULL,
  `variant_id`   VARCHAR(36)   NOT NULL,
  `quantity`     INT           NOT NULL DEFAULT 1,
  `unit_price`   DECIMAL(12,2) NOT NULL DEFAULT 0,
  PRIMARY KEY (`cart_item_id`),
  UNIQUE KEY `uq_cart_variant` (`cart_id`, `variant_id`),
  KEY `idx_cart_items_variant` (`variant_id`),
  CONSTRAINT `fk_cart_items_cart`
    FOREIGN KEY (`cart_id`) REFERENCES `carts` (`cart_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cart_items_variant`
    FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`variant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 12. ORDERS
-- ============================================================
CREATE TABLE IF NOT EXISTS `orders` (
  `order_id`        VARCHAR(36)   NOT NULL,
  `customer_id`     VARCHAR(36)   NOT NULL,
  `address_id`      VARCHAR(36)   DEFAULT NULL,
  `promotion_id`    VARCHAR(36)   DEFAULT NULL,
  `order_date`      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `total_amount`    DECIMAL(12,2) NOT NULL DEFAULT 0,
  `discount_amount` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `final_amount`    DECIMAL(12,2) NOT NULL DEFAULT 0,
  `order_status`    ENUM('pending','confirmed','shipped','delivered','cancelled')
                    NOT NULL DEFAULT 'pending',
  `payment_status`  ENUM('pending','paid','failed','refunded')
                    NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`order_id`),
  KEY `idx_orders_customer` (`customer_id`),
  KEY `idx_orders_address`  (`address_id`),
  KEY `idx_orders_promo`    (`promotion_id`),
  CONSTRAINT `fk_orders_customer`
    FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`),
  CONSTRAINT `fk_orders_address`
    FOREIGN KEY (`address_id`) REFERENCES `addresses` (`address_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_orders_promotion`
    FOREIGN KEY (`promotion_id`) REFERENCES `promotions` (`promotion_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 13. ORDER_ITEMS
-- ============================================================
CREATE TABLE IF NOT EXISTS `order_items` (
  `order_item_id` VARCHAR(36)   NOT NULL DEFAULT (UUID()),
  `order_id`      VARCHAR(36)   NOT NULL,
  `variant_id`    VARCHAR(36)   NOT NULL,
  `quantity`      INT           NOT NULL DEFAULT 1,
  `unit_price`    DECIMAL(12,2) NOT NULL DEFAULT 0,
  PRIMARY KEY (`order_item_id`),
  KEY `idx_order_items_order`   (`order_id`),
  KEY `idx_order_items_variant` (`variant_id`),
  CONSTRAINT `fk_order_items_order`
    FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_order_items_variant`
    FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`variant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 14. SHIPMENTS
-- ============================================================
CREATE TABLE IF NOT EXISTS `shipments` (
  `shipment_id`            VARCHAR(36)  NOT NULL DEFAULT (UUID()),
  `order_id`               VARCHAR(36)  NOT NULL,
  `carrier`                VARCHAR(100) DEFAULT NULL,
  `tracking_number`        VARCHAR(100) DEFAULT NULL,
  `status`                 ENUM('pending','in_transit','delivered','failed')
                           NOT NULL DEFAULT 'pending',
  `shipping_address`       TEXT         DEFAULT NULL,
  `estimated_delivery_date` DATE         DEFAULT NULL,
  PRIMARY KEY (`shipment_id`),
  UNIQUE KEY `uq_shipments_order` (`order_id`),
  CONSTRAINT `fk_shipments_order`
    FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 15. PAYMENTS
-- ============================================================
CREATE TABLE IF NOT EXISTS `payments` (
  `payment_id`      VARCHAR(36)   NOT NULL DEFAULT (UUID()),
  `order_id`        VARCHAR(36)   NOT NULL,
  `payment_method`  VARCHAR(50)   NOT NULL DEFAULT 'cod',
  `payment_date`    DATETIME      DEFAULT NULL,
  `amount`          DECIMAL(12,2) NOT NULL DEFAULT 0,
  `payment_status`  ENUM('pending','paid','failed','refunded')
                    NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`payment_id`),
  KEY `idx_payments_order` (`order_id`),
  CONSTRAINT `fk_payments_order`
    FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 16. REVIEWS
-- ============================================================
CREATE TABLE IF NOT EXISTS `reviews` (
  `review_id`   VARCHAR(36) NOT NULL DEFAULT (UUID()),
  `customer_id` VARCHAR(36) NOT NULL,
  `product_id`  VARCHAR(36) NOT NULL,
  `rating`      TINYINT     NOT NULL DEFAULT 5 CHECK (`rating` BETWEEN 1 AND 5),
  `comment`     TEXT        DEFAULT NULL,
  `created_at`  DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`review_id`),
  KEY `idx_reviews_customer` (`customer_id`),
  KEY `idx_reviews_product`  (`product_id`),
  CONSTRAINT `fk_reviews_customer`
    FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_reviews_product`
    FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
--  STORED PROCEDURES
-- ============================================================

DELIMITER $$

DROP PROCEDURE IF EXISTS `sp_admin_get_dashboard_stats`$$
CREATE PROCEDURE `sp_admin_get_dashboard_stats`()
BEGIN
  SELECT 
    (SELECT COUNT(*) FROM products)  AS product_count,
    (SELECT COUNT(*) FROM orders)    AS order_count,
    (SELECT COUNT(*) FROM customers) AS customer_count;
END$$

DROP PROCEDURE IF EXISTS `sp_admin_get_brands`$$
CREATE PROCEDURE `sp_admin_get_brands`()
BEGIN
  SELECT brand_id, brand_name
  FROM brands
  ORDER BY brand_name ASC;
END$$

DROP PROCEDURE IF EXISTS `sp_admin_count_products`$$
CREATE PROCEDURE `sp_admin_count_products`()
BEGIN
  SELECT COUNT(*) AS total_items
  FROM products;
END$$

DROP PROCEDURE IF EXISTS `sp_admin_get_products_paginated`$$
CREATE PROCEDURE `sp_admin_get_products_paginated`(
  IN p_limit INT,
  IN p_offset INT
)
BEGIN
  SELECT p.*, b.brand_name,
         (SELECT image_url
            FROM product_images
           WHERE product_id = p.product_id AND is_primary = 1
           LIMIT 1) AS image
  FROM products p
  LEFT JOIN brands b ON p.brand_id = b.brand_id
  ORDER BY p.product_name ASC
  LIMIT p_limit OFFSET p_offset;
END$$

DROP PROCEDURE IF EXISTS `sp_admin_create_product`$$
CREATE PROCEDURE `sp_admin_create_product`(
  IN p_brand_id VARCHAR(36),
  IN p_product_name VARCHAR(200),
  IN p_description TEXT,
  IN p_base_price DECIMAL(12,2),
  IN p_category VARCHAR(100),
  IN p_gender_type VARCHAR(20),
  IN p_status VARCHAR(20),
  IN p_image_url VARCHAR(500),
  IN p_color VARCHAR(50)
)
BEGIN
  DECLARE v_product_id VARCHAR(36);

  SET v_product_id = UUID();

  INSERT INTO products (
    product_id, brand_id, product_name, description, base_price, category, gender_type, status
  ) VALUES (
    v_product_id, p_brand_id, p_product_name, p_description, p_base_price, p_category, p_gender_type, p_status
  );

  IF p_image_url IS NOT NULL AND TRIM(p_image_url) <> '' THEN
    INSERT INTO product_images (image_id, product_id, image_url, is_primary)
    VALUES (UUID(), v_product_id, p_image_url, 1);
  END IF;

  INSERT INTO product_variants (variant_id, product_id, size, color, stock_quantity, price_adjustment)
  VALUES
    (UUID(), v_product_id, 'S',  COALESCE(NULLIF(TRIM(p_color), ''), 'Trắng'), 50, 0),
    (UUID(), v_product_id, 'M',  COALESCE(NULLIF(TRIM(p_color), ''), 'Trắng'), 50, 0),
    (UUID(), v_product_id, 'L',  COALESCE(NULLIF(TRIM(p_color), ''), 'Trắng'), 50, 0),
    (UUID(), v_product_id, 'XL', COALESCE(NULLIF(TRIM(p_color), ''), 'Trắng'), 50, 0);

  SELECT v_product_id AS product_id;
END$$

DROP PROCEDURE IF EXISTS `sp_admin_update_product`$$
CREATE PROCEDURE `sp_admin_update_product`(
  IN p_product_id VARCHAR(36),
  IN p_brand_id VARCHAR(36),
  IN p_product_name VARCHAR(200),
  IN p_description TEXT,
  IN p_base_price DECIMAL(12,2),
  IN p_category VARCHAR(100),
  IN p_gender_type VARCHAR(20),
  IN p_status VARCHAR(20),
  IN p_image_url VARCHAR(500)
)
BEGIN
  UPDATE products
     SET brand_id = p_brand_id,
         product_name = p_product_name,
         description = p_description,
         base_price = p_base_price,
         category = p_category,
         gender_type = p_gender_type,
         status = p_status
   WHERE product_id = p_product_id;

  IF p_image_url IS NOT NULL AND TRIM(p_image_url) <> '' THEN
    IF EXISTS (
      SELECT 1 FROM product_images
      WHERE product_id = p_product_id AND is_primary = 1
    ) THEN
      UPDATE product_images
         SET image_url = p_image_url
       WHERE product_id = p_product_id AND is_primary = 1;
    ELSE
      INSERT INTO product_images (image_id, product_id, image_url, is_primary)
      VALUES (UUID(), p_product_id, p_image_url, 1);
    END IF;
  END IF;

  SELECT p_product_id AS product_id;
END$$

DROP PROCEDURE IF EXISTS `sp_admin_delete_product`$$
CREATE PROCEDURE `sp_admin_delete_product`(
  IN p_product_id VARCHAR(36)
)
BEGIN
  DECLARE v_order_item_count INT DEFAULT 0;

  SELECT COUNT(*) INTO v_order_item_count
  FROM order_items oi
  INNER JOIN product_variants pv ON oi.variant_id = pv.variant_id
  WHERE pv.product_id = p_product_id;

  IF v_order_item_count > 0 THEN
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'Sản phẩm đang có đơn hàng, không thể xóa. Vui lòng tắt hiển thị hoặc đổi trạng thái.';
  ELSE
    DELETE FROM product_images WHERE product_id = p_product_id;
    DELETE FROM product_variants WHERE product_id = p_product_id;
    DELETE FROM products WHERE product_id = p_product_id;
    SELECT 'Xóa sản phẩm thành công.' AS message;
  END IF;
END$$

DROP PROCEDURE IF EXISTS `sp_find_or_create_customer_by_phone`$$
CREATE PROCEDURE `sp_find_or_create_customer_by_phone`(
  IN p_phone VARCHAR(20),
  IN p_full_name VARCHAR(100)
)
BEGIN
  DECLARE v_user_id VARCHAR(36);
  DECLARE v_customer_id VARCHAR(36);
  DECLARE v_email VARCHAR(100);

  SELECT user_id INTO v_user_id
  FROM users
  WHERE phone = p_phone
  LIMIT 1;

  IF v_user_id IS NULL THEN
    SET v_user_id = UUID();
    SET v_email = CONCAT('guest_', UNIX_TIMESTAMP(), '_', FLOOR(RAND() * 10000), '@shop.com');

    INSERT INTO users (user_id, full_name, email, phone, password, role)
    VALUES (v_user_id, p_full_name, v_email, p_phone, '123456', 'customer');
  ELSE
    UPDATE users
       SET full_name = COALESCE(NULLIF(TRIM(p_full_name), ''), full_name)
     WHERE user_id = v_user_id;
  END IF;

  SELECT customer_id INTO v_customer_id
  FROM customers
  WHERE user_id = v_user_id
  LIMIT 1;

  SELECT v_user_id AS user_id, v_customer_id AS customer_id;
END$$

DROP PROCEDURE IF EXISTS `sp_create_address`$$
CREATE PROCEDURE `sp_create_address`(
  IN p_address_id VARCHAR(36),
  IN p_customer_id VARCHAR(36),
  IN p_receiver_name VARCHAR(100),
  IN p_phone VARCHAR(20),
  IN p_street_address VARCHAR(255),
  IN p_city VARCHAR(100)
)
BEGIN
  INSERT INTO addresses (address_id, customer_id, receiver_name, phone, street_address, city)
  VALUES (p_address_id, p_customer_id, p_receiver_name, p_phone, p_street_address, p_city);

  SELECT p_address_id AS address_id;
END$$

DROP PROCEDURE IF EXISTS `sp_create_order`$$
CREATE PROCEDURE `sp_create_order`(
  IN p_order_id VARCHAR(36),
  IN p_customer_id VARCHAR(36),
  IN p_address_id VARCHAR(36),
  IN p_total_amount DECIMAL(12,2),
  IN p_discount_amount DECIMAL(12,2)
)
BEGIN
  INSERT INTO orders (
    order_id, customer_id, address_id, total_amount, discount_amount, order_status, payment_status
  ) VALUES (
    p_order_id, p_customer_id, p_address_id, p_total_amount, p_discount_amount, 'pending', 'pending'
  );

  SELECT p_order_id AS order_id;
END$$

DROP PROCEDURE IF EXISTS `sp_find_variant_for_checkout`$$
CREATE PROCEDURE `sp_find_variant_for_checkout`(
  IN p_product_id VARCHAR(36),
  IN p_size VARCHAR(20)
)
BEGIN
  DECLARE v_variant_id VARCHAR(36);

  SELECT variant_id INTO v_variant_id
  FROM product_variants
  WHERE product_id = p_product_id
    AND size = p_size
  LIMIT 1;

  IF v_variant_id IS NULL THEN
    SELECT variant_id INTO v_variant_id
    FROM product_variants
    WHERE product_id = p_product_id
    LIMIT 1;
  END IF;

  SELECT v_variant_id AS variant_id;
END$$

DROP PROCEDURE IF EXISTS `sp_create_order_item`$$
CREATE PROCEDURE `sp_create_order_item`(
  IN p_order_item_id VARCHAR(36),
  IN p_order_id VARCHAR(36),
  IN p_variant_id VARCHAR(36),
  IN p_quantity INT,
  IN p_unit_price DECIMAL(12,2)
)
BEGIN
  INSERT INTO order_items (order_item_id, order_id, variant_id, quantity, unit_price)
  VALUES (p_order_item_id, p_order_id, p_variant_id, p_quantity, p_unit_price);

  SELECT p_order_item_id AS order_item_id;
END$$

DELIMITER ;


-- ============================================================
--  SAMPLE DATA (tuỳ chọn)
-- ============================================================

-- Admin user
INSERT INTO `users` (`user_id`, `full_name`, `email`, `password`, `role`, `status`) VALUES
('admin-001', 'Admin', 'admin@shop.com', '$2y$12$LJkv7W8sKh2XrNmZ0qP3NeW.O6RgvC8eIhFEhKXJyuD9bpLAlkVmS', 'admin', 'active');
-- Mật khẩu trên là hash của "admin123" (bcrypt)

INSERT INTO `admins` (`admin_id`, `user_id`, `permission_level`) VALUES
('admins-001', 'admin-001', 3);

-- Brands
INSERT INTO `brands` (`brand_id`, `brand_name`, `description`, `origin`, `status`) VALUES
('brand-001', 'Nike',   'Just Do It',        'USA',   'active'),
('brand-002', 'Adidas', 'Impossible Is Nothing', 'Germany', 'active'),
('brand-003', 'Local Brand VN', 'Thương hiệu Việt', 'Vietnam', 'active');

-- Promotions
INSERT INTO `promotions` (`promotion_id`, `code`, `discount_type`, `discount_value`, `start_date`, `end_date`, `minimum_value`, `is_active`) VALUES
('promo-001', 'WELCOME10', 'percent', 10, '2026-01-01', '2026-12-31', 0,       1),
('promo-002', 'SALE50K',   'fixed',   50000, '2026-01-01', '2026-12-31', 200000, 1);

-- ============================================================
--  TRIGGERS
-- ============================================================

DELIMITER $$

-- ─────────────────────────────────────────────────────────────
-- TR-1: Sau khi INSERT vào users với role='customer'
--        → tự động tạo bản ghi tương ứng trong customers
-- ─────────────────────────────────────────────────────────────
CREATE TRIGGER `trg_after_insert_user_create_customer`
AFTER INSERT ON `users`
FOR EACH ROW
BEGIN
  IF NEW.role = 'customer' THEN
    INSERT INTO `customers` (`customer_id`, `user_id`, `gender`, `loyalty_points`)
    VALUES (UUID(), NEW.user_id, 'other', 0);
  END IF;
END$$

-- ─────────────────────────────────────────────────────────────
-- TR-2: Trước khi INSERT vào order_items
--        → kiểm tra tồn kho, báo lỗi nếu không đủ hàng
-- ─────────────────────────────────────────────────────────────
CREATE TRIGGER `trg_before_insert_order_item_check_stock`
BEFORE INSERT ON `order_items`
FOR EACH ROW
BEGIN
  DECLARE v_stock INT DEFAULT 0;

  SELECT `stock_quantity` INTO v_stock
  FROM `product_variants`
  WHERE `variant_id` = NEW.variant_id;

  IF v_stock < NEW.quantity THEN
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'Không đủ hàng trong kho cho sản phẩm này.';
  END IF;
END$$

-- ─────────────────────────────────────────────────────────────
-- TR-3: Sau khi INSERT vào order_items
--        → trừ tồn kho trong product_variants
-- ─────────────────────────────────────────────────────────────
CREATE TRIGGER `trg_after_insert_order_item_reduce_stock`
AFTER INSERT ON `order_items`
FOR EACH ROW
BEGIN
  UPDATE `product_variants`
  SET `stock_quantity` = `stock_quantity` - NEW.quantity
  WHERE `variant_id` = NEW.variant_id;
END$$

-- ─────────────────────────────────────────────────────────────
-- TR-4: Sau khi UPDATE orders: khi order_status chuyển sang 'cancelled'
--        → hoàn lại tồn kho cho tất cả order_items của đơn đó
-- ─────────────────────────────────────────────────────────────
CREATE TRIGGER `trg_after_update_order_restore_stock`
AFTER UPDATE ON `orders`
FOR EACH ROW
BEGIN
  IF OLD.order_status <> 'cancelled' AND NEW.order_status = 'cancelled' THEN
    UPDATE `product_variants` pv
    JOIN `order_items` oi ON oi.variant_id = pv.variant_id
    SET pv.`stock_quantity` = pv.`stock_quantity` + oi.`quantity`
    WHERE oi.order_id = NEW.order_id;
  END IF;
END$$

-- ─────────────────────────────────────────────────────────────
-- TR-5: Sau khi UPDATE orders: khi order_status chuyển sang 'delivered'
--        → cộng loyalty points cho customer (1 điểm / 10.000 VNĐ)
-- ─────────────────────────────────────────────────────────────
CREATE TRIGGER `trg_after_update_order_add_loyalty`
AFTER UPDATE ON `orders`
FOR EACH ROW
BEGIN
  DECLARE v_points INT;

  IF OLD.order_status <> 'delivered' AND NEW.order_status = 'delivered' THEN
    SET v_points = FLOOR(NEW.final_amount / 10000);
    UPDATE `customers`
    SET `loyalty_points` = `loyalty_points` + v_points
    WHERE `customer_id` = NEW.customer_id;
  END IF;
END$$

-- ─────────────────────────────────────────────────────────────
-- TR-6: Sau khi INSERT/UPDATE/DELETE cart_items
--        → cập nhật lại total_amount trong carts
-- ─────────────────────────────────────────────────────────────
CREATE TRIGGER `trg_after_insert_cart_item_update_total`
AFTER INSERT ON `cart_items`
FOR EACH ROW
BEGIN
  UPDATE `carts`
  SET `total_amount` = (
    SELECT COALESCE(SUM(ci.unit_price * ci.quantity), 0)
    FROM `cart_items` ci
    WHERE ci.cart_id = NEW.cart_id
  )
  WHERE `cart_id` = NEW.cart_id;
END$$

CREATE TRIGGER `trg_after_update_cart_item_update_total`
AFTER UPDATE ON `cart_items`
FOR EACH ROW
BEGIN
  UPDATE `carts`
  SET `total_amount` = (
    SELECT COALESCE(SUM(ci.unit_price * ci.quantity), 0)
    FROM `cart_items` ci
    WHERE ci.cart_id = NEW.cart_id
  )
  WHERE `cart_id` = NEW.cart_id;
END$$

CREATE TRIGGER `trg_after_delete_cart_item_update_total`
AFTER DELETE ON `cart_items`
FOR EACH ROW
BEGIN
  UPDATE `carts`
  SET `total_amount` = (
    SELECT COALESCE(SUM(ci.unit_price * ci.quantity), 0)
    FROM `cart_items` ci
    WHERE ci.cart_id = OLD.cart_id
  )
  WHERE `cart_id` = OLD.cart_id;
END$$

-- ─────────────────────────────────────────────────────────────
-- TR-7: Trước khi INSERT vào orders
--        → tự động tính final_amount = total_amount - discount_amount
-- ─────────────────────────────────────────────────────────────
CREATE TRIGGER `trg_before_insert_order_calc_final`
BEFORE INSERT ON `orders`
FOR EACH ROW
BEGIN
  SET NEW.final_amount = NEW.total_amount - NEW.discount_amount;
  IF NEW.final_amount < 0 THEN
    SET NEW.final_amount = 0;
  END IF;
END$$

-- ─────────────────────────────────────────────────────────────
-- TR-8: Sau khi UPDATE payments: khi payment_status → 'paid'
--        → set payment_date = NOW() và cập nhật payment_status trong orders
-- ─────────────────────────────────────────────────────────────
CREATE TRIGGER `trg_after_update_payment_set_date`
AFTER UPDATE ON `payments`
FOR EACH ROW
BEGIN
  IF OLD.payment_status <> 'paid' AND NEW.payment_status = 'paid' THEN
    UPDATE `payments`
    SET `payment_date` = NOW()
    WHERE `payment_id` = NEW.payment_id;

    UPDATE `orders`
    SET `payment_status` = 'paid'
    WHERE `order_id` = NEW.order_id;
  END IF;
END$$

DELIMITER ;

-- ============================================================
--  Tổng kết Triggers
-- ============================================================
-- TR-1  AFTER  INSERT  users          → tạo customer record tự động
-- TR-2  BEFORE INSERT  order_items    → kiểm tra tồn kho (báo lỗi nếu thiếu)
-- TR-3  AFTER  INSERT  order_items    → trừ stock_quantity
-- TR-4  AFTER  UPDATE  orders         → hoàn kho khi đơn bị huỷ
-- TR-5  AFTER  UPDATE  orders         → cộng loyalty points khi giao thành công
-- TR-6a AFTER  INSERT  cart_items     → cập nhật total_amount của cart
-- TR-6b AFTER  UPDATE  cart_items     → cập nhật total_amount của cart
-- TR-6c AFTER  DELETE  cart_items     → cập nhật total_amount của cart
-- TR-7  BEFORE INSERT  orders         → tính final_amount tự động
-- TR-8  AFTER  UPDATE  payments       → set payment_date + sync payment_status vào orders

-- ============================================================
--  DỮ LIỆU MẪU (SAMPLE DATA)
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- Tắt TR-1 tạm để insert customers thủ công
DROP TRIGGER IF EXISTS `trg_after_insert_user_create_customer`;
-- Tắt TR-2/TR-3 để tránh trừ kho khi insert mẫu
DROP TRIGGER IF EXISTS `trg_before_insert_order_item_check_stock`;
DROP TRIGGER IF EXISTS `trg_after_insert_order_item_reduce_stock`;
-- Tắt TR-6 để tránh tự tính cart total
DROP TRIGGER IF EXISTS `trg_after_insert_cart_item_update_total`;
DROP TRIGGER IF EXISTS `trg_after_update_cart_item_update_total`;
DROP TRIGGER IF EXISTS `trg_after_delete_cart_item_update_total`;
-- Tắt TR-7
DROP TRIGGER IF EXISTS `trg_before_insert_order_calc_final`;

-- ── USERS ─────────────────────────────────────────────────────
INSERT INTO `users` (`user_id`,`full_name`,`email`,`phone`,`password`,`role`,`status`,`created_at`) VALUES
('usr-001','Nguyễn Văn An',  'an.nguyen@gmail.com', '0901111111','$2y$12$LJkv7W8sKh2XrNmZ0qP3NeW.O6RgvC8eIhFEhKXJyuD9bpLAlkVmS','customer','active','2026-01-10 08:00:00'),
('usr-002','Trần Thị Bình',  'binh.tran@gmail.com', '0902222222','$2y$12$LJkv7W8sKh2XrNmZ0qP3NeW.O6RgvC8eIhFEhKXJyuD9bpLAlkVmS','customer','active','2026-01-15 09:30:00'),
('usr-003','Lê Minh Châu',   'chau.le@gmail.com',   '0903333333','$2y$12$LJkv7W8sKh2XrNmZ0qP3NeW.O6RgvC8eIhFEhKXJyuD9bpLAlkVmS','customer','active','2026-02-01 10:00:00'),
('usr-004','Phạm Quỳnh Dung','dung.pham@gmail.com', '0904444444','$2y$12$LJkv7W8sKh2XrNmZ0qP3NeW.O6RgvC8eIhFEhKXJyuD9bpLAlkVmS','customer','active','2026-02-10 14:00:00'),
('usr-005','Hoàng Tuấn Em',  'em.hoang@gmail.com',  '0905555555','$2y$12$LJkv7W8sKh2XrNmZ0qP3NeW.O6RgvC8eIhFEhKXJyuD9bpLAlkVmS','customer','banned','2026-03-01 11:00:00');
-- Mật khẩu tất cả: admin123

-- ── CUSTOMERS ─────────────────────────────────────────────────
INSERT INTO `customers` (`customer_id`,`user_id`,`gender`,`date_of_birth`,`loyalty_points`,`default_address_id`) VALUES
('cust-001','usr-001','male',  '1995-03-15',120,NULL),
('cust-002','usr-002','female','1998-07-22',470,NULL),
('cust-003','usr-003','male',  '1992-11-05', 80,NULL),
('cust-004','usr-004','female','2000-01-30',500,NULL),
('cust-005','usr-005','male',  '1990-05-20',  0,NULL);

-- ── PRODUCTS ──────────────────────────────────────────────────
INSERT INTO `products` (`product_id`,`brand_id`,`product_name`,`description`,`base_price`,`category`,`gender_type`,`status`) VALUES
('prod-001','brand-001','Nike Air Force 1',     'Giày sneaker cổ điển, đế cao su chắc chắn.',         1200000,'shoes', 'unisex','active'),
('prod-002','brand-001','Nike Dri-FIT T-Shirt', 'Áo thun thể thao thoáng khí, chất liệu Dri-FIT.',    450000,'tops',  'male',  'active'),
('prod-003','brand-002','Adidas Ultraboost 22', 'Giày chạy bộ với công nghệ Boost êm ái.',            2500000,'shoes', 'unisex','active'),
('prod-004','brand-002','Adidas Tiro Track Pant','Quần training co giãn 4 chiều.',                     550000,'bottoms','male', 'active'),
('prod-005','brand-003','Local Tee Classic',    'Áo thun cotton 100%, in hoạ tiết thương hiệu Việt.', 280000,'tops',  'unisex','active'),
('prod-006','brand-003','Local Street Hoodie',  'Áo hoodie form rộng, chất nỉ bông dày dặn.',          680000,'tops',  'unisex','active');

-- ── PRODUCT_VARIANTS ──────────────────────────────────────────
INSERT INTO `product_variants` (`variant_id`,`product_id`,`size`,`color`,`stock_quantity`,`price_adjustment`) VALUES
('var-001','prod-001','40','Trắng',   50,      0),('var-002','prod-001','41','Trắng',   40,      0),
('var-003','prod-001','42','Trắng',   30,      0),('var-004','prod-001','41','Đen',     25,  50000),
('var-005','prod-001','42','Đen',     20,  50000),
('var-006','prod-002','S', 'Xanh navy',60,     0),('var-007','prod-002','M', 'Xanh navy',80,   0),
('var-008','prod-002','L', 'Đen',     70,      0),('var-009','prod-002','XL','Đen',     40,     0),
('var-010','prod-003','40','Trắng/Xanh',15,    0),('var-011','prod-003','41','Trắng/Xanh',20,  0),
('var-012','prod-003','42','Đen/Đỏ',  18, 100000),('var-013','prod-003','43','Đen/Đỏ',  10, 100000),
('var-014','prod-004','S', 'Đen',     90,      0),('var-015','prod-004','M', 'Đen',     85,     0),
('var-016','prod-004','L', 'Đen',     70,      0),
('var-017','prod-005','S', 'Kem',    100,      0),('var-018','prod-005','M', 'Kem',    120,     0),
('var-019','prod-005','L', 'Xám',     90,      0),('var-020','prod-005','XL','Xám',     60,     0),
('var-021','prod-006','M', 'Đen',     45,      0),('var-022','prod-006','L', 'Đen',     40,     0),
('var-023','prod-006','XL','Xanh rêu',30,  50000),('var-024','prod-006','L', 'Xanh rêu',35, 50000);

-- ── PRODUCT_IMAGES ────────────────────────────────────────────
INSERT INTO `product_images` (`image_id`,`product_id`,`image_url`,`is_primary`) VALUES
('img-001','prod-001','/assets/image/nike-af1-1.jpg',    1),
('img-002','prod-001','/assets/image/nike-af1-2.jpg',    0),
('img-003','prod-002','/assets/image/nike-dri-fit.jpg',  1),
('img-004','prod-003','/assets/image/adidas-ub22-1.jpg', 1),
('img-005','prod-003','/assets/image/adidas-ub22-2.jpg', 0),
('img-006','prod-004','/assets/image/adidas-tiro.jpg',   1),
('img-007','prod-005','/assets/image/local-tee.jpg',     1),
('img-008','prod-006','/assets/image/local-hoodie-1.jpg',1),
('img-009','prod-006','/assets/image/local-hoodie-2.jpg',0);

-- ── ADDRESSES ─────────────────────────────────────────────────
INSERT INTO `addresses` (`address_id`,`customer_id`,`receiver_name`,`phone`,`street_address`,`district`,`city`,`zip_code`) VALUES
('addr-001','cust-001','Nguyễn Văn An',  '0901111111','12 Nguyễn Huệ',    'Quận 1',          'TP. Hồ Chí Minh','70000'),
('addr-002','cust-002','Trần Thị Bình',  '0902222222','45 Lý Thường Kiệt','Quận Hoàn Kiếm',  'Hà Nội',         '10000'),
('addr-003','cust-002','Trần Thị Bình',  '0902222222','8 Trần Phú',       'Quận Ba Đình',    'Hà Nội',         '10000'),
('addr-004','cust-003','Lê Minh Châu',   '0903333333','99 Bùi Thị Xuân',  'Quận Hai Bà Trưng','Hà Nội',        '10000'),
('addr-005','cust-004','Phạm Quỳnh Dung','0904444444','23 Pasteur',       'Quận 3',          'TP. Hồ Chí Minh','70000');

UPDATE `customers` SET `default_address_id`='addr-001' WHERE `customer_id`='cust-001';
UPDATE `customers` SET `default_address_id`='addr-002' WHERE `customer_id`='cust-002';
UPDATE `customers` SET `default_address_id`='addr-004' WHERE `customer_id`='cust-003';
UPDATE `customers` SET `default_address_id`='addr-005' WHERE `customer_id`='cust-004';

-- ── CARTS ─────────────────────────────────────────────────────
INSERT INTO `carts` (`cart_id`,`customer_id`,`total_amount`) VALUES
('cart-001','cust-001',1240000),
('cart-002','cust-002',      0),
('cart-003','cust-003',      0),
('cart-004','cust-004',2500000);

INSERT INTO `cart_items` (`cart_item_id`,`cart_id`,`variant_id`,`quantity`,`unit_price`) VALUES
('ci-001','cart-001','var-021',1, 680000),
('ci-002','cart-001','var-019',2, 280000),
('ci-003','cart-004','var-011',1,2500000);

-- ── ORDERS ────────────────────────────────────────────────────
INSERT INTO `orders` (`order_id`,`customer_id`,`address_id`,`promotion_id`,`order_date`,`total_amount`,`discount_amount`,`final_amount`,`order_status`,`payment_status`) VALUES
('ord-001','cust-002','addr-002',NULL,       '2026-03-01 10:15:00',1200000,      0,1200000,'delivered','paid'),
('ord-002','cust-002','addr-003','promo-001','2026-03-15 14:30:00',2500000, 250000,2250000,'shipped',  'paid'),
('ord-003','cust-003','addr-004','promo-002','2026-03-20 09:00:00', 730000,  50000, 680000,'confirmed','pending');

-- ── ORDER_ITEMS ───────────────────────────────────────────────
INSERT INTO `order_items` (`order_item_id`,`order_id`,`variant_id`,`quantity`,`unit_price`) VALUES
('oi-001','ord-001','var-001',1,1200000),
('oi-002','ord-002','var-010',1,2500000),
('oi-003','ord-003','var-017',1, 280000),
('oi-004','ord-003','var-022',1, 680000);

-- ── PAYMENTS ──────────────────────────────────────────────────
INSERT INTO `payments` (`payment_id`,`order_id`,`payment_method`,`payment_date`,`amount`,`payment_status`) VALUES
('pay-001','ord-001','cod',  '2026-03-03 16:00:00',1200000,'paid'),
('pay-002','ord-002','momo', '2026-03-15 14:31:00',2250000,'paid'),
('pay-003','ord-003','cod',  NULL,                  680000,'pending');

-- ── SHIPMENTS ─────────────────────────────────────────────────
INSERT INTO `shipments` (`shipment_id`,`order_id`,`carrier`,`tracking_number`,`status`,`shipping_address`,`estimated_delivery_date`) VALUES
('ship-001','ord-001','GHN', 'GHN123456789','delivered', '45 Lý Thường Kiệt, Hoàn Kiếm, Hà Nội','2026-03-03'),
('ship-002','ord-002','GHTK','GHTK987654321','in_transit','8 Trần Phú, Ba Đình, Hà Nội',          '2026-03-18'),
('ship-003','ord-003','GHN', 'GHN111222333','pending',   '99 Bùi Thị Xuân, Hai Bà Trưng, Hà Nội','2026-03-24');

-- ── REVIEWS ───────────────────────────────────────────────────
INSERT INTO `reviews` (`review_id`,`customer_id`,`product_id`,`rating`,`comment`,`created_at`) VALUES
('rev-001','cust-002','prod-001',5,'Giày rất đẹp, đúng size, đóng gói cẩn thận. Sẽ mua lại!',       '2026-03-05 10:00:00'),
('rev-002','cust-001','prod-001',4,'Chất lượng tốt nhưng giao hơi chậm. Nhìn chung hài lòng.',      '2026-03-06 08:30:00'),
('rev-003','cust-002','prod-003',5,'Giày chạy bộ cực êm, phù hợp buổi sáng. Đáng đồng tiền!',      '2026-03-19 20:00:00'),
('rev-004','cust-003','prod-005',3,'Áo ổn, chất vải khá dày. Màu sắc hơi khác ảnh một chút.',       '2026-03-22 15:00:00'),
('rev-005','cust-001','prod-006',5,'Hoodie siêu ấm, mặc mùa đông cực phê. Form rộng thoải mái!',   '2026-03-25 09:00:00');

-- ── Khôi phục tất cả Triggers ─────────────────────────────────
DELIMITER $$

CREATE TRIGGER `trg_after_insert_user_create_customer`
AFTER INSERT ON `users` FOR EACH ROW
BEGIN
  IF NEW.role = 'customer' THEN
    INSERT INTO `customers`(`customer_id`,`user_id`,`gender`,`loyalty_points`)
    VALUES(UUID(),NEW.user_id,'other',0);
  END IF;
END$$

CREATE TRIGGER `trg_before_insert_order_item_check_stock`
BEFORE INSERT ON `order_items` FOR EACH ROW
BEGIN
  DECLARE v_stock INT DEFAULT 0;
  SELECT `stock_quantity` INTO v_stock FROM `product_variants` WHERE `variant_id`=NEW.variant_id;
  IF v_stock < NEW.quantity THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Không đủ hàng trong kho.';
  END IF;
END$$

CREATE TRIGGER `trg_after_insert_order_item_reduce_stock`
AFTER INSERT ON `order_items` FOR EACH ROW
BEGIN
  UPDATE `product_variants` SET `stock_quantity`=`stock_quantity`-NEW.quantity WHERE `variant_id`=NEW.variant_id;
END$$

CREATE TRIGGER `trg_after_insert_cart_item_update_total`
AFTER INSERT ON `cart_items` FOR EACH ROW
BEGIN
  UPDATE `carts` SET `total_amount`=(SELECT COALESCE(SUM(unit_price*quantity),0) FROM `cart_items` WHERE cart_id=NEW.cart_id) WHERE `cart_id`=NEW.cart_id;
END$$

CREATE TRIGGER `trg_after_update_cart_item_update_total`
AFTER UPDATE ON `cart_items` FOR EACH ROW
BEGIN
  UPDATE `carts` SET `total_amount`=(SELECT COALESCE(SUM(unit_price*quantity),0) FROM `cart_items` WHERE cart_id=NEW.cart_id) WHERE `cart_id`=NEW.cart_id;
END$$

CREATE TRIGGER `trg_after_delete_cart_item_update_total`
AFTER DELETE ON `cart_items` FOR EACH ROW
BEGIN
  UPDATE `carts` SET `total_amount`=(SELECT COALESCE(SUM(unit_price*quantity),0) FROM `cart_items` WHERE cart_id=OLD.cart_id) WHERE `cart_id`=OLD.cart_id;
END$$

CREATE TRIGGER `trg_before_insert_order_calc_final`
BEFORE INSERT ON `orders` FOR EACH ROW
BEGIN
  SET NEW.final_amount=NEW.total_amount-NEW.discount_amount;
  IF NEW.final_amount<0 THEN SET NEW.final_amount=0; END IF;
END$$

DELIMITER ;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
--  TỔNG KẾT DỮ LIỆU MẪU
-- ============================================================
-- Users     : 6  (1 admin + 5 customer; 1 bị banned)
-- Brands    : 3  (Nike, Adidas, Local Brand VN)
-- Products  : 6  (giày, áo, quần)
-- Variants  : 24 (nhiều size & màu)
-- Images    : 9
-- Addresses : 5
-- Carts     : 4  (2 giỏ đang có hàng)
-- Orders    : 3  (delivered / shipped / confirmed)
-- Payments  : 3  (2 paid / 1 pending)
-- Shipments : 3
-- Reviews   : 5
-- Promotions: 2  (WELCOME10 / SALE50K)
