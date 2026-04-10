<?php
require_once 'includes/auth.php';
require_once 'models/Conversation.php';
require_once 'models/Message.php';
require_once 'includes/functions.php'; // untuk getCartCount()

redirectIfNotLoggedIn();

$msgModel = new Message();
$unreadCount = $msgModel->countUnread($_SESSION['user_id']);

$convModel = new Conversation();
$admin_id = 1; // ganti dengan ID admin yang benar (biasanya 1)

$conversation = $convModel->getOrCreate($_SESSION['user_id'], $admin_id);
$conv_id = $conversation['id'];

// Tandai pesan sebagai sudah dibaca
$msgModel->markAsRead($conv_id, $_SESSION['user_id']);

// Ambil semua pesan
$messages = $msgModel->getMessages($conv_id);

// Kirim pesan baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    if (!empty($message)) {
        $msgModel->send($conv_id, $_SESSION['user_id'], $admin_id, $message);
        header("Location: inbox.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan Saya - CariBuku</title>
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
        .footer { background: #1e2a3e; color: #cbd5e1; padding: 2rem 0; margin-top: 2rem; }
        .chat-container { max-width: 800px; margin: 0 auto; background: white; border-radius: 24px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); overflow: hidden; }
        .chat-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 1rem 1.5rem; color: white; }
        .chat-messages { height: 400px; overflow-y: auto; padding: 1.5rem; background: #f8f9fc; display: flex; flex-direction: column; gap: 1rem; }
        .message { max-width: 75%; padding: 0.75rem 1rem; border-radius: 20px; }
        .message.sent { align-self: flex-end; background: #667eea; color: white; border-bottom-right-radius: 5px; }
        .message.received { align-self: flex-start; background: white; border: 1px solid #e2e8f0; border-bottom-left-radius: 5px; }
        .message small { font-size: 0.7rem; opacity: 0.7; display: block; margin-top: 0.25rem; }
        .chat-input { padding: 1rem; border-top: 1px solid #e2e8f0; background: white; }
        .btn-send { border-radius: 60px; padding: 0.5rem 1.5rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; color: white; font-weight: 500; }
        .btn-send:hover { transform: scale(1.02); }
        .back-link { display: inline-block; margin-bottom: 1rem; }
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
                            <li><a class="dropdown-item" href="cart.php"><i class="fas fa-shopping-cart"></i> Keranjang <span class="badge bg-primary rounded-pill"><?= getCartCount() ?></span></a></li>
                            <li><a class="dropdown-item" href="my_orders.php"><i class="fas fa-list"></i> Pesanan Saya</a></li>
                            <li><a class="dropdown-item" href="inbox.php"><i class="fas fa-envelope"></i> Pesan <?php if($unreadCount > 0): ?><span class="badge bg-danger ms-1"><?= $unreadCount ?></span><?php endif; ?></a></li>
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
    <div class="container py-4">
        <a href="index.php" class="back-link btn btn-outline-secondary rounded-pill"><i class="fas fa-arrow-left me-2"></i>Kembali ke Beranda</a>
        <div class="chat-container">
            <div class="chat-header">
                <h5 class="mb-0"><i class="fas fa-headset me-2"></i>Customer Support</h5>
                <small>Admin akan merespon secepatnya</small>
            </div>
            <div class="chat-messages" id="chatMessages">
                <?php foreach ($messages as $msg): ?>
                    <div class="message <?= $msg['sender_id'] == $_SESSION['user_id'] ? 'sent' : 'received' ?>">
                        <?= nl2br(htmlspecialchars($msg['message'])) ?>
                        <small><?= date('H:i, d/m/Y', strtotime($msg['created_at'])) ?></small>
                    </div>
                <?php endforeach; ?>
            </div>
            <form method="POST" class="chat-input d-flex gap-2">
                <input type="text" name="message" class="form-control rounded-pill" placeholder="Tulis pesan Anda..." required autocomplete="off">
                <button type="submit" class="btn-send"><i class="fas fa-paper-plane"></i> Kirim</button>
            </form>
        </div>
    </div>
</div>

<script>
    const container = document.getElementById('chatMessages');
    if (container) container.scrollTop = container.scrollHeight;
</script>

<footer class="footer">
    <div class="container text-center">&copy; <?= date('Y') ?> CariBuku. All rights reserved.</div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>