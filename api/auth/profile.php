<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$stmt = $pdo->prepare('SELECT id, name, email, role, created_at FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if ($user) {
    echo json_encode(['success' => true, 'data' => $user]);
} else {
    echo json_encode(['error' => 'User not found']);
}
?>
