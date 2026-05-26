<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Please login to add items to cart']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['product_id'], $data['quantity'])) {
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = $data['product_id'];
$quantity = $data['quantity'];

// Check if item already exists in cart
$stmt = $pdo->prepare('SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?');
$stmt->execute([$user_id, $product_id]);
$cart_item = $stmt->fetch();

if ($cart_item) {
    // Update quantity
    $new_quantity = $cart_item['quantity'] + $quantity;
    $stmt = $pdo->prepare('UPDATE cart SET quantity = ? WHERE id = ?');
    $stmt->execute([$new_quantity, $cart_item['id']]);
} else {
    // Insert new item
    $stmt = $pdo->prepare('INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)');
    $stmt->execute([$user_id, $product_id, $quantity]);
}

echo json_encode(['success' => true, 'message' => 'Item added to cart']);
?>
