<?php
$adminPageTitle = 'Dashboard - Admin';
$adminActivePage = 'dashboard';
require __DIR__ . '/partials/head.php';
?>
<body class="admin-body">
<?php require __DIR__ . '/partials/navbar.php'; ?>

<main class="admin-shell">
    <div class="container-fluid px-3 px-lg-4">

        <section class="admin-hero">
            <div class="admin-kicker">Control Center</div>
            <h1 class="admin-title">Dashboard Admin</h1>
        </section>

        <section class="row g-4">
            <div class="col-sm-6 col-xl">
                <div class="metric-card metric-dark">
                    <div class="card-body">
                        <div class="metric-label"><i class="bi bi-box-seam me-2"></i>Tổng sản phẩm</div>
                        <div class="metric-value"><?= $productCount ?? 0 ?></div>
                        <div class="metric-note">Kho sản phẩm đang được quản lý.</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl">
                <div class="metric-card metric-green">
                    <div class="card-body">
                        <div class="metric-label"><i class="bi bi-receipt me-2"></i>Đơn hàng</div>
                        <div class="metric-value"><?= $orderCount ?? 0 ?></div>
                        <div class="metric-note">Tổng lượt mua đã phát sinh.</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl">
                <div class="metric-card metric-gold">
                    <div class="card-body">
                        <div class="metric-label"><i class="bi bi-people me-2"></i>Khách hàng</div>
                        <div class="metric-value"><?= $customerCount ?? 0 ?></div>
                        <div class="metric-note">Số khách có dữ liệu trong hệ thống.</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl">
                <div class="metric-card metric-blue">
                    <div class="card-body">
                        <div class="metric-label"><i class="bi bi-cash-coin me-2"></i>Doanh thu</div>
                        <div class="metric-value"><?= number_format($totalRevenue ?? 0, 0, ',', '.') ?> ₫</div>
                        <div class="metric-note">Giá trị doanh thu tích lũy.</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl">
                <div class="metric-card metric-plum">
                    <div class="card-body">
                        <div class="metric-label"><i class="bi bi-person-badge me-2"></i>User account</div>
                        <div class="metric-value"><?= $userCount ?? 0 ?></div>
                        <div class="metric-note">Tài khoản người dùng đang hoạt động.</div>
                    </div>
                </div>
            </div>
        </section>

    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>