<?php
require_once 'includes/auth.php';
require_once 'models/Cart.php';
require_once 'models/Order.php';

redirectIfNotLoggedIn();

$error = '';
$shipping = $conn->query("SELECT flat_rate, free_shipping_min FROM shipping_settings LIMIT 1")->fetch_assoc();
$flat_rate = $shipping['flat_rate'] ?? 15000;
$free_min = $shipping['free_shipping_min'] ?? 150000;
$subtotal = Cart::getTotal();
$ongkir = ($subtotal >= $free_min) ? 0 : $flat_rate;
$total = $subtotal + $ongkir;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $address = trim($_POST['address']);
    $notes = trim($_POST['notes']);
    $payment_method = $_POST['payment_method'] ?? 'cod';
    $items = Cart::getItems();
    
    if (empty($address)) {
        $error = "Alamat pengiriman harus diisi.";
    } elseif (empty($items)) {
        $error = "Keranjang kosong.";
    } elseif ($payment_method == 'transfer' && (!isset($_FILES['payment_proof']) || $_FILES['payment_proof']['error'] != 0)) {
        $error = "Harap upload bukti transfer.";
    } else {
        $payment_proof = null;
        if ($payment_method == 'transfer' && isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] == 0) {
            $allowed = ['jpg','jpeg','png','gif','pdf'];
            $ext = strtolower(pathinfo($_FILES['payment_proof']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed)) {
                $newName = time() . '_' . uniqid() . '.' . $ext;
                $destination = 'uploads/payments/' . $newName;
                if (!is_dir('uploads/payments')) mkdir('uploads/payments', 0777, true);
                if (move_uploaded_file($_FILES['payment_proof']['tmp_name'], $destination)) {
                    $payment_proof = $destination;
                } else {
                    $error = "Gagal upload bukti.";
                }
            } else {
                $error = "Format file tidak didukung.";
            }
        }
        if (!$error) {
            $orderModel = new OrderModel();
            $order_id = $orderModel->create($_SESSION['user_id'], $total, $address, $notes, $items, $payment_method, $payment_proof);
            if ($order_id) {
                Cart::clear();
                header('Location: my_orders.php?success=1');
                exit();
            } else {
                $error = "Gagal memproses pesanan.";
            }
        }
    }
}

$items = Cart::getItems();
if (empty($items)) {
    header('Location: cart.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - CariBuku</title>
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
        .checkout-card { border: none; border-radius: 24px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); overflow: hidden; }
        .checkout-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 1.5rem; color: white; }
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
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown"><i class="fas fa-user-circle"></i> <?= htmlspecialchars($_SESSION['name']) ?></a>
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
                <div class="checkout-card">
                    <div class="checkout-header">
                        <h2><i class="fas fa-credit-card me-2"></i> Checkout</h2>
                        <p>Konfirmasi alamat dan pilih metode pembayaran</p>
                    </div>
                    <div class="p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger rounded-4"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Alamat Pengiriman</label>
                                <textarea name="address" class="form-control rounded-3" rows="4" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Catatan (opsional)</label>
                                <textarea name="notes" class="form-control rounded-3" rows="2"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Metode Pembayaran</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" id="cod" value="cod" checked>
                                        <label class="form-check-label" for="cod">Cash on Delivery</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" id="transfer" value="transfer">
                                        <label class="form-check-label" for="transfer">Transfer Bank</label>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3" id="bankInfo" style="display: none;">
                                <div class="alert alert-info">
                                    <strong>Nomor Rekening:</strong><br>
                                    BCA: 123-456-7890 a.n. CariBuku<br>
                                    Mandiri: 987-654-3210 a.n. CariBuku
                                </div>
                                <label class="form-label">Upload Bukti Transfer</label>
                                <input type="file" name="payment_proof" class="form-control" accept="image/*,application/pdf">
                            </div>
                            <div class="alert alert-info mb-3">
                                <strong>Ringkasan Biaya:</strong><br>
                                Subtotal: Rp <?= number_format($subtotal, 0, ',', '.') ?><br>
                                Ongkir: <?= $ongkir == 0 ? 'Gratis' : 'Rp '.number_format($ongkir, 0, ',', '.') ?><br>
                                <strong>Total: Rp <?= number_format($total, 0, ',', '.') ?></strong>
                            </div>
                            <button type="submit" class="btn btn-primary-custom text-white">Konfirmasi Pesanan</button>
                            <a href="cart.php" class="btn btn-secondary">Kembali</a>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-transparent fw-semibold">Ringkasan Pesanan</div>
                    <div class="card-body">
                        <?php foreach ($items as $item): ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span><?= htmlspecialchars($item['title']) ?> x<?= $item['quantity'] ?></span>
                                <span>Rp <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?></span>
                            </div>
                        <?php endforeach; ?>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold">
                            <span>Total</span>
                            <span class="text-primary">Rp <?= number_format($total, 0, ',', '.') ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const cod = document.getElementById('cod');
    const transfer = document.getElementById('transfer');
    const bankInfo = document.getElementById('bankInfo');
    function toggle() { bankInfo.style.display = transfer.checked ? 'block' : 'none'; }
    cod.addEventListener('change', toggle);
    transfer.addEventListener('change', toggle);
    toggle();
</script>

<footer class="footer">
    <div class="container text-center">&copy; <?= date('Y') ?> CariBuku. All rights reserved.</div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>