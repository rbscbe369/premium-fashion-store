<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Please login first']);
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare('
    SELECT c.id as cart_id, c.quantity, p.id as product_id, p.name, p.price, p.image 
    FROM cart c 
    JOIN products p ON c.product_id = p.id 
    WHERE c.user_id = ?
');
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

echo json_encode(['success' => true, 'data' => $cart_items]);
?>
