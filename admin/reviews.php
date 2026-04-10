<?php
session_start();
require_once __DIR__ . '/includes/header.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

if (isset($_GET['approve'])) {
    $id = intval($_GET['approve']);
    $conn->query("UPDATE reviews SET status = 'approved' WHERE id = $id");
    echo '<script>window.location.href = "reviews.php";</script>';
    exit;
}
if (isset($_GET['reject'])) {
    $id = intval($_GET['reject']);
    $conn->query("UPDATE reviews SET status = 'rejected' WHERE id = $id");
    echo '<script>window.location.href = "reviews.php";</script>';
    exit;
}
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM reviews WHERE id = $id");
    echo '<script>window.location.href = "reviews.php";</script>';
    exit;
}

$reviews = $conn->query("
    SELECT r.*, u.name as user_name, b.title as book_title 
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    JOIN books b ON r.book_id = b.id
    ORDER BY r.created_at DESC
");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div><h1 class="display-6 fw-semibold">Kelola Ulasan Buku</h1><p class="text-muted">Setujui atau tolak ulasan dari pelanggan.</p></div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr><th>ID</th><th>User</th><th>Buku</th><th>Rating</th><th>Komentar</th><th>Status</th><th>Tanggal</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    <?php while($row = $reviews->fetch_assoc()): ?>
                    <tr>
                        <td>#<?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['user_name']) ?></td>
                        <td><?= htmlspecialchars($row['book_title']) ?></td>
                        <td>
                            <?php for($i=1; $i<=5; $i++): ?>
                                <i class="fas fa-star <?= $i <= $row['rating'] ? 'text-warning' : 'text-secondary' ?>"></i>
                            <?php endfor; ?>
                        </td>
                        <td><?= nl2br(htmlspecialchars(substr($row['comment'], 0, 100))) ?>...</td>
                        <td>
                            <span class="badge bg-<?= $row['status'] == 'approved' ? 'success' : ($row['status'] == 'pending' ? 'warning' : 'danger') ?>">
                                <?= ucfirst($row['status']) ?>
                            </span>
                        </td>
                        <td><?= date('d/m/Y', strtotime($row['created_at'])) ?></td>
                        <td>
                            <?php if ($row['status'] == 'pending'): ?>
                                <a href="?approve=<?= $row['id'] ?>" class="btn btn-sm btn-success rounded-pill">Setujui</a>
                                <a href="?reject=<?= $row['id'] ?>" class="btn btn-sm btn-danger rounded-pill">Tolak</a>
                            <?php else: ?>
                                <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger rounded-pill" onclick="return confirm('Hapus ulasan ini?')">Hapus</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>