<?php
$adminPageTitle = 'Quản lý Tài khoản - Admin';
$adminActivePage = 'users';
require __DIR__ . '/partials/head.php';
$totalUserOrders = count($users) > 0 ? array_sum(array_column($users, 'order_count')) : 0;
?>
<body class="admin-body">
<?php require __DIR__ . '/partials/navbar.php'; ?>

<main class="admin-shell">
    <div class="container-fluid px-3 px-lg-4">
        <section class="admin-hero">
            <div class="admin-kicker">Users</div>
            <h1 class="admin-title">Quản lý tài khoản người dùng</h1>
        </section>

        <?php if (isset($_SESSION['admin_flash_success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i><?= $_SESSION['admin_flash_success'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['admin_flash_success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['admin_flash_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i><?= $_SESSION['admin_flash_error'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['admin_flash_error']); ?>
        <?php endif; ?>

        <section class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="metric-card metric-dark">
                    <div class="card-body">
                        <div class="metric-label"><i class="bi bi-person-check me-2"></i>Tổng user accounts</div>
                        <div class="metric-value"><?= $userCount ?? 0 ?></div>
                        <div class="metric-note">Tổng số tài khoản user hiện có trong hệ thống.</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="metric-card metric-blue">
                    <div class="card-body">
                        <div class="metric-label"><i class="bi bi-cart-check me-2"></i>Tổng đơn hàng</div>
                        <div class="metric-value"><?= $totalUserOrders ?></div>
                        <div class="metric-note">Tổng đơn đã được gắn với các tài khoản người dùng.</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="panel">
            <div class="panel-header d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <div>
                    <h2 class="panel-title">Danh sách tài khoản</h2>
                </div>
                <a href="<?= BASE_URL ?>/admin/dashboard" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Quay lại tổng quan
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle table-admin">
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Họ tên</th>
                            <th>Email</th>
                            <th>Ngày đăng ký</th>
                            <th>Đơn hàng</th>
                            <th class="text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">Chưa có tài khoản user nào.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $index => $user): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($user['full_name']) ?></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></td>
                                    <td><span class="soft-badge soft-badge--info"><?= $user['order_count'] ?? 0 ?></span></td>
                                    <td class="text-center">
                                        <a href="<?= BASE_URL ?>/admin/users/delete?delete=<?= $user['user_id'] ?>" class="btn btn-danger" onclick="return confirm('Bạn chắc chắn muốn xóa tài khoản này? Hành động này không thể hoàn tác!');">
                                            <i class="bi bi-trash me-2"></i>Xóa
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
