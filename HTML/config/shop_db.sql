-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th4 14, 2026 lúc 05:05 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12
CREATE DATABASE IF NOT EXISTS shop_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
USE shop_db;

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

--
-- Đang đổ dữ liệu cho bảng `addresses`
--

INSERT INTO `addresses` (`address_id`, `customer_id`, `receiver_name`, `phone`, `street_address`, `district`, `city`, `zip_code`) VALUES
('c513dfa7-b732-4fd8-befc-04868a43f8e6', 'f999b781-351c-11f1-a277-088fc383b93a', '', '', '', NULL, '', '000000');

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

--
-- Đang đổ dữ liệu cho bảng `carts`
--

INSERT INTO `carts` (`cart_id`, `customer_id`, `total_amount`) VALUES
('fd67d5ca-351c-11f1-a277-088fc383b93a', 'f999b781-351c-11f1-a277-088fc383b93a', 0.00);

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
-- Cấu trúc bảng cho bảng `categories`
--

CREATE TABLE `categories` (
  `category_id` varchar(36) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `size_mode` enum('default','numeric_38_42') NOT NULL DEFAULT 'default'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `slug`, `size_mode`) VALUES
('0d8da5f8-37e1-11f1-a3b6-088fc383b93a', 'Hoodie', 'hoodie', 'default'),
('0d8dc766-37e1-11f1-a3b6-088fc383b93a', 'T-Shirt', 'tshirt', 'default'),
('0d8de45e-37e1-11f1-a3b6-088fc383b93a', 'Cargo', 'cargo', 'default'),
('0d8e073f-37e1-11f1-a3b6-088fc383b93a', 'Jacket', 'jacket', 'default'),
('0d8e2ece-37e1-11f1-a3b6-088fc383b93a', 'Shorts', 'shorts', 'default'),
('0d8e50af-37e1-11f1-a3b6-088fc383b93a', 'Shoes', 'shoes', 'numeric_38_42'),
('0d8e72c2-37e1-11f1-a3b6-088fc383b93a', 'Bottoms', 'bottoms', 'default'),
('0d8edafd-37e1-11f1-a3b6-088fc383b93a', 'Tops', 'tops', 'default');

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

--
-- Đang đổ dữ liệu cho bảng `customers`
--

INSERT INTO `customers` (`customer_id`, `user_id`, `gender`, `date_of_birth`, `loyalty_points`, `default_address_id`) VALUES
('f999b781-351c-11f1-a277-088fc383b93a', 'd5f78cfe-a7c6-4141-9a88-584df0119739', 'male', NULL, 0, NULL);

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
-- Đang đổ dữ liệu cho bảng `orders`
--

