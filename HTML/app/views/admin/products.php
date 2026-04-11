<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Sản phẩm - Admin </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
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
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background-color: var(--se-bg) !important;
            color: var(--se-dark);
        }

        /* ── Navbar ── */
        .navbar.bg-dark {
            background: var(--se-dark) !important;
            border-bottom: 2px solid var(--se-accent);
            min-height: 58px;
            padding-top: 0; padding-bottom: 0;
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
            color: rgba(255,255,255,0.55) !important;
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
            color: rgba(255,255,255,0.35) !important;
        }
        .navbar-nav .nav-link.text-danger:hover {
            color: #f87171 !important;
            border-bottom-color: transparent;
        }

        /* ── Container & heading ── */
        .container.mt-4 {
            max-width: 1200px;
            padding-top: 36px !important;
        }
        .d-flex h2 {
            font-family: 'Be Vietnam Pro', sans-serif;
            font-size: 20px;
            font-weight: 800;
        }

        /* ── Alert flash ── */
        .alert {
            border: none;
            border-radius: var(--se-radius) !important;
            font-size: 13.5px;
            font-weight: 500;
        }
        .alert-success {
            background: #dcf4e4 !important;
            color: #1e5c3a !important;
        }
        .alert-danger {
            background: #fde8e4 !important;
            color: #9b2a1a !important;
        }

        /* ── Btn primary (Thêm Mới) ── */
        .btn-primary {
            background: var(--se-accent) !important;
            border-color: var(--se-accent) !important;
            color: var(--se-dark) !important;
            font-weight: 700 !important;
            font-size: 13.5px !important;
            border-radius: 10px !important;
            padding: 9px 20px !important;
            transition: all 0.18s !important;
        }
        .btn-primary:hover {
            background: #b8904f !important;
            border-color: #b8904f !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(201,164,106,0.35) !important;
        }

        /* ── Card / table wrapper ── */
        .card.shadow-sm {
            border: 1px solid var(--se-border) !important;
            border-radius: var(--se-radius) !important;
            box-shadow: 0 2px 16px rgba(0,0,0,0.06) !important;
            background: var(--se-surface) !important;
            overflow: hidden;
        }

        /* ── Table ── */
        .table {
            font-size: 13.5px !important;
            margin-bottom: 0 !important;
        }
        .table thead.table-dark th {
            background: var(--se-dark) !important;
            color: rgba(255,255,255,0.55) !important;
            font-size: 11px !important;
            font-weight: 700 !important;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            border: none !important;
            padding: 14px 16px !important;
        }
        .table tbody td {
            padding: 13px 16px !important;
            border-color: var(--se-border) !important;
            vertical-align: middle !important;
            color: var(--se-dark);
        }
        .table-hover tbody tr:hover td {
            background-color: #f5f0e8 !important;
        }
        .table-striped tbody tr:nth-of-type(odd) td {
            background-color: rgba(0,0,0,0.012) !important;
        }

        /* Product image */
        .table td img {
            border-radius: 9px !important;
            border: 1px solid var(--se-border);
            object-fit: cover;
        }

        /* fw-bold product name */
        .table td.text-start.fw-bold {
            font-weight: 600 !important;
            color: var(--se-dark);
        }

        /* Price */
        .table td.text-danger.fw-bold {
            color: #b84a2e !important;
            font-weight: 700 !important;
        }

        /* ── Badges ── */
        .badge.bg-secondary {
            background: #ede8e0 !important;
            color: var(--se-muted) !important;
            font-weight: 600 !important;
            font-size: 11.5px !important;
            border-radius: 6px !important;
            padding: 4px 9px !important;
        }
        .badge.bg-info {
            background: #ddeeff !important;
            color: #2a5fa8 !important;
            font-weight: 600 !important;
            font-size: 11.5px !important;
            border-radius: 6px !important;
            padding: 4px 9px !important;
        }

        /* ── Action Buttons ── */
        .btn-sm.btn-warning {
            background: #fef3e2 !important;
            border-color: #fcd98a !important;
            color: #92560f !important;
            border-radius: 8px !important;
            padding: 5px 10px !important;
            font-size: 13px !important;
            transition: all 0.15s !important;
        }
        .btn-sm.btn-warning:hover {
            background: #f8ca4d !important;
            color: #5a3400 !important;
        }
        .btn-sm.btn-danger {
            background: #fde8e4 !important;
            border-color: #f5b7ae !important;
            color: #9b2a1a !important;
            border-radius: 8px !important;
            padding: 5px 10px !important;
            font-size: 13px !important;
            transition: all 0.15s !important;
        }
        .btn-sm.btn-danger:hover {
            background: #e04e38 !important;
            border-color: #e04e38 !important;
            color: #fff !important;
        }

        /* ── Pagination ── */
        .p-3.border-top { border-color: var(--se-border) !important; background: var(--se-surface); }
        .pagination { gap: 3px; }
        .page-link {
            font-family: 'DM Sans', sans-serif !important;
            font-size: 13px !important;
            font-weight: 600 !important;
            border-radius: 8px !important;
            border: 1px solid var(--se-border) !important;
            color: var(--se-muted) !important;
            padding: 6px 13px !important;
            transition: all 0.15s !important;
            background: var(--se-surface) !important;
        }
        .page-link:hover {
            background: var(--se-accent) !important;
            border-color: var(--se-accent) !important;
            color: var(--se-dark) !important;
        }
        .page-item.active .page-link {
            background: var(--se-dark) !important;
            border-color: var(--se-dark) !important;
            color: #fff !important;
        }
        .page-item.disabled .page-link {
            opacity: 0.35 !important;
        }

        /* ── Modal ── */
        .modal-content {
            border-radius: var(--se-radius) !important;
            border: 1px solid var(--se-border) !important;
            overflow: hidden;
            font-family: 'DM Sans', sans-serif;
            box-shadow: 0 24px 64px rgba(0,0,0,0.18) !important;
        }
        .modal-header.bg-dark {
            background: var(--se-dark) !important;
            border-bottom: none !important;
            padding: 20px 24px !important;
        }
        .modal-title {
            font-family: 'Be Vietnam Pro', sans-serif !important;
            font-size: 15px !important;
            font-weight: 700 !important;
            letter-spacing: 0.04em;
        }
        .modal-body {
            background: var(--se-surface) !important;
            padding: 24px !important;
        }
        .modal-footer {
            background: var(--se-surface) !important;
            border-top: 1px solid var(--se-border) !important;
            padding: 16px 24px !important;
        }

        /* Form inputs inside modal */
        .modal .form-label {
            font-size: 11.5px !important;
            font-weight: 700 !important;
            color: var(--se-muted) !important;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 6px !important;
        }
        .modal .form-control,
        .modal .form-select {
            font-family: 'DM Sans', sans-serif !important;
            font-size: 13.5px !important;
            border-radius: 9px !important;
            border: 1px solid var(--se-border) !important;
            color: var(--se-dark) !important;
            background: #fff !important;
            padding: 9px 13px !important;
            transition: border-color 0.15s, box-shadow 0.15s !important;
        }
        .modal .form-control:focus,
        .modal .form-select:focus {
            border-color: var(--se-accent) !important;
            box-shadow: 0 0 0 3px rgba(201,164,106,0.18) !important;
            outline: none !important;
        }

        /* Alert inside modal */
        .modal .alert-info {
            background: #f0ece5 !important;
            border: 1px solid var(--se-border) !important;
            color: var(--se-muted) !important;
            border-radius: 9px !important;
            font-size: 13px !important;
        }

        /* Modal buttons */
        .modal .btn-secondary {
            background: transparent !important;
            border: 1px solid var(--se-border) !important;
            color: var(--se-muted) !important;
            border-radius: 9px !important;
            font-weight: 600 !important;
            font-size: 13.5px !important;
            padding: 9px 20px !important;
            transition: all 0.15s !important;
        }
        .modal .btn-secondary:hover {
            border-color: var(--se-muted) !important;
            color: var(--se-dark) !important;
            background: transparent !important;
        }
        .modal .btn-success {
            background: var(--se-accent) !important;
            border-color: var(--se-accent) !important;
            color: var(--se-dark) !important;
            border-radius: 9px !important;
            font-weight: 700 !important;
            font-size: 13.5px !important;
            padding: 9px 22px !important;
            transition: all 0.18s !important;
        }
        .modal .btn-success:hover {
            background: #b8904f !important;
            border-color: #b8904f !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(201,164,106,0.3) !important;
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
          <a class="nav-link active" href="<?= BASE_URL ?>/admin/products">Quản lý Sản phẩm</a>
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
    <?php if (!empty($_SESSION['admin_flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['admin_flash_success']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['admin_flash_success']); endif; ?>

    <?php if (!empty($_SESSION['admin_flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['admin_flash_error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['admin_flash_error']); endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Danh Sách Sản Phẩm</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productModal" onclick="resetForm()">
            <i class="bi bi-plus-circle"></i> Thêm Mới
        </button>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 text-center align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Ảnh</th>
                            <th>Tên sản phẩm</th>
                            <th>Thương Hiệu</th>
                            <th>Danh Mục</th>
                            <th>Giá (VNĐ)</th>
                            <th>Giới Tính</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($products as $p): ?>
                        <tr>
                            <td><img src="<?= BASE_URL ?>/<?= htmlspecialchars($p['image']) ?>" alt="img" width="50" height="50" class="object-fit-cover rounded"></td>
                            <td class="text-start fw-bold"><?= htmlspecialchars($p['product_name']) ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($p['brand_name'] ?? 'Không') ?></span></td>
                            <td><span class="badge bg-info text-dark"><?= htmlspecialchars($p['category']) ?></span></td>
                            <td class="text-danger fw-bold"><?= number_format($p['base_price'], 0, ',', '.') ?> đ</td>
                            <td><?= ucfirst($p['gender_type']) ?></td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick='editProduct(<?= json_encode($p) ?>)'>
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <a href="<?= BASE_URL ?>/admin/products/delete?delete=<?= $p['product_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Xóa sản phẩm này sẽ tự động xóa luôn các biến thể Variant và Image nhờ Khóa Ngoại. Bạn có chắc chắn?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($products)): ?>
                        <tr><td colspan="7">Chưa có sản phẩm nào.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            <div class="p-3 border-top">
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
        </div>
    </div>
</div>

<!-- Modal Thêm/Sửa -->
<div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="<?= BASE_URL ?>/admin/products/save" method="POST" enctype="multipart/form-data">
          <div class="modal-header bg-dark text-white">
            <h5 class="modal-title" id="modalTitle">Thêm Sản Phẩm Mới</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              <input type="hidden" name="id" id="pid">
              <div class="row mb-3">
                  <div class="col-md-6">
                      <label class="form-label">Tên Sản Phẩm *</label>
                      <input type="text" class="form-control" name="product_name" id="pname" required>
                  </div>
                  <div class="col-md-3">
                      <label class="form-label">Thương Hiệu *</label>
                      <select class="form-select" name="brand_id" id="pbrand" required>
                          <?php foreach($brands as $b): ?>
                          <option value="<?= $b['brand_id'] ?>"><?= $b['brand_name'] ?></option>
                          <?php endforeach; ?>
                      </select>
                  </div>
                  <div class="col-md-3">
                      <label class="form-label">Danh Mục</label>
                      <select class="form-select" name="category" id="pcat">
                          <option value="hoodie">Hoodie</option>
                          <option value="tshirt">T-Shirt</option>
                          <option value="cargo">Cargo</option>
                          <option value="jacket">Jacket</option>
                          <option value="shorts">Shorts</option>
                          <option value="shoes">Shoes</option>
                          <option value="bottoms">Bottoms</option>
                      </select>
                  </div>
              </div>
              <div class="row mb-3">
                  <div class="col-md-4">
                      <label class="form-label">Giá Cơ Bản (VNĐ) *</label>
                      <input type="number" class="form-control" name="base_price" id="pprice" required>
                  </div>
                  <div class="col-md-4">
                      <label class="form-label">Giới Tính</label>
                      <select class="form-select" name="gender_type" id="pgender">
                          <option value="unisex">Unisex</option>
                          <option value="male">Nam</option>
                          <option value="female">Nữ</option>
                      </select>
                  </div>
                  <div class="col-md-4">
                      <label class="form-label">Trạng Thái</label>
                      <select class="form-select" name="status" id="pstatus">
                          <option value="active">Active</option>
                          <option value="inactive">Inactive</option>
                      </select>
                  </div>
              </div>
              <div class="row mb-3">
                  <div class="col-md-6">
                      <label class="form-label">Link Hình Ảnh Chính</label>
                      <input type="text" class="form-control" name="image" id="pimage">
                  </div>
                  <div class="col-md-6">
                      <label class="form-label">Hoặc Tải Lên File Ảnh</label>
                      <input type="file" class="form-control" name="image_file" id="pimage_file">
                  </div>
              </div>
              <div class="mb-3">
                  <label class="form-label">Mô tả Sản Phẩm</label>
                  <textarea class="form-control" name="description" id="pdesc" rows="3"></textarea>
              </div>
              <div class="alert alert-info">
                  <i class="bi bi-info-circle"></i> Khi Thêm Mới, hệ thống CSDL Sẽ tự động khởi tạo mặc định các Variant (S,M,L,XL - màu trắng) để đẩy lên trang SPA. 
              </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Chấp Nhận Lưu</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function resetForm() {
        document.getElementById('modalTitle').innerText = 'Thêm Sản Phẩm Mới (16 Tables)';
        document.getElementById('pid').value = '';
        document.getElementById('pname').value = '';
        document.getElementById('pprice').value = '';
        document.getElementById('pimage').value = '';
        document.getElementById('pimage_file').value = '';
        document.getElementById('pdesc').value = '';
        document.getElementById('pstatus').value = 'active';
    }

    function editProduct(p) {
        document.getElementById('modalTitle').innerText = 'Sửa Sản Phẩm (Map CSDL chuẩn)';
        document.getElementById('pid').value = p.product_id;
        document.getElementById('pname').value = p.product_name;
        document.getElementById('pbrand').value = p.brand_id;
        document.getElementById('pcat').value = p.category;
        document.getElementById('pprice').value = p.base_price;
        document.getElementById('pgender').value = p.gender_type;
        document.getElementById('pstatus').value = p.status;
        document.getElementById('pdesc').value = p.description;
        document.getElementById('pimage').value = p.image || '';
        
        var myModal = new bootstrap.Modal(document.getElementById('productModal'));
        myModal.show();
    }
</script>
</body>
</html>