<?php
require_once __DIR__ . '/../config/database.php';

class Book {
    private $conn;
    
    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }
    
    public function getAll($search = '') {
        $sql = "SELECT books.*, categories.name as category_name FROM books 
                LEFT JOIN categories ON books.category_id = categories.id";
        if ($search) {
            $sql .= " WHERE books.title LIKE '%$search%' OR books.author LIKE '%$search%'";
        }
        $sql .= " ORDER BY books.created_at DESC";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT books.*, categories.name as category_name FROM books 
                                      LEFT JOIN categories ON books.category_id = categories.id WHERE books.id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function create($category_id, $title, $author, $description, $price, $stock, $cover_image) {
        $stmt = $this->conn->prepare("INSERT INTO books (category_id, title, author, description, price, stock, cover_image) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssdis", $category_id, $title, $author, $description, $price, $stock, $cover_image);
        return $stmt->execute();
    }
    
    public function update($id, $category_id, $title, $author, $description, $price, $stock, $cover_image = null) {
        if ($cover_image) {
            $stmt = $this->conn->prepare("UPDATE books SET category_id=?, title=?, author=?, description=?, price=?, stock=?, cover_image=? WHERE id=?");
            $stmt->bind_param("isssdisi", $category_id, $title, $author, $description, $price, $stock, $cover_image, $id);
        } else {
            $stmt = $this->conn->prepare("UPDATE books SET category_id=?, title=?, author=?, description=?, price=?, stock=? WHERE id=?");
            $stmt->bind_param("isssdii", $category_id, $title, $author, $description, $price, $stock, $id);
        }
        return $stmt->execute();
    }
    
    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM books WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
    
    public function updateStock($id, $stock) {
        $stmt = $this->conn->prepare("UPDATE books SET stock = stock - ? WHERE id = ?");
        $stmt->bind_param("ii", $stock, $id);
        return $stmt->execute();
    }
}
?>