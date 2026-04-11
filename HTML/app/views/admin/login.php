<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Admin Login — SOFT EDGE</title>
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
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            background: #fdfbf7;
            border-radius: 20px;
            box-shadow: 0 8px 40px rgba(0,0,0,0.10);
            padding: 2.5rem 2rem;
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
            font-family: 'DM Sans', sans-serif;
            font-size: 0.9rem;
        }
        .form-control:focus {
            border-color: #1a1a1a;
            box-shadow: 0 0 0 3px rgba(26,26,26,0.08);
            background: #fff;
        }
        .btn-dark {
            background: #1a1a1a;
            border-radius: 100px;
            padding: 0.8rem;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.82rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            transition: all 0.2s;
        }
        .btn-dark:hover { background: #333; transform: translateY(-1px); }
        .hint-box {
            background: rgba(200,216,192,0.3);
            border-radius: 10px;
            padding: 0.75rem 1rem;
            font-size: 0.78rem;
            color: #555;
            margin-top: 1.2rem;
        }
        .hint-box strong { color: #1a1a1a; }
    </style>
</head>
<body>

<div class="login-card">
    <div class="text-center mb-4">
        <div class="brand-title">SOFT<span class="edge">EDGE</span></div>
        <span class="admin-label">Admin Dashboard</span>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger rounded-3 py-2 px-3 mb-3" style="font-size:0.85rem;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>/admin/login">
        <div class="mb-3">
            <label class="form-label">Tài khoản</label>
            <input type="text" name="username" class="form-control" required placeholder="admin">
        </div>
        <div class="mb-3">
            <label class="form-label">Mật khẩu</label>
            <input type="password" name="password" class="form-control" required placeholder="••••••">
        </div>
        <button type="submit" class="btn btn-dark w-100 mt-2">Đăng Nhập</button>
    </form>

    <div class="hint-box text-center">
        <strong>Tài khoản mặc định:</strong><br>
        Username: <strong>adminsoftedge@gmail.com</strong> &nbsp;|&nbsp; Password: <strong>admin123</strong>
    </div>
</div>

</body>
</html>
