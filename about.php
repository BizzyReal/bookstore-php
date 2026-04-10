<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/functions.php';
require_once 'models/Message.php';

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
    <title>Tentang CariBuku | Toko Buku Online</title>
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
        .main-content { flex: 1; }
        .footer { background: #0f172a; color: #cbd5e1; padding: 3rem 0 1.5rem; margin-top: 3rem; }
        .footer a { color: #94a3b8; text-decoration: none; }
        .footer a:hover { color: #3b82f6; }
        .about-hero { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); border-radius: 32px; padding: 3rem 2rem; margin-bottom: 2rem; color: white; }
        .about-hero h1 { font-weight: 800; font-size: 2.5rem; margin-bottom: 1rem; }
        .stat-number { font-size: 2rem; font-weight: 800; color: #3b82f6; }
        .mission-card, .vision-card { transition: transform 0.2s; }
        .mission-card:hover, .vision-card:hover { transform: translateY(-5px); }
        .team-img { width: 120px; height: 120px; object-fit: cover; border-radius: 50%; border: 3px solid #3b82f6; margin-bottom: 1rem; }
        .value-icon { font-size: 2rem; color: #3b82f6; margin-bottom: 1rem; }
        .floating-chat { position: fixed; bottom: 20px; right: 20px; width: 60px; height: 60px; background: #3b82f6; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 28px; box-shadow: 0 4px 12px rgba(0,0,0,0.2); transition: 0.2s; z-index: 1000; text-decoration: none; }
        .floating-chat:hover { background: #2563eb; transform: scale(1.05); color: white; }
        @media (max-width: 768px) {
            .search-form { width: 100%; margin-top: 0.5rem; }
            .about-hero h1 { font-size: 1.8rem; }
        }
    </style>
</head>
<body>

<!-- Navbar (sama persis dengan index.php) -->
<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand" href="index.php"><i class="fas fa-book-open me-2"></i> CariBuku</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Beranda</a></li>
                <li class="nav-item"><a class="nav-link active" href="about.php">Tentang</a></li>
                <li class="nav-item"><a class="nav-link" href="search.php">Semua Buku</a></li>
            </ul>
            <form class="d-flex mx-auto search-form" method="GET" action="search.php">
                <div class="input-group">
                    <input class="form-control search-input" type="search" name="q" placeholder="Cari judul atau penulis...">
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
    <div class="container">
        <!-- Hero Section -->
        <div class="about-hero">
            <h1>Tentang CariBuku</h1>
            <p class="lead">Lebih dari sekadar toko buku, kami adalah teman perjalanan literasi Anda.</p>
        </div>

        <!-- Cerita Kami -->
        <div class="row align-items-center mb-5">
            <div class="col-md-6">
                <h2 class="fw-bold mb-3">Cerita Kami</h2>
                <p>CariBuku lahir dari kecintaan terhadap buku dan keinginan untuk membuat buku lebih mudah diakses. Didirikan pada tahun 2020, CariBuku telah berkembang menjadi salah satu toko buku online terpercaya di Indonesia.</p>
                <p>Hingga saat ini, kami telah melayani lebih dari <strong>ribuan pelanggan setia</strong> dengan koleksi lebih dari <strong>ratusan judul buku</strong> dari berbagai penerbit ternama.</p>
            </div>
            <div class="col-md-6 text-center">
                <img src="assets/images/about-story.svg" alt="Our Story" class="img-fluid" style="max-height: 300px;" onerror="this.src='https://placehold.co/500x300?text=📖+CariBuku'">
            </div>
        </div>


        <!-- Visi & Misi -->
        <div class="row g-4 mb-5">
            <div class="col-md-6">
                <div class="card vision-card h-100 p-4 text-center shadow-sm border-0 rounded-4">
                    <i class="fas fa-eye fa-3x text-primary mb-3"></i>
                    <h3>Visi</h3>
                    <p>Menjadi ekosistem literasi terdepan di Indonesia yang menghubungkan pembaca dengan buku berkualitas.</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card mission-card h-100 p-4 shadow-sm border-0 rounded-4">
                    <i class="fas fa-bullseye fa-3x text-primary mb-3 text-center d-block"></i>
                    <h3 class="text-center">Misi</h3>
                    <ul class="text-start mt-3">
                        <li>Menyediakan koleksi buku terlengkap dari berbagai genre</li>
                        <li>Memberikan harga terbaik dan promosi menarik untuk pelanggan</li>
                        <li>Membangun komunitas pembaca yang aktif melalui event dan diskon khusus</li>
                        <li>Mendukung literasi nasional dengan program donasi buku untuk sekolah</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Nilai Kami -->
        <div class="text-center mb-4">
            <h2 class="fw-bold">Nilai Kami</h2>
            <p class="lead">Prinsip yang kami pegang dalam melayani Anda</p>
        </div>
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="card h-100 text-center p-4 border-0 shadow-sm rounded-4">
                    <i class="fas fa-handshake value-icon"></i>
                    <h5>Integritas</h5>
                    <p>Kami menjamin keaslian buku dan kejujuran dalam setiap transaksi.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 text-center p-4 border-0 shadow-sm rounded-4">
                    <i class="fas fa-heart value-icon"></i>
                    <h5>Pelayanan</h5>
                    <p>Customer service ramah dan siap membantu 24/7.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 text-center p-4 border-0 shadow-sm rounded-4">
                    <i class="fas fa-leaf value-icon"></i>
                    <h5>Inovasi</h5>
                    <p>Terus berinovasi untuk pengalaman berbelanja yang lebih baik.</p>
                </div>
            </div>
        </div>


        <!-- Alamat & Kontak -->
        <div class="row g-4 mb-5">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                    <h3 class="fw-bold mb-3"><i class="fas fa-map-marker-alt text-primary"></i> Alamat Kami</h3>
                    <p>Jl. Buku No. 123, Kelurahan Literasi,<br>Kecamatan Membaca, Jakarta Selatan 12345</p>
                    <hr>
                    <p><i class="fas fa-phone-alt text-primary"></i> <strong>Telepon:</strong> +62 813 8722 1775</p>
                    <p><i class="fas fa-envelope text-primary"></i> <strong>Email:</strong> support@caribuku.com</p>
                    <p><i class="fas fa-clock text-primary"></i> <strong>Jam Operasional:</strong> Senin - Minggu, 08.00 - 21.00</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3966.521260322283!2d106.828561!3d-6.200000!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69f391c71cc489%3A0x2334c2f5b3c2f4c1!2sJakarta!5e0!3m2!1sen!2sid!4v1690000000000!5m2!1sen!2sid" width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                    <div class="p-3 text-center bg-light">
                        <a href="search.php" class="btn btn-primary rounded-pill">Mulai Belanja</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Floating Chat Button -->
<a href="inbox.php" class="floating-chat">
    <i class="fas fa-comment-dots"></i>
</a>

<!-- Footer (sama persis dengan index.php) -->
<footer class="footer">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <h5 class="text-white fw-bold">CariBuku</h5>
                <p>Toko buku online terlengkap dan terpercaya.</p>
            </div>
            <div class="col-md-4">
                <h5 class="text-white fw-bold">Tautan</h5>
                <ul class="list-unstyled">
                    <li><a href="index.php">Beranda</a></li>
                    <li><a href="about.php">Tentang Kami</a></li>
                    <li><a href="search.php">Semua Buku</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h5 class="text-white fw-bold">Kontak</h5>
                <p><i class="fas fa-envelope me-2"></i> support@caribuku.com</p>
                <p><i class="fas fa-phone me-2"></i> +62 813 8722 1775</p>
                <p>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="inbox.php" class="text-white text-decoration-none"><i class="fas fa-comment me-2"></i> Hubungi Admin</a>
                    <?php else: ?>
                        <a href="login.php" class="text-white text-decoration-none"><i class="fas fa-sign-in-alt me-2"></i> Login untuk hubungi admin</a>
                    <?php endif; ?>
                </p>
            </div>
        </div>
        <hr class="bg-secondary">
        <div class="text-center">&copy; <?= date('Y') ?> CariBuku. All rights reserved.</div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>