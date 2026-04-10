<?php
require_once 'includes/auth.php';
require_once 'models/Cart.php';
require_once 'includes/functions.php';
require_once 'models/Message.php';

redirectIfNotLoggedIn();

// Handle tambah cepat dari parameter add
if (isset($_GET['add'])) {
    $book_id = intval($_GET['add']);
    require_once 'config/database.php';
    $book = getBookByIdSafe($book_id);
    if ($book && $book['stock'] > 0) {
        Cart::add($book['id'], $book['title'], $book['price'], 1);
    }
    header('Location: cart.php');
    exit();
}

// Proses update jumlah via AJAX (tanpa reload)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajax_update'])) {
    $id = intval($_POST['id']);
    $qty = intval($_POST['qty']);
    if ($qty > 0) {
        Cart::update($id, $qty);
    } else {
        Cart::remove($id);
    }
    $total = Cart::getTotal();
    $subtotal = Cart::getItems()[$id]['price'] * $qty;
    echo json_encode(['success' => true, 'total' => $total, 'subtotal' => $subtotal]);
    exit();
}

// Proses hapus item via AJAX
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajax_remove'])) {
    $id = intval($_POST['id']);
    Cart::remove($id);
    $total = Cart::getTotal();
    echo json_encode(['success' => true, 'total' => $total]);
    exit();
}

$items = Cart::getItems();
$total = Cart::getTotal();

$unreadMessages = 0;
if (isset($_SESSION['user_id'])) {
    $msgModel = new Message();
    $unreadMessages = $msgModel->countUnread($_SESSION['user_id']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - CariBuku</title>
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
        .cart-card { border: none; border-radius: 24px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); overflow: hidden; }
        .cart-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 1.5rem; color: white; }
        .btn-primary-custom { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; border-radius: 60px; padding: 0.75rem 1.5rem; font-weight: 600; transition: 0.2s; }
        .btn-primary-custom:hover { transform: scale(1.02); box-shadow: 0 5px 15px rgba(102,126,234,0.4); }
        .quantity-input { width: 80px; border-radius: 60px; text-align: center; padding: 0.4rem; }
        .toast-notif { position: fixed; bottom: 20px; right: 20px; background: #28a745; color: white; padding: 10px 20px; border-radius: 50px; z-index: 1050; display: none; }
        @media (max-width: 768px) {
            .table-cart thead { display: none; }
            .table-cart, .table-cart tbody, .table-cart tr, .table-cart td { display: block; width: 100%; }
            .table-cart tr { margin-bottom: 1rem; border-bottom: 1px solid #dee2e6; padding-bottom: 1rem; }
            .table-cart td { display: flex; justify-content: space-between; align-items: center; text-align: right; padding: 0.5rem; }
            .table-cart td::before { content: attr(data-label); font-weight: bold; text-align: left; width: 40%; }
        }
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
                            <li><a class="dropdown-item" href="cart.php"><i class="fas fa-shopping-cart"></i> Keranjang <span class="badge bg-primary rounded-pill"><?= getCartCount() ?></span></a></li>
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

        <div class="cart-card">
            <div class="cart-header">
                <h2><i class="fas fa-shopping-cart me-2"></i> Keranjang Belanja</h2>
                <p>Perubahan jumlah akan langsung tersimpan secara otomatis.</p>
            </div>
            <div class="p-4">
                <?php if (empty($items)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
                        <h4>Keranjang Anda kosong</h4>
                        <p>Yuk, mulai belanja buku favorit Anda sekarang!</p>
                        <a href="index.php" class="btn btn-primary-custom text-white mt-2">
                            <i class="fas fa-book me-2"></i> Mulai Belanja
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-cart align-middle" id="cartTable">
                            <thead>
                                <tr><th>Buku</th><th>Harga</th><th width="120">Jumlah</th><th>Subtotal</th><th width="100"></th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $id => $item): ?>
                                <tr data-id="<?= $id ?>" data-price="<?= $item['price'] ?>">
                                    <td data-label="Buku"><strong><?= htmlspecialchars($item['title']) ?></strong></td>
                                    <td data-label="Harga" class="item-price">Rp <?= number_format($item['price'], 0, ',', '.') ?></td>
                                    <td data-label="Jumlah">
                                        <input type="number" class="form-control quantity-input update-qty" value="<?= $item['quantity'] ?>" min="1">
                                    </td>
                                    <td data-label="Subtotal" class="item-subtotal">Rp <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?></td>
                                    <td data-label="Aksi">
                                        <button class="btn btn-sm btn-outline-danger rounded-pill remove-item" data-id="<?= $id ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr><th colspan="3" class="text-end">Total</th><th colspan="2" id="cartTotal">Rp <?= number_format($total, 0, ',', '.') ?></th></tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="d-flex flex-wrap gap-3 mt-4 justify-content-between align-items-center">
                        <div></div>
                        <div class="d-flex gap-2">
                            <a href="index.php" class="btn btn-secondary rounded-pill">
                                <i class="fas fa-arrow-left me-2"></i> Lanjut Belanja
                            </a>
                            <a href="checkout.php" class="btn btn-primary-custom text-white">
                                <i class="fas fa-credit-card me-2"></i> Lanjut ke Checkout
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
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

<div id="toastNotif" class="toast-notif">✓ Jumlah diperbarui</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function showNotif(msg) {
        let toast = document.getElementById('toastNotif');
        toast.textContent = msg;
        toast.style.display = 'block';
        setTimeout(() => { toast.style.display = 'none'; }, 1500);
    }

    function updateTotal() {
        let total = 0;
        document.querySelectorAll('#cartTable tbody tr').forEach(row => {
            let subtotalText = row.querySelector('.item-subtotal').innerText.replace('Rp ', '').replace(/\./g, '');
            total += parseInt(subtotalText);
        });
        document.getElementById('cartTotal').innerHTML = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
    }

    // Update jumlah via AJAX
    document.querySelectorAll('.update-qty').forEach(input => {
        input.addEventListener('change', function() {
            let row = this.closest('tr');
            let id = row.getAttribute('data-id');
            let price = parseFloat(row.getAttribute('data-price'));
            let newQty = parseInt(this.value);
            if (isNaN(newQty) || newQty < 1) newQty = 1;
            this.value = newQty;

            fetch('cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'ajax_update=1&id=' + id + '&qty=' + newQty
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    let subtotal = price * newQty;
                    row.querySelector('.item-subtotal').innerHTML = 'Rp ' + new Intl.NumberFormat('id-ID').format(subtotal);
                    document.getElementById('cartTotal').innerHTML = 'Rp ' + new Intl.NumberFormat('id-ID').format(data.total);
                    showNotif('Jumlah berhasil diupdate');
                }
            });
        });
    });

    // Hapus item via AJAX
    document.querySelectorAll('.remove-item').forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('Hapus item ini dari keranjang?')) {
                let row = this.closest('tr');
                let id = this.getAttribute('data-id');
                fetch('cart.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'ajax_remove=1&id=' + id
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        row.remove();
                        document.getElementById('cartTotal').innerHTML = 'Rp ' + new Intl.NumberFormat('id-ID').format(data.total);
                        showNotif('Item dihapus');
                        if (document.querySelectorAll('#cartTable tbody tr').length === 0) {
                            location.reload(); // reload untuk tampilkan keranjang kosong
                        }
                    }
                });
            }
        });
    });
</script>
</body>
</html>