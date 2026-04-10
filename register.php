<?php
session_start();
require_once 'config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim(mysqli_real_escape_string($conn, $_POST['name']));
    $email = trim(mysqli_real_escape_string($conn, $_POST['email']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validasi
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Semua field harus diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif ($password !== $confirm_password) {
        $error = 'Konfirmasi password tidak cocok.';
    } else {
        // Cek apakah email sudah terdaftar
        $check = $conn->query("SELECT id FROM users WHERE email = '$email'");
        if ($check->num_rows > 0) {
            $error = 'Email sudah terdaftar. Gunakan email lain.';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'user';
            $conn->query("INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$hashed_password', '$role')");
            if ($conn->affected_rows > 0) {
                $success = 'Pendaftaran berhasil! Silakan login.';
                // Kosongkan form
                $name = $email = '';
            } else {
                $error = 'Terjadi kesalahan. Silakan coba lagi.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - CariBuku</title>
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
        .register-card {
            border: none;
            border-radius: 32px;
            background: white;
            box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.2s;
            max-width: 500px;
            width: 100%;
        }
        .register-card:hover {
            transform: translateY(-5px);
        }
        .register-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 2rem;
            text-align: center;
            color: white;
        }
        .register-header i {
            font-size: 3rem;
            margin-bottom: 0.5rem;
        }
        .register-header h3 {
            font-weight: 700;
            margin-bottom: 0.25rem;
        }
        .register-header p {
            opacity: 0.9;
            font-size: 0.9rem;
        }
        .register-body {
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
        .input-group-text {
            border-radius: 60px 0 0 60px;
            background: transparent;
            border-right: none;
        }
        .form-control.rounded-end-pill {
            border-radius: 0 60px 60px 0;
            border-left: none;
        }
        .btn-register {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 60px;
            padding: 0.75rem;
            font-weight: 600;
            font-size: 1rem;
            transition: 0.2s;
        }
        .btn-register:hover {
            transform: scale(1.02);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.9rem;
        }
        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        .login-link a:hover {
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
            z-index: 10;
        }
        .back-home:hover {
            background: #667eea;
            color: white;
        }
        @media (max-width: 480px) {
            .register-body { padding: 1.5rem; }
            .register-header { padding: 1.5rem; }
        }
    </style>
</head>
<body>
    <a href="index.php" class="back-home">
        <i class="fas fa-arrow-left me-2"></i> Kembali ke Beranda
    </a>
    <div class="register-card">
        <div class="register-header">
            <i class="fas fa-user-plus"></i>
            <h3>Daftar Akun Baru</h3>
            <p>Bergabunglah dengan ribuan pembaca lainnya</p>
        </div>
        <div class="register-body">
            <?php if ($error): ?>
                <div class="alert alert-danger alert-custom mb-4">
                    <i class="fas fa-exclamation-circle me-2"></i> <?= $error ?>
                </div>
            <?php elseif ($success): ?>
                <div class="alert alert-success alert-custom mb-4">
                    <i class="fas fa-check-circle me-2"></i> <?= $success ?>
                    <a href="login.php" class="alert-link">Login sekarang</a>
                </div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama Lengkap</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0">
                            <i class="fas fa-user text-muted"></i>
                        </span>
                        <input type="text" name="name" class="form-control rounded-end-pill" placeholder="Nama Anda" value="<?= htmlspecialchars($name ?? '') ?>" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Alamat Email</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0">
                            <i class="fas fa-envelope text-muted"></i>
                        </span>
                        <input type="email" name="email" class="form-control rounded-end-pill" placeholder="contoh@email.com" value="<?= htmlspecialchars($email ?? '') ?>" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0">
                            <i class="fas fa-lock text-muted"></i>
                        </span>
                        <input type="password" name="password" class="form-control rounded-end-pill" placeholder="Minimal 6 karakter" required>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Konfirmasi Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0">
                            <i class="fas fa-lock text-muted"></i>
                        </span>
                        <input type="password" name="confirm_password" class="form-control rounded-end-pill" placeholder="Ketik ulang password" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-register w-100 text-white">
                    <i class="fas fa-user-plus me-2"></i> Daftar Sekarang
                </button>
            </form>
            <div class="login-link">
                Sudah punya akun? <a href="login.php">Masuk di sini</a>
            </div>
            <hr class="my-4">
            <div class="text-center small text-muted">
                <i class="fas fa-shield-alt me-1"></i> Data Anda aman dan terenkripsi
            </div>
        </div>
    </div>
</body>
</html>