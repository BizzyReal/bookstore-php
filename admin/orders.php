<?php
session_start();
require_once __DIR__ . '/includes/header.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Proses Tambah Pesanan Offline (sama seperti sebelumnya)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_offline_order'])) {
    $user_id = intval($_POST['user_id']);
    $book_ids = $_POST['book_id'] ?? [];
    $quantities = $_POST['quantity'] ?? [];
    
    $total_amount = 0;
    $items = [];
    for ($i = 0; $i < count($book_ids); $i++) {
        $bid = intval($book_ids[$i]);
        $qty = intval($quantities[$i]);
        if ($qty > 0) {
            $book = $conn->query("SELECT price, stock FROM books WHERE id = $bid")->fetch_assoc();
            if ($book && $book['stock'] >= $qty) {
                $total_amount += $book['price'] * $qty;
                $items[] = ['id' => $bid, 'qty' => $qty, 'price' => $book['price']];
            } else {
                $error = "Stok tidak mencukupi untuk salah satu buku.";
                break;
            }
        }
    }
    if (!isset($error) && count($items) > 0) {
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status, order_type, order_date) VALUES (?, ?, 'pending', 'offline', NOW())");
            $stmt->bind_param("id", $user_id, $total_amount);
            $stmt->execute();
            $order_id = $stmt->insert_id;
            $stmt->close();
            
            foreach ($items as $item) {
                $stmt2 = $conn->prepare("INSERT INTO order_items (order_id, book_id, quantity, price) VALUES (?, ?, ?, ?)");
                $stmt2->bind_param("iiid", $order_id, $item['id'], $item['qty'], $item['price']);
                $stmt2->execute();
                $stmt2->close();
                $conn->query("UPDATE books SET stock = stock - {$item['qty']} WHERE id = {$item['id']}");
            }
            $conn->commit();
            echo '<script>window.location.href = "orders.php?success=added";</script>';
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Gagal menyimpan pesanan: " . $e->getMessage();
        }
    } else if (!isset($error)) {
        $error = "Pilih minimal satu buku dengan jumlah > 0.";
    }
}

// Filter user_id untuk lihat riwayat
$user_id_filter = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$where = $user_id_filter ? "WHERE o.user_id = $user_id_filter" : "";
$orders = $conn->query("
    SELECT o.*, u.name as user_name 
    FROM orders o
    JOIN users u ON o.user_id = u.id
    $where
    ORDER BY o.order_date DESC
");

// Data untuk form offline: daftar buku dengan search
$search = isset($_GET['search_book']) ? mysqli_real_escape_string($conn, $_GET['search_book']) : '';
$bookQuery = "SELECT id, title, author, price, stock FROM books WHERE stock > 0";
if ($search) $bookQuery .= " AND (title LIKE '%$search%' OR author LIKE '%$search%')";
$bookQuery .= " ORDER BY title LIMIT 10";
$books = $conn->query($bookQuery);

$users = $conn->query("SELECT id, name FROM users WHERE role = 'user' ORDER BY name");
?>

<div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
    <div><h1 class="display-6 fw-semibold">Manajemen Pesanan</h1><p class="text-muted">Kelola pesanan online & buat pesanan offline (toko).</p></div>
    <button class="btn btn-success rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#offlineOrderModal"><i class="fas fa-store me-2"></i>Tambah Pesanan Offline</button>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success rounded-4">Pesanan offline berhasil ditambahkan.</div>
<?php endif; if (isset($error)): ?>
    <div class="alert alert-danger rounded-4"><?= $error ?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-transparent border-0 pt-4"><h5 class="fw-semibold mb-0"><i class="fas fa-shopping-cart me-2 text-primary"></i>Daftar Pesanan</h5></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr><th>ID</th><th>User</th><th>Total</th><th>Status</th><th>Tipe</th><th>Tanggal</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    <?php if ($orders->num_rows > 0): ?>
                        <?php while($order = $orders->fetch_assoc()): ?>
                        <tr>
                            <td>#<?= $order['id'] ?></td>
                            <td><i class="fas fa-user-circle me-1"></i><?= htmlspecialchars($order['user_name']) ?></td>
                            <td>Rp <?= number_format($order['total_amount'], 0, ',', '.') ?></td>
                            <td><span class="badge bg-<?= $order['status']=='pending'?'warning':'primary' ?> rounded-pill"><?= $order['status'] ?></span></td>
                            <td>
                                <?php if ($order['order_type'] == 'offline'): ?>
                                    <span class="badge bg-secondary rounded-pill"><i class="fas fa-store me-1"></i>Offline (Toko)</span>
                                <?php else: ?>
                                    <span class="badge bg-info rounded-pill"><i class="fas fa-globe me-1"></i>Online</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($order['order_date'])) ?></td>
                            <td><a href="order_detail.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill"><i class="fas fa-eye me-1"></i>Detail</a></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">Belum ada pesanan.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH PESANAN OFFLINE (sama seperti sebelumnya, tidak perlu diubah) -->
