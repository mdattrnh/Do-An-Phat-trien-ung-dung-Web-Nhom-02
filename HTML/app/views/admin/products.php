<?php
$adminPageTitle = 'Quản lý Sản phẩm - Admin';
$adminActivePage = 'products';
require __DIR__ . '/partials/head.php';
?>
<body class="admin-body">
<?php require __DIR__ . '/partials/navbar.php'; ?>

<main class="admin-shell">
    <div class="container-fluid px-3 px-lg-4">
        <section class="admin-hero">
            <div class="row align-items-center g-3">
                <div class="col-lg-8">
                    <div class="admin-kicker">Products</div>
                    <h1 class="admin-title">Quản lý sản phẩm</h1>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productModal" onclick="resetForm()">
                        <i class="bi bi-plus-circle me-2"></i>Thêm mới
                    </button>
                </div>
            </div>
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
                    <h2 class="panel-title">Danh sách sản phẩm</h2>
                </div>
                <span class="soft-badge soft-badge--neutral">
                    <i class="bi bi-box-seam"></i><?= count($products) ?> sản phẩm trong trang này
                </span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle text-center table-admin">
                    <thead>
                        <tr>
                            <th>Ảnh</th>
                            <th class="text-start">Tên sản phẩm</th>
                            <th>Danh mục</th>
                            <th>Size</th>
                            <th>Giá (VNĐ)</th>
                            <th>Giới tính</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $p): ?>
                            <tr>
                                <td><img src="<?= BASE_URL ?>/<?= htmlspecialchars($p['image']) ?>" alt="img" class="thumb-image"></td>
                                <td class="text-start fw-semibold"><?= htmlspecialchars($p['product_name']) ?></td>
                                <td><span class="soft-badge soft-badge--info"><?= htmlspecialchars($p['category']) ?></span></td>
                                <td><span class="soft-badge soft-badge--neutral"><?= htmlspecialchars($p['sizes'] ?? 'S, M, L, XL') ?></span></td>
                                <td class="fw-bold text-danger"><?= number_format($p['base_price'], 0, ',', '.') ?> đ</td>
                                <td><span class="soft-badge soft-badge--amber"><?= ucfirst($p['gender_type']) ?></span></td>
                                <td>
                                    <div class="d-inline-flex gap-2">
                                        <button class="btn btn-warning btn-icon" onclick='editProduct(<?= json_encode($p) ?>)'>
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <a href="<?= BASE_URL ?>/admin/products/delete?delete=<?= $p['product_id'] ?>" class="btn btn-danger btn-icon" onclick="return confirm('Xóa sản phẩm này sẽ tự động xóa luôn các biến thể Variant và Image nhờ Khóa Ngoại. Bạn có chắc chắn?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="7" class="py-5 text-muted">Chưa có sản phẩm nào.</td>
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

