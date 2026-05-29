<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$stmt = $pdo->prepare('
    SELECT o.*, COUNT(oi.id) as items_count 
    FROM orders o 
    LEFT JOIN order_items oi ON o.id = oi.order_id 
    WHERE o.user_id = ? 
    GROUP BY o.id 
    ORDER BY o.created_at DESC
');
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();

echo json_encode(['success' => true, 'data' => $orders]);
?>
