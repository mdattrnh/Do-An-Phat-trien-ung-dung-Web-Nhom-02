<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản Lý Tài Khoản - Admin</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&family=Be+Vietnam+Pro:wght@700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --se-bg:       #f0ece5;
            --se-surface:  #faf8f5;
            --se-dark:     #1c1917;
            --se-accent:   #c9a46a;
            --se-muted:    #78716c;
            --se-border:   #e7e2da;
            --se-radius:   14px;
            --se-green:    #1e5c3a;
            --se-amber:    #92560f;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background-color: var(--se-bg) !important;
            color: var(--se-dark);
        }

        .navbar.bg-dark {
            background: var(--se-dark) !important;
            border-bottom: 2px solid var(--se-accent);
            padding-top: 0; padding-bottom: 0;
            min-height: 58px;
        }
        .navbar-brand {
            font-family: 'Be Vietnam Pro', sans-serif !important;
            font-size: 14px !important;
            letter-spacing: 0.14em;
            padding: 16px 0;
        }
        .navbar-brand span {
            display: inline-block;
            width: 7px; height: 7px;
            background: var(--se-accent);
            border-radius: 2px;
            margin-right: 8px;
            vertical-align: middle;
            position: relative; top: -1px;
        }
        .navbar-nav .nav-link {
            font-size: 13.5px !important;
            font-weight: 500;
            padding: 18px 14px !important;
            color: rgba(255,255,255,0.6) !important;
            border-bottom: 2px solid transparent;
            margin-bottom: -2px;
            transition: all 0.18s;
        }
        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-link.active {
            color: #fff !important;
            border-bottom-color: var(--se-accent);
        }
        .navbar-nav .nav-link.text-danger {
            color: rgba(255,255,255,0.4) !important;
        }
        .navbar-nav .nav-link.text-danger:hover {
            color: #f87171 !important;
            border-bottom-color: transparent;
        }

        .container.mt-4 {
            max-width: 1100px;
            padding-top: 40px !important;
        }
        .container.mt-4 > h2 {
            font-family: 'Be Vietnam Pro', sans-serif;
            font-size: 24px;
            font-weight: 800;
            color: var(--se-dark);
            margin-bottom: 32px !important;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .container.mt-4 > h2::before {
            content: '';
            display: inline-block;
            width: 4px; height: 24px;
            background: var(--se-accent);
            border-radius: 2px;
        }

        .table-responsive {
            background: white;
            border-radius: var(--se-radius);
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
            overflow: hidden;
        }

        .table {
            margin: 0;
            font-size: 14px;
        }

        .table thead {
            background: var(--se-dark);
            color: white;
        }

        .table thead th {
            border: none;
            font-weight: 600;
            padding: 16px;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.05em;
        }

        .table tbody td {
            border-bottom: 1px solid var(--se-border);
            padding: 14px 16px;
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background: #fafaf9;
        }

        .btn-delete {
            background: #dc2626;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-delete:hover {
            background: #b91c1c;
            transform: translateY(-2px);
        }

        .alert {
            border-radius: var(--se-radius);
            border: none;
            margin-bottom: 24px;
        }

        .stats-row {
            background: white;
            border-radius: var(--se-radius);
            padding: 20px;
            margin-bottom: 24px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
        }

        .stat-item {
            flex: 1;
            min-width: 200px;
        }

        .stat-label {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--se-muted);
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: var(--se-dark);
        }
    </style>
</head>
<body class="bg-light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="<?= BASE_URL ?>/admin/dashboard"><span></span>SOFTEDGE ADMIN</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link" href="<?= BASE_URL ?>/admin/dashboard">Thống kê</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?= BASE_URL ?>/admin/products">Quản lý Sản phẩm</a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" href="<?= BASE_URL ?>/admin/users">Quản lý Tài khoản</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?= BASE_URL ?>/">Xem Website</a>
        </li>
      </ul>
      <ul class="navbar-nav">
          <li class="nav-item">
              <a class="nav-link text-danger" href="<?= BASE_URL ?>/admin/logout"><i class="bi bi-box-arrow-right"></i> Đăng xuất</a>
          </li>
      </ul>
    </div>
  </div>
</nav>

<!-- Content -->
<div class="container mt-4">
    <h2 class="mb-4">Quản Lý Tài Khoản User</h2>

    <?php if(isset($_SESSION['admin_flash_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> <?= $_SESSION['admin_flash_success'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['admin_flash_success']); ?>
    <?php endif; ?>

    <?php if(isset($_SESSION['admin_flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle"></i> <?= $_SESSION['admin_flash_error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['admin_flash_error']); ?>
    <?php endif; ?>

    <!-- Statistics -->
    <div class="stats-row">
        <div class="stat-item">
            <div class="stat-label"><i class="bi bi-person-check"></i> Tổng User Accounts</div>
            <div class="stat-value"><?= $userCount ?? 0 ?></div>
        </div>
        <div class="stat-item">
            <div class="stat-label"><i class="bi bi-cart-check"></i> Tất Cả Đơn Hàng</div>
            <div class="stat-value"><?= count($users) > 0 ? array_sum(array_column($users, 'order_count')) : 0 ?></div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th><i class="bi bi-hash"></i> STT</th>
                    <th><i class="bi bi-person"></i> Họ Tên</th>
                    <th><i class="bi bi-envelope"></i> Email</th>
                    <th><i class="bi bi-calendar"></i> Ngày Đăng Ký</th>
                    <th><i class="bi bi-cart-check"></i> Số Đơn Hàng</th>
                    <th><i class="bi bi-gear"></i> Hành Động</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($users)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">Chưa có tài khoản user nào</td>
                    </tr>
                <?php else: ?>
                    <?php foreach($users as $index => $user): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td>
                                <div style="font-weight: 500;"><?= htmlspecialchars($user['full_name']) ?></div>
                            </td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></td>
                            <td>
                                <span class="badge bg-info"><?= $user['order_count'] ?? 0 ?></span>
                            </td>
                            <td>
                                <a href="<?= BASE_URL ?>/admin/users/delete?delete=<?= $user['user_id'] ?>" 
                                   class="btn-delete" 
                                   onclick="return confirm('Bạn chắc chắn muốn xóa tài khoản này? Hành động này không thể hoàn tác!');">
                                    <i class="bi bi-trash"></i> Xóa
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        <a href="<?= BASE_URL ?>/admin/dashboard" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại Tổng quan
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
