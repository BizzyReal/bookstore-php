<?php
session_start();
require_once __DIR__ . '/includes/header.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Proses Tambah Buku
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_book'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $author = mysqli_real_escape_string($conn, $_POST['author']);
    $price = floatval($_POST['price']);
    $category_id = intval($_POST['category_id']);
    $stock = intval($_POST['stock']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    
    $cover_image = '';
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $filename = $_FILES['cover_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $newName = time() . '_' . uniqid() . '.' . $ext;
            $destination = __DIR__ . '/../uploads/' . $newName;
            if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $destination)) {
                $cover_image = $newName;
            }
        }
    }
    
    $stmt = $conn->prepare("INSERT INTO books (title, author, price, category_id, stock, description, cover_image) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdiiss", $title, $author, $price, $category_id, $stock, $description, $cover_image);
    $stmt->execute();
    $stmt->close();
    echo '<script>window.location.href = "books.php";</script>';
    exit;
}

// Proses Edit Buku
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $result = $conn->query("SELECT * FROM books WHERE id = $id");
    $editBook = $result->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_book'])) {
    $id = intval($_POST['id']);
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $author = mysqli_real_escape_string($conn, $_POST['author']);
    $price = floatval($_POST['price']);
    $category_id = intval($_POST['category_id']);
    $stock = intval($_POST['stock']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    
    $result = $conn->query("SELECT cover_image FROM books WHERE id = $id");
    $oldImage = $result->fetch_assoc()['cover_image'];
    $cover_image = $oldImage;
    
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $filename = $_FILES['cover_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $newName = time() . '_' . uniqid() . '.' . $ext;
            $destination = __DIR__ . '/../uploads/' . $newName;
            if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $destination)) {
                $cover_image = $newName;
                if (!empty($oldImage) && file_exists(__DIR__ . '/../uploads/' . $oldImage)) {
                    unlink(__DIR__ . '/../uploads/' . $oldImage);
                }
            }
        }
    }
    
    $stmt = $conn->prepare("UPDATE books SET title=?, author=?, price=?, category_id=?, stock=?, description=?, cover_image=? WHERE id=?");
    $stmt->bind_param("ssdiissi", $title, $author, $price, $category_id, $stock, $description, $cover_image, $id);
    $stmt->execute();
    $stmt->close();
    echo '<script>window.location.href = "books.php";</script>';
    exit;
}

// Proses Hapus Buku
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $result = $conn->query("SELECT cover_image FROM books WHERE id = $id");
    if ($row = $result->fetch_assoc()) {
        if (!empty($row['cover_image']) && file_exists(__DIR__ . '/../uploads/' . $row['cover_image'])) {
            unlink(__DIR__ . '/../uploads/' . $row['cover_image']);
        }
    }
    $conn->query("DELETE FROM books WHERE id = $id");
    echo '<script>window.location.href = "books.php";</script>';
    exit;
}

// Search & Filter
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$category_filter = isset($_GET['category']) ? intval($_GET['category']) : 0;

$where = [];
if ($search) $where[] = "(title LIKE '%$search%' OR author LIKE '%$search%')";
if ($category_filter) $where[] = "category_id = $category_filter";
$whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

$limit = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$totalResult = $conn->query("SELECT COUNT(*) as total FROM books $whereClause");
$totalRows = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

