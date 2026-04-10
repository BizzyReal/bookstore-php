<?php
session_start();
require_once 'models/Cart.php';
$count = Cart::count();
header('Content-Type: application/json');
echo json_encode(['count' => $count]);
?>