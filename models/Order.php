<?php
require_once __DIR__ . '/../config/database.php';

class OrderModel {
    private $conn;
    
    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }
    
    public function create($user_id, $total_amount, $address, $notes, $items, $payment_method = 'cod', $payment_proof = null) {
        $this->conn->begin_transaction();
        try {
            $stmt = $this->conn->prepare("INSERT INTO orders (user_id, total_amount, address, notes, payment_method, payment_proof, payment_status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
            $stmt->bind_param("idssss", $user_id, $total_amount, $address, $notes, $payment_method, $payment_proof);
            $stmt->execute();
            $order_id = $this->conn->insert_id;
            
            $stmt_item = $this->conn->prepare("INSERT INTO order_items (order_id, book_id, quantity, price) VALUES (?, ?, ?, ?)");
            foreach ($items as $item) {
                $stmt_item->bind_param("iiid", $order_id, $item['id'], $item['quantity'], $item['price']);
                $stmt_item->execute();
                // Kurangi stok
                $this->conn->query("UPDATE books SET stock = stock - {$item['quantity']} WHERE id = {$item['id']}");
            }
            $this->conn->commit();
            return $order_id;
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }
    
    public function getByUser($user_id) {
        $stmt = $this->conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getAll() {
        $result = $this->conn->query("SELECT orders.*, users.name as user_name FROM orders 
                                      JOIN users ON orders.user_id = users.id 
                                      ORDER BY order_date DESC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function updateStatus($order_id, $status) {
        $stmt = $this->conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $order_id);
        return $stmt->execute();
    }
    
    public function updatePaymentStatus($order_id, $payment_status) {
        $stmt = $this->conn->prepare("UPDATE orders SET payment_status = ? WHERE id = ?");
        $stmt->bind_param("si", $payment_status, $order_id);
        return $stmt->execute();
    }
    
    public function getItems($order_id) {
        $stmt = $this->conn->prepare("SELECT order_items.*, books.title FROM order_items 
                                      JOIN books ON order_items.book_id = books.id 
                                      WHERE order_id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>