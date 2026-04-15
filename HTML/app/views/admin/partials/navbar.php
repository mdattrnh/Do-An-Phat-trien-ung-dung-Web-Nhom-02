<?php
$adminActivePage = $adminActivePage ?? '';
$adminNavItems = [
    'dashboard' => ['label' => 'Thống kê', 'icon' => 'bi-speedometer2', 'href' => BASE_URL . '/admin/dashboard'],
    'products' => ['label' => 'Sản phẩm', 'icon' => 'bi-box-seam', 'href' => BASE_URL . '/admin/products'],
    'categories' => ['label' => 'Danh mục', 'icon' => 'bi-grid', 'href' => BASE_URL . '/admin/categories'],
    'orders' => ['label' => 'Đơn hàng', 'icon' => 'bi-receipt', 'href' => BASE_URL . '/admin/orders'],
    'users' => ['label' => 'Tài khoản', 'icon' => 'bi-people', 'href' => BASE_URL . '/admin/users'],
];
?>
<nav class="navbar navbar-expand-xl navbar-dark admin-navbar sticky-top">
    <div class="container-fluid px-3 px-lg-4">
        <a class="navbar-brand" href="<?= BASE_URL ?>/admin/dashboard">
            <span class="brand-mark"></span>
            SOFTEDGE ADMIN
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="adminNavbar">
            <ul class="navbar-nav me-auto mb-3 mb-xl-0">
                <?php foreach ($adminNavItems as $key => $item): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $adminActivePage === $key ? 'active' : '' ?>" href="<?= $item['href'] ?>">
                            <i class="bi <?= $item['icon'] ?> me-1"></i><?= $item['label'] ?>
                        </a>
                    </li>
                <?php endforeach; ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/">
                        <i class="bi bi-globe2 me-1"></i>Xem website
                    </a>
                </li>
            </ul>
            <a class="nav-link text-danger px-0 px-xl-3" href="<?= BASE_URL ?>/admin/logout">
                <i class="bi bi-box-arrow-right me-1"></i>Đăng xuất
            </a>
        </div>
    </div>
</nav>
