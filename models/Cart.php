<?php
class Cart {
    public static function add($book_id, $title, $price, $quantity = 1) {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        if (isset($_SESSION['cart'][$book_id])) {
            $_SESSION['cart'][$book_id]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$book_id] = [
                'id' => $book_id,
                'title' => $title,
                'price' => $price,
                'quantity' => $quantity
            ];
        }
    }
    
    public static function update($book_id, $quantity) {
        if ($quantity <= 0) {
            unset($_SESSION['cart'][$book_id]);
        } else {
            $_SESSION['cart'][$book_id]['quantity'] = $quantity;
        }
    }
    
    public static function remove($book_id) {
        unset($_SESSION['cart'][$book_id]);
    }
    
    public static function getItems() {
        return isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
    }
    
    public static function getTotal() {
        $total = 0;
        foreach (self::getItems() as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }
    
    public static function clear() {
        unset($_SESSION['cart']);
    }
    
    public static function count() {
        $count = 0;
        foreach (self::getItems() as $item) {
            $count += $item['quantity'];
        }
        return $count;
    }
}
?>