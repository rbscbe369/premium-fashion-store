<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['name'], $data['email'], $data['password'])) {
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

$name = trim($data['name']);
$email = trim($data['email']);
$password = password_hash($data['password'], PASSWORD_DEFAULT);

// Check if email exists
$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute([$email]);
if ($stmt->fetch()) {
    echo json_encode(['error' => 'Email already registered']);
    exit;
}

// Insert user
$stmt = $pdo->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
if ($stmt->execute([$name, $email, $password])) {
    echo json_encode(['success' => true, 'message' => 'Registration successful']);
} else {
    echo json_encode(['error' => 'Registration failed']);
}
?>
