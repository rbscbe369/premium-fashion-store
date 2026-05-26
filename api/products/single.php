<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(['error' => 'Product ID is required']);
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
$stmt->execute([$id]);
$product = $stmt->fetch();

if ($product) {
    echo json_encode(['success' => true, 'data' => $product]);
} else {
    echo json_encode(['error' => 'Product not found']);
}
?>
