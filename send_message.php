<?php
require_once 'includes/auth.php';
require_once 'models/Message.php';
redirectIfNotLoggedIn();

$messageModel = new Message();
$error = '';
$success = '';

// Ambil daftar user (untuk admin)
$users = [];
if ($_SESSION['role'] === 'admin') {
    global $conn;
    $result = $conn->query("SELECT id, name, email FROM users WHERE role = 'user' ORDER BY name");
    $users = $result->fetch_all(MYSQLI_ASSOC);
}

// Proses kirim pesan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receiver_id = intval($_POST['receiver_id']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    
    if (empty($subject) || empty($message)) {
        $error = "Subjek dan pesan harus diisi.";
    } elseif ($receiver_id <= 0) {
        $error = "Pilih penerima pesan.";
    } else {
        if ($messageModel->send($_SESSION['user_id'], $receiver_id, $subject, $message)) {
            $success = "Pesan berhasil dikirim!";
            // Reset form
            $subject = $message = '';
        } else {
            $error = "Gagal mengirim pesan. Silakan coba lagi.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kirim Pesan - CariBuku</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: #f8f9fc; display: flex; flex-direction: column; min-height: 100vh; }
        .navbar { background: white; box-shadow: 0 2px 12px rgba(0,0,0,0.05); padding: 1rem 0; }
        .navbar-brand { font-weight: 700; font-size: 1.8rem; color: #2c3e66 !important; }
        .navbar-brand i { color: #667eea; }
        .nav-link { color: #4a5568 !important; font-weight: 500; }
        .nav-link:hover { color: #667eea !important; }
        .main-content { flex: 1; }
        .footer { background: #1e2a3e; color: #cbd5e1; padding: 2.5rem 0; margin-top: 2rem; }
        .footer a { color: #cbd5e1; text-decoration: none; }
        .footer a:hover { color: #667eea; }
        .form-card { border: none; border-radius: 24px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); overflow: hidden; }
        .form-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 1.5rem; color: white; }
        .btn-primary-custom { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; border-radius: 60px; padding: 0.75rem 1.5rem; font-weight: 600; transition: 0.2s; }
        .btn-primary-custom:hover { transform: scale(1.02); box-shadow: 0 5px 15px rgba(102,126,234,0.4); }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand" href="index.php"><i class="fas fa-book-open me-2"></i> CariBuku</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav ms-auto">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> <?= htmlspecialchars($_SESSION['name']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="cart.php"><i class="fas fa-shopping-cart"></i> Keranjang</a></li>
                            <li><a class="dropdown-item" href="my_orders.php"><i class="fas fa-list"></i> Pesanan Saya</a></li>
                            <li><a class="dropdown-item" href="inbox.php"><i class="fas fa-envelope"></i> Pesan</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="register.php"><i class="fas fa-user-plus"></i> Daftar</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="main-content">
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="form-card">
                    <div class="form-header">
                        <h2><i class="fas fa-paper-plane me-2"></i> Kirim Pesan</h2>
                        <p>Kirim pesan ke admin atau customer service kami</p>
                    </div>
                    <div class="p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger rounded-4"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success rounded-4"><?= htmlspecialchars($success) ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Penerima</label>
                                <?php if ($_SESSION['role'] === 'admin'): ?>
                                    <select name="receiver_id" class="form-select rounded-3" required>
                                        <option value="">-- Pilih Penerima --</option>
                                        <?php foreach ($users as $user): ?>
                                            <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['name']) ?> (<?= htmlspecialchars($user['email']) ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text">Admin dapat mengirim pesan ke user tertentu.</div>
                                <?php else: ?>
                                    <input type="hidden" name="receiver_id" value="1">
                                    <input type="text" class="form-control rounded-3" value="Admin" disabled>
                                    <div class="form-text">Pesan akan dikirim ke administrator.</div>
                                <?php endif; ?>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Subjek</label>
                                <input type="text" name="subject" class="form-control rounded-3" placeholder="Judul pesan..." required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Pesan</label>
                                <textarea name="message" class="form-control rounded-3" rows="5" placeholder="Tulis pesan Anda di sini..." required></textarea>
                            </div>
                            <div class="d-flex gap-3">
                                <button type="submit" class="btn btn-primary-custom text-white px-4">
                                    <i class="fas fa-paper-plane me-2"></i> Kirim Pesan
                                </button>
                                <a href="inbox.php" class="btn btn-secondary rounded-pill px-4">
                                    <i class="fas fa-arrow-left me-2"></i> Kembali ke Inbox
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <div class="row">
            <div class="col-md-4"><h5 class="text-white">CariBuku</h5><p>Toko buku online terlengkap dan terpercaya.</p></div>
            <div class="col-md-4"><h5 class="text-white">Tautan</h5><ul class="list-unstyled"><li><a href="index.php">Beranda</a></li><li><a href="contact.php">Kontak Kami</a></li></ul></div>
            <div class="col-md-4"><h5 class="text-white">Kontak</h5><p><i class="fas fa-envelope me-2"></i> support@caribuku.com</p><p><i class="fas fa-phone me-2"></i> +62 812 3456 7890</p></div>
        </div>
        <hr><div class="text-center">&copy; <?= date('Y') ?> CariBuku. All rights reserved.</div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>