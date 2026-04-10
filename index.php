<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'models/Message.php';

// Statistik
$totalBooksAll = $conn->query("SELECT COUNT(*) as total FROM books")->fetch_assoc()['total'];
$totalCategoriesAll = $conn->query("SELECT COUNT(*) as total FROM categories")->fetch_assoc()['total'];
$totalCustomers = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='user'")->fetch_assoc()['total'];

// Buku terbaru
$latestBooks = $conn->query("
    SELECT books.*, categories.name as category_name 
    FROM books 
    LEFT JOIN categories ON books.category_id = categories.id 
    ORDER BY books.id DESC 
    LIMIT 8
");

// Rekomendasi
$recommended = $conn->query("
    SELECT books.*, categories.name as category_name 
    FROM books 
    LEFT JOIN categories ON books.category_id = categories.id 
    WHERE books.stock > 0 
    ORDER BY RAND() 
    LIMIT 4
");

// Kategori populer
$popularCategories = $conn->query("
    SELECT c.id, c.name, COUNT(b.id) as book_count
    FROM categories c
    LEFT JOIN books b ON c.id = b.category_id
    GROUP BY c.id
    ORDER BY book_count DESC
    LIMIT 6
");

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
    <title>CariBuku | Toko Buku Online Terpercaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: #f8fafc; display: flex; flex-direction: column; min-height: 100vh; }
        .navbar { background: rgba(255,255,255,0.98); backdrop-filter: blur(10px); box-shadow: 0 1px 3px rgba(0,0,0,0.05); padding: 1rem 0; }
        .navbar-brand { font-weight: 800; font-size: 1.8rem; color: #1e293b !important; letter-spacing: -0.5px; }
        .navbar-brand i { color: #3b82f6; }
        .nav-link { color: #475569 !important; font-weight: 500; transition: 0.2s; }
        .nav-link:hover { color: #3b82f6 !important; }
        .search-form { width: 300px; }
        .search-input { border-radius: 60px; border: 1px solid #e2e8f0; padding: 0.5rem 1rem; font-size: 0.9rem; }
        .search-btn { border-radius: 60px; padding: 0.5rem 1.2rem; background: #3b82f6; border: none; color: white; font-weight: 500; }
        .search-btn:hover { background: #2563eb; }
        .hero { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); border-radius: 32px; padding: 3rem 2rem; margin: 1.5rem 0 2rem; color: white; position: relative; overflow: hidden; }
        .hero::before { content: ''; position: absolute; top: -50%; right: -20%; width: 300px; height: 300px; background: rgba(59,130,246,0.2); border-radius: 50%; pointer-events: none; }
        .hero h1 { font-weight: 800; font-size: 2.5rem; margin-bottom: 1rem; }
        .hero p { font-size: 1.1rem; opacity: 0.9; margin-bottom: 1.5rem; }
        .hero-stats { background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); border-radius: 24px; padding: 1rem; text-align: center; }
        .hero-stats .number { font-size: 1.8rem; font-weight: 800; }
        .section-title { font-weight: 700; font-size: 1.8rem; margin-bottom: 1.5rem; position: relative; display: inline-block; }
        .section-title:after { content: ''; position: absolute; bottom: -8px; left: 0; width: 60px; height: 3px; background: #3b82f6; border-radius: 3px; }
        .book-card { background: white; border: none; border-radius: 24px; overflow: hidden; transition: all 0.3s ease; box-shadow: 0 4px 12px rgba(0,0,0,0.05); height: 100%; }
        .book-card:hover { transform: translateY(-8px); box-shadow: 0 20px 30px -12px rgba(0,0,0,0.15); }
        .book-cover { height: 240px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; object-fit: cover; width: 100%; }
        .book-title { font-weight: 700; font-size: 1.1rem; margin-bottom: 0.25rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .book-author { color: #64748b; font-size: 0.85rem; margin-bottom: 0.5rem; }
        .book-price { font-weight: 700; font-size: 1.2rem; color: #3b82f6; }
        .btn-outline-primary-custom { border-radius: 40px; border: 1px solid #3b82f6; color: #3b82f6; font-weight: 500; padding: 0.4rem 0; transition: 0.2s; }
        .btn-outline-primary-custom:hover { background: #3b82f6; color: white; }
        .promo-banner { background: linear-gradient(90deg, #fef08a 0%, #fde047 100%); border-radius: 20px; padding: 1rem; margin: 2rem 0; text-align: center; font-weight: 600; color: #854d0e; }
        .category-icon { background: white; padding: 1rem; border-radius: 20px; text-align: center; transition: 0.2s; }
        .category-icon:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .footer { background: #0f172a; color: #cbd5e1; padding: 3rem 0 1.5rem; margin-top: 3rem; }
        .footer a { color: #94a3b8; text-decoration: none; }
        .footer a:hover { color: #3b82f6; }
        .floating-chat { position: fixed; bottom: 20px; right: 20px; width: 60px; height: 60px; background: #3b82f6; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 28px; box-shadow: 0 4px 12px rgba(0,0,0,0.2); transition: 0.2s; z-index: 1000; text-decoration: none; }
        .floating-chat:hover { background: #2563eb; transform: scale(1.05); color: white; }
        @media (max-width: 768px) {
            .search-form { width: 100%; margin-top: 0.5rem; }
            .hero h1 { font-size: 1.8rem; }
            .hero-stats .number { font-size: 1.2rem; }
            .book-cover { height: 180px; }
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand" href="index.php"><i class="fas fa-book-open me-2"></i> CariBuku</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarMain">
            <!-- Menu kiri -->
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Beranda</a></li>
                <li class="nav-item"><a class="nav-link" href="about.php">Tentang</a></li>
                <li class="nav-item"><a class="nav-link" href="search.php">Semua Buku</a></li>
            </ul>
            <!-- Pencarian -->
            <form class="d-flex mx-auto search-form" method="GET" action="search.php">
                <div class="input-group">
                    <input class="form-control search-input" type="search" name="q" placeholder="Cari judul atau penulis...">
                    <button class="btn search-btn" type="submit"><i class="fas fa-search"></i></button>
                </div>
            </form>
            <!-- Menu kanan (user) -->
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
    <div class="container">
        <!-- Hero -->
        <div class="hero">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <h1>Temukan Buku Favoritmu</h1>
                    <p>Ribuan koleksi buku terbaik dari berbagai genre. Nikmati promo menarik setiap harinya dan gratis ongkir untuk pembelian di atas Rp150.000.</p>
                    <div class="d-flex gap-3">
                        <a href="#latest-books" class="btn btn-light rounded-pill px-4">Mulai Belanja</a>
                        <a href="#recommended" class="btn btn-outline-light rounded-pill px-4">Lihat Rekomendasi</a>
                    </div>
                </div>
                <div class="col-lg-5 mt-4 mt-lg-0">
                    <div class="row g-3 text-center">
                        <div class="col-4"><div class="hero-stats"><div class="number"><?= $totalBooksAll ?></div><div class="small">Judul Buku</div></div></div>
                        <div class="col-4"><div class="hero-stats"><div class="number"><?= $totalCategoriesAll ?></div><div class="small">Kategori</div></div></div>
                        <div class="col-4"><div class="hero-stats"><div class="number"><?= $totalCustomers ?></div><div class="small">Pelanggan</div></div></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kategori Populer -->
        <?php if ($popularCategories && $popularCategories->num_rows > 0): ?>
        <div class="mb-5">
            <h2 class="section-title">Kategori Populer</h2>
            <div class="row g-4 mt-2">
                <?php $icons = ['fas fa-book-open', 'fas fa-chart-line', 'fas fa-laptop-code', 'fas fa-flask', 'fas fa-heartbeat', 'fas fa-globe']; $i=0; while($cat = $popularCategories->fetch_assoc()): ?>
                <div class="col-md-2 col-4 text-center">
                    <a href="search.php?category=<?= $cat['id'] ?>" class="text-decoration-none">
                        <div class="category-icon">
                            <i class="<?= $icons[$i % count($icons)] ?> fa-3x text-primary"></i>
                            <p class="mt-2 mb-0 fw-semibold"><?= htmlspecialchars($cat['name']) ?></p>
                        </div>
                    </a>
                </div>
                <?php $i++; endwhile; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Promo Banner -->
        <div class="promo-banner">
            <i class="fas fa-truck-fast me-2"></i> Gratis ongkir minimal belanja Rp150.000 | 
            <i class="fas fa-tag ms-3 me-2"></i> Diskon 20% untuk member baru | 
            <i class="fas fa-credit-card ms-3 me-2"></i> Bayar di tempat (COD) atau Transfer
        </div>

        <!-- Buku Terbaru -->
        <div id="latest-books" class="mb-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="section-title">Buku Terbaru</h2>
                <a href="search.php" class="text-decoration-none">Lihat semua →</a>
            </div>
            <div class="row g-4">
                <?php if ($latestBooks && $latestBooks->num_rows > 0): ?>
                    <?php while($book = $latestBooks->fetch_assoc()): ?>
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
                <?php else: ?>
                    <div class="col-12 text-muted">Belum ada buku.</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Rekomendasi -->
        <div id="recommended" class="mb-5">
            <h2 class="section-title">Rekomendasi untuk Anda</h2>
            <div class="row g-4 mt-2">
                <?php if ($recommended && $recommended->num_rows > 0): ?>
                    <?php while($rec = $recommended->fetch_assoc()): ?>
                        <div class="col-md-3 col-sm-6">
                            <div class="book-card">
                                <div class="book-cover">
                                    <?php if (hasCover($rec['cover_image'])): ?>
                                        <img src="<?= getCoverUrl($rec['cover_image']) ?>" class="book-cover" alt="<?= htmlspecialchars($rec['title']) ?>">
                                    <?php else: ?>
                                        <i class="fas fa-book-open fa-4x text-secondary"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="p-3">
                                    <h5 class="book-title"><?= htmlspecialchars($rec['title']) ?></h5>
                                    <div class="book-author"><?= htmlspecialchars($rec['author']) ?></div>
                                    <div class="book-price">Rp <?= number_format($rec['price'], 0, ',', '.') ?></div>
                                    <div class="mt-2">
                                        <a href="book-detail.php?id=<?= $rec['id'] ?>" class="btn btn-outline-primary-custom w-100">Lihat Detail</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12 text-muted">Belum ada rekomendasi.</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quotes -->
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="bg-white p-4 rounded-4 text-center shadow-sm h-100">
                    <i class="fas fa-quote-right fa-2x text-primary opacity-50 mb-3"></i>
                    <p class="fst-italic">"Membaca adalah jendela dunia. Semakin banyak kau membaca, semakin luas kau memandang."</p>
                    <p class="fw-bold text-primary mb-0">— Najwa Shihab</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="bg-white p-4 rounded-4 text-center shadow-sm h-100">
                    <i class="fas fa-quote-right fa-2x text-primary opacity-50 mb-3"></i>
                    <p class="fst-italic">"Buku adalah teman terbaik. Ia tak pernah mengkhianati, selalu siap memberi ilmu kapan saja."</p>
                    <p class="fw-bold text-primary mb-0">— Pramoedya Ananta Toer</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="bg-white p-4 rounded-4 text-center shadow-sm h-100">
                    <i class="fas fa-quote-right fa-2x text-primary opacity-50 mb-3"></i>
                    <p class="fst-italic">"Membaca satu jam sehari akan membuatmu menjadi ahli dalam tiga tahun."</p>
                    <p class="fw-bold text-primary mb-0">— Soekarno</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Floating Chat Button -->
<a href="inbox.php" class="floating-chat">
    <i class="fas fa-comment-dots"></i>
</a>

<?php require_once 'includes/footer.php'; ?>