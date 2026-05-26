<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';
session_start();

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['email'], $data['password'])) {
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

$email = trim($data['email']);
$password = $data['password'];

$stmt = $pdo->prepare('SELECT id, name, password FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    echo json_encode(['success' => true, 'message' => 'Login successful', 'user' => ['id' => $user['id'], 'name' => $user['name']]]);
} else {
    echo json_encode(['error' => 'Invalid email or password']);
}
?>
