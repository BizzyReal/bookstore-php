<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Conversation.php';
require_once __DIR__ . '/../models/Message.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$admin_id = $_SESSION['user_id'];
$convModel = new Conversation();
$msgModel = new Message();

// Ambil parameter
$selected_user_id = isset($_GET['user']) ? intval($_GET['user']) : 0;
$search_user = isset($_GET['search_user']) ? trim($_GET['search_user']) : '';

// Proses kirim pesan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message']) && $selected_user_id) {
    $message = trim($_POST['message']);
    if (!empty($message)) {
        $conv = $convModel->getOrCreate($selected_user_id, $admin_id);
        $msgModel->send($conv['id'], $admin_id, $selected_user_id, $message);
        header("Location: chat.php?user=$selected_user_id" . ($search_user ? "&search_user=" . urlencode($search_user) : ""));
        exit();
    }
}

// Ambil daftar percakapan atau hasil pencarian user
$conversations = [];
if ($search_user !== '') {
    // Cari user berdasarkan nama
    $stmt = $conn->prepare("
        SELECT u.id as user_id, u.name as user_name, u.email as user_email,
               (SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND sender_id = u.id AND is_read = 0) as unread_count,
               (SELECT last_message FROM conversations WHERE user_id = u.id AND admin_id = ? LIMIT 1) as last_message,
               (SELECT last_message_time FROM conversations WHERE user_id = u.id AND admin_id = ? LIMIT 1) as last_message_time
        FROM users u
        WHERE u.role = 'user' AND u.name LIKE ?
        ORDER BY last_message_time DESC
    ");
    $like = "%$search_user%";
    $stmt->bind_param("iiis", $admin_id, $admin_id, $admin_id, $like);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $conversations[] = [
            'user_id' => $row['user_id'],
            'user_name' => $row['user_name'],
            'user_email' => $row['user_email'],
            'unread_count' => $row['unread_count'],
            'last_message' => $row['last_message'],
            'last_message_time' => $row['last_message_time']
        ];
    }
} else {
    $conversations = $convModel->getAllForAdmin($admin_id);
}

// Untuk dropdown semua user
$allUsers = $conn->query("SELECT id, name, email FROM users WHERE role = 'user' ORDER BY name");

// Ambil pesan jika ada user dipilih
$messages = [];
if ($selected_user_id) {
    $current_conv = $convModel->getOrCreate($selected_user_id, $admin_id);
    $msgModel->markAsRead($current_conv['id'], $admin_id);
    $messages = $msgModel->getMessages($current_conv['id']);
}

// Hitung notifikasi chat (untuk badge di header)
$unreadChatCount = $msgModel->countUnread($admin_id);
?>
<?php require_once __DIR__ . '/includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div><h1 class="display-6 fw-semibold">Pesan Pelanggan</h1><p class="text-muted">Kelola percakapan dengan pelanggan.</p></div>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-transparent fw-semibold">Daftar Percakapan</div>
            <div class="card-body p-2">
                <form method="GET" action="chat.php" class="mb-3 px-2">
                    <div class="input-group">
                        <input type="text" name="search_user" class="form-control rounded-pill" placeholder="Cari user..." value="<?= htmlspecialchars($search_user) ?>">
                        <button class="btn btn-primary rounded-pill ms-2" type="submit"><i class="fas fa-search"></i></button>
                        <?php if ($search_user): ?>
                            <a href="chat.php" class="btn btn-secondary rounded-pill ms-2"><i class="fas fa-times"></i> Reset</a>
                        <?php endif; ?>
                    </div>
                    <?php if ($selected_user_id): ?>
                        <input type="hidden" name="user" value="<?= $selected_user_id ?>">
                    <?php endif; ?>
                </form>
                <div class="list-group list-group-flush" style="max-height: 500px; overflow-y: auto;">
                    <?php if (empty($conversations)): ?>
                        <div class="list-group-item text-muted">Belum ada percakapan atau user tidak ditemukan.</div>
                    <?php else: ?>
                        <?php foreach ($conversations as $conv): ?>
                            <a href="?user=<?= $conv['user_id'] ?><?= $search_user ? '&search_user='.urlencode($search_user) : '' ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?= ($selected_user_id == $conv['user_id']) ? 'active' : '' ?>">
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
            <div class="card-footer bg-transparent">
                <form method="GET" action="chat.php" class="d-flex gap-2">
                    <select name="user" class="form-select rounded-pill" required>
                        <option value="">-- Pilih User (Semua) --</option>
                        <?php while($u = $allUsers->fetch_assoc()): ?>
                            <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?> (<?= htmlspecialchars($u['email']) ?>)</option>
                        <?php endwhile; ?>
                    </select>
                    <button type="submit" class="btn btn-primary rounded-pill"><i class="fas fa-comment"></i> Chat</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <?php if ($selected_user_id): ?>
            <?php
            $userName = $conn->query("SELECT name FROM users WHERE id = $selected_user_id")->fetch_assoc()['name'];
            ?>
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-transparent fw-semibold d-flex justify-content-between">
                    <span><i class="fas fa-user-circle me-2"></i> Percakapan dengan <?= htmlspecialchars($userName) ?></span>
                    <span class="text-muted small"><?= count($messages) ?> pesan</span>
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
                <p>Gunakan pencarian atau dropdown di sebelah kiri untuk memilih user.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    const chatBox = document.getElementById('chatMessages');
    if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>