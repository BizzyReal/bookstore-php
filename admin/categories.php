<?php
session_start();
require_once __DIR__ . '/includes/header.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Proses Tambah Kategori
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    if (!empty($name)) {
        $conn->query("INSERT INTO categories (name) VALUES ('$name')");
        echo '<script>window.location.href = "categories.php";</script>';
        exit;
    }
}

// Proses Edit Kategori
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $result = $conn->query("SELECT * FROM categories WHERE id = $id");
    $editCategory = $result->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_category'])) {
    $id = intval($_POST['id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $conn->query("UPDATE categories SET name = '$name' WHERE id = $id");
    echo '<script>window.location.href = "categories.php";</script>';
    exit;
}

// Proses Hapus Kategori dengan validasi
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    // Cek apakah ada buku yang menggunakan kategori ini
    $check = $conn->query("SELECT COUNT(*) as total FROM books WHERE category_id = $id");
    $row = $check->fetch_assoc();
    if ($row['total'] > 0) {
        $_SESSION['error'] = "Kategori tidak dapat dihapus karena masih digunakan oleh {$row['total']} buku.";
    } else {
        $conn->query("DELETE FROM categories WHERE id = $id");
        $_SESSION['success'] = "Kategori berhasil dihapus.";
    }
    echo '<script>window.location.href = "categories.php";</script>';
    exit;
}

$categories = $conn->query("SELECT * FROM categories ORDER BY id DESC");

$errorMsg = isset($_SESSION['error']) ? $_SESSION['error'] : '';
$successMsg = isset($_SESSION['success']) ? $_SESSION['success'] : '';
unset($_SESSION['error'], $_SESSION['success']);
?>

<div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
    <div>
        <h1 class="display-6 fw-semibold">Manajemen Kategori</h1>
        <p class="text-muted">Kelola semua kategori buku di toko Anda.</p>
    </div>
    <button class="btn btn-primary rounded-pill px-4 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#addModal">
        <i class="fas fa-plus-circle me-2"></i>Tambah Kategori
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
        <h5 class="fw-semibold mb-0"><i class="fas fa-tags me-2 text-primary"></i>Daftar Kategori</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr><th width="80">ID</th><th>Nama Kategori</th><th width="180" class="text-center">Aksi</th></tr>
                </thead>
                <tbody>
                    <?php if ($categories->num_rows > 0): ?>
                        <?php while($cat = $categories->fetch_assoc()): ?>
                        <tr>
                            <td>#<?= $cat['id'] ?></td>
                            <td><i class="fas fa-folder-open text-primary me-2"></i><?= htmlspecialchars($cat['name']) ?></td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-primary rounded-pill px-3 me-1" data-bs-toggle="modal" data-bs-target="#editModal" 
                                        data-id="<?= $cat['id'] ?>" data-name="<?= htmlspecialchars($cat['name']) ?>">
                                    <i class="fas fa-edit me-1"></i> Edit
                                </button>
                                <button class="btn btn-sm btn-outline-danger rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#deleteModal" 
                                        data-id="<?= $cat['id'] ?>" data-name="<?= htmlspecialchars($cat['name']) ?>">
                                    <i class="fas fa-trash-alt me-1"></i> Hapus
                                </button>
                             </nav>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="3" class="text-center text-muted py-4">Belum ada kategori. Silakan tambah kategori baru. </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Kategori -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pt-4 px-4">
                <h5 class="modal-title fw-semibold"><i class="fas fa-plus-circle text-primary me-2"></i>Tambah Kategori</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body px-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Kategori</label>
                        <input type="text" name="name" class="form-control rounded-3" placeholder="Contoh: Novel, Sejarah, Teknologi" required>
                        <div class="form-text">Nama kategori harus unik dan deskriptif.</div>
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="add_category" class="btn btn-primary rounded-pill px-4">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Kategori -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pt-4 px-4">
                <h5 class="modal-title fw-semibold"><i class="fas fa-edit text-warning me-2"></i>Edit Kategori</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body px-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Kategori</label>
                        <input type="text" name="name" id="edit_name" class="form-control rounded-3" required>
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="update_category" class="btn btn-primary rounded-pill px-4">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Hapus Kategori -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pt-4 px-4">
                <h5 class="modal-title fw-semibold"><i class="fas fa-trash-alt text-danger me-2"></i>Hapus Kategori</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4">
                <p>Apakah Anda yakin ingin menghapus kategori <strong id="delete_name"></strong>?</p>
                <p class="text-danger small">Kategori tidak dapat dihapus jika masih ada buku yang menggunakannya.</p>
            </div>
            <div class="modal-footer border-0 pb-4 px-4">
                <form method="GET">
                    <input type="hidden" name="delete" id="delete_id">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4">Ya, Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    var editModal = document.getElementById('editModal');
    editModal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        var id = button.getAttribute('data-id');
        var name = button.getAttribute('data-name');
        editModal.querySelector('#edit_id').value = id;
        editModal.querySelector('#edit_name').value = name;
    });
    var deleteModal = document.getElementById('deleteModal');
    deleteModal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        var id = button.getAttribute('data-id');
        var name = button.getAttribute('data-name');
        deleteModal.querySelector('#delete_id').value = id;
        deleteModal.querySelector('#delete_name').textContent = name;
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>