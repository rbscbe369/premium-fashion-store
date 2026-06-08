<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';
session_start();

// Check Admin
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

$stmt = $pdo->query("
    SELECT t.*, p.name as product_name, u.name as user_name 
    FROM tryon_requests t
    LEFT JOIN products p ON t.product_id = p.id
    LEFT JOIN users u ON t.user_id = u.id
    ORDER BY t.created_at DESC
");
$requests = $stmt->fetchAll();

echo json_encode(['success' => true, 'data' => $requests]);
?>
