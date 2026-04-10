<?php
session_start();
require_once __DIR__ . '/includes/header.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Tambah User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'user';
    
    $check = $conn->query("SELECT id FROM users WHERE email = '$email'");
    if ($check->num_rows == 0) {
        $conn->query("INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$password', '$role')");
        $_SESSION['success'] = "Pembeli berhasil ditambahkan.";
    } else {
        $_SESSION['error'] = "Email sudah terdaftar!";
    }
    echo '<script>window.location.href = "users.php";</script>';
    exit;
}

// Hapus User dengan validasi
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    // Cek apakah user memiliki pesanan
    $check = $conn->query("SELECT COUNT(*) as total FROM orders WHERE user_id = $id");
    $row = $check->fetch_assoc();
    if ($row['total'] > 0) {
        $_SESSION['error'] = "User tidak dapat dihapus karena masih memiliki {$row['total']} pesanan.";
    } else {
        $conn->query("DELETE FROM users WHERE id = $id");
        $_SESSION['success'] = "User berhasil dihapus.";
    }
    echo '<script>window.location.href = "users.php";</script>';
    exit;
}

$users = $conn->query("
    SELECT u.id, u.name, u.email, u.created_at, COUNT(o.id) as total_orders
    FROM users u
    LEFT JOIN orders o ON u.id = o.user_id
    WHERE u.role = 'user'
    GROUP BY u.id
    ORDER BY u.created_at DESC
");

$errorMsg = isset($_SESSION['error']) ? $_SESSION['error'] : '';
$successMsg = isset($_SESSION['success']) ? $_SESSION['success'] : '';
unset($_SESSION['error'], $_SESSION['success']);
?>

<div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
    <div>
        <h1 class="display-6 fw-semibold">Data Pembeli</h1>
        <p class="text-muted">Kelola akun pembeli (tambah, hapus, lihat riwayat).</p>
    </div>
    <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addUserModal">
        <i class="fas fa-user-plus me-2"></i>Tambah Pembeli
    </button>
</div>

<?php if ($errorMsg): ?>
    <div class="alert alert-danger rounded-4"><?= htmlspecialchars($errorMsg) ?></div>
<?php endif; ?>
<?php if ($successMsg): ?>
    <div class="alert alert-success rounded-4"><?= htmlspecialchars($successMsg) ?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-transparent border-0 pt-4">
        <h5 class="fw-semibold mb-0"><i class="fas fa-users me-2 text-primary"></i>Daftar Pembeli</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr><th>ID</th><th>Nama</th><th>Email</th><th>Tanggal Daftar</th><th class="text-center">Total Pesanan</th><th class="text-center">Aksi</th></tr>
                </thead>
                <tbody>
                    <?php if ($users->num_rows > 0): ?>
                        <?php while($user = $users->fetch_assoc()): ?>
                        <tr>
                            <td>#<?= $user['id'] ?></td>
                            <td><i class="fas fa-user-circle text-primary me-2"></i><?= htmlspecialchars($user['name']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                            <td class="text-center"><span class="badge bg-primary rounded-pill px-3"><?= $user['total_orders'] ?> pesanan</span></td>
                            <td class="text-center">
                                <a href="orders.php?user_id=<?= $user['id'] ?>" class="btn btn-sm btn-outline-info rounded-pill me-1"><i class="fas fa-receipt me-1"></i>Riwayat</a>
                                <button class="btn btn-sm btn-outline-danger rounded-pill" data-bs-toggle="modal" data-bs-target="#deleteUserModal" 
                                    data-id="<?= $user['id'] ?>" data-name="<?= htmlspecialchars($user['name']) ?>" data-orders="<?= $user['total_orders'] ?>">
                                    <i class="fas fa-trash me-1"></i>Hapus
                                </button>
                             </nav>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">Belum ada pembeli. Silakan tambah pembeli baru. </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH PEMBELI -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <div class="modal-header border-0 pt-4 px-4">
                <h5 class="modal-title fw-semibold"><i class="fas fa-user-plus text-primary me-2"></i>Tambah Pembeli Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body px-4">
                    <div class="mb-3"><label class="form-label fw-semibold">Nama Lengkap</label><input type="text" name="name" class="form-control rounded-3" required></div>
                    <div class="mb-3"><label class="form-label fw-semibold">Email</label><input type="email" name="email" class="form-control rounded-3" required></div>
                    <div class="mb-3"><label class="form-label fw-semibold">Password</label><input type="password" name="password" class="form-control rounded-3" required></div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="add_user" class="btn btn-primary rounded-pill px-4">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL HAPUS USER -->
<div class="modal fade" id="deleteUserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <div class="modal-header border-0 pt-4 px-4">
                <h5 class="modal-title fw-semibold"><i class="fas fa-trash-alt text-danger me-2"></i>Hapus Pembeli</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4">
                <p>Yakin hapus <strong id="delete_name"></strong>?</p>
                <p class="text-danger small" id="delete_warning"></p>
            </div>
            <div class="modal-footer border-0 pb-4 px-4">
                <form method="GET">
                    <input type="hidden" name="delete" id="delete_id">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4" id="confirmDeleteBtn">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    var deleteModal = document.getElementById('deleteUserModal');
    deleteModal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        var id = button.getAttribute('data-id');
        var name = button.getAttribute('data-name');
        var orders = parseInt(button.getAttribute('data-orders'));
        deleteModal.querySelector('#delete_id').value = id;
        deleteModal.querySelector('#delete_name').textContent = name;
        var warningSpan = deleteModal.querySelector('#delete_warning');
        var confirmBtn = deleteModal.querySelector('#confirmDeleteBtn');
        if (orders > 0) {
            warningSpan.innerHTML = 'User tidak dapat dihapus karena masih memiliki ' + orders + ' pesanan.';
            confirmBtn.disabled = true;
        } else {
            warningSpan.innerHTML = 'User tidak memiliki pesanan, aman untuk dihapus.';
            confirmBtn.disabled = false;
        }
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>