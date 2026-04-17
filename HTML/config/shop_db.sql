-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th4 10, 2026 lúc 10:35 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `shop_db`
--

DELIMITER $$
--
-- Thủ tục
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_admin_count_products` ()   BEGIN
  SELECT COUNT(*) AS total_items
  FROM products;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_admin_create_product` (IN `p_brand_id` VARCHAR(36), IN `p_product_name` VARCHAR(200), IN `p_description` TEXT, IN `p_base_price` DECIMAL(12,2), IN `p_category` VARCHAR(100), IN `p_gender_type` VARCHAR(20), IN `p_status` VARCHAR(20), IN `p_image_url` VARCHAR(500), IN `p_color` VARCHAR(50))   BEGIN
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

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_admin_delete_product` (IN `p_product_id` VARCHAR(36))   BEGIN
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

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_admin_get_brands` ()   BEGIN
  SELECT brand_id, brand_name
  FROM brands
  ORDER BY brand_name ASC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_admin_get_dashboard_stats` ()   BEGIN
  SELECT 
    (SELECT COUNT(*) FROM products)  AS product_count,
    (SELECT COUNT(*) FROM orders)    AS order_count,
    (SELECT COUNT(*) FROM customers) AS customer_count;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_admin_get_products_paginated` (IN `p_limit` INT, IN `p_offset` INT)   BEGIN
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

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_admin_update_product` (IN `p_product_id` VARCHAR(36), IN `p_brand_id` VARCHAR(36), IN `p_product_name` VARCHAR(200), IN `p_description` TEXT, IN `p_base_price` DECIMAL(12,2), IN `p_category` VARCHAR(100), IN `p_gender_type` VARCHAR(20), IN `p_status` VARCHAR(20), IN `p_image_url` VARCHAR(500))   BEGIN
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

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_create_address` (IN `p_address_id` VARCHAR(36), IN `p_customer_id` VARCHAR(36), IN `p_receiver_name` VARCHAR(100), IN `p_phone` VARCHAR(20), IN `p_street_address` VARCHAR(255), IN `p_city` VARCHAR(100))   BEGIN
  INSERT INTO addresses (address_id, customer_id, receiver_name, phone, street_address, city)
  VALUES (p_address_id, p_customer_id, p_receiver_name, p_phone, p_street_address, p_city);

  SELECT p_address_id AS address_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_create_order` (IN `p_order_id` VARCHAR(36), IN `p_customer_id` VARCHAR(36), IN `p_address_id` VARCHAR(36), IN `p_total_amount` DECIMAL(12,2), IN `p_discount_amount` DECIMAL(12,2))   BEGIN
  INSERT INTO orders (
    order_id, customer_id, address_id, total_amount, discount_amount, order_status, payment_status
  ) VALUES (
    p_order_id, p_customer_id, p_address_id, p_total_amount, p_discount_amount, 'pending', 'pending'
  );

  SELECT p_order_id AS order_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_create_order_item` (IN `p_order_item_id` VARCHAR(36), IN `p_order_id` VARCHAR(36), IN `p_variant_id` VARCHAR(36), IN `p_quantity` INT, IN `p_unit_price` DECIMAL(12,2))   BEGIN
  INSERT INTO order_items (order_item_id, order_id, variant_id, quantity, unit_price)
  VALUES (p_order_item_id, p_order_id, p_variant_id, p_quantity, p_unit_price);

  SELECT p_order_item_id AS order_item_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_find_or_create_customer_by_phone` (IN `p_phone` VARCHAR(20), IN `p_full_name` VARCHAR(100))   BEGIN
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

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_find_variant_for_checkout` (IN `p_product_id` VARCHAR(36), IN `p_size` VARCHAR(20))   BEGIN
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

DELIMITER ;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `addresses`
--

