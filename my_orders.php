<?php
require_once 'includes/auth.php';
require_once 'models/Order.php';

redirectIfNotLoggedIn();

$orderModel = new OrderModel();
$orders = $orderModel->getByUser($_SESSION['user_id']);

// Notifikasi pesan (opsional)
$unreadMessages = 0;
if (file_exists('models/Message.php')) {
    require_once 'models/Message.php';
    $msgModel = new Message();
    $unreadMessages = $msgModel->countUnread($_SESSION['user_id']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya - CariBuku</title>
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
        .status-badge { font-size: 0.75rem; padding: 0.3rem 0.8rem; border-radius: 30px; }
        .accordion-button:not(.collapsed) { background-color: #f8f9fc; color: #2c3e66; }
        .btn-primary-custom { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; border-radius: 60px; padding: 0.5rem 1rem; font-weight: 600; transition: 0.2s; }
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
                            <li><a class="dropdown-item" href="inbox.php"><i class="fas fa-envelope"></i> Pesan <?php if($unreadMessages > 0): ?><span class="badge bg-danger ms-1"><?= $unreadMessages ?></span><?php endif; ?></a></li>
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
        <div class="mb-3">
    <a href="index.php" class="btn btn-outline-secondary rounded-pill">
        <i class="fas fa-arrow-left me-2"></i> Kembali ke Beranda
    </a>
</div>
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
            <h2 class="fw-bold"><i class="fas fa-list-ul text-primary me-2"></i> Pesanan Saya</h2>
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show rounded-pill mb-0" role="alert">
                    <i class="fas fa-check-circle me-2"></i> Pesanan berhasil dibuat!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
        </div>

        <?php if (empty($orders)): ?>
            <div class="card border-0 shadow-sm rounded-4 text-center py-5">
                <div class="card-body">
                    <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                    <h4>Belum ada pesanan</h4>
                    <p>Yuk, belanja buku pertama Anda!</p>
                    <a href="index.php" class="btn btn-primary-custom text-white">Mulai Belanja</a>
                </div>
            </div>
        <?php else: ?>
            <div class="accordion" id="orderAccordion">
                <?php foreach ($orders as $order): ?>
                    <div class="accordion-item mb-3 border rounded-4 overflow-hidden shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#order<?= $order['id'] ?>">
                                <div class="d-flex flex-wrap justify-content-between w-100 me-3 align-items-center">
                                    <span><strong>Order #<?= $order['id'] ?></strong> - <?= date('d/m/Y H:i', strtotime($order['order_date'])) ?></span>
                                    <span class="status-badge bg-<?= $order['status'] == 'pending' ? 'warning' : ($order['status'] == 'processing' ? 'info' : 'success') ?> text-dark">
                                        <?= $order['status'] == 'pending' ? 'Menunggu' : ($order['status'] == 'processing' ? 'Diproses' : 'Selesai') ?>
                                    </span>
                                    <span class="ms-3">Total: Rp <?= number_format($order['total_amount'], 0, ',', '.') ?></span>
                                </div>
                            </button>
                        </h2>
                        <div id="order<?= $order['id'] ?>" class="accordion-collapse collapse" data-bs-parent="#orderAccordion">
                            <div class="accordion-body">
                                <p><strong><i class="fas fa-map-marker-alt me-2"></i> Alamat:</strong> <?= nl2br(htmlspecialchars($order['address'])) ?></p>
                                <?php if (!empty($order['notes'])): ?>
                                    <p><strong><i class="fas fa-sticky-note me-2"></i> Catatan:</strong> <?= nl2br(htmlspecialchars($order['notes'])) ?></p>
                                <?php endif; ?>
                                <p><strong><i class="fas fa-credit-card me-2"></i> Metode Pembayaran:</strong> <?= $order['payment_method'] == 'cod' ? 'Cash on Delivery' : 'Transfer Bank' ?></p>
                                <p><strong><i class="fas fa-money-check-alt me-2"></i> Status Pembayaran:</strong> 
                                    <span class="badge bg-<?= $order['payment_status'] == 'paid' ? 'success' : ($order['payment_status'] == 'pending' ? 'warning' : 'danger') ?>">
                                        <?= ucfirst($order['payment_status']) ?>
                                    </span>
                                </p>
                                <?php if ($order['payment_method'] == 'transfer' && $order['payment_status'] == 'paid'): ?>
                                    <div class="alert alert-success">Pembayaran Anda telah dikonfirmasi. Pesanan akan segera diproses.</div>
                                <?php elseif ($order['payment_method'] == 'transfer' && $order['payment_status'] == 'pending'): ?>
                                    <div class="alert alert-warning">Menunggu konfirmasi pembayaran dari admin.</div>
                                <?php elseif ($order['payment_method'] == 'transfer' && $order['payment_status'] == 'failed'): ?>
                                    <div class="alert alert-danger">Pembayaran gagal. Silakan hubungi admin.</div>
                                <?php endif; ?>
                                <h6 class="mt-3">Detail Buku:</h6>
                                <ul class="list-group">
                                    <?php $items = $orderModel->getItems($order['id']); ?>
                                    <?php foreach ($items as $item): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <?= htmlspecialchars($item['title']) ?> x <?= $item['quantity'] ?>
                                            <span class="badge bg-primary rounded-pill">Rp <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <div class="row">
            <div class="col-md-4"><h5 class="text-white">CariBuku</h5><p>Toko buku online terlengkap dan terpercaya.</p></div>
            <div class="col-md-4"><h5 class="text-white">Tautan</h5><ul class="list-unstyled"><li><a href="index.php">Beranda</a></li><li><a href="contact.php">Kontak Kami</a></li></ul></div>
            <div class="col-md-4"><h5 class="text-white">Kontak</h5><p><i class="fas fa-envelope me-2"></i> support@caribuku.com</p><p><i class="fas fa-phone me-2"></i> +62 813 8722 1775</p></div>
        </div>
        <hr><div class="text-center">&copy; <?= date('Y') ?> CariBuku. All rights reserved.</div>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>