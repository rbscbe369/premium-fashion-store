<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';

$category = $_GET['category'] ?? null;

if ($category) {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE category = ?');
    $stmt->execute([$category]);
} else {
    $stmt = $pdo->query('SELECT * FROM products');
}

$products = $stmt->fetchAll();
echo json_encode(['success' => true, 'data' => $products]);
?>
