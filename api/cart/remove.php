<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Please login first']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['cart_id'])) {
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

$user_id = $_SESSION['user_id'];
$cart_id = $data['cart_id'];

$stmt = $pdo->prepare('DELETE FROM cart WHERE id = ? AND user_id = ?');
if ($stmt->execute([$cart_id, $user_id])) {
    echo json_encode(['success' => true, 'message' => 'Item removed from cart']);
} else {
    echo json_encode(['error' => 'Failed to remove item']);
}
?>
