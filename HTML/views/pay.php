<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh Toán - SOFT EDGE</title>
    <link href="https://fonts.googleapis.com/css2?family=Urbanist:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400&family=Inter:wght@300;400;500;700&family=Bebas+Neue&display=swap" rel="stylesheet">
    <style>
        :root {
            color-scheme: light;
            color: #1b1b1b;
            background: #f5efe6;
            font-family: 'Urbanist', sans-serif;
            font-size: 16px;
            line-height: 1.6;
        }

        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; min-height: 100%; }
        body { background: linear-gradient(180deg, #f7f1e8, #fff 65%); color: #1f1f1f; }
        a { color: inherit; text-decoration: none; }
        img { display: block; max-width: 100%; }

        .pay-page {
            max-width: 1180px;
            margin: 0 auto;
            padding: 30px 20px 60px;
        }

        .page-top {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-bottom: 28px;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.4rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.14em;
        }

        .brand span { color: #000; }

        .breadcrumb {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.95rem;
            color: #555;
        }

        .breadcrumb a { color: #555; }

        .stepbar {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 32px;
        }

        .step {
            padding: 18px 20px;
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 18px 45px rgba(0,0,0,0.05);
            display: grid;
            grid-template-columns: 40px 1fr;
            align-items: center;
            gap: 14px;
        }

        .step span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #f0f0f0;
            color: #666;
            font-weight: 700;
        }

        .step.active {
            background: #1b1b1b;
            color: #fff;
        }

        .step.active span {
            background: #fff;
            color: #1b1b1b;
        }

        .step-title {
            font-size: 0.95rem;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .step-sub {
            color: #7a7a7a;
            font-size: 0.82rem;
        }

        .payment-grid {
            display: grid;
            grid-template-columns: 1.65fr 1fr;
            gap: 28px;
        }

        .panel {
            background: rgba(255,255,255,0.95);
            border-radius: 28px;
            padding: 32px;
            box-shadow: 0 28px 70px rgba(0,0,0,0.08);
        }

        .panel h2 {
            font-size: clamp(1.5rem, 2vw, 2rem);
            margin-bottom: 12px;
            letter-spacing: -0.04em;
        }

        .panel p.lead {
            color: #666;
            font-size: 0.96rem;
            margin-bottom: 24px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 26px;
        }

        .info-card {
            background: #f8f3ec;
            border: 1px solid rgba(0,0,0,0.05);
            border-radius: 20px;
            padding: 20px;
            min-height: 110px;
        }

        .info-card strong {
            display: block;
            margin-bottom: 10px;
            color: #1b1b1b;
            font-size: 0.98rem;
        }

        .info-card span {
            color: #5e5e5e;
            font-size: 0.92rem;
        }

        .address-panel {
            display: grid;
            gap: 16px;
            margin-bottom: 24px;
        }

        .address-card {
            background: #fff;
            border: 1px solid rgba(0,0,0,0.06);
            border-radius: 20px;
            padding: 22px;
            display: grid;
            gap: 10px;
        }

        .address-card strong {
            font-size: 1rem;
            margin-bottom: 6px;
        }

        .address-card span {
            color: #6b6b6b;
            font-size: 0.94rem;
            line-height: 1.7;
        }

        .payment-methods {
            display: grid;
            gap: 14px;
            margin-bottom: 24px;
        }

        .payment-method {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px 18px;
            border-radius: 16px;
            border: 1px solid rgba(0,0,0,0.08);
            background: #fff;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .payment-method:hover,
        .payment-method input:checked + label {
            border-color: #000;
            transform: translateY(-1px);
        }

        .payment-method input {
            accent-color: #000;
            width: 18px;
            height: 18px;
        }

        .payment-method label {
            font-weight: 600;
            color: #1b1b1b;
            margin: 0;
            cursor: pointer;
        }

        .order-list {
            display: grid;
            gap: 14px;
        }

        .order-item {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 14px;
            padding: 18px 20px;
            border-radius: 20px;
            background: #fbf6f0;
            border: 1px solid rgba(0,0,0,0.04);
        }

        .item-info {
            display: grid;
            gap: 8px;
        }

        .item-name {
            font-weight: 700;
            font-size: 0.98rem;
        }

        .item-meta {
            color: #636363;
            font-size: 0.9rem;
        }

        .item-price {
            font-weight: 700;
            color: #000;
            white-space: nowrap;
            align-self: center;
        }

        .summary-panel {
            display: grid;
            gap: 24px;
        }

        .summary-box {
            background: #fdfbf8;
            border: 1px solid rgba(0,0,0,0.06);
            border-radius: 24px;
            padding: 28px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
            font-size: 0.95rem;
            margin-bottom: 14px;
            color: #4f4f4f;
        }

        .summary-row strong {
            font-weight: 700;
            color: #1b1b1b;
        }

        .summary-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 16px;
            border-top: 1px solid rgba(0,0,0,0.08);
            font-size: 1.15rem;
            font-weight: 700;
        }

        .summary-note {
            background: #fff;
            border-radius: 18px;
            padding: 18px;
            color: #5e5e5e;
            font-size: 0.94rem;
            line-height: 1.7;
            border: 1px solid rgba(0,0,0,0.04);
        }

        .confirm-btn {
            width: 100%;
            border: none;
            border-radius: 16px;
            padding: 16px 20px;
            background: #1b1b1b;
            color: #fff;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.2s ease, background 0.2s ease;
        }

        .confirm-btn:hover {
            background: #333;
            transform: translateY(-1px);
        }

        .help-link {
            display: block;
            text-align: center;
            margin-top: 16px;
            font-size: 0.94rem;
            color: #6b6b6b;
        }

        .help-link:hover {
            color: #000;
        }

        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.52);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal-overlay.show { display: flex; }

        .modal {
            background: #fff;
            border-radius: 28px;
            max-width: 420px;
            width: 100%;
            padding: 32px;
            text-align: center;
            box-shadow: 0 24px 80px rgba(0,0,0,0.18);
        }

        .modal h3 {
            font-size: 1.35rem;
            margin-bottom: 18px;
        }

        .modal p {
            color: #6c6c6c;
            margin-bottom: 28px;
            font-size: 0.98rem;
        }

        .modal-actions {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
        }

        .modal-actions button {
            flex: 1;
            min-width: 120px;
            border: none;
            border-radius: 14px;
            padding: 14px 16px;
            font-weight: 700;
            cursor: pointer;
        }

        .modal-actions .btn-secondary {
            background: #f1f1f1;
            color: #222;
        }

        .modal-actions .btn-primary {
            background: #1b1b1b;
            color: #fff;
        }

        .loading,
        .success-message {
            display: none;
            background: rgba(255,255,255,0.95);
            position: fixed;
            inset: 0;
            z-index: 1100;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .loading.show,
        .success-message.show { display: flex; }

        .loading-box,
        .success-box {
            background: #fff;
            padding: 36px;
            border-radius: 28px;
            text-align: center;
            box-shadow: 0 24px 90px rgba(0,0,0,0.14);
            width: min(460px, 100%);
        }

        .spinner {
            width: 52px;
            height: 52px;
            border: 5px solid #ececec;
            border-top: 5px solid #1b1b1b;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        .success-icon {
            font-size: 56px;
            margin-bottom: 18px;
            display: block;
        }

        .success-box h3 {
            margin-bottom: 14px;
            font-size: 1.6rem;
        }

        .success-box p {
            color: #5e5e5e;
            margin-bottom: 24px;
        }

        .success-box a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 14px 24px;
            background: #1b1b1b;
            color: #fff;
            border-radius: 14px;
            font-weight: 700;
        }

        @media (max-width: 980px) {
            .payment-grid { grid-template-columns: 1fr; }
            .info-grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 640px) {
            .pay-page { padding: 18px 12px 40px; }
            .stepbar { grid-template-columns: 1fr; }
            .page-top { align-items: flex-start; }
            .brand { font-size: 1.2rem; }
        }
    </style>
</head>
<body>
    <div class="pay-page">
        <div class="page-top">
            <a class="brand" href="<?= BASE_URL ?>">SOFT<span>EDGE</span></a>
            <div class="breadcrumb">
                <a href="<?= BASE_URL ?>">Trang chủ</a>
                <span>›</span>
                <span>Thanh toán</span>
            </div>
        </div>

        <div class="stepbar">
            <div class="step completed">
                <span>1</span>
                <div>
                    <div class="step-title">Giỏ hàng</div>
                    <div class="step-sub">Đã hoàn thành</div>
                </div>
            </div>
            <div class="step active">
                <span>2</span>
                <div>
                    <div class="step-title">Thanh toán</div>
                    <div class="step-sub">Xác nhận đơn hàng</div>
                </div>
            </div>
            <div class="step">
                <span>3</span>
                <div>
                    <div class="step-title">Hoàn tất</div>
                    <div class="step-sub">Nhận đơn hàng</div>
                </div>
            </div>
        </div>

        <div class="payment-grid">
            <section class="panel">
                <h2>Thông tin đơn hàng</h2>
                <p class="lead">Kiểm tra lại thông tin, địa chỉ và phương thức thanh toán trước khi hoàn tất đơn hàng.</p>

                <div class="info-grid">
                    <div class="info-card">
                        <strong>Khách hàng</strong>
                        <span><?= htmlspecialchars($user['full_name'] ?? ''); ?></span>
                    </div>
                    <div class="info-card">
                        <strong>Email</strong>
                        <span><?= htmlspecialchars($user['email'] ?? ''); ?></span>
                    </div>
                    <div class="info-card">
                        <strong>Số lượng món</strong>
                        <span><?= count($cartItems); ?> sản phẩm</span>
                    </div>
                    <div class="info-card">
                        <strong>Giá trị tạm</strong>
                        <span><?= number_format($totalAmount, 0, ',', '.') ?> ₫</span>
                    </div>
                </div>

                <div class="address-panel">
                    <div class="address-card">
                        <strong>Địa chỉ giao hàng</strong>
                        <?php if ($address): ?>
                            <span><?= htmlspecialchars($address['receiver_name']) ?> · <?= htmlspecialchars($address['phone']) ?></span>
                            <span><?= htmlspecialchars($address['street_address']) ?>, <?= htmlspecialchars($address['city'] ?? '') ?></span>
                        <?php else: ?>
                            <span>Chưa có địa chỉ giao hàng.</span>
                            <span>Vui lòng cập nhật địa chỉ trước khi thanh toán.</span>
                        <?php endif; ?>
                    </div>

                    <div class="payment-methods">
                        <label class="payment-method">
                            <input type="radio" name="paymentMethod" value="cod" checked>
                            <label>Thanh toán khi nhận hàng (COD)</label>
                        </label>
                        <label class="payment-method">
                            <input type="radio" name="paymentMethod" value="online">
                            <label>Thanh toán online</label>
                        </label>
                    </div>
                </div>

                <div class="order-list">
                    <?php if (!empty($cartItems)): ?>
                        <?php foreach ($cartItems as $item): ?>
                            <div class="order-item">
                                <div class="item-info">
                                    <div class="item-name"><?= htmlspecialchars($item['product_name']) ?></div>
                                    <div class="item-meta">Size: <?= htmlspecialchars($item['size']) ?> · Số lượng: <?= $item['quantity'] ?></div>
                                </div>
                                <div class="item-price"><?= number_format($item['unit_price'] * $item['quantity'], 0, ',', '.') ?> ₫</div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="address-card">
                            <span>Giỏ hàng của bạn đang trống. Vui lòng trở lại chọn sản phẩm.</span>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <aside class="panel summary-panel">
                <div class="summary-box">
                    <div class="summary-row">
                        <span>Giá trị đơn hàng</span>
                        <strong><?= number_format($totalAmount, 0, ',', '.') ?> ₫</strong>
                    </div>
                    <div class="summary-row">
                        <span>Phí vận chuyển</span>
                        <strong>0 ₫</strong>
                    </div>
                    <div class="summary-row">
                        <span>Giảm giá</span>
                        <strong>0 ₫</strong>
                    </div>
                    <div class="summary-total">
                        <span>Tổng thanh toán</span>
                        <span><?= number_format($totalAmount, 0, ',', '.') ?> ₫</span>
                    </div>
                </div>

                <div class="summary-box summary-note">
                    <strong>Lưu ý khi thanh toán</strong>
                    <p>Bạn có thể thay đổi địa chỉ giao hàng hoặc phương thức thanh toán trước khi hoàn tất. Đơn hàng sẽ được xử lý ngay khi bạn xác nhận thanh toán.</p>
                </div>

                <button class="confirm-btn" onclick="confirmPayment()">Xác nhận và thanh toán</button>
                <a class="help-link" href="<?= BASE_URL ?>">← Quay về trang chủ</a>
            </aside>
        </div>
    </div>

    <div class="modal-overlay" id="confirmModal">
        <div class="modal">
            <h3>Xác nhận thanh toán</h3>
            <p>Bạn có muốn thanh toán đơn hàng này với số tiền <strong id="modalAmount">0 ₫</strong> không?</p>
            <div class="modal-actions">
                <button class="btn-secondary" onclick="cancelPayment()">Quay lại</button>
                <button class="btn-primary" onclick="proceedPayment()">Thanh toán</button>
            </div>
        </div>
    </div>

    <div class="loading" id="loading">
        <div class="loading-box">
            <div class="spinner"></div>
            <p>Đang xử lý đơn hàng của bạn...</p>
        </div>
    </div>

    <div class="success-message" id="successMessage">
        <div class="success-box">
            <span class="success-icon">✓</span>
            <h3>Thanh toán thành công!</h3>
            <p>Đơn hàng của bạn đã được gửi đến hệ thống. Chúng tôi sẽ xử lý và liên hệ bạn sớm nhất.</p>
            <a href="<?= BASE_URL ?>">Về trang chủ</a>
        </div>
    </div>

    <input type="hidden" id="addressId" value="<?= $address['address_id'] ?? '' ?>">
    <input type="hidden" id="totalAmount" value="<?= $totalAmount ?>">

    <script>
        const totalAmount = Number(document.getElementById('totalAmount').value || 0);

        function formatCurrency(value) {
            return value.toLocaleString('vi-VN') + ' ₫';
        }

        function confirmPayment() {
            document.getElementById('modalAmount').textContent = formatCurrency(totalAmount);
            document.getElementById('confirmModal').classList.add('show');
        }

        function cancelPayment() {
            document.getElementById('confirmModal').classList.remove('show');
        }

        function proceedPayment() {
            document.getElementById('confirmModal').classList.remove('show');
            document.getElementById('loading').classList.add('show');
            const addressId = document.getElementById('addressId').value;
            const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked')?.value || 'cod';

            fetch('<?= BASE_URL ?>/api/payment', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    address_id: addressId,
                    amount: totalAmount,
                    payment_method: paymentMethod
                })
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('loading').classList.remove('show');
                if (data.success) {
                    document.getElementById('successMessage').classList.add('show');
                    setTimeout(() => {
                        window.location.href = '<?= BASE_URL ?>/';
                    }, 2200);
                } else {
                    alert('Lỗi: ' + (data.error || 'Không thể thanh toán')); 
                }
            })
            .catch(err => {
                document.getElementById('loading').classList.remove('show');
                alert('Lỗi kết nối: ' + err.message);
            });
        }
    </script>
</body>
</html>
