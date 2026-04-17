# QUY TRÌNH HOẠT ĐỘNG CỦA DỰ ÁN "CHATHANHNE SPA - PHP MVC"

Dự án này là sự kết hợp giữa mặt tiền (Front-end) vô cùng mượt mà dạng Single Page Application (Trang đơn, không tải lại trang) và nền tảng hậu cần vững chắc từ Back-end (PHP thuần chuẩn MVC). Dưới đây là quy trình dòng chảy dữ liệu từ khi người dùng gõ URL đến lúc họ thao tác trên web.

---

## 1. Request Đầu Tiên: Vẫn Mượt Mà Chào Đón (Trang Chủ)

1. **Người dùng mở URL:** `http://localhost/HTML/public/`
2. **Cổng bảo vệ (Front Controller):** Tất cả các luồng truy cập đều bắt buộc đi qua cửa chính là file `public/index.php`.
   - File này nạp các cấu hình kết nối DB (`database.php`) và danh sách các đường dẫn cho phép (`routes.php`).
   - Tự động gọi các `Class` dựa trên cơ chế *Autoloader*.
3. **Bộ Định Tuyến (Router):** Router trong `index.php` thấy URL là `/` (trang chủ gốc), nó tra bảng `routes.php` và quyết định gọi Class `HomeController@index`.
4. **Trả về Giao Diện:** `HomeController` lấy file giao diện tĩnh `app/views/home.php` (chính là bản copy của file `index.html` xuất sắc của bạn) bọc lại và trả thẳng về trình duyệt.
   - Trình duyệt tải Giao diện CSS (`style.css`), Font chữ, Animation các icon.

---

## 2. Quá Trình Nuôi Sống Dữ Liệu (Call API)

Ngay khi trình duyệt tải xong bộ xương `home.php`, nó bắt đầu gọi dữ liệu sản phẩm để lấp đầy "thịt" vào giao diện. Sự ảo thuật của dự án Single Page nằm ở đây!

1. **JavaScript Khởi Động:** File `script.js` ở dưới đáy trang chạy func `initStore()`.
2. **"Đổ Gạch" Data P1 (Lệnh Ajax/Fetch):** JS bắn một câu lệnh nền tĩnh lặng (ngầm trong trình duyệt): 
   `fetch('/HTML/public/api/products')` yêu cầu lấy 100 cái áo/quần/mũ.
3. **Tiếp Nhận Phía Server (Router API):** File `index.php` nhận được lệnh gửi tới `/api/products`. Router lôi cổ ông `ApiController@getProducts` ra làm việc.
4. **Model Kéo Data Từ Đáy Biển:**
   - `ApiController` gọi Model `Product->getAllForSpa()`.
   - Model `Product` xin instance kết nối PDO từ `Database`, rồi dùng câu lệnh `SELECT * FROM spa_products` lấy 100 dòng.
   - Dữ liệu ở cột `sizes` đang trong trạng thái JSON String được bung ra thành mảng Array gọn gàng (`json_decode`).
5. **Đóng Gói Chuyển Phát Nhanh:** Controller cầm đống dữ liệu đó gắn nhãn `Content-Type: application/json` và quăng lại trả cho trình duyệt.
6. **"Đổ Gạch" Data P2 (Cập Nhật UI Mượt):** 
   - Hàm `initStore()` của phần JS nhận về cục JSON. Nó gán cho biến bộ nhớ tạm `products` và `filtered`.
   - Cuối cùng gọi `renderGrid()`. Lập tức 100 ô vuông sản phẩm hiện ra đẹp đẽ, kèm đầy đủ filter (Nam/Nữ, Áo thun/Quần...), phân trang, hình ảnh cực căng nét mà *không hề chuyển trang nháy màn hình*.

---

## 3. Quá Trình Tương Tác Của Người Dùng (Client-Side Action)

Sau khi giao diện đã sạc đầy dữ liệu, server PHP đã làm xong nhiệm vụ của nó. Bây giờ JavaScript bắt đầu lo liệu mọi tương tác để tiết kiệm băng thông và tăng độ sướng của người dùng:

*   **Chỉnh Bộ Lọc:** Bấm qua "Quần Cargo" hoặc "Áo Hoodie", JS tự gọi biến tạm `filtered` tìm lọc đúng loại và in ra tức thị (`renderGrid`).
*   **Mở Chi Tiết (Product Modal):** Khi bấm vào 1 sản phẩm `openModal(id)`, JS dò trong bộ nhớ lấy đúng sản phẩm, điền size, điền tên màu, đẩy hình lên Modal bóng bẩy ở giữa màn hình.
*   **Giỏ Hàng (Cart Sidebar):**
    *   Bấm "M" + "Thêm vào giỏ": Dữ liệu bắn thẳng vào cột thẻ `cart` trong bộ nhớ cục bộ (`LocalStorage`). Toast Notify hiện lên xanh lá góc dưới "Đã thêm vào Giỏ hàng ✓".
    *   Sau này tắt tab đi mở lại, `loadCartFromLocal()` lại mọc ra nguyên cái giỏ hàng chưa kịp thanh toán.

---

## 4. Đặc Trưng Của Kiến Trúc "Độc Bản" Này

1. **Front-End làm "Bình Mới", Back-End làm "Rượu Cũ Mịn Màng":** 
    Đây là cách thiết kế tiệm cận với dự án *ReactJS + Backend Laravel API* thực tế. Bạn tận hưởng tính thẩm mỹ cao của Vanilla JS và tốc độ phi thường, nhưng vẫn áp dụng tiêu chuẩn chuẩn hóa quản lý Data bảo mật ở hệ thống Back-end PHP thông qua MySQL thay vì dữ liệu *Fake cứng*.
2. **Khả năng Bành Trướng (Scalability):**
    Trong tương lai, nếu khách click chọn "Thanh Toán Đơn", File JS chỉ cần gọi lệnh `fetch('/HTML/public/api/checkout', {method: 'POST'})` -> Ném dữ liệu LocalStorage xuống PHP. Controller `OrderController` tiếp nhận rồi chém trực tiếp dữ liệu đó vào Bảng SQL Orders rất sạch sẽ và bảo mật toàn diện.