<div class="modal fade" id="offlineOrderModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content rounded-4">
            <div class="modal-header border-0 pt-4 px-4">
                <h5 class="modal-title fw-semibold"><i class="fas fa-store text-success me-2"></i>Buat Pesanan Offline</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body px-4">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Pilih Pembeli</label>
                            <select name="user_id" class="form-select rounded-3" required>
                                <option value="">-- Pilih Pembeli --</option>
                                <?php 
                                $users->data_seek(0);
                                while($u = $users->fetch_assoc()): ?>
                                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                            <div class="form-text">Jika belum ada, <a href="users.php" target="_blank">tambah pembeli dulu</a>.</div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Cari Buku</label>
                        <div class="input-group">
                            <input type="text" name="search_book" class="form-control rounded-start-3" placeholder="Judul atau penulis..." value="<?= htmlspecialchars($search) ?>">
                            <button type="submit" formmethod="GET" class="btn btn-primary rounded-end-3">Cari</button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr><th>ID</th><th>Judul</th><th>Penulis</th><th>Harga</th><th>Stok</th><th width="120">Jumlah</th></tr>
                            </thead>
                            <tbody>
                                <?php if ($books->num_rows > 0): ?>
                                    <?php while($book = $books->fetch_assoc()): ?>
                                    <tr>
                                        <td>#<?= $book['id'] ?></td>
                                        <td><?= htmlspecialchars($book['title']) ?></td>
                                        <td><?= htmlspecialchars($book['author']) ?></td>
                                        <td>Rp <?= number_format($book['price'], 0, ',', '.') ?></td>
                                        <td><?= $book['stock'] ?></td>
                                        <td>
                                            <input type="hidden" name="book_id[]" value="<?= $book['id'] ?>">
                                            <input type="number" name="quantity[]" class="form-control qty-input" min="0" max="<?= $book['stock'] ?>" value="0" style="width: 90px;">
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="6" class="text-center">Tidak ada buku tersedia.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-info mt-3" id="totalPreview">Total: Rp 0</div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="add_offline_order" class="btn btn-success rounded-pill px-4"><i class="fas fa-save me-2"></i>Simpan Pesanan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const qtyInputs = document.querySelectorAll('.qty-input');
    const totalPreview = document.getElementById('totalPreview');
    function updateTotal() {
        let total = 0;
        document.querySelectorAll('#offlineOrderModal tbody tr').forEach(row => {
            const priceText = row.cells[3]?.innerText.replace('Rp ', '').replace(/\./g, '');
            const price = parseInt(priceText) || 0;
            const qty = parseInt(row.querySelector('.qty-input')?.value) || 0;
            total += price * qty;
        });
        totalPreview.innerText = 'Total: Rp ' + new Intl.NumberFormat('id-ID').format(total);
    }
    qtyInputs.forEach(inp => inp.addEventListener('input', updateTotal));
    updateTotal();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>