<div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form action="<?= BASE_URL ?>/admin/products/save" method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Thêm sản phẩm mới</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="pid">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Tên sản phẩm *</label>
                            <input type="text" class="form-control" name="product_name" id="pname" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Danh mục</label>
                            <select class="form-select" name="category" id="pcat" onchange="updateSizeOptions()">
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= htmlspecialchars($cat['slug']) ?>" data-size-mode="<?= htmlspecialchars($cat['size_mode']) ?>">
                                        <?= htmlspecialchars($cat['category_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Size khả dụng</label>
                            <div class="pt-2">
                                <div class="d-flex flex-wrap gap-2" id="standardSizeGroup">
                                    <?php foreach (['XS', 'S', 'M', 'L', 'XL', 'XXL'] as $sizeOption): ?>
                                        <div class="form-check form-check-inline me-0">
                                            <input class="form-check-input" type="checkbox" name="sizes[]" id="size<?= $sizeOption ?>" value="<?= $sizeOption ?>">
                                            <label class="form-check-label" for="size<?= $sizeOption ?>"><?= $sizeOption ?></label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="d-flex flex-wrap gap-2" id="numericSizeGroup" style="display:none;">
                                    <?php foreach (['38', '39', '40', '41', '42'] as $sizeOption): ?>
                                        <div class="form-check form-check-inline me-0">
                                            <input class="form-check-input" type="checkbox" name="sizes[]" id="size<?= $sizeOption ?>" value="<?= $sizeOption ?>">
                                            <label class="form-check-label" for="size<?= $sizeOption ?>"><?= $sizeOption ?></label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Giá cơ bản (VNĐ) *</label>
                            <input type="number" class="form-control" name="base_price" id="pprice" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Giới tính</label>
                            <select class="form-select" name="gender_type" id="pgender">
                                <option value="unisex">Unisex</option>
                                <option value="male">Nam</option>
                                <option value="female">Nữ</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Trạng thái</label>
                            <select class="form-select" name="status" id="pstatus">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Link hình ảnh chính</label>
                            <input type="text" class="form-control" name="image" id="pimage">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Hoặc tải lên file ảnh</label>
                            <input type="file" class="form-control" name="image_file" id="pimage_file">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Mô tả sản phẩm</label>
                        <textarea class="form-control" name="description" id="pdesc" rows="3"></textarea>
                    </div>

                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle me-2"></i>Chọn size khả dụng trước khi lưu. Nếu không chọn, hệ thống sẽ khởi tạo mặc định S, M, L, XL.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-save me-2"></i>Chấp nhận lưu</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function getSelectedCategorySizeMode() {
    var select = document.getElementById('pcat');
    return select.options[select.selectedIndex].getAttribute('data-size-mode');
}

function updateSizeOptions() {
    var mode = getSelectedCategorySizeMode();
    var standardGroup = document.getElementById('standardSizeGroup');
    var numericGroup = document.getElementById('numericSizeGroup');

    if (mode === 'numeric_38_42') {
        standardGroup.style.display = 'none';
        numericGroup.style.display = 'flex';
        document.querySelectorAll('#standardSizeGroup input[name="sizes[]"]').forEach(function(input) {
            input.checked = false;
        });
        document.querySelectorAll('#numericSizeGroup input[name="sizes[]"]').forEach(function(input) {
            if (['38', '39', '40', '41', '42'].includes(input.value)) {
                input.checked = true;
            }
        });
    } else {
        numericGroup.style.display = 'none';
        standardGroup.style.display = 'flex';
        document.querySelectorAll('#numericSizeGroup input[name="sizes[]"]').forEach(function(input) {
            input.checked = false;
        });
        document.querySelectorAll('#standardSizeGroup input[name="sizes[]"]').forEach(function(input) {
            input.checked = ['S', 'M', 'L', 'XL'].includes(input.value);
        });
    }
}

function resetForm() {
    document.getElementById('modalTitle').innerText = 'Thêm sản phẩm mới';
    document.getElementById('pid').value = '';
    document.getElementById('pname').value = '';
    document.getElementById('pcat').selectedIndex = 0;
    document.getElementById('pprice').value = '';
    document.getElementById('pimage').value = '';
    document.getElementById('pimage_file').value = '';
    document.getElementById('pdesc').value = '';
    document.getElementById('pstatus').value = 'active';
    document.getElementById('pgender').value = 'unisex';
    updateSizeOptions();
}

function editProduct(p) {
    document.getElementById('modalTitle').innerText = 'Sửa sản phẩm';
    document.getElementById('pid').value = p.product_id;
    document.getElementById('pname').value = p.product_name;
    document.getElementById('pcat').value = p.category;
    document.getElementById('pprice').value = p.base_price;
    document.getElementById('pgender').value = p.gender_type;
    document.getElementById('pstatus').value = p.status;
    document.getElementById('pdesc').value = p.description;
    document.getElementById('pimage').value = p.image || '';
    updateSizeOptions();

    var selectedSizes = (p.sizes || '').split(',').map(function(size) {
        return size.trim();
    });

    document.querySelectorAll('input[name="sizes[]"]').forEach(function(input) {
        input.checked = selectedSizes.includes(input.value);
    });

    new bootstrap.Modal(document.getElementById('productModal')).show();
}
</script>
</body>
</html>