CREATE TABLE `addresses` (
  `address_id` varchar(36) NOT NULL DEFAULT uuid(),
  `customer_id` varchar(36) NOT NULL,
  `receiver_name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `street_address` varchar(255) NOT NULL,
  `district` varchar(100) DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `zip_code` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `admins`
--

CREATE TABLE `admins` (
  `admin_id` varchar(36) NOT NULL DEFAULT uuid(),
  `user_id` varchar(36) NOT NULL,
  `permission_level` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `admins`
--

INSERT INTO `admins` (`admin_id`, `user_id`, `permission_level`) VALUES
('admins-001', 'admin-001', 3);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `brands`
--

CREATE TABLE `brands` (
  `brand_id` varchar(36) NOT NULL DEFAULT uuid(),
  `brand_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `origin` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `brands`
--

INSERT INTO `brands` (`brand_id`, `brand_name`, `description`, `logo`, `origin`, `status`) VALUES
('brand-001', 'Nike', 'Just Do It', NULL, 'USA', 'active'),
('brand-002', 'Adidas', 'Impossible Is Nothing', NULL, 'Germany', 'active'),
('brand-003', 'Local Brand VN', 'Thương hiệu Việt', NULL, 'Vietnam', 'active');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `carts`
--

CREATE TABLE `carts` (
  `cart_id` varchar(36) NOT NULL DEFAULT uuid(),
  `customer_id` varchar(36) NOT NULL,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cart_items`
--

CREATE TABLE `cart_items` (
  `cart_item_id` varchar(36) NOT NULL DEFAULT uuid(),
  `cart_id` varchar(36) NOT NULL,
  `variant_id` varchar(36) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(12,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Bẫy `cart_items`
--
DELIMITER $$
CREATE TRIGGER `trg_after_delete_cart_item_update_total` AFTER DELETE ON `cart_items` FOR EACH ROW BEGIN
  UPDATE `carts` SET `total_amount`=(SELECT COALESCE(SUM(unit_price*quantity),0) FROM `cart_items` WHERE cart_id=OLD.cart_id) WHERE `cart_id`=OLD.cart_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_insert_cart_item_update_total` AFTER INSERT ON `cart_items` FOR EACH ROW BEGIN
  UPDATE `carts` SET `total_amount`=(SELECT COALESCE(SUM(unit_price*quantity),0) FROM `cart_items` WHERE cart_id=NEW.cart_id) WHERE `cart_id`=NEW.cart_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_cart_item_update_total` AFTER UPDATE ON `cart_items` FOR EACH ROW BEGIN
  UPDATE `carts` SET `total_amount`=(SELECT COALESCE(SUM(unit_price*quantity),0) FROM `cart_items` WHERE cart_id=NEW.cart_id) WHERE `cart_id`=NEW.cart_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `customers`
--

CREATE TABLE `customers` (
  `customer_id` varchar(36) NOT NULL DEFAULT uuid(),
  `user_id` varchar(36) NOT NULL,
  `gender` enum('male','female','other') NOT NULL DEFAULT 'other',
  `date_of_birth` date DEFAULT NULL,
  `loyalty_points` int(11) NOT NULL DEFAULT 0,
  `default_address_id` varchar(36) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

CREATE TABLE `orders` (
  `order_id` varchar(36) NOT NULL,
  `customer_id` varchar(36) NOT NULL,
  `address_id` varchar(36) DEFAULT NULL,
  `promotion_id` varchar(36) DEFAULT NULL,
  `order_date` datetime NOT NULL DEFAULT current_timestamp(),
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `final_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `order_status` enum('pending','confirmed','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `payment_status` enum('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Bẫy `orders`
--
DELIMITER $$
CREATE TRIGGER `trg_after_update_order_add_loyalty` AFTER UPDATE ON `orders` FOR EACH ROW BEGIN
  DECLARE v_points INT;

  IF OLD.order_status <> 'delivered' AND NEW.order_status = 'delivered' THEN
    SET v_points = FLOOR(NEW.final_amount / 10000);
    UPDATE `customers`
    SET `loyalty_points` = `loyalty_points` + v_points
    WHERE `customer_id` = NEW.customer_id;
  END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_order_restore_stock` AFTER UPDATE ON `orders` FOR EACH ROW BEGIN
  IF OLD.order_status <> 'cancelled' AND NEW.order_status = 'cancelled' THEN
    UPDATE `product_variants` pv
    JOIN `order_items` oi ON oi.variant_id = pv.variant_id
    SET pv.`stock_quantity` = pv.`stock_quantity` + oi.`quantity`
    WHERE oi.order_id = NEW.order_id;
  END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_before_insert_order_calc_final` BEFORE INSERT ON `orders` FOR EACH ROW BEGIN
  SET NEW.final_amount=NEW.total_amount-NEW.discount_amount;
  IF NEW.final_amount<0 THEN SET NEW.final_amount=0; END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` varchar(36) NOT NULL DEFAULT uuid(),
  `order_id` varchar(36) NOT NULL,
  `variant_id` varchar(36) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(12,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Bẫy `order_items`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_order_item_reduce_stock` AFTER INSERT ON `order_items` FOR EACH ROW BEGIN
  UPDATE `product_variants` SET `stock_quantity`=`stock_quantity`-NEW.quantity WHERE `variant_id`=NEW.variant_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_before_insert_order_item_check_stock` BEFORE INSERT ON `order_items` FOR EACH ROW BEGIN
  DECLARE v_stock INT DEFAULT 0;
  SELECT `stock_quantity` INTO v_stock FROM `product_variants` WHERE `variant_id`=NEW.variant_id;
  IF v_stock < NEW.quantity THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Không đủ hàng trong kho.';
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `payments`
--

CREATE TABLE `payments` (
  `payment_id` varchar(36) NOT NULL DEFAULT uuid(),
  `order_id` varchar(36) NOT NULL,
  `payment_method` varchar(50) NOT NULL DEFAULT 'cod',
  `payment_date` datetime DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `payment_status` enum('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Bẫy `payments`
--
DELIMITER $$
CREATE TRIGGER `trg_after_update_payment_set_date` AFTER UPDATE ON `payments` FOR EACH ROW BEGIN
  IF OLD.payment_status <> 'paid' AND NEW.payment_status = 'paid' THEN
    UPDATE `payments`
    SET `payment_date` = NOW()
    WHERE `payment_id` = NEW.payment_id;

    UPDATE `orders`
    SET `payment_status` = 'paid'
    WHERE `order_id` = NEW.order_id;
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

CREATE TABLE `products` (
  `product_id` varchar(36) NOT NULL DEFAULT uuid(),
  `brand_id` varchar(36) NOT NULL,
  `product_name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `base_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `category` varchar(100) DEFAULT NULL,
  `gender_type` enum('male','female','unisex') NOT NULL DEFAULT 'unisex',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `products`
--

INSERT INTO `products` (`product_id`, `brand_id`, `product_name`, `description`, `base_price`, `category`, `gender_type`, `status`) VALUES
('118618d5-33b6-11f1-a607-088fc383b93a', 'brand-003', 'áo thun 1', 'ok', 20000000.00, 'tshirt', 'unisex', 'active'),
('prod-001', 'brand-001', 'Nike Air Force 1', 'Giày sneaker cổ điển, đế cao su chắc chắn.', 1200000.00, 'shoes', 'unisex', 'active'),
('prod-002', 'brand-001', 'Nike Dri-FIT T-Shirt', 'Áo thun thể thao thoáng khí, chất liệu Dri-FIT.', 450000.00, 'tops', 'male', 'active'),
('prod-003', 'brand-002', 'Adidas Ultraboost 22', 'Giày chạy bộ với công nghệ Boost êm ái.', 2500000.00, 'shoes', 'unisex', 'active'),
('prod-005', 'brand-003', 'Local Tee Classic', 'Áo thun cotton 100%, in hoạ tiết thương hiệu Việt.', 280000.00, 'tops', 'unisex', 'active'),
('prod-006', 'brand-003', 'Local Street Hoodie', 'Áo hoodie form rộng, chất nỉ bông dày dặn.', 680000.00, 'tops', 'unisex', 'active');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_images`
--

CREATE TABLE `product_images` (
  `image_id` varchar(36) NOT NULL DEFAULT uuid(),
  `product_id` varchar(36) NOT NULL,
  `image_url` varchar(500) NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `product_images`
--

INSERT INTO `product_images` (`image_id`, `product_id`, `image_url`, `is_primary`) VALUES
('11863e9d-33b6-11f1-a607-088fc383b93a', '118618d5-33b6-11f1-a607-088fc383b93a', 'public/uploads/products/1775699249__ao-puma-portugal-national-team-short-sleeve-jersey-2026-red-788141-77_fd7976bf9b554016b6e00844a7846f26_master.png', 1),
('img-001', 'prod-001', '/assets/image/shoes_premium.png', 1),
('img-002', 'prod-001', '/assets/image/shoes_premium.png', 0),
('img-003', 'prod-002', '/assets/image/tshirt_graphic.png', 1),
('img-004', 'prod-003', '/assets/image/jacket_urban.png', 1),
('img-005', 'prod-003', '/assets/image/jacket_urban.png', 0),
('img-007', 'prod-005', '/assets/image/acc_cap.png', 1),
('img-008', 'prod-006', '/assets/image/hoodie_minimal.png', 1),
('img-009', 'prod-006', '/assets/image/hoodie_minimal.png', 0);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_variants`
--

CREATE TABLE `product_variants` (
  `variant_id` varchar(36) NOT NULL DEFAULT uuid(),
  `product_id` varchar(36) NOT NULL,
  `size` varchar(20) NOT NULL,
  `color` varchar(50) NOT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `price_adjustment` decimal(12,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `product_variants`
--

INSERT INTO `product_variants` (`variant_id`, `product_id`, `size`, `color`, `stock_quantity`, `price_adjustment`) VALUES
('11864d58-33b6-11f1-a607-088fc383b93a', '118618d5-33b6-11f1-a607-088fc383b93a', 'S', 'Trắng', 50, 0.00),
('11864f98-33b6-11f1-a607-088fc383b93a', '118618d5-33b6-11f1-a607-088fc383b93a', 'M', 'Trắng', 48, 0.00),
('11865051-33b6-11f1-a607-088fc383b93a', '118618d5-33b6-11f1-a607-088fc383b93a', 'L', 'Trắng', 49, 0.00),
('118650b2-33b6-11f1-a607-088fc383b93a', '118618d5-33b6-11f1-a607-088fc383b93a', 'XL', 'Trắng', 50, 0.00),
('var-001', 'prod-001', '40', 'Trắng', 50, 0.00),
('var-002', 'prod-001', '41', 'Trắng', 40, 0.00),
('var-003', 'prod-001', '42', 'Trắng', 30, 0.00),
('var-004', 'prod-001', '41', 'Đen', 25, 50000.00),
('var-005', 'prod-001', '42', 'Đen', 20, 50000.00),
('var-006', 'prod-002', 'S', 'Xanh navy', 60, 0.00),
('var-007', 'prod-002', 'M', 'Xanh navy', 80, 0.00),
('var-008', 'prod-002', 'L', 'Đen', 70, 0.00),
('var-009', 'prod-002', 'XL', 'Đen', 40, 0.00),
('var-010', 'prod-003', '40', 'Trắng/Xanh', 15, 0.00),
('var-011', 'prod-003', '41', 'Trắng/Xanh', 20, 0.00),
('var-012', 'prod-003', '42', 'Đen/Đỏ', 18, 100000.00),
('var-013', 'prod-003', '43', 'Đen/Đỏ', 10, 100000.00),
('var-017', 'prod-005', 'S', 'Kem', 100, 0.00),
('var-018', 'prod-005', 'M', 'Kem', 120, 0.00),
('var-019', 'prod-005', 'L', 'Xám', 90, 0.00),
('var-020', 'prod-005', 'XL', 'Xám', 60, 0.00),
('var-021', 'prod-006', 'M', 'Đen', 45, 0.00),
('var-022', 'prod-006', 'L', 'Đen', 40, 0.00),
('var-023', 'prod-006', 'XL', 'Xanh rêu', 29, 50000.00),
('var-024', 'prod-006', 'L', 'Xanh rêu', 35, 50000.00);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `promotions`
--

CREATE TABLE `promotions` (
  `promotion_id` varchar(36) NOT NULL DEFAULT uuid(),
  `code` varchar(50) NOT NULL,
  `promotion_type` varchar(100) DEFAULT NULL,
  `discount_type` enum('percent','fixed') NOT NULL DEFAULT 'percent',
  `discount_value` decimal(10,2) NOT NULL DEFAULT 0.00,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `minimum_value` decimal(12,2) NOT NULL DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `promotions`
--

INSERT INTO `promotions` (`promotion_id`, `code`, `promotion_type`, `discount_type`, `discount_value`, `start_date`, `end_date`, `minimum_value`, `is_active`) VALUES
('promo-001', 'WELCOME10', NULL, 'percent', 10.00, '2026-01-01', '2026-12-31', 0.00, 1),
('promo-002', 'SALE50K', NULL, 'fixed', 50000.00, '2026-01-01', '2026-12-31', 200000.00, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `reviews`
--

CREATE TABLE `reviews` (
  `review_id` varchar(36) NOT NULL DEFAULT uuid(),
  `customer_id` varchar(36) NOT NULL,
  `product_id` varchar(36) NOT NULL,
  `rating` tinyint(4) NOT NULL DEFAULT 5 CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `shipments`
--

CREATE TABLE `shipments` (
  `shipment_id` varchar(36) NOT NULL DEFAULT uuid(),
  `order_id` varchar(36) NOT NULL,
  `carrier` varchar(100) DEFAULT NULL,
  `tracking_number` varchar(100) DEFAULT NULL,
  `status` enum('pending','in_transit','delivered','failed') NOT NULL DEFAULT 'pending',
  `shipping_address` text DEFAULT NULL,
  `estimated_delivery_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `user_id` varchar(36) NOT NULL DEFAULT uuid(),
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('customer','admin') NOT NULL DEFAULT 'customer',
  `status` enum('active','banned') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `phone`, `password`, `role`, `status`, `created_at`) VALUES
('admin-001', 'Admin', 'admin@shop.com', NULL, '$2y$12$LJkv7W8sKh2XrNmZ0qP3NeW.O6RgvC8eIhFEhKXJyuD9bpLAlkVmS', 'admin', 'active', '2026-04-09 07:29:04');

--
-- Bẫy `users`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_user_create_customer` AFTER INSERT ON `users` FOR EACH ROW BEGIN
  IF NEW.role = 'customer' THEN
    INSERT INTO `customers`(`customer_id`,`user_id`,`gender`,`loyalty_points`)
    VALUES(UUID(),NEW.user_id,'other',0);
  END IF;
END
$$
DELIMITER ;

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `idx_addresses_customer` (`customer_id`);

--
-- Chỉ mục cho bảng `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `uq_admins_user` (`user_id`);

--
-- Chỉ mục cho bảng `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`brand_id`),
  ADD UNIQUE KEY `uq_brands_name` (`brand_name`);

--
-- Chỉ mục cho bảng `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`cart_id`),
  ADD UNIQUE KEY `uq_carts_customer` (`customer_id`);

--
-- Chỉ mục cho bảng `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`cart_item_id`),
  ADD UNIQUE KEY `uq_cart_variant` (`cart_id`,`variant_id`),
  ADD KEY `idx_cart_items_variant` (`variant_id`);

--
-- Chỉ mục cho bảng `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`),
  ADD UNIQUE KEY `uq_customers_user` (`user_id`),
  ADD KEY `fk_customers_default_addr` (`default_address_id`);

--
-- Chỉ mục cho bảng `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `idx_orders_customer` (`customer_id`),
  ADD KEY `idx_orders_address` (`address_id`),
  ADD KEY `idx_orders_promo` (`promotion_id`);

--
-- Chỉ mục cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `idx_order_items_order` (`order_id`),
  ADD KEY `idx_order_items_variant` (`variant_id`);

--
-- Chỉ mục cho bảng `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `idx_payments_order` (`order_id`);

--
-- Chỉ mục cho bảng `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `idx_products_brand` (`brand_id`),
  ADD KEY `idx_products_category` (`category`);

--
-- Chỉ mục cho bảng `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `idx_images_product` (`product_id`);

--
-- Chỉ mục cho bảng `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`variant_id`),
  ADD KEY `idx_variants_product` (`product_id`);

--
-- Chỉ mục cho bảng `promotions`
--
ALTER TABLE `promotions`
  ADD PRIMARY KEY (`promotion_id`),
  ADD UNIQUE KEY `uq_promotions_code` (`code`);

--
-- Chỉ mục cho bảng `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `idx_reviews_customer` (`customer_id`),
  ADD KEY `idx_reviews_product` (`product_id`);

--
-- Chỉ mục cho bảng `shipments`
--
ALTER TABLE `shipments`
  ADD PRIMARY KEY (`shipment_id`),
  ADD UNIQUE KEY `uq_shipments_order` (`order_id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `uq_users_email` (`email`);

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `fk_addresses_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`);

