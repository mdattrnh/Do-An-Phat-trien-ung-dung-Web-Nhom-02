<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Admin</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&family=Be+Vietnam+Pro:wght@700;800&display=swap" rel="stylesheet">

    <style>
        /* =============================================
           SOFTEDGE THEME — Bootstrap Override Only
           Không thay đổi cấu trúc HTML gốc
        ============================================= */
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

        /* Body */
        body {
            font-family: 'DM Sans', sans-serif;
            background-color: var(--se-bg) !important;
            color: var(--se-dark);
        }

        /* ── Navbar ── */
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

        /* ── Container heading ── */
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

        /* ── Cards ── */
        .card {
            border: none !important;
            border-radius: var(--se-radius) !important;
            overflow: hidden;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 36px rgba(0,0,0,0.13) !important;
        }
        .card.shadow-sm {
            box-shadow: 0 2px 12px rgba(0,0,0,0.07) !important;
        }
        .card-body.py-4 {
            padding: 30px 28px !important;
            position: relative;
        }

        /* Màu card override */
        .card.bg-primary {
            background: linear-gradient(135deg, #1c1917 0%, #3b2f26 100%) !important;
        }
        .card.bg-success {
            background: linear-gradient(135deg, var(--se-green) 0%, #2d7a4f 100%) !important;
        }
        .card.bg-warning {
            background: linear-gradient(135deg, #b07d2a 0%, var(--se-accent) 100%) !important;
        }

        /* Card decorative circle */
        .card-body.py-4::after {
            content: '';
            position: absolute;
            right: -20px; bottom: -20px;
            width: 100px; height: 100px;
            border-radius: 50%;
            background: rgba(255,255,255,0.06);
            pointer-events: none;
        }
        .card-body.py-4::before {
            content: '';
            position: absolute;
            right: 20px; bottom: 30px;
            width: 60px; height: 60px;
            border-radius: 50%;
            background: rgba(255,255,255,0.04);
            pointer-events: none;
        }

        .card-title {
            font-size: 11px !important;
            font-weight: 700 !important;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            opacity: 0.65;
            margin-bottom: 14px !important;
            display: flex; align-items: center; gap: 6px;
        }
        .card-title i { font-size: 14px; }

        .card-body h2.fw-bold {
            font-family: 'Be Vietnam Pro', sans-serif !important;
            font-size: 46px !important;
            font-weight: 800 !important;
            letter-spacing: -0.04em;
            line-height: 1;
            margin: 0;
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
          <a class="nav-link active" href="<?= BASE_URL ?>/admin/dashboard">Thống kê</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?= BASE_URL ?>/admin/products">Quản lý Sản phẩm</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?= BASE_URL ?>/admin/users">Quản lý Tài khoản</a>
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
    <h2 class="mb-4">Chào mừng trở lại, Admin!</h2>
    <div class="row">
        <div class="col-md-4">
            <div class="card bg-primary text-white shadow-sm mb-4">
                <div class="card-body py-4">
                    <h5 class="card-title"><i class="bi bi-box-seam"></i> Tổng Sản Phẩm</h5>
                    <h2 class="fw-bold"><?= $productCount ?? 0 ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white shadow-sm mb-4">
                <div class="card-body py-4">
                    <h5 class="card-title"><i class="bi bi-cart-check"></i> Số Đơn Hàng</h5>
                    <h2 class="fw-bold"><?= $orderCount ?? 0 ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-dark shadow-sm mb-4">
                <div class="card-body py-4">
                    <h5 class="card-title"><i class="bi bi-people"></i> Khách Hàng</h5>
                    <h2 class="fw-bold"><?= $customerCount ?? 0 ?></h2>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%); border: none; color: white;">
                <div class="card-body py-4">
                    <h5 class="card-title"><i class="bi bi-cash-coin"></i> Tổng Doanh Thu</h5>
                    <h2 class="fw-bold"><?= number_format($totalRevenue ?? 0, 0, ',', '.') ?> ₫</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card" style="background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%); border: none; color: white;">
                <div class="card-body py-4">
                    <h5 class="card-title"><i class="bi bi-person-check"></i> Tài Khoản User</h5>
                    <h2 class="fw-bold"><?= $userCount ?? 0 ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card" style="background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%); border: none; color: white;">
                <div class="card-body py-4">
                    <h5 class="card-title"><i class="bi bi-link-45deg"></i> Quản Lý Tài Khoản</h5>
                    <a href="<?= BASE_URL ?>/admin/users" class="btn btn-light btn-sm mt-2">Xem Chi Tiết</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>