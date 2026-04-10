<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'models/Message.php';

// Pagination & filter
$limit = 12;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Jika parameter 'all' ada, maka tampilkan semua buku tanpa kata kunci
$show_all = isset($_GET['all']) && $_GET['all'] == 1;
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
if ($show_all) $search = ''; // abaikan q jika all=1

$category_id = isset($_GET['category']) ? intval($_GET['category']) : 0;

$where = [];
if (!empty($search)) $where[] = "(title LIKE '%$search%' OR author LIKE '%$search%')";
if ($category_id) $where[] = "category_id = $category_id";
$whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

$totalResult = $conn->query("SELECT COUNT(*) as total FROM books $whereClause");
$totalRows = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

$books = $conn->query("
    SELECT books.*, categories.name as category_name 
    FROM books 
    LEFT JOIN categories ON books.category_id = categories.id 
    $whereClause 
    ORDER BY books.id DESC 
    LIMIT $offset, $limit
");

// Ambil kategori untuk filter
$categories = $conn->query("SELECT id, name FROM categories ORDER BY name");

// Notifikasi pesan (untuk badge)
$unreadMessages = 0;
if (isset($_SESSION['user_id'])) {
    $msgModel = new Message();
    $unreadMessages = $msgModel->countUnread($_SESSION['user_id']);
}

// Judul halaman
if ($show_all) {
    $page_title = "Semua Buku";
} elseif (!empty($search)) {
    $page_title = "Hasil pencarian: " . htmlspecialchars($search);
} else {
    $page_title = "Koleksi Buku";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - CariBuku</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: #f8fafc; display: flex; flex-direction: column; min-height: 100vh; }
        .navbar { background: rgba(255,255,255,0.98); backdrop-filter: blur(10px); box-shadow: 0 1px 3px rgba(0,0,0,0.05); padding: 1rem 0; }
        .navbar-brand { font-weight: 800; font-size: 1.8rem; color: #1e293b !important; }
        .navbar-brand i { color: #3b82f6; }
        .nav-link { color: #475569 !important; font-weight: 500; }
        .nav-link:hover { color: #3b82f6 !important; }
        .search-form { width: 300px; }
        .search-input { border-radius: 60px; border: 1px solid #e2e8f0; padding: 0.5rem 1rem; font-size: 0.9rem; }
        .search-btn { border-radius: 60px; padding: 0.5rem 1.2rem; background: #3b82f6; border: none; color: white; font-weight: 500; }
        .search-btn:hover { background: #2563eb; }
        .main-content { flex: 1; }
        .footer { background: #0f172a; color: #cbd5e1; padding: 2rem 0; margin-top: 2rem; }
        .footer a { color: #94a3b8; text-decoration: none; }
        .footer a:hover { color: #3b82f6; }
        .book-card { background: white; border: none; border-radius: 24px; overflow: hidden; transition: all 0.3s; box-shadow: 0 4px 12px rgba(0,0,0,0.05); height: 100%; }
        .book-card:hover { transform: translateY(-5px); box-shadow: 0 20px 30px -12px rgba(0,0,0,0.15); }
        .book-cover { height: 240px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; object-fit: cover; width: 100%; }
        .book-title { font-weight: 700; font-size: 1.1rem; margin-bottom: 0.25rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .book-author { color: #64748b; font-size: 0.85rem; margin-bottom: 0.5rem; }
        .book-price { font-weight: 700; font-size: 1.2rem; color: #3b82f6; }
        .btn-outline-primary-custom { border-radius: 40px; border: 1px solid #3b82f6; color: #3b82f6; font-weight: 500; padding: 0.4rem 0; transition: 0.2s; }
        .btn-outline-primary-custom:hover { background: #3b82f6; color: white; }
        .category-filter-wrapper { overflow-x: auto; white-space: nowrap; margin-bottom: 1.5rem; }
        .category-filter { display: inline-flex; gap: 0.5rem; background: white; border-radius: 60px; padding: 0.5rem 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.03); }
        .category-badge { background: #eef2f6; padding: 0.5rem 1.2rem; border-radius: 40px; text-decoration: none; color: #2c3e66; font-weight: 500; transition: 0.2s; font-size: 0.85rem; display: inline-block; }
        .category-badge:hover, .category-badge.active { background: #3b82f6; color: white; transform: translateY(-2px); }
        .pagination .page-link { border-radius: 30px; margin: 0 4px; color: #1e293b; }
        .pagination .active .page-link { background: #3b82f6; border-color: #3b82f6; color: white; }
        @media (max-width: 768px) {
            .search-form { width: 100%; margin-top: 0.5rem; }
            .book-cover { height: 180px; }
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
            <form class="d-flex mx-auto search-form" method="GET" action="search.php">
                <div class="input-group">
                    <input class="form-control search-input" type="search" name="q" placeholder="Cari judul atau penulis..." value="<?= htmlspecialchars($search) ?>">
                    <button class="btn search-btn" type="submit"><i class="fas fa-search"></i></button>
                </div>
            </form>
            <ul class="navbar-nav ms-auto">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown"><i class="fas fa-user-circle"></i> <?= htmlspecialchars($_SESSION['name']) ?></a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="cart.php"><i class="fas fa-shopping-cart"></i> Keranjang <span class="badge bg-primary rounded-pill"><?= getCartCount() ?></span></a></li>
                            <li><a class="dropdown-item" href="my_orders.php"><i class="fas fa-list"></i> Pesanan Saya</a></li>
                            <li><a class="dropdown-item" href="inbox.php"><i class="fas fa-envelope"></i> Pesan <?php if($unreadMessages > 0): ?><span class="badge bg-danger ms-1"><?= $unreadMessages ?></span><?php endif; ?></a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="login.php"><i class="fas fa-sign-in-alt me-1"></i> Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="register.php"><i class="fas fa-user-plus me-1"></i> Daftar</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="main-content">
    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold"><i class="fas fa-book me-2 text-primary"></i> <?= $page_title ?></h2>
            <a href="index.php" class="btn btn-outline-secondary rounded-pill"><i class="fas fa-home me-2"></i>Kembali ke Beranda</a>
        </div>

        <!-- Filter Kategori -->
        <div class="category-filter-wrapper">
            <div class="category-filter">
                <?php if ($show_all): ?>
                    <a href="?all=1" class="category-badge active">📚 Semua</a>
                <?php else: ?>
                    <a href="?<?= !empty($search) ? 'q='.urlencode($search).'&' : '' ?>all=1" class="category-badge <?= !$category_id ? 'active' : '' ?>">📚 Semua</a>
                <?php endif; ?>
                <?php 
                $categories->data_seek(0);
                while($cat = $categories->fetch_assoc()): 
                    $active = ($category_id == $cat['id']);
                    $url = "?category={$cat['id']}";
                    if (!empty($search)) $url .= "&q=" . urlencode($search);
                    if ($show_all) $url .= "&all=1";
                ?>
                    <a href="<?= $url ?>" class="category-badge <?= $active ? 'active' : '' ?>"><?= htmlspecialchars($cat['name']) ?></a>
                <?php endwhile; ?>
            </div>
        </div>

        <?php if ($totalRows == 0): ?>
            <div class="alert alert-info text-center py-5 rounded-4">
                <i class="fas fa-book-open fa-4x mb-3"></i>
                <h4>Tidak ada buku ditemukan</h4>
                <p>Coba kata kunci lain atau lihat koleksi buku kami.</p>
                <a href="search.php?all=1" class="btn btn-primary rounded-pill">Lihat Semua Buku</a>
            </div>
        <?php else: ?>
            <p class="text-muted mb-3">Menampilkan <?= $totalRows ?> buku</p>
            <div class="row g-4">
                <?php while($book = $books->fetch_assoc()): ?>
                    <div class="col-md-3 col-sm-6">
                        <div class="book-card">
                            <div class="book-cover">
                                <?php if (hasCover($book['cover_image'])): ?>
                                    <img src="<?= getCoverUrl($book['cover_image']) ?>" class="book-cover" alt="<?= htmlspecialchars($book['title']) ?>">
                                <?php else: ?>
                                    <i class="fas fa-book-open fa-4x text-secondary"></i>
                                <?php endif; ?>
                            </div>
                            <div class="p-3">
                                <h5 class="book-title"><?= htmlspecialchars($book['title']) ?></h5>
                                <div class="book-author"><?= htmlspecialchars($book['author']) ?></div>
                                <div class="book-price">Rp <?= number_format($book['price'], 0, ',', '.') ?></div>
                                <div class="mt-2">
                                    <a href="book-detail.php?id=<?= $book['id'] ?>" class="btn btn-outline-primary-custom w-100">Lihat Detail</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <nav class="mt-5">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<footer class="footer">
    <div class="container text-center">&copy; <?= date('Y') ?> CariBuku. All rights reserved.</div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>