--
-- Các ràng buộc cho bảng `admins`
--
ALTER TABLE `admins`
  ADD CONSTRAINT `fk_admins_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Các ràng buộc cho bảng `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `fk_carts_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`);

--
-- Các ràng buộc cho bảng `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `fk_cart_items_cart` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`cart_id`),
  ADD CONSTRAINT `fk_cart_items_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`variant_id`);

--
-- Các ràng buộc cho bảng `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `fk_customers_default_addr` FOREIGN KEY (`default_address_id`) REFERENCES `addresses` (`address_id`),
  ADD CONSTRAINT `fk_customers_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Các ràng buộc cho bảng `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_address` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`address_id`),
  ADD CONSTRAINT `fk_orders_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`),
  ADD CONSTRAINT `fk_orders_promotion` FOREIGN KEY (`promotion_id`) REFERENCES `promotions` (`promotion_id`);

--
-- Các ràng buộc cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `fk_order_items_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`variant_id`);

--
-- Các ràng buộc cho bảng `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`);

--
-- Các ràng buộc cho bảng `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_brand` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`brand_id`);

--
-- Các ràng buộc cho bảng `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `fk_images_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Các ràng buộc cho bảng `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `fk_variants_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Các ràng buộc cho bảng `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `fk_reviews_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`),
  ADD CONSTRAINT `fk_reviews_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Các ràng buộc cho bảng `shipments`
--
ALTER TABLE `shipments`
  ADD CONSTRAINT `fk_shipments_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
