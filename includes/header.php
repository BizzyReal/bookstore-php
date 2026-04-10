<?php
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}
// Hitung notifikasi untuk badge
require_once '../config/database.php';
$result = $conn->query("SELECT COUNT(*) as total FROM contacts WHERE status='unread'");
$unreadMessages = $result->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - CariBuku</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #f4f6f9; }
        .sidebar { background: #2c3e50; min-height: 100vh; position: fixed; top: 0; left: 0; width: 250px; }
        .sidebar a { color: #ecf0f1; text-decoration: none; padding: 12px 20px; display: block; transition: 0.3s; }
        .sidebar a:hover, .sidebar a.active { background: #1abc9c; }
        .sidebar i { width: 25px; margin-right: 10px; }
        .content { margin-left: 250px; padding: 20px; }
        @media (max-width: 768px) {
            .sidebar { position: relative; width: 100%; min-height: auto; }
            .content { margin-left: 0; }
        }
    </style>
</head>
<body>
<div class="container-fluid p-0">
    <div class="row g-0">
        <div class="col-auto sidebar p-0">
            <div class="text-center py-4">
                <h4 class="text-white">CariBuku Admin</h4>
            </div>
            <a href="index.php" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="categories.php" class="<?= basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : '' ?>">
                <i class="fas fa-tags"></i> Kategori
            </a>
            <a href="books.php" class="<?= basename($_SERVER['PHP_SELF']) == 'books.php' || basename($_SERVER['PHP_SELF']) == 'book_form.php' ? 'active' : '' ?>">
                <i class="fas fa-book"></i> Buku
            </a>
            <a href="users.php" class="<?= basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : '' ?>">
                <i class="fas fa-users"></i> User
            </a>
            <a href="orders.php" class="<?= basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : '' ?>">
                <i class="fas fa-shopping-cart"></i> Pesanan
            </a>
            <a href="messages.php" class="<?= basename($_SERVER['PHP_SELF']) == 'messages.php' ? 'active' : '' ?>">
                <i class="fas fa-envelope"></i> Kontak
                <?php if($unreadMessages > 0): ?>
                    <span class="badge bg-danger rounded-pill"><?= $unreadMessages ?></span>
                <?php endif; ?>
            </a>
            <a href="../logout.php">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
        <div class="col content">