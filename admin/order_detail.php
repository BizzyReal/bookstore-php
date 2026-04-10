<?php
session_start();
require_once __DIR__ . '/includes/header.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id == 0) {
    header('Location: orders.php');
    exit;
}

$order = $conn->query("SELECT o.*, u.name as user_name FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = $id")->fetch_assoc();
if (!$order) {
    header('Location: orders.php');
    exit;
}

$items = $conn->query("SELECT oi.*, b.title FROM order_items oi JOIN books b ON oi.book_id = b.id WHERE oi.order_id = $id");

// Update status pesanan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    $allowed = ['pending', 'processing', 'delivered', 'cancelled'];
    if (in_array($new_status, $allowed)) {
        $conn->query("UPDATE orders SET status = '$new_status' WHERE id = $id");
        echo '<script>window.location.href = "order_detail.php?id=' . $id . '&success=1";</script>';
        exit;
    }
}

// Konfirmasi pembayaran transfer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
    $payment_status = $_POST['payment_status'];
    if ($payment_status == 'paid' || $payment_status == 'failed') {
        $conn->query("UPDATE orders SET payment_status = '$payment_status' WHERE id = $id");
        echo '<script>window.location.href = "order_detail.php?id=' . $id . '&success=1";</script>';
        exit;
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="display-6 fw-semibold">Detail Pesanan #<?= $id ?></h1>
        <p class="text-muted">Lihat dan ubah status pesanan serta pembayaran.</p>
    </div>
    <a href="orders.php" class="btn btn-secondary rounded-pill"><i class="fas fa-arrow-left me-2"></i>Kembali</a>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success rounded-4">Status berhasil diperbarui.</div>
<?php endif; ?>

<div class="row">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-transparent fw-semibold border-0 pt-4">Informasi Pemesan & Pesanan</div>
            <div class="card-body">
                <p><strong>Nama:</strong> <?= htmlspecialchars($order['user_name']) ?></p>
                <p><strong>Alamat:</strong> <?= nl2br(htmlspecialchars($order['address'])) ?></p>
                <p><strong>Catatan:</strong> <?= nl2br(htmlspecialchars($order['notes'] ?: '-')) ?></p>
                <p><strong>Metode Bayar:</strong> <?= $order['payment_method'] == 'cod' ? 'Cash on Delivery' : 'Transfer Bank' ?></p>
                <p><strong>Status Pembayaran:</strong> 
                    <span class="badge bg-<?= $order['payment_status'] == 'paid' ? 'success' : ($order['payment_status'] == 'pending' ? 'warning' : 'danger') ?>">
                        <?= ucfirst($order['payment_status']) ?>
                    </span>
                </p>
                <?php if ($order['payment_method'] == 'transfer' && $order['payment_proof']): ?>
                    <p><strong>Bukti Transfer:</strong> <a href="../<?= $order['payment_proof'] ?>" target="_blank" class="btn btn-sm btn-outline-primary">Lihat Bukti</a></p>
                <?php endif; ?>
                <p><strong>Status Pesanan:</strong> <?= ucfirst($order['status']) ?></p>
                <p><strong>Tipe Pesanan:</strong> <?= $order['order_type'] == 'offline' ? 'Offline (Toko)' : 'Online' ?></p>
                <p><strong>Tanggal:</strong> <?= date('d/m/Y H:i', strtotime($order['order_date'])) ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-transparent fw-semibold border-0 pt-4">Ubah Status Pesanan</div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Status Pesanan</label>
                        <select name="status" class="form-select">
                            <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>Pending (Menunggu)</option>
                            <option value="processing" <?= $order['status'] == 'processing' ? 'selected' : '' ?>>Processing (Diproses)</option>
                            <option value="delivered" <?= $order['status'] == 'delivered' ? 'selected' : '' ?>>Delivered (Terkirim)</option>
                            <option value="cancelled" <?= $order['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled (Dibatalkan)</option>
                        </select>
                    </div>
                    <button type="submit" name="update_status" class="btn btn-primary rounded-pill">Update Status Pesanan</button>
                </form>
            </div>
        </div>
        
        <?php if ($order['payment_method'] == 'transfer' && $order['payment_status'] == 'pending'): ?>
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-transparent fw-semibold border-0 pt-4">Konfirmasi Pembayaran</div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Status Pembayaran</label>
                        <select name="payment_status" class="form-select">
                            <option value="paid">Sudah Dibayar (Paid)</option>
                            <option value="failed">Gagal / Tidak Valid</option>
                        </select>
                    </div>
                    <button type="submit" name="confirm_payment" class="btn btn-success rounded-pill">Konfirmasi Pembayaran</button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-transparent fw-semibold border-0 pt-4">Daftar Buku yang Dipesan</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light"><tr><th>Judul Buku</th><th>Jumlah</th><th>Harga Satuan</th><th>Subtotal</th></tr></thead>
                <tbody>
                    <?php $total = 0; while($item = $items->fetch_assoc()): $subtotal = $item['price'] * $item['quantity']; $total += $subtotal; ?>
                    <tr><td><?= htmlspecialchars($item['title']) ?></td><td><?= $item['quantity'] ?></td><td>Rp <?= number_format($item['price'], 0, ',', '.') ?></td><td>Rp <?= number_format($subtotal, 0, ',', '.') ?></td></tr>
                    <?php endwhile; ?>
                </tbody>
                <tfoot><tr class="table-light fw-bold"><td colspan="3" class="text-end">Total</td><td>Rp <?= number_format($total, 0, ',', '.') ?></td></tr></tfoot>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>