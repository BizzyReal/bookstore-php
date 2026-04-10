<?php
require_once __DIR__ . '/../config/database.php';

class ContactModel {
    private $conn;
    
    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }
    
    public function send($user_id, $name, $email, $message) {
        $stmt = $this->conn->prepare("INSERT INTO contacts (user_id, name, email, message) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $user_id, $name, $email, $message);
        return $stmt->execute();
    }
    
    public function getAll() {
        $result = $this->conn->query("SELECT * FROM contacts ORDER BY created_at DESC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function markAsRead($id) {
        $stmt = $this->conn->prepare("UPDATE contacts SET status = 'read' WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>