<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký tài khoản - SOFT EDGE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #1a1a1a 0%, #3d3d3d 100%);
            --accent-color: #e6b800;
        }
        body {
            font-family: 'Outfit', sans-serif;
            background: #f4f7f6;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }
        .register-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
            width: 100%;
            max-width: 900px;
            display: flex;
        }
        .register-image {
            background: var(--primary-gradient);
            color: white;
            padding: 40px;
            width: 40%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            text-align: center;
        }
        .register-form {
            padding: 50px;
            width: 60%;
        }
        .register-image h2 {
            font-weight: 700;
            margin-bottom: 20px;
        }
        .register-image p {
            font-weight: 300;
            opacity: 0.8;
        }
        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 1px solid #e1e1e1;
            margin-bottom: 20px;
        }
        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(230, 184, 0, 0.2);
            border-color: var(--accent-color);
        }
        .btn-register {
            background: var(--primary-gradient);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            width: 100%;
            transition: transform 0.3s ease;
        }
        .btn-register:hover {
            transform: translateY(-3px);
            color: var(--accent-color);
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9rem;
        }
        .login-link a {
            color: #333;
            font-weight: 600;
            text-decoration: none;
        }
        .form-container {
            display: none;
        }
        .form-container.active {
            display: block;
        }
    </style>
</head>
<body>

<div class="register-card">
    <div class="register-image">
        <h2>SOFT EDGE</h2>
        <p>Gia nhập cộng đồng thời trang cao cấp của chúng tôi ngay hôm nay.</p>
        <div class="mt-4">
            <small>© 2026 SOFT EDGE. All rights reserved.</small>
        </div>
    </div>
    <div class="register-form">
        <div class="text-center mb-4">
            <button class="btn btn-outline-primary me-2" id="showRegister">Đăng ký</button>
            <button class="btn btn-outline-secondary" id="showLogin">Đăng nhập</button>
        </div>
        
        <?php if($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <!-- Register Form -->
        <div id="registerForm" class="form-container">
            <h3 class="mb-4 fw-bold">Đăng ký tài khoản</h3>
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
                <div class="mb-4">
                    <label class="form-label">Giới tính</label>
                    <div class="d-flex gap-4">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="gender" value="male" id="male" checked>
                            <label class="form-check-label" for="male">Nam</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="gender" value="female" id="female">
                            <label class="form-check-label" for="female">Nữ</label>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-register">ĐĂNG KÝ NGAY</button>
            </form>
        </div>

        <!-- Login Form -->
        <div id="loginForm" class="form-container" style="display: none;">
            <h3 class="mb-4 fw-bold">Đăng nhập</h3>
            <form action="<?= BASE_URL ?>/register" method="POST">
                <input type="hidden" name="form_action" value="login">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" placeholder="email@example.com" required>
                </div>
                <div class="mb-4">
                    <label class="form-label">Mật khẩu</label>
                    <input type="password" class="form-control" name="password" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn btn-register">ĐĂNG NHẬP</button>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('showRegister').addEventListener('click', function() {
        document.getElementById('registerForm').style.display = 'block';
        document.getElementById('loginForm').style.display = 'none';
        this.classList.remove('btn-outline-primary');
        this.classList.add('btn-primary');
        document.getElementById('showLogin').classList.remove('btn-primary');
        document.getElementById('showLogin').classList.add('btn-outline-secondary');
    });

    document.getElementById('showLogin').addEventListener('click', function() {
        document.getElementById('registerForm').style.display = 'none';
        document.getElementById('loginForm').style.display = 'block';
        this.classList.remove('btn-outline-secondary');
        this.classList.add('btn-primary');
        document.getElementById('showRegister').classList.remove('btn-primary');
        document.getElementById('showRegister').classList.add('btn-outline-primary');
    });

    // Default to register form
    document.getElementById('registerForm').style.display = 'block';
    document.getElementById('showRegister').classList.add('btn-primary');
    document.getElementById('showRegister').classList.remove('btn-outline-primary');
</script>

</body>
</html>
