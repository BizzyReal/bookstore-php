<?php
require_once __DIR__ . '/../models/Cart.php';

function escape($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

function getCartCount() {
    return Cart::count();
}

function getBookById($id) {
    global $conn;
    $result = $conn->query("SELECT * FROM books WHERE id = $id");
    return $result->fetch_assoc();
}

function getBookByIdSafe($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM books WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function hasCover($cover_image) {
    if (empty($cover_image)) return false;
    return file_exists('uploads/' . $cover_image);
}

function getCoverUrl($cover_image) {
    if (hasCover($cover_image)) {
        return 'uploads/' . $cover_image;
    }
    return 'https://placehold.co/300x400?text=No+Cover';
}
?>