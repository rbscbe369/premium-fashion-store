<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';
session_start();

// Basic admin check
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$stmt = $pdo->prepare('SELECT role FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
if (!$user || $user['role'] !== 'admin') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $pdo->query('SELECT * FROM products ORDER BY id DESC');
    $products = $stmt->fetchAll();
    echo json_encode(['success' => true, 'data' => $products]);
} else if ($method === 'POST') {
    // Basic implementation for adding a product (Placeholder)
    echo json_encode(['error' => 'POST method for products not fully implemented in this demo']);
}
?>
