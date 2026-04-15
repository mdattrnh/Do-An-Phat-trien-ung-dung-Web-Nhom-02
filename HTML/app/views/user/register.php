<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký tài khoản - SOFT EDGE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        body {
            background: #f5f0e8;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            font-family: 'DM Sans', sans-serif;
            margin: 0;
        }
        .register-card {
            width: 100%;
            max-width: 520px;
            background: #fdfbf7;
            border-radius: 20px;
            box-shadow: 0 8px 40px rgba(0,0,0,0.10);
            padding: 2.4rem 2rem;
        }
        .brand-title {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 2.2rem;
            letter-spacing: 0.1em;
            color: #1a1a1a;
        }
        .brand-title .edge {
            -webkit-text-stroke: 1.5px #1a1a1a;
            color: transparent;
            font-style: italic;
        }
        .admin-label {
            font-size: 0.72rem;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: #888;
            margin-bottom: 1.5rem;
            display: block;
        }
        .form-label {
            font-size: 0.72rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #888;
            font-weight: 500;
        }
        .form-control {
            background: #f5f0e8;
            border: 1px solid rgba(0,0,0,0.10);
            border-radius: 10px;
            padding: 0.75rem 1rem;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }
        .form-control:focus {
            border-color: #1a1a1a;
            box-shadow: 0 0 0 3px rgba(26,26,26,0.08);
            background: #fff;
        }
        .btn-dark {
            background: #1a1a1a;
            border-radius: 100px;
            padding: 0.85rem;
            font-size: 0.82rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            transition: all 0.2s;
            width: 100%;
            color: #fff;
        }
        .btn-dark:hover { background: #333; transform: translateY(-1px); }
        .tab-buttons {
            display: flex;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }
        .tab-buttons .btn {
            flex: 1;
            border-radius: 100px;
            text-transform: uppercase;
            font-size: 0.78rem;
            font-weight: 600;
        }
        .tab-buttons .btn.active {
            background: #1a1a1a;
            color: #fff;
            border-color: #1a1a1a;
        }
        .tab-buttons .btn:not(.active) {
            background: #f5f0e8;
            color: #1a1a1a;
            border: 1px solid rgba(0,0,0,0.10);
        }
        .hint-box {
            background: rgba(200,216,192,0.3);
            border-radius: 10px;
            padding: 0.75rem 1rem;
            font-size: 0.78rem;
            color: #555;
            margin-top: 1.5rem;
        }
        .hint-box strong { color: #1a1a1a; }
        .admin-login-link {
            display: block;
            text-align: center;
            margin-top: 1rem;
            font-size: 0.85rem;
        }
        .admin-login-link a {
            color: #1a1a1a;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>

<div style="position: fixed; top: 20px; left: 20px; z-index: 1000;">
    <a href="/HTML/" class="btn btn-sm btn-outline-dark" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">Trang chủ</a>
</div>

<div class="register-card">
    <div class="text-center mb-4">
        <div class="brand-title">SOFT<span class="edge">EDGE</span></div>
        <span class="admin-label">Customer Registration</span>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger rounded-3 py-2 px-3 mb-3" style="font-size:0.85rem;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success rounded-3 py-2 px-3 mb-3" style="font-size:0.85rem;">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <div class="tab-buttons">
        <button class="btn active" id="showRegister">Đăng ký</button>
        <button class="btn" id="showLogin">Đăng nhập</button>
    </div>

    <div id="registerForm">
        <form action="<?= BASE_URL ?>/register" method="POST">
            <input type="hidden" name="form_action" value="register">
            <div class="mb-3">
                <label class="form-label">Họ và Tên</label>
                <input type="text" class="form-control" name="full_name" placeholder="Nguyễn Văn A" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="email" placeholder="email@example.com" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Mật khẩu</label>
                <input type="password" class="form-control" name="password" placeholder="••••••••" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Giới tính</label>
                <select class="form-control" name="gender" required>
                    <option value="male">Nam</option>
                    <option value="female">Nữ</option>
                    <option value="other">Khác</option>
                </select>
            </div>
            <button type="submit" class="btn btn-dark mt-2">ĐĂNG KÝ NGAY</button>
        </form>
    </div>

    <div id="loginForm" style="display:none;">
        <form action="<?= BASE_URL ?>/register" method="POST">
            <input type="hidden" name="form_action" value="login">
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="email" placeholder="email@example.com" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Mật khẩu</label>
                <input type="password" class="form-control" name="password" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-dark mt-2">ĐĂNG NHẬP</button>
        </form>
    </div>

    <div class="admin-login-link">
        <a href="<?= BASE_URL ?>/admin/login">Chuyển sang Admin đăng nhập</a>
    </div>

    <div class="hint-box text-center">
        <strong>Ghi chú:</strong> Bạn có thể dùng tài khoản khách để đăng ký hoặc chuyển sang admin nếu cần quản lý.
    </div>
</div>

<script>
    const showRegister = document.getElementById('showRegister');
    const showLogin = document.getElementById('showLogin');
    const registerForm = document.getElementById('registerForm');
    const loginForm = document.getElementById('loginForm');

    showRegister.addEventListener('click', function() {
        registerForm.style.display = 'block';
        loginForm.style.display = 'none';
        showRegister.classList.add('active');
        showLogin.classList.remove('active');
    });

    showLogin.addEventListener('click', function() {
        registerForm.style.display = 'none';
        loginForm.style.display = 'block';
        showLogin.classList.add('active');
        showRegister.classList.remove('active');
    });
</script>

</body>
</html>
