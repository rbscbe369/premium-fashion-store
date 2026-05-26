<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Please login first']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $pdo->beginTransaction();

    // Get cart items
    $stmt = $pdo->prepare('
        SELECT c.quantity, p.id as product_id, p.price 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ?
    ');
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll();

    if (empty($cart_items)) {
        throw new Exception('Cart is empty');
    }

    $total_price = 0;
    foreach ($cart_items as $item) {
        $total_price += $item['price'] * $item['quantity'];
    }

    // Create order
    $stmt = $pdo->prepare('INSERT INTO orders (user_id, total_price) VALUES (?, ?)');
    $stmt->execute([$user_id, $total_price]);
    $order_id = $pdo->lastInsertId();

    // Insert order items
    $stmt = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)');
    foreach ($cart_items as $item) {
        $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
    }

    // Clear cart
    $stmt = $pdo->prepare('DELETE FROM cart WHERE user_id = ?');
    $stmt->execute([$user_id]);

    $pdo->commit();

    echo json_encode(['success' => true, 'message' => 'Order placed successfully', 'order_id' => $order_id]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['error' => 'Order failed: ' . $e->getMessage()]);
}
?>
