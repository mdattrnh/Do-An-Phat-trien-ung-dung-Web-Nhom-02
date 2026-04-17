# 🛍️ SoftEdge — Website Bán Quần Áo

> Đồ án môn học **Phát Triển Ứng Dụng Web** — Nhóm 02  
> Trường Công Nghệ và Thiết Kế, Đại Học Kinh Tế TP. Hồ Chí Minh

---

## 👥 Thành Viên Nhóm

| STT | Họ và Tên | MSSV |
|-----|-----------|------|
| 1 | Trịnh Minh Đạt *(Nhóm Trưởng)* | 31241022341 |
| 2 | Nguyễn Cao Điền | 31241023473 |
| 3 | Nguyễn Đức Trung | |
| 4 | Lý Minh Đạt |31241022041 |
| 5 | Lê Dương Anh Khoa | 31241020839 |

**Giảng viên hướng dẫn:** TS. Đặng Ngọc Hoàng Thành

---

## 📌 Giới Thiệu Dự Án

**SoftEdge** là một website thương mại điện tử bán quần áo thời trang, được xây dựng bằng PHP thuần theo kiến trúc **Custom MVC Framework**. Dự án hướng đến việc cung cấp giải pháp bán hàng online cho các thương hiệu thời trang vừa và nhỏ tại Việt Nam, thay thế việc phụ thuộc vào các sàn thương mại điện tử trung gian.

---

## ✨ Tính Năng Chính

### 👤 Người Dùng (User)
- Xem danh sách và chi tiết sản phẩm
- Tìm kiếm và lọc sản phẩm theo danh mục, giá, size
- Thêm sản phẩm vào giỏ hàng, quản lý giỏ hàng
- Đặt hàng và thanh toán (COD, chuyển khoản)
- Đăng ký, đăng nhập tài khoản
- Đánh giá và bình luận sản phẩm
- Xem Lookbook thương hiệu

### 🔧 Quản Trị Viên (Admin)
- Dashboard thống kê tổng quan (sản phẩm, đơn hàng, doanh thu, khách hàng)
- Quản lý sản phẩm (thêm, sửa, xóa)
- Quản lý danh mục sản phẩm
- Quản lý đơn hàng và cập nhật trạng thái
- Quản lý tài khoản người dùng

---

## 🛠️ Công Nghệ Sử Dụng

| Thành phần | Công nghệ |
|------------|-----------|
| Backend | PHP 8.x (Custom MVC Framework) |
| Frontend | HTML5, CSS3, JavaScript, AJAX |
| UI Framework | Bootstrap |
| Database | MySQL |
| Server | Apache (XAMPP) |
| Bảo mật | PDO Prepared Statements, CSRF Token, Password Hash |

---

## 📁 Cấu Trúc Thư Mục

```
clothing-shop/
├── config/
│   └── database.php        # Kết nối database (PDO Singleton)
├── controllers/
│   ├── ApiController.php   # Xử lý REST API
│   ├── OrderController.php # Xử lý đơn hàng
│   └── UserController.php  # Xử lý tài khoản
├── models/
│   ├── Product.php
│   ├── Cart.php
│   ├── Order.php
│   └── ...
├── views/
│   ├── home/
│   ├── product/
│   ├── checkout/
│   ├── admin/
│   └── auth/
├── public/
│   ├── css/
│   ├── js/
│   └── images/
├── shop_db.sql             # File database
└── index.php               # Entry point
```

---

## ⚙️ Hướng Dẫn Cài Đặt

### Yêu Cầu Hệ Thống

- XAMPP 8.x trở lên (PHP 8.0+, MySQL 5.7+)
- Trình duyệt Chrome / Firefox / Edge

### Bước 1 — Cài đặt XAMPP

1. Tải XAMPP tại https://www.apachefriends.org
2. Cài đặt và mở **XAMPP Control Panel**
3. Nhấn **Start** cho **Apache** và **MySQL**

### Bước 2 — Clone hoặc tải mã nguồn

```bash
cd C:\xampp\htdocs
git clone https://github.com/mdattrnh/Do-An-Phat-trien-ung-dung-Web-Nhom-02.git
```

Hoặc tải ZIP từ GitHub → giải nén vào `C:\xampp\htdocs\`

### Bước 3 — Import Database

1. Mở trình duyệt → truy cập **http://localhost/phpmyadmin**
2. Tạo database mới tên `shop_db`, collation `utf8mb4_unicode_ci`
3. Chọn tab **Import** → chọn file `shop_db.sql` trong thư mục dự án
4. Nhấn **Import**

### Bước 4 — Cấu hình kết nối

Mở file `config/database.php` và kiểm tra:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'shop_db');
define('DB_USER', 'root');
define('DB_PASS', '');  // Mặc định XAMPP để trống
```

### Bước 5 — Chạy website

Mở trình duyệt và truy cập:

```
http://localhost/Do-An-Phat-trien-ung-dung-Web-Nhom-02/
```

---

## 🔐 Tài Khoản Mặc Định

| Vai trò | Email | Mật khẩu |
|---------|-------|----------|
| Admin | admin@softedge.com | admin123 |
| Khách hàng | user@softedge.com | user123 |

> ⚠️ Vui lòng đổi mật khẩu khi triển khai thực tế.

---

## 🗄️ Sơ Đồ Cơ Sở Dữ Liệu

Hệ thống gồm **17 bảng** chính:

`users` · `admins` · `customers` · `addresses` · `products` · `product_variants` · `product_images` · `categories` · `brands` · `carts` · `cart_items` · `orders` · `order_items` · `payments` · `shipments` · `promotions` · `reviews`

---

## 🔌 Danh Sách API

| Method | Endpoint | Chức năng |
|--------|----------|-----------|
| GET | `/api/products` | Lấy danh sách sản phẩm |
| GET | `/api/categories` | Lấy danh mục |
| GET | `/api/cart` | Lấy giỏ hàng |
| POST | `/api/cart/add` | Thêm vào giỏ hàng |
| POST | `/api/checkout` | Tạo đơn hàng |
| POST | `/api/login` | Đăng nhập |
| POST | `/api/register` | Đăng ký |

---

## ❗ Xử Lý Lỗi Thường Gặp

| Lỗi | Cách khắc phục |
|-----|----------------|
| Trang trắng / 404 | Kiểm tra lại tên thư mục trong `htdocs` |
| `Access denied for user 'root'` | Kiểm tra `DB_USER` và `DB_PASS` trong `database.php` |
| `Unknown database 'shop_db'` | Thực hiện lại bước Import Database |
| Apache không Start | Tắt Skype/IIS hoặc đổi port Apache sang 8080 |
| MySQL không Start | Tắt MySQL service đang chạy nền của Windows |

---

## 📄 Giấy Phép

Dự án được thực hiện cho mục đích học tập tại **Đại Học Kinh Tế TP. Hồ Chí Minh**.  
© 2026 Nhóm 02 — Khoa Công Nghệ và Thiết Kế, Khóa K50.
