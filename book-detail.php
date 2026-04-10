<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
require_once 'models/Book.php';
require_once 'models/Cart.php';
require_once 'includes/functions.php';

$bookModel = new Book();
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$book = $bookModel->getById($id);

if (!$book) {
    header('Location: index.php');
    exit();
}

// Proses ulasan
$review_error = '';
$review_success = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    if (!isset($_SESSION['user_id'])) {
        $review_error = "Silakan login terlebih dahulu untuk memberikan ulasan.";
    } else {
        $rating = intval($_POST['rating']);
        $comment = trim($_POST['comment']);
        if ($rating < 1 || $rating > 5) {
            $review_error = "Rating harus antara 1-5.";
        } elseif (empty($comment)) {
            $review_error = "Komentar tidak boleh kosong.";
        } else {
            $user_id = $_SESSION['user_id'];
            $stmt = $conn->prepare("INSERT INTO reviews (book_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiis", $id, $user_id, $rating, $comment);
            if ($stmt->execute()) {
                $review_success = "Terima kasih! Ulasan Anda akan ditampilkan setelah disetujui admin.";
            } else {
                $review_error = "Gagal menyimpan ulasan. Silakan coba lagi.";
            }
        }
    }
}

// Ambil ulasan approved
$reviews = $conn->query("
    SELECT r.*, u.name as user_name 
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    WHERE r.book_id = $id AND r.status = 'approved'
    ORDER BY r.created_at DESC
");

$avgRating = $conn->query("SELECT AVG(rating) as avg, COUNT(*) as total FROM reviews WHERE book_id = $id AND status = 'approved'")->fetch_assoc();
$avg = round($avgRating['avg'] ?? 0, 1);
$totalReviews = $avgRating['total'] ?? 0;

// Cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    $quantity = intval($_POST['quantity']);
    if ($quantity < 1) $quantity = 1;
    if ($quantity > $book['stock']) $quantity = $book['stock'];
    Cart::add($book['id'], $book['title'], $book['price'], $quantity);
    header('Location: cart.php');
    exit();
}

$recommended = $conn->query("
    SELECT id, title, author, price, cover_image 
    FROM books 
    WHERE id != $id AND stock > 0 
    ORDER BY RAND() 
    LIMIT 4
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($book['title']) ?> | CariBuku</title>
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
        .book-cover-large { max-height: 450px; width: auto; max-width: 100%; object-fit: contain; border-radius: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .book-price { font-size: 2rem; font-weight: 700; color: #667eea; }
        .btn-primary-custom { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; border-radius: 60px; padding: 0.75rem 2rem; font-weight: 600; transition: 0.2s; }
        .btn-primary-custom:hover { transform: scale(1.02); box-shadow: 0 5px 15px rgba(102,126,234,0.4); }
        .rating-stars { color: #fbbf24; font-size: 1.2rem; }
        .review-card { background: white; border-radius: 20px; padding: 1rem; margin-bottom: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
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
        <div class="row g-5">
            <div class="col-md-5">
                <div class="text-center">
                    <?php if (hasCover($book['cover_image'])): ?>
                        <img src="<?= getCoverUrl($book['cover_image']) ?>" class="book-cover-large" alt="<?= htmlspecialchars($book['title']) ?>">
                    <?php else: ?>
                        <div class="bg-light d-flex align-items-center justify-content-center" style="height: 400px; border-radius: 24px;">
                            <i class="fas fa-book-open fa-6x text-secondary"></i>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-7">
                <h1 class="fw-bold mb-2"><?= htmlspecialchars($book['title']) ?></h1>
                <p class="text-muted mb-2">
                    <i class="fas fa-user-edit me-2"></i> <?= htmlspecialchars($book['author']) ?>
                    <span class="mx-2">|</span>
                    <i class="fas fa-tag me-2"></i> <?= htmlspecialchars($book['category_name'] ?? 'Umum') ?>
                </p>
                <div class="mb-3">
                    <div class="rating-stars">
                        <?php for($i=1; $i<=5; $i++): ?>
                            <i class="fas fa-star <?= $i <= $avg ? 'text-warning' : 'text-secondary' ?>"></i>
                        <?php endfor; ?>
                        <span class="text-muted ms-2">(<?= $totalReviews ?> ulasan)</span>
                    </div>
                </div>
                <div class="mb-3">
                    <?php if ($book['stock'] > 0): ?>
                        <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Stok tersisa: <?= $book['stock'] ?></span>
                    <?php else: ?>
                        <span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i> Stok Habis</span>
                    <?php endif; ?>
                </div>
                <div class="book-price mb-3">Rp <?= number_format($book['price'], 0, ',', '.') ?></div>
                <div class="mb-4">
                    <h5 class="fw-semibold">Deskripsi Buku</h5>
                    <p class="text-secondary"><?= nl2br(htmlspecialchars($book['description'] ?: 'Tidak ada deskripsi untuk buku ini.')) ?></p>
                </div>
                <?php if ($book['stock'] > 0): ?>
                <form method="POST" class="mt-4">
                    <div class="row g-3 align-items-center">
                        <div class="col-auto">
                            <label class="fw-semibold">Jumlah</label>
                            <input type="number" name="quantity" value="1" min="1" max="<?= $book['stock'] ?>" class="form-control" style="width: 80px;">
                        </div>
                        <div class="col-auto">
                            <button type="submit" name="add_to_cart" class="btn btn-primary-custom text-white">Tambah ke Keranjang</button>
                        </div>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Ulasan -->
        <div class="row mt-5">
            <div class="col-md-8">
                <h4 class="fw-bold mb-3">Ulasan Pembaca</h4>
                <?php if ($reviews && $reviews->num_rows > 0): ?>
                    <?php while($rev = $reviews->fetch_assoc()): ?>
                        <div class="review-card">
                            <div class="d-flex justify-content-between">
                                <strong><?= htmlspecialchars($rev['user_name']) ?></strong>
                                <div class="rating-stars">
                                    <?php for($i=1; $i<=5; $i++): ?>
                                        <i class="fas fa-star <?= $i <= $rev['rating'] ? 'text-warning' : 'text-secondary' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <p class="mt-2"><?= nl2br(htmlspecialchars($rev['comment'])) ?></p>
                            <small class="text-muted"><?= date('d/m/Y', strtotime($rev['created_at'])) ?></small>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-muted">Belum ada ulasan untuk buku ini. Jadilah yang pertama!</p>
                <?php endif; ?>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-transparent fw-semibold">Beri Ulasan</div>
                    <div class="card-body">
                        <?php if ($review_success): ?>
                            <div class="alert alert-success"><?= $review_success ?></div>
                        <?php endif; ?>
                        <?php if ($review_error): ?>
                            <div class="alert alert-danger"><?= $review_error ?></div>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Rating</label>
                                    <select name="rating" class="form-select" required>
                                        <option value="5">★★★★★ (5)</option>
                                        <option value="4">★★★★☆ (4)</option>
                                        <option value="3">★★★☆☆ (3)</option>
                                        <option value="2">★★☆☆☆ (2)</option>
                                        <option value="1">★☆☆☆☆ (1)</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Komentar</label>
                                    <textarea name="comment" rows="4" class="form-control" required placeholder="Tulis pengalaman Anda membaca buku ini..."></textarea>
                                </div>
                                <button type="submit" name="submit_review" class="btn btn-primary-custom w-100">Kirim Ulasan</button>
                            </form>
                        <?php else: ?>
                            <p class="text-muted">Silakan <a href="login.php">login</a> untuk memberikan ulasan.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rekomendasi -->
        <?php if ($recommended && $recommended->num_rows > 0): ?>
        <div class="mt-5">
            <h4 class="fw-bold">Buku Lain yang Mungkin Anda Suka</h4>
            <div class="row g-4 mt-2">
                <?php while($rec = $recommended->fetch_assoc()): ?>
                    <div class="col-md-3">
                        <div class="card h-100 border-0 shadow-sm rounded-4">
                            <div class="card-body text-center">
                                <?php if (hasCover($rec['cover_image'])): ?>
                                    <img src="<?= getCoverUrl($rec['cover_image']) ?>" style="height: 120px; object-fit: cover; border-radius: 12px;">
                                <?php else: ?>
                                    <i class="fas fa-book-open fa-3x text-secondary"></i>
                                <?php endif; ?>
                                <h6 class="mt-2 fw-bold"><?= htmlspecialchars($rec['title']) ?></h6>
                                <p class="small text-muted"><?= htmlspecialchars($rec['author']) ?></p>
                                <div class="fw-bold text-primary">Rp <?= number_format($rec['price'], 0, ',', '.') ?></div>
                                <a href="book-detail.php?id=<?= $rec['id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill mt-2">Lihat</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<footer class="footer">
    <div class="container text-center">&copy; <?= date('Y') ?> CariBuku. All rights reserved.</div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>