INSERT INTO `orders` (`order_id`, `customer_id`, `address_id`, `promotion_id`, `order_date`, `total_amount`, `discount_amount`, `final_amount`, `order_status`, `payment_status`) VALUES
('4f3b6a1a-c838-4feb-9b0c-024b0737613a', 'f999b781-351c-11f1-a277-088fc383b93a', 'c513dfa7-b732-4fd8-befc-04868a43f8e6', NULL, '2026-04-14 17:54:34', 20000000.00, 0.00, 20000000.00, 'confirmed', 'paid');

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
-- Đang đổ dữ liệu cho bảng `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `variant_id`, `quantity`, `unit_price`) VALUES
('3e4d393c-1904-4ec2-81fb-164bb69eee1c', '4f3b6a1a-c838-4feb-9b0c-024b0737613a', '11865051-33b6-11f1-a607-088fc383b93a', 1, 20000000.00);

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
-- Đang đổ dữ liệu cho bảng `payments`
--

INSERT INTO `payments` (`payment_id`, `order_id`, `payment_method`, `payment_date`, `amount`, `payment_status`) VALUES
('05383457-650a-4307-a290-f25db7139cb8', '4f3b6a1a-c838-4feb-9b0c-024b0737613a', 'online', '2026-04-14 17:54:34', 20000000.00, 'paid');

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
('02fe9427-3811-11f1-81dd-088fc383b93a', 'brand-002', 'ao4', '', 299999999.00, 'tshirt', 'unisex', 'active'),
('0bffc4f2-3811-11f1-81dd-088fc383b93a', 'brand-002', 'ao5', '', 2999999.00, 'tshirt', 'unisex', 'active'),
('118618d5-33b6-11f1-a607-088fc383b93a', 'brand-003', 'áo thun 1', 'ok', 20000000.00, 'tshirt', 'unisex', 'active'),
('29e63f89-3811-11f1-81dd-088fc383b93a', 'brand-002', 'ao6', '', 2999999.00, 'tshirt', 'unisex', 'active'),
('2c93bec2-380f-11f1-81dd-088fc383b93a', 'brand-002', 'áo thun 2', '', 299999.00, 'tshirt', 'unisex', 'active'),
('37228c1a-3811-11f1-81dd-088fc383b93a', 'brand-002', 'ao7', '', 29999999.00, 'tshirt', 'unisex', 'active'),
('4a471526-380f-11f1-81dd-088fc383b93a', 'brand-002', 'quần jean', '', 2999999.00, 'bottoms', 'unisex', 'active'),
('58c4336f-380f-11f1-81dd-088fc383b93a', 'brand-002', 'aura hoodie', '', 299999.00, 'hoodie', 'unisex', 'active'),
('633a578c-3811-11f1-81dd-088fc383b93a', 'brand-002', 'ao8', '', 2999997.00, 'tshirt', 'unisex', 'active'),
('66bd6ec6-380f-11f1-81dd-088fc383b93a', 'brand-002', 'quần ngắn', '', 2999999.00, 'shorts', 'unisex', 'active'),
('aa4de64d-37f4-11f1-81dd-088fc383b93a', 'brand-002', 'giày', 'oke', 1000000.00, 'shoes', 'unisex', 'active'),
('e3b0641d-380f-11f1-81dd-088fc383b93a', 'brand-002', 'giày h1', '', 2999999.00, 'shoes', 'unisex', 'active'),
('e7889abf-3810-11f1-81dd-088fc383b93a', 'brand-002', 'ao1', '', 199999.00, 'tshirt', 'unisex', 'active'),
('ec38ae2e-380f-11f1-81dd-088fc383b93a', 'brand-002', 'giày h2', '', 499999.00, 'shoes', 'unisex', 'active'),
('f084e60f-3810-11f1-81dd-088fc383b93a', 'brand-002', 'ao2', '', 299999.00, 'tshirt', 'unisex', 'active'),
('f6e2de7e-380f-11f1-81dd-088fc383b93a', 'brand-002', 'giày h3', '', 6999999.00, 'shoes', 'unisex', 'active'),
('fadcc139-3810-11f1-81dd-088fc383b93a', 'brand-002', 'ao3', '', 2999999.00, 'tshirt', 'unisex', 'active');

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
('02ff1ed1-3811-11f1-81dd-088fc383b93a', '02fe9427-3811-11f1-81dd-088fc383b93a', 'public/uploads/products/1776178114_ao4.jpg', 1),
('0c004d2b-3811-11f1-81dd-088fc383b93a', '0bffc4f2-3811-11f1-81dd-088fc383b93a', 'public/uploads/products/1776178129_ao5.jpg', 1),
('11863e9d-33b6-11f1-a607-088fc383b93a', '118618d5-33b6-11f1-a607-088fc383b93a', 'public/uploads/products/1775699249__ao-puma-portugal-national-team-short-sleeve-jersey-2026-red-788141-77_fd7976bf9b554016b6e00844a7846f26_master.png', 1),
('29e6f05f-3811-11f1-81dd-088fc383b93a', '29e63f89-3811-11f1-81dd-088fc383b93a', 'public/uploads/products/1776178179_ao6.jpg', 1),
('2c948a8c-380f-11f1-81dd-088fc383b93a', '2c93bec2-380f-11f1-81dd-088fc383b93a', 'public/uploads/products/1776177324_ao.jpg', 1),
('37233c59-3811-11f1-81dd-088fc383b93a', '37228c1a-3811-11f1-81dd-088fc383b93a', 'public/uploads/products/1776178201_ao8.jpg', 1),
('4a486e3e-380f-11f1-81dd-088fc383b93a', '4a471526-380f-11f1-81dd-088fc383b93a', 'public/uploads/products/1776177374_jeans.jpg', 1),
('58c5127f-380f-11f1-81dd-088fc383b93a', '58c4336f-380f-11f1-81dd-088fc383b93a', 'public/uploads/products/1776177399_hd.jpg', 1),
('633b0dd0-3811-11f1-81dd-088fc383b93a', '633a578c-3811-11f1-81dd-088fc383b93a', 'public/uploads/products/1776178275_ao8.jpg', 1),
('66bdfd9f-380f-11f1-81dd-088fc383b93a', '66bd6ec6-380f-11f1-81dd-088fc383b93a', 'public/uploads/products/1776177422_quan.jpg', 1),
('aa4ec86b-37f4-11f1-81dd-088fc383b93a', 'aa4de64d-37f4-11f1-81dd-088fc383b93a', 'public/uploads/products/1776165939_giày-thể-thao-nam-cj80-trắng-decathlon-8799767.avif', 1),
('e3b16586-380f-11f1-81dd-088fc383b93a', 'e3b0641d-380f-11f1-81dd-088fc383b93a', 'public/uploads/products/1776177632_giay1.jpg', 1),
('e78905b7-3810-11f1-81dd-088fc383b93a', 'e7889abf-3810-11f1-81dd-088fc383b93a', 'public/uploads/products/1776178068_ao1.jpg', 1),
('ec39283a-380f-11f1-81dd-088fc383b93a', 'ec38ae2e-380f-11f1-81dd-088fc383b93a', 'public/uploads/products/1776177646_giay2.jpg', 1),
('f085a0f5-3810-11f1-81dd-088fc383b93a', 'f084e60f-3810-11f1-81dd-088fc383b93a', 'public/uploads/products/1776178083_ao2.jpg', 1),
('f6e38364-380f-11f1-81dd-088fc383b93a', 'f6e2de7e-380f-11f1-81dd-088fc383b93a', 'public/uploads/products/1776177664_giay3.jpg', 1),
('fadd470e-3810-11f1-81dd-088fc383b93a', 'fadcc139-3810-11f1-81dd-088fc383b93a', 'public/uploads/products/1776178100_ao3.jpg', 1);

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
('02ff5ac7-3811-11f1-81dd-088fc383b93a', '02fe9427-3811-11f1-81dd-088fc383b93a', 'S', '', 50, 0.00),
('02ffa358-3811-11f1-81dd-088fc383b93a', '02fe9427-3811-11f1-81dd-088fc383b93a', 'M', '', 50, 0.00),
('02ffce90-3811-11f1-81dd-088fc383b93a', '02fe9427-3811-11f1-81dd-088fc383b93a', 'L', '', 50, 0.00),
('02fff190-3811-11f1-81dd-088fc383b93a', '02fe9427-3811-11f1-81dd-088fc383b93a', 'XL', '', 50, 0.00),
('0c007d65-3811-11f1-81dd-088fc383b93a', '0bffc4f2-3811-11f1-81dd-088fc383b93a', 'S', '', 50, 0.00),
('0c009fa2-3811-11f1-81dd-088fc383b93a', '0bffc4f2-3811-11f1-81dd-088fc383b93a', 'M', '', 50, 0.00),
('0c00c8ce-3811-11f1-81dd-088fc383b93a', '0bffc4f2-3811-11f1-81dd-088fc383b93a', 'L', '', 50, 0.00),
('0c00f777-3811-11f1-81dd-088fc383b93a', '0bffc4f2-3811-11f1-81dd-088fc383b93a', 'XL', '', 50, 0.00),
('11864d58-33b6-11f1-a607-088fc383b93a', '118618d5-33b6-11f1-a607-088fc383b93a', 'S', 'Trắng', 50, 0.00),
('11864f98-33b6-11f1-a607-088fc383b93a', '118618d5-33b6-11f1-a607-088fc383b93a', 'M', 'Trắng', 48, 0.00),
('11865051-33b6-11f1-a607-088fc383b93a', '118618d5-33b6-11f1-a607-088fc383b93a', 'L', 'Trắng', 48, 0.00),
('29e74325-3811-11f1-81dd-088fc383b93a', '29e63f89-3811-11f1-81dd-088fc383b93a', 'S', '', 50, 0.00),
('29e7609a-3811-11f1-81dd-088fc383b93a', '29e63f89-3811-11f1-81dd-088fc383b93a', 'M', '', 50, 0.00),
('29e78741-3811-11f1-81dd-088fc383b93a', '29e63f89-3811-11f1-81dd-088fc383b93a', 'L', '', 50, 0.00),
('29e7a7a5-3811-11f1-81dd-088fc383b93a', '29e63f89-3811-11f1-81dd-088fc383b93a', 'XL', '', 50, 0.00),
('2c95254a-380f-11f1-81dd-088fc383b93a', '2c93bec2-380f-11f1-81dd-088fc383b93a', 'L', '', 50, 0.00),
('37238bdf-3811-11f1-81dd-088fc383b93a', '37228c1a-3811-11f1-81dd-088fc383b93a', 'S', '', 50, 0.00),
('3723c95f-3811-11f1-81dd-088fc383b93a', '37228c1a-3811-11f1-81dd-088fc383b93a', 'M', '', 50, 0.00),
('3723f981-3811-11f1-81dd-088fc383b93a', '37228c1a-3811-11f1-81dd-088fc383b93a', 'L', '', 50, 0.00),
('372431bd-3811-11f1-81dd-088fc383b93a', '37228c1a-3811-11f1-81dd-088fc383b93a', 'XL', '', 50, 0.00),
('4a48d716-380f-11f1-81dd-088fc383b93a', '4a471526-380f-11f1-81dd-088fc383b93a', 'S', '', 50, 0.00),
('4a49065b-380f-11f1-81dd-088fc383b93a', '4a471526-380f-11f1-81dd-088fc383b93a', 'M', '', 50, 0.00),
('4a493af3-380f-11f1-81dd-088fc383b93a', '4a471526-380f-11f1-81dd-088fc383b93a', 'L', '', 50, 0.00),
('4a49671e-380f-11f1-81dd-088fc383b93a', '4a471526-380f-11f1-81dd-088fc383b93a', 'XL', '', 50, 0.00),
('58c5782e-380f-11f1-81dd-088fc383b93a', '58c4336f-380f-11f1-81dd-088fc383b93a', 'S', '', 50, 0.00),
('58c5b57f-380f-11f1-81dd-088fc383b93a', '58c4336f-380f-11f1-81dd-088fc383b93a', 'M', '', 50, 0.00),
('58c5e543-380f-11f1-81dd-088fc383b93a', '58c4336f-380f-11f1-81dd-088fc383b93a', 'L', '', 50, 0.00),
('58c62ea4-380f-11f1-81dd-088fc383b93a', '58c4336f-380f-11f1-81dd-088fc383b93a', 'XL', '', 50, 0.00),
('633b6291-3811-11f1-81dd-088fc383b93a', '633a578c-3811-11f1-81dd-088fc383b93a', 'S', '', 50, 0.00),
('633b868f-3811-11f1-81dd-088fc383b93a', '633a578c-3811-11f1-81dd-088fc383b93a', 'M', '', 50, 0.00),
('633ba183-3811-11f1-81dd-088fc383b93a', '633a578c-3811-11f1-81dd-088fc383b93a', 'L', '', 50, 0.00),
('633bc141-3811-11f1-81dd-088fc383b93a', '633a578c-3811-11f1-81dd-088fc383b93a', 'XL', '', 50, 0.00),
('66be39fc-380f-11f1-81dd-088fc383b93a', '66bd6ec6-380f-11f1-81dd-088fc383b93a', 'S', '', 50, 0.00),
('66be618c-380f-11f1-81dd-088fc383b93a', '66bd6ec6-380f-11f1-81dd-088fc383b93a', 'M', '', 50, 0.00),
('66be8bb9-380f-11f1-81dd-088fc383b93a', '66bd6ec6-380f-11f1-81dd-088fc383b93a', 'L', '', 50, 0.00),
('66beb8a2-380f-11f1-81dd-088fc383b93a', '66bd6ec6-380f-11f1-81dd-088fc383b93a', 'XL', '', 50, 0.00),
('8148c9b8-380f-11f1-81dd-088fc383b93a', '2c93bec2-380f-11f1-81dd-088fc383b93a', 'XS', '', 50, 0.00),
('aa4f0f00-37f4-11f1-81dd-088fc383b93a', 'aa4de64d-37f4-11f1-81dd-088fc383b93a', '38', '', 50, 0.00),
('aa4f4785-37f4-11f1-81dd-088fc383b93a', 'aa4de64d-37f4-11f1-81dd-088fc383b93a', '39', '', 50, 0.00),
('aa4faf71-37f4-11f1-81dd-088fc383b93a', 'aa4de64d-37f4-11f1-81dd-088fc383b93a', '40', '', 50, 0.00),
('aa4fdeed-37f4-11f1-81dd-088fc383b93a', 'aa4de64d-37f4-11f1-81dd-088fc383b93a', '41', '', 50, 0.00),
('aa500274-37f4-11f1-81dd-088fc383b93a', 'aa4de64d-37f4-11f1-81dd-088fc383b93a', '42', '', 50, 0.00),
('e3b1e23c-380f-11f1-81dd-088fc383b93a', 'e3b0641d-380f-11f1-81dd-088fc383b93a', '38', '', 50, 0.00),
('e3b21e24-380f-11f1-81dd-088fc383b93a', 'e3b0641d-380f-11f1-81dd-088fc383b93a', '39', '', 50, 0.00),
('e3b26d9f-380f-11f1-81dd-088fc383b93a', 'e3b0641d-380f-11f1-81dd-088fc383b93a', '40', '', 50, 0.00),
('e3b29ce7-380f-11f1-81dd-088fc383b93a', 'e3b0641d-380f-11f1-81dd-088fc383b93a', '41', '', 50, 0.00),
('e3b2c25f-380f-11f1-81dd-088fc383b93a', 'e3b0641d-380f-11f1-81dd-088fc383b93a', '42', '', 50, 0.00),
('e7895641-3810-11f1-81dd-088fc383b93a', 'e7889abf-3810-11f1-81dd-088fc383b93a', 'S', '', 50, 0.00),
('e7899a13-3810-11f1-81dd-088fc383b93a', 'e7889abf-3810-11f1-81dd-088fc383b93a', 'M', '', 50, 0.00),
('e789e36e-3810-11f1-81dd-088fc383b93a', 'e7889abf-3810-11f1-81dd-088fc383b93a', 'L', '', 50, 0.00),
('e78a10d8-3810-11f1-81dd-088fc383b93a', 'e7889abf-3810-11f1-81dd-088fc383b93a', 'XL', '', 50, 0.00),
('f085dc03-3810-11f1-81dd-088fc383b93a', 'f084e60f-3810-11f1-81dd-088fc383b93a', 'S', '', 50, 0.00),
('f08609c3-3810-11f1-81dd-088fc383b93a', 'f084e60f-3810-11f1-81dd-088fc383b93a', 'M', '', 50, 0.00),
('f08638e8-3810-11f1-81dd-088fc383b93a', 'f084e60f-3810-11f1-81dd-088fc383b93a', 'L', '', 50, 0.00),
('f0867cbd-3810-11f1-81dd-088fc383b93a', 'f084e60f-3810-11f1-81dd-088fc383b93a', 'XL', '', 50, 0.00),
('f6e405cf-380f-11f1-81dd-088fc383b93a', 'f6e2de7e-380f-11f1-81dd-088fc383b93a', '38', '', 50, 0.00),
('f6e441ae-380f-11f1-81dd-088fc383b93a', 'f6e2de7e-380f-11f1-81dd-088fc383b93a', '39', '', 50, 0.00),
('f6e48ecd-380f-11f1-81dd-088fc383b93a', 'f6e2de7e-380f-11f1-81dd-088fc383b93a', '40', '', 50, 0.00),
('f6e4bde6-380f-11f1-81dd-088fc383b93a', 'f6e2de7e-380f-11f1-81dd-088fc383b93a', '41', '', 50, 0.00),
('f6e4edb5-380f-11f1-81dd-088fc383b93a', 'f6e2de7e-380f-11f1-81dd-088fc383b93a', '42', '', 50, 0.00),
('faddab6d-3810-11f1-81dd-088fc383b93a', 'fadcc139-3810-11f1-81dd-088fc383b93a', 'S', '', 50, 0.00),
('fadded06-3810-11f1-81dd-088fc383b93a', 'fadcc139-3810-11f1-81dd-088fc383b93a', 'M', '', 50, 0.00),
('fade2fb5-3810-11f1-81dd-088fc383b93a', 'fadcc139-3810-11f1-81dd-088fc383b93a', 'L', '', 50, 0.00),
('fade5aa5-3810-11f1-81dd-088fc383b93a', 'fadcc139-3810-11f1-81dd-088fc383b93a', 'XL', '', 50, 0.00),
('fe27913e-380f-11f1-81dd-088fc383b93a', 'ec38ae2e-380f-11f1-81dd-088fc383b93a', '38', '', 50, 0.00),
('fe27ecae-380f-11f1-81dd-088fc383b93a', 'ec38ae2e-380f-11f1-81dd-088fc383b93a', '39', '', 50, 0.00),
('fe285eb5-380f-11f1-81dd-088fc383b93a', 'ec38ae2e-380f-11f1-81dd-088fc383b93a', '40', '', 50, 0.00);

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
('admin-001', 'Admin', 'admin@shop.com', NULL, '$2y$12$LJkv7W8sKh2XrNmZ0qP3NeW.O6RgvC8eIhFEhKXJyuD9bpLAlkVmS', 'admin', 'active', '2026-04-09 07:29:04'),
('d5f78cfe-a7c6-4141-9a88-584df0119739', 'Trịnh Minh Đạt', 'tmdat28@gmail.com', NULL, '$2y$10$wzWnBjNUffN5hdvPnuTz2u47o5bFSQZYB8Dx/OnS8tq24HKqPCCnW', 'customer', 'active', '2026-04-11 03:36:36');

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
-- Chỉ mục cho bảng `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `slug` (`slug`);

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
