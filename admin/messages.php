<?php
session_start();
require_once '../models/Conversation.php';
require_once '../models/Message.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$admin_id = $_SESSION['user_id'];
$convModel = new Conversation();
$msgModel = new Message();

// Proses kirim pesan (harus sebelum output)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message']) && isset($_GET['user'])) {
    $selected_user_id = intval($_GET['user']);
    $message = trim($_POST['message']);
    if (!empty($message)) {
        $conv = $convModel->getOrCreate($selected_user_id, $admin_id);
        $msgModel->send($conv['id'], $admin_id, $selected_user_id, $message);
        header("Location: messages.php?user=$selected_user_id");
        exit();
    }
}

// Sekarang baru include header
require_once __DIR__ . '/includes/header.php';

$selected_user_id = isset($_GET['user']) ? intval($_GET['user']) : 0;
$conversations = $convModel->getAllForAdmin($admin_id);
$current_conv = null;
$messages = [];

if ($selected_user_id) {
    $current_conv = $convModel->getOrCreate($selected_user_id, $admin_id);
    $msgModel->markAsRead($current_conv['id'], $admin_id);
    $messages = $msgModel->getMessages($current_conv['id']);
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div><h1 class="display-6 fw-semibold">Pesan Pelanggan</h1><p class="text-muted">Kelola percakapan dengan pelanggan.</p></div>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-transparent fw-semibold">Daftar Percakapan</div>
            <div class="list-group list-group-flush">
                <?php if (empty($conversations)): ?>
                    <div class="list-group-item text-muted">Belum ada percakapan.</div>
                <?php else: ?>
                    <?php foreach ($conversations as $conv): ?>
                        <a href="?user=<?= $conv['user_id'] ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?= ($selected_user_id == $conv['user_id']) ? 'active' : '' ?>">
                            <div>
                                <strong><?= htmlspecialchars($conv['user_name']) ?></strong><br>
                                <small><?= htmlspecialchars(substr($conv['last_message'] ?? '', 0, 40)) ?></small>
                            </div>
                            <?php if ($conv['unread_count'] > 0): ?>
                                <span class="badge bg-danger rounded-pill"><?= $conv['unread_count'] ?></span>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <?php if ($selected_user_id): ?>
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-transparent fw-semibold d-flex justify-content-between">
                    <span><i class="fas fa-user-circle me-2"></i> Percakapan dengan User ID: <?= $selected_user_id ?></span>
                </div>
                <div class="card-body" style="height: 400px; overflow-y: auto; background: #f8f9fc;" id="chatMessages">
                    <?php foreach ($messages as $msg): ?>
                        <div class="d-flex mb-3 <?= $msg['sender_id'] == $admin_id ? 'justify-content-end' : 'justify-content-start' ?>">
                            <div class="p-2 rounded-3 <?= $msg['sender_id'] == $admin_id ? 'bg-primary text-white' : 'bg-white border' ?>" style="max-width: 75%;">
                                <?= nl2br(htmlspecialchars($msg['message'])) ?>
                                <div class="small <?= $msg['sender_id'] == $admin_id ? 'text-white-50' : 'text-muted' ?>"><?= date('H:i d/m/Y', strtotime($msg['created_at'])) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <form method="POST" class="p-3 border-top d-flex gap-2">
                    <input type="text" name="message" class="form-control rounded-pill" placeholder="Tulis balasan..." required autocomplete="off">
                    <button type="submit" class="btn btn-primary rounded-pill"><i class="fas fa-paper-plane"></i> Kirim</button>
                </form>
            </div>
        <?php else: ?>
            <div class="card border-0 shadow-sm rounded-4 text-center py-5">
                <i class="fas fa-comments fa-4x text-muted mb-3"></i>
                <h4>Pilih percakapan</h4>
                <p>Klik salah satu percakapan di sebelah kiri untuk mulai membalas pesan pelanggan.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    const chatBox = document.getElementById('chatMessages');
    if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>