$books = $conn->query("SELECT books.*, categories.name as category_name 
                       FROM books 
                       LEFT JOIN categories ON books.category_id = categories.id 
                       $whereClause 
                       ORDER BY books.id DESC 
                       LIMIT $offset, $limit");

$categories = $conn->query("SELECT id, name FROM categories ORDER BY name");
?>

<div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
    <div>
        <h1 class="display-6 fw-semibold">Manajemen Buku</h1>
        <p class="text-muted">Kelola koleksi buku toko Anda.</p>
    </div>
    <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addBookModal">
        <i class="fas fa-plus-circle me-2"></i>Tambah Buku
    </button>
</div>

<!-- Search & Filter -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control rounded-pill" placeholder="Cari judul atau penulis..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-4">
                <select name="category" class="form-select rounded-pill">
                    <option value="0">Semua Kategori</option>
                    <?php
                    $catResult = $conn->query("SELECT id, name FROM categories ORDER BY name");
                    while ($cat = $catResult->fetch_assoc()):
                    ?>
                        <option value="<?= $cat['id'] ?>" <?= $category_filter == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary rounded-pill w-100"><i class="fas fa-search me-2"></i>Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-transparent border-0 pt-4">
        <h5 class="fw-semibold mb-0"><i class="fas fa-book me-2 text-primary"></i>Daftar Buku</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr><th width="60">ID</th><th width="70">Sampul</th><th>Judul</th><th>Penulis</th><th>Kategori</th><th>Harga</th><th>Stok</th><th width="140" class="text-center">Aksi</th></tr>
                </thead>
                <tbody>
                    <?php if ($books->num_rows > 0): ?>
                        <?php while ($book = $books->fetch_assoc()): ?>
                        <tr>
                            <td>#<?= $book['id'] ?></td>
                            <td class="text-center">
                                <?php if (!empty($book['cover_image']) && file_exists(__DIR__ . '/../uploads/' . $book['cover_image'])): ?>
                                    <img src="../uploads/<?= $book['cover_image'] ?>" width="50" height="60" style="object-fit: cover; border-radius: 8px;">
                                <?php else: ?>
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 50px; height: 60px;"><i class="fas fa-book fa-2x text-secondary"></i></div>
                                <?php endif; ?>
                             </td>
                            <td><strong><?= htmlspecialchars($book['title']) ?></strong></td>
                            <td><?= htmlspecialchars($book['author']) ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($book['category_name'] ?? 'Tanpa Kategori') ?></span></td>
                            <td>Rp <?= number_format($book['price'], 0, ',', '.') ?></td>
                            <td><?= $book['stock'] ?></td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-primary rounded-pill me-1" data-bs-toggle="modal" data-bs-target="#editBookModal" 
                                    data-id="<?= $book['id'] ?>"
                                    data-title="<?= htmlspecialchars($book['title']) ?>"
                                    data-author="<?= htmlspecialchars($book['author']) ?>"
                                    data-price="<?= $book['price'] ?>"
                                    data-category="<?= $book['category_id'] ?>"
                                    data-stock="<?= $book['stock'] ?>"
                                    data-description="<?= htmlspecialchars($book['description']) ?>"
                                    data-cover="<?= $book['cover_image'] ?>">
                                    <i class="fas fa-edit me-1"></i> Edit
                                </button>
                                <button class="btn btn-sm btn-outline-danger rounded-pill" data-bs-toggle="modal" data-bs-target="#deleteBookModal" 
                                    data-id="<?= $book['id'] ?>" 
                                    data-title="<?= htmlspecialchars($book['title']) ?>">
                                    <i class="fas fa-trash me-1"></i> Hapus
                                </button>
                              </td>
                          </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="text-center text-muted py-4">Belum ada buku. Silakan tambah buku baru.</td></tr>
                    <?php endif; ?>
                </tbody>
              </table>
        </div>
        <?php if ($totalPages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link rounded-pill mx-1" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&category=<?= $category_filter ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<!-- MODAL TAMBAH BUKU -->
<div class="modal fade" id="addBookModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4">
            <div class="modal-header border-0 pt-4 px-4">
                <h5 class="modal-title fw-semibold"><i class="fas fa-plus-circle text-primary me-2"></i>Tambah Buku Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body px-4">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3"><label class="form-label fw-semibold">Judul Buku</label><input type="text" name="title" class="form-control rounded-3" required></div>
                            <div class="mb-3"><label class="form-label fw-semibold">Penulis</label><input type="text" name="author" class="form-control rounded-3" required></div>
                            <div class="row">
                                <div class="col-md-6"><label class="form-label fw-semibold">Harga (Rp)</label><input type="number" name="price" class="form-control rounded-3" step="1000" required></div>
                                <div class="col-md-6"><label class="form-label fw-semibold">Stok</label><input type="number" name="stock" class="form-control rounded-3" required></div>
                            </div>
                            <div class="mb-3 mt-2"><label class="form-label fw-semibold">Kategori</label><select name="category_id" class="form-select rounded-3" required>
                                <option value="">Pilih Kategori</option>
                                <?php $catList = $conn->query("SELECT id, name FROM categories ORDER BY name");
                                while ($cat = $catList->fetch_assoc()): ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                <?php endwhile; ?>
                            </select></div>
                            <div class="mb-3"><label class="form-label fw-semibold">Deskripsi</label><textarea name="description" rows="3" class="form-control rounded-3"></textarea></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Cover Buku</label>
                            <div class="border rounded-3 p-3 text-center">
                                <img id="addPreview" src="#" alt="Preview" style="max-width: 100%; max-height: 180px; display: none; margin-bottom: 10px;">
                                <input type="file" name="cover_image" id="addCoverInput" class="form-control" accept="image/*">
                                <div class="form-text mt-2">jpg, png, gif, webp (max 2MB)</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="add_book" class="btn btn-primary rounded-pill px-4">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL EDIT BUKU -->
<div class="modal fade" id="editBookModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4">
            <div class="modal-header border-0 pt-4 px-4">
                <h5 class="modal-title fw-semibold"><i class="fas fa-edit text-warning me-2"></i>Edit Buku</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body px-4">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3"><label class="form-label fw-semibold">Judul Buku</label><input type="text" name="title" id="edit_title" class="form-control rounded-3" required></div>
                            <div class="mb-3"><label class="form-label fw-semibold">Penulis</label><input type="text" name="author" id="edit_author" class="form-control rounded-3" required></div>
                            <div class="row">
                                <div class="col-md-6"><label class="form-label fw-semibold">Harga (Rp)</label><input type="number" name="price" id="edit_price" class="form-control rounded-3" step="1000" required></div>
                                <div class="col-md-6"><label class="form-label fw-semibold">Stok</label><input type="number" name="stock" id="edit_stock" class="form-control rounded-3" required></div>
                            </div>
                            <div class="mb-3 mt-2"><label class="form-label fw-semibold">Kategori</label><select name="category_id" id="edit_category" class="form-select rounded-3" required>
                                <?php $catList2 = $conn->query("SELECT id, name FROM categories ORDER BY name");
                                while ($cat = $catList2->fetch_assoc()): ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                <?php endwhile; ?>
                            </select></div>
                            <div class="mb-3"><label class="form-label fw-semibold">Deskripsi</label><textarea name="description" id="edit_description" rows="3" class="form-control rounded-3"></textarea></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Cover Buku</label>
                            <div class="border rounded-3 p-3 text-center">
                                <img id="editPreview" src="#" alt="Preview" style="max-width: 100%; max-height: 180px; margin-bottom: 10px;">
                                <input type="file" name="cover_image" id="editCoverInput" class="form-control" accept="image/*">
                                <div class="form-text mt-2">Kosongkan jika tidak ingin mengganti cover.</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="update_book" class="btn btn-primary rounded-pill px-4">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL HAPUS BUKU -->
<div class="modal fade" id="deleteBookModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <div class="modal-header border-0 pt-4 px-4">
                <h5 class="modal-title fw-semibold"><i class="fas fa-trash-alt text-danger me-2"></i>Hapus Buku</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4">
                <p>Apakah Anda yakin ingin menghapus buku <strong id="delete_title"></strong>?</p>
                <p class="text-danger small">Tindakan ini akan menghapus cover buku dan data terkait. Tidak dapat dibatalkan.</p>
            </div>
            <div class="modal-footer border-0 pb-4 px-4">
                <form method="GET">
                    <input type="hidden" name="delete" id="delete_id">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4">Ya, Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('addCoverInput').addEventListener('change', function(e) {
        const preview = document.getElementById('addPreview');
        if (e.target.files && e.target.files[0]) {
            const reader = new FileReader();
            reader.onload = function(ev) { preview.src = ev.target.result; preview.style.display = 'block'; }
            reader.readAsDataURL(e.target.files[0]);
        } else { preview.style.display = 'none'; }
    });
    var editModal = document.getElementById('editBookModal');
    editModal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        document.getElementById('edit_id').value = button.getAttribute('data-id');
        document.getElementById('edit_title').value = button.getAttribute('data-title');
        document.getElementById('edit_author').value = button.getAttribute('data-author');
        document.getElementById('edit_price').value = button.getAttribute('data-price');
        document.getElementById('edit_stock').value = button.getAttribute('data-stock');
        document.getElementById('edit_description').value = button.getAttribute('data-description');
        document.getElementById('edit_category').value = button.getAttribute('data-category');
        var oldCover = button.getAttribute('data-cover');
        var preview = document.getElementById('editPreview');
        if (oldCover && oldCover !== '') {
            preview.src = '../uploads/' + oldCover;
            preview.style.display = 'block';
        } else { preview.style.display = 'none'; }
    });
    document.getElementById('editCoverInput').addEventListener('change', function(e) {
        const preview = document.getElementById('editPreview');
        if (e.target.files && e.target.files[0]) {
            const reader = new FileReader();
            reader.onload = function(ev) { preview.src = ev.target.result; preview.style.display = 'block'; }
            reader.readAsDataURL(e.target.files[0]);
        }
    });
    var deleteModal = document.getElementById('deleteBookModal');
    deleteModal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        document.getElementById('delete_id').value = button.getAttribute('data-id');
        document.getElementById('delete_title').textContent = button.getAttribute('data-title');
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>