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

$stmt = $pdo->query('SELECT * FROM orders ORDER BY created_at DESC');
$orders = $stmt->fetchAll();

echo json_encode(['success' => true, 'data' => $orders]);
?>
