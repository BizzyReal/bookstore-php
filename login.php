<?php
session_start();
require_once 'config/database.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    
    $result = $conn->query("SELECT * FROM users WHERE email = '$email'");
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password']) || md5($password) === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            if ($user['role'] === 'admin') {
                header('Location: admin/index.php');
            } else {
                header('Location: index.php');
            }
            exit;
        } else {
            $error = 'Password salah';
        }
    } else {
        $error = 'Email tidak ditemukan';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CariBuku</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e9edf2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .login-card {
            border: none;
            border-radius: 32px;
            background: white;
            box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.2s;
            max-width: 460px;
            width: 100%;
        }
        .login-card:hover {
            transform: translateY(-5px);
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 2rem;
            text-align: center;
            color: white;
        }
        .login-header i {
            font-size: 3rem;
            margin-bottom: 0.5rem;
        }
        .login-header h3 {
            font-weight: 700;
            margin-bottom: 0.25rem;
        }
        .login-header p {
            opacity: 0.9;
            font-size: 0.9rem;
        }
        .login-body {
            padding: 2rem;
        }
        .form-control {
            border-radius: 60px;
            padding: 0.75rem 1.25rem;
            border: 1px solid #e2e8f0;
            font-size: 0.95rem;
            transition: all 0.2s;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 60px;
            padding: 0.75rem;
            font-weight: 600;
            font-size: 1rem;
            transition: 0.2s;
        }
        .btn-login:hover {
            transform: scale(1.02);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .register-link {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.9rem;
        }
        .register-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        .register-link a:hover {
            text-decoration: underline;
        }
        .alert-custom {
            border-radius: 60px;
            font-size: 0.85rem;
            padding: 0.6rem 1.2rem;
        }
        .back-home {
            position: absolute;
            top: 20px;
            left: 20px;
            background: white;
            border-radius: 60px;
            padding: 0.5rem 1.2rem;
            text-decoration: none;
            color: #4a5568;
            font-weight: 500;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: 0.2s;
        }
        .back-home:hover {
            background: #667eea;
            color: white;
        }
        @media (max-width: 480px) {
            .login-body { padding: 1.5rem; }
            .login-header { padding: 1.5rem; }
        }
    </style>
</head>
<body>
    <a href="index.php" class="back-home">
        <i class="fas fa-arrow-left me-2"></i> Kembali ke Beranda
    </a>
    <div class="login-card">
        <div class="login-header">
            <i class="fas fa-book-open"></i>
            <h3>Selamat Datang Kembali</h3>
            <p>Masuk ke akun CariBuku Anda</p>
        </div>
        <div class="login-body">
            <?php if ($error): ?>
                <div class="alert alert-danger alert-custom mb-4">
                    <i class="fas fa-exclamation-circle me-2"></i> <?= $error ?>
                </div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Alamat Email</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0 rounded-start-pill">
                            <i class="fas fa-envelope text-muted"></i>
                        </span>
                        <input type="email" name="email" class="form-control border-start-0 rounded-end-pill" placeholder="contoh@email.com" required>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0 rounded-start-pill">
                            <i class="fas fa-lock text-muted"></i>
                        </span>
                        <input type="password" name="password" class="form-control border-start-0 rounded-end-pill" placeholder="******" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-login w-100 text-white">
                    <i class="fas fa-sign-in-alt me-2"></i> Masuk
                </button>
            </form>
            <div class="register-link">
                Belum punya akun? <a href="register.php">Daftar Sekarang</a>
            </div>
            <hr class="my-4">
            <div class="text-center small text-muted">
                <i class="fas fa-shield-alt me-1"></i> Data Anda aman dan terenkripsi
            </div>
        </div>
    </div>
</body>
</html>