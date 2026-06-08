<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$stmt = $pdo->prepare("
    SELECT t.*, p.name as product_name 
    FROM tryon_requests t
    JOIN products p ON t.product_id = p.id
    WHERE t.user_id = ?
    ORDER BY t.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$history = $stmt->fetchAll();

echo json_encode(['success' => true, 'data' => $history]);
?>
