<?php
$adminPageTitle = 'Quản lý Đơn hàng - Admin';
$adminActivePage = 'orders';
require __DIR__ . '/partials/head.php';
?>
<body class="admin-body">
<?php require __DIR__ . '/partials/navbar.php'; ?>

<main class="admin-shell">
    <div class="container-fluid px-3 px-lg-4">
        <section class="admin-hero">
            <div class="admin-kicker">Orders</div>
            <h1 class="admin-title">Quản lý đơn hàng</h1>
        </section>

        <?php if (!empty($_SESSION['admin_flash_success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['admin_flash_success']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['admin_flash_success']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['admin_flash_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['admin_flash_error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['admin_flash_error']); ?>
        <?php endif; ?>

        <section class="panel">
            <div class="panel-header d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <div>
                    <h2 class="panel-title">Danh sách đơn hàng</h2>
                </div>
                <span class="soft-badge soft-badge--neutral">
                    <i class="bi bi-receipt"></i><?= count($orders) ?> đơn trong trang này
                </span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle text-center table-admin">
                    <thead>
                        <tr>
                            <th class="text-start">Mã đơn</th>
                            <th>Khách hàng</th>
                            <th class="text-start">Sản phẩm</th>
                            <th>Số lượng</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái đơn</th>
                            <th>Thanh toán</th>
                            <th>Ngày đặt</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td class="text-start"><code><?= substr($order['order_id'], 0, 8) ?></code></td>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($order['full_name'] ?? 'N/A') ?></div>
                                    <div class="text-muted small"><?= htmlspecialchars($order['email'] ?? '') ?></div>
                                </td>
                                <td class="text-start"><small><?= htmlspecialchars(substr($order['product_names'] ?? 'N/A', 0, 50)) ?></small></td>
                                <td><span class="soft-badge soft-badge--neutral"><?= $order['total_items'] ?? 0 ?></span></td>
                                <td class="fw-bold text-danger"><?= number_format($order['final_amount'], 0, ',', '.') ?> đ</td>
                                <td>
                                    <span class="soft-badge status-badge badge-<?= $order['order_status'] ?>">
                                        <?php
                                        $statusNames = [
                                            'pending'   => 'Chờ xác nhận',
                                            'confirmed' => 'Đã xác nhận',
                                            'shipped'   => 'Đang vận chuyển',
                                            'delivered' => 'Đã giao',
                                            'cancelled' => 'Đã hủy',
                                        ];
                                        echo $statusNames[$order['order_status']] ?? $order['order_status'];
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="soft-badge status-badge badge-<?= $order['payment_status'] ?>">
                                        <?php
                                        $paymentNames = [
                                            'pending'  => 'Chờ thanh toán',
                                            'paid'     => 'Đã thanh toán',
                                            'failed'   => 'Thanh toán thất bại',
                                            'refunded' => 'Đã hoàn tiền',
                                        ];
                                        echo $paymentNames[$order['payment_status']] ?? $order['payment_status'];
                                        ?>
                                    </span>
                                </td>
                                <td><small><?= date('d/m/Y', strtotime($order['order_date'])) ?></small></td>
                                <td>
                                    <div class="d-inline-flex gap-2">
                                        <button class="btn btn-primary btn-icon" data-bs-toggle="modal" data-bs-target="#updateModal" onclick='setOrderData(<?= htmlspecialchars(json_encode($order), ENT_QUOTES, 'UTF-8') ?>)'>
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <a href="<?= BASE_URL ?>/admin/orders/delete?delete=<?= urlencode($order['order_id']) ?>" class="btn btn-danger btn-icon" onclick="return confirm('Bạn có chắc chắn muốn xóa đơn hàng này?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="9" class="py-5 text-muted">Chưa có đơn hàng nào.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="panel-footer">
                <nav>
                    <ul class="pagination justify-content-center mb-0">
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page - 1 ?>">Trước</a>
                        </li>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page + 1 ?>">Sau</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </section>
    </div>
</main>

<div class="modal fade" id="updateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="<?= BASE_URL ?>/admin/orders/update" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Cập nhật trạng thái đơn hàng</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="order_id" id="orderId">

                    <div class="mb-3">
                        <label class="form-label">Mã đơn hàng</label>
                        <input type="text" class="form-control" id="orderIdDisplay" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Trạng thái đơn hàng</label>
                        <select class="form-select" name="order_status" id="orderStatus">
                            <option value="pending">Chờ xác nhận</option>
                            <option value="confirmed">Đã xác nhận</option>
                            <option value="shipped">Đang vận chuyển</option>
                            <option value="delivered">Đã giao</option>
                            <option value="cancelled">Đã hủy</option>
                        </select>
                    </div>

                    <div class="mb-0">
                        <label class="form-label">Trạng thái thanh toán</label>
                        <select class="form-select" name="payment_status" id="paymentStatus">
                            <option value="pending">Chờ thanh toán</option>
                            <option value="paid">Đã thanh toán</option>
                            <option value="failed">Thanh toán thất bại</option>
                            <option value="refunded">Đã hoàn tiền</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function setOrderData(order) {
    document.getElementById('orderId').value = order.order_id;
    document.getElementById('orderIdDisplay').value = order.order_id;
    document.getElementById('orderStatus').value = order.order_status;
    document.getElementById('paymentStatus').value = order.payment_status;
}
</script>
</body>
</html>