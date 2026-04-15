<?php
$adminPageTitle = 'Quản lý Danh mục - Admin';
$adminActivePage = 'categories';
require __DIR__ . '/partials/head.php';
?>
<body class="admin-body">
<?php require __DIR__ . '/partials/navbar.php'; ?>

<main class="admin-shell">
    <div class="container-fluid px-3 px-lg-4">
        <section class="admin-hero">
            <div class="row align-items-center g-3">
                <div class="col-lg-8">
                    <div class="admin-kicker">Categories</div>
                    <h1 class="admin-title">Quản lý danh mục sản phẩm</h1>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal" onclick="resetCategoryForm()">
                        <i class="bi bi-plus-circle me-2"></i>Thêm danh mục
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
                    <h2 class="panel-title">Danh sách danh mục</h2>
                </div>
                <span class="soft-badge soft-badge--neutral">
                    <i class="bi bi-grid"></i><?= count($categories) ?> danh mục
                </span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle text-center table-admin">
                    <thead>
                        <tr>
                            <th class="text-start">Tên danh mục</th>
                            <th>Slug</th>
                            <th>Kiểu size</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td class="text-start fw-semibold"><?= htmlspecialchars($category['category_name']) ?></td>
                                <td><code><?= htmlspecialchars($category['slug']) ?></code></td>
                                <td>
                                    <span class="soft-badge <?= $category['size_mode'] === 'numeric_38_42' ? 'soft-badge--info' : 'soft-badge--amber' ?>">
                                        <?= $category['size_mode'] === 'numeric_38_42' ? '38-42' : 'S, M, L, XL, XXL' ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-inline-flex gap-2">
                                        <button class="btn btn-warning btn-icon" onclick='editCategory(<?= json_encode($category) ?>)'>
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <a href="<?= BASE_URL ?>/admin/categories/delete?delete=<?= $category['category_id'] ?>" class="btn btn-danger btn-icon" onclick="return confirm('Bạn có chắc chắn muốn xóa danh mục này?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($categories)): ?>
                            <tr>
                                <td colspan="4" class="py-5 text-muted">Chưa có danh mục nào.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</main>

<div class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <form action="<?= BASE_URL ?>/admin/categories/save" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="categoryModalTitle">Thêm danh mục mới</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="category_id" id="category_id">
                    <div class="mb-3">
                        <label class="form-label">Tên danh mục *</label>
                        <input type="text" class="form-control" name="category_name" id="category_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Slug</label>
                        <select class="form-select" name="slug" id="slug">
                            <option value="">Tự tạo từ tên danh mục</option>
                            <?php
                            $seenSlugs = [];
                            foreach ($categories as $cat):
                                if (!in_array($cat['slug'], $seenSlugs, true)):
                                    $seenSlugs[] = $cat['slug'];
                                    ?>
                                    <option value="<?= htmlspecialchars($cat['slug']) ?>"><?= htmlspecialchars($cat['slug']) ?></option>
                                <?php
                                endif;
                            endforeach;
                            ?>
                        </select>
                        <div class="form-text">Chọn slug có sẵn hoặc để trống để hệ thống tự tạo.</div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Kiểu size</label>
                        <select class="form-select" name="size_mode" id="size_mode">
                            <option value="default">Chuẩn S, M, L, XL, XXL</option>
                            <option value="numeric_38_42">Số 38-42 (Shoes / Bottoms)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-save me-2"></i>Lưu</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function resetCategoryForm() {
    document.getElementById('categoryModalTitle').innerText = 'Thêm danh mục mới';
    document.getElementById('category_id').value = '';
    document.getElementById('category_name').value = '';
    document.getElementById('slug').value = '';
    document.getElementById('size_mode').value = 'default';
}

function editCategory(category) {
    document.getElementById('categoryModalTitle').innerText = 'Sửa danh mục';
    document.getElementById('category_id').value = category.category_id;
    document.getElementById('category_name').value = category.category_name;
    document.getElementById('slug').value = category.slug;
    document.getElementById('size_mode').value = category.size_mode;
    new bootstrap.Modal(document.getElementById('categoryModal')).show();
}
</script>
</body>
</html>
