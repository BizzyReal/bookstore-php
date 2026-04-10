<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Message.php';

// Notifikasi chat belum dibaca
$msgModel = new Message();
$unreadChatCount = $msgModel->countUnread($_SESSION['user_id']);

// Notifikasi pesanan pending
$pendingOrdersCount = 0;
$pendingResult = $conn->query("SELECT COUNT(*) as total FROM orders WHERE status = 'pending'");
if ($pendingResult) {
    $pendingOrdersCount = $pendingResult->fetch_assoc()['total'];
}

// Notifikasi ulasan pending
$pendingReviewsCount = 0;
$reviewResult = $conn->query("SELECT COUNT(*) as total FROM reviews WHERE status = 'pending'");
if ($reviewResult) {
    $pendingReviewsCount = $reviewResult->fetch_assoc()['total'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CariBuku</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: #f0f2f5; margin: 0; padding: 0; }
        .sidebar {
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            min-height: 100vh;
            width: 280px;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 100;
            box-shadow: 2px 0 12px rgba(0,0,0,0.08);
            transition: all 0.3s;
        }
        .sidebar .brand { padding: 24px 20px; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 20px; }
        .sidebar .brand h3 { color: white; font-weight: 600; margin: 0; letter-spacing: -0.5px; }
        .sidebar .brand span { font-size: 12px; color: #94a3b8; }
        .sidebar .nav-link {
            color: #cbd5e1;
            padding: 10px 20px;
            margin: 2px 12px;
            border-radius: 12px;
            transition: all 0.2s;
            font-weight: 500;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            white-space: nowrap;
        }
        .sidebar .nav-link i { width: 28px; margin-right: 12px; font-size: 1.1rem; flex-shrink: 0; }
        .sidebar .nav-link .menu-text {
            flex: 1;
            text-align: left;
        }
        .sidebar .nav-link .badge {
            margin-left: 8px;
            flex-shrink: 0;
        }
        .sidebar .nav-link:hover { background: rgba(255,255,255,0.1); color: white; transform: translateX(4px); }
        .sidebar .nav-link.active { background: #3b82f6; color: white; box-shadow: 0 4px 8px rgba(59,130,246,0.3); }
        .main-content { margin-left: 280px; padding: 24px 32px; }
        .stat-card { background: white; border: none; border-radius: 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); transition: transform 0.2s, box-shadow 0.2s; overflow: hidden; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 12px 24px rgba(0,0,0,0.1); }
        .stat-card .card-body { padding: 1.5rem; }
        .stat-icon { width: 54px; height: 54px; border-radius: 18px; display: flex; align-items: center; justify-content: center; font-size: 28px; color: white; }
        .stat-title { font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; color: #64748b; }
        .stat-number { font-size: 2.2rem; font-weight: 700; color: #1e293b; line-height: 1.2; }
        .quick-btn { border-radius: 16px; padding: 12px 20px; font-weight: 600; transition: all 0.2s; }
        .quick-btn:hover { transform: scale(1.02); }
        @media (max-width: 768px) {
            .sidebar { width: 100%; position: relative; min-height: auto; }
            .main-content { margin-left: 0; padding: 16px; }
        }
    </style>
</head>
<body>
<div class="sidebar">
    <div class="brand">
        <h3><i class="fas fa-book-open me-2"></i>CariBuku</h3>
        <span>Admin Panel</span>
    </div>
    <nav class="nav flex-column">
        <a href="index.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
            <i class="fas fa-tachometer-alt"></i>
            <span class="menu-text">Dashboard</span>
        </a>
        <a href="categories.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : '' ?>">
            <i class="fas fa-tags"></i>
            <span class="menu-text">Kategori</span>
        </a>
        <a href="books.php" class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'books.php' || basename($_SERVER['PHP_SELF']) == 'book_form.php') ? 'active' : '' ?>">
            <i class="fas fa-book"></i>
            <span class="menu-text">Buku</span>
        </a>
        <a href="users.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : '' ?>">
            <i class="fas fa-users"></i>
            <span class="menu-text">User</span>
        </a>
        <a href="orders.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : '' ?>">
            <i class="fas fa-shopping-cart"></i>
            <span class="menu-text">Pesanan</span>
            <?php if($pendingOrdersCount > 0): ?>
                <span class="badge bg-danger rounded-pill"><?= $pendingOrdersCount ?></span>
            <?php endif; ?>
        </a>
        <a href="chat.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'chat.php' ? 'active' : '' ?>">
            <i class="fas fa-comments"></i>
            <span class="menu-text">Pesan Pelanggan</span>
            <?php if($unreadChatCount > 0): ?>
                <span class="badge bg-danger rounded-pill"><?= $unreadChatCount ?></span>
            <?php endif; ?>
        </a>
        <a href="reviews.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'reviews.php' ? 'active' : '' ?>">
            <i class="fas fa-star"></i>
            <span class="menu-text">Ulasan Buku</span>
            <?php if($pendingReviewsCount > 0): ?>
                <span class="badge bg-danger rounded-pill"><?= $pendingReviewsCount ?></span>
            <?php endif; ?>
        </a>
        <a href="../logout.php" class="nav-link">
            <i class="fas fa-sign-out-alt"></i>
            <span class="menu-text">Logout</span>
        </a>
    </nav>
</div>
<div class="main-content">