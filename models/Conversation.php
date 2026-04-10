<?php
require_once __DIR__ . '/../config/database.php';

class Conversation {
    private $conn;
    
    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }
    
    public function getOrCreate($user_id, $admin_id = 1) {
        $stmt = $this->conn->prepare("SELECT * FROM conversations WHERE user_id = ? AND admin_id = ?");
        $stmt->bind_param("ii", $user_id, $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row;
        }
        $stmt2 = $this->conn->prepare("INSERT INTO conversations (user_id, admin_id) VALUES (?, ?)");
        $stmt2->bind_param("ii", $user_id, $admin_id);
        $stmt2->execute();
        $new_id = $this->conn->insert_id;
        return ['id' => $new_id, 'user_id' => $user_id, 'admin_id' => $admin_id];
    }
    
    public function getAllForAdmin($admin_id = 1) {
        $stmt = $this->conn->prepare("
            SELECT c.*, u.name as user_name, u.email as user_email,
                   (SELECT COUNT(*) FROM messages WHERE conversation_id = c.id AND receiver_id = ? AND is_read = 0) as unread_count
            FROM conversations c
            JOIN users u ON c.user_id = u.id
            WHERE c.admin_id = ?
            ORDER BY c.last_message_time DESC
        ");
        $stmt->bind_param("ii", $admin_id, $admin_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getForUser($user_id) {
        $stmt = $this->conn->prepare("
            SELECT c.*, 
                   (SELECT COUNT(*) FROM messages WHERE conversation_id = c.id AND receiver_id = ? AND is_read = 0) as unread_count
            FROM conversations c
            WHERE c.user_id = ?
            LIMIT 1
        ");
        $stmt->bind_param("ii", $user_id, $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function updateLastMessage($conversation_id, $message) {
        $stmt = $this->conn->prepare("UPDATE conversations SET last_message = ?, last_message_time = NOW() WHERE id = ?");
        $stmt->bind_param("si", $message, $conversation_id);
        return $stmt->execute();
    }
}
?>