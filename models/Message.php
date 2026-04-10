<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Conversation.php';

class Message {
    private $conn;
    
    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }
    
    public function send($conversation_id, $sender_id, $receiver_id, $message) {
        $stmt = $this->conn->prepare("INSERT INTO messages (conversation_id, sender_id, receiver_id, message) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $conversation_id, $sender_id, $receiver_id, $message);
        $result = $stmt->execute();
        if ($result) {
            $convModel = new Conversation();
            $convModel->updateLastMessage($conversation_id, substr($message, 0, 100));
        }
        return $result;
    }
    
    public function getMessages($conversation_id, $limit = 50) {
        $stmt = $this->conn->prepare("
            SELECT m.*, u.name as sender_name 
            FROM messages m
            JOIN users u ON m.sender_id = u.id
            WHERE m.conversation_id = ?
            ORDER BY m.created_at ASC
            LIMIT ?
        ");
        $stmt->bind_param("ii", $conversation_id, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function markAsRead($conversation_id, $receiver_id) {
        $stmt = $this->conn->prepare("UPDATE messages SET is_read = 1 WHERE conversation_id = ? AND receiver_id = ? AND is_read = 0");
        $stmt->bind_param("ii", $conversation_id, $receiver_id);
        return $stmt->execute();
    }
    
    public function countUnread($user_id) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM messages WHERE receiver_id = ? AND is_read = 0");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['total'];
    }
}
?>