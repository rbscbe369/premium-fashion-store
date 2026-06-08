<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';
require_once '../../services/CloudinaryService.php';
require_once '../../services/TryOnService.php';
session_start();

$config = require '../../includes/config.php';

// Check limits
$userId = $_SESSION['user_id'] ?? null;
$identifier = $userId ? "user_{$userId}" : "session_" . session_id();
$today = date('Y-m-d');
$limit = $userId ? $config['tryon']['registered_limit'] : $config['tryon']['guest_limit'];

$stmt = $pdo->prepare("SELECT tryons_count FROM tryon_usage WHERE identifier = ? AND usage_date = ?");
$stmt->execute([$identifier, $today]);
$usage = $stmt->fetch();
$currentCount = $usage ? $usage['tryons_count'] : 0;

if ($currentCount >= $limit) {
    echo json_encode(['error' => 'Daily try-on limit reached.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

if (!isset($_FILES['user_image']) || !isset($_POST['product_id'])) {
    echo json_encode(['error' => 'Missing image or product ID']);
    exit;
}

$productId = (int)$_POST['product_id'];

// Fetch garment image from product
$stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch();
if (!$product) {
    echo json_encode(['error' => 'Product not found']);
    exit;
}
$garmentImageUrl = 'http://localhost/fashion-store/' . $product['image']; // Simulate absolute URL for APIs

// File Validation
$file = $_FILES['user_image'];
if ($file['size'] > 10 * 1024 * 1024) { // 10MB
    echo json_encode(['error' => 'File too large (max 10MB)']);
    exit;
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

$allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
if (!in_array($mime, $allowedMimes)) {
    echo json_encode(['error' => 'Invalid file format. Only JPG, PNG, WEBP allowed.']);
    exit;
}

// Ensure upload directory exists
$uploadDir = '../../uploads/users/uploads/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

$fileName = uniqid() . '_' . basename($file['name']);
$localPath = $uploadDir . $fileName;

if (move_uploaded_file($file['tmp_name'], $localPath)) {
    // 1. Upload to Cloudinary
    $cloudinary = new CloudinaryService();
    $personImageUrl = $cloudinary->uploadImage($localPath, 'users/uploads');

    if (!$personImageUrl) {
        echo json_encode(['error' => 'Failed to upload to Cloudinary']);
        exit;
    }

    // 2. Queue IDM-VTON Job
    $tryon = new TryOnService();
    $jobId = $tryon->queueTryOnJob($personImageUrl, $garmentImageUrl);

    if (!$jobId) {
        echo json_encode(['error' => 'Failed to queue Try-On job']);
        exit;
    }

    // 3. Save to Database
    $stmt = $pdo->prepare("INSERT INTO tryon_requests (user_id, session_id, product_id, person_image_url, garment_image_url, status) VALUES (?, ?, ?, ?, ?, 'processing')");
    $stmt->execute([$userId, session_id(), $productId, $personImageUrl, $garmentImageUrl]);
    $requestId = $pdo->lastInsertId();

    // 4. Update usage tracker
    if ($usage) {
        $pdo->prepare("UPDATE tryon_usage SET tryons_count = tryons_count + 1 WHERE id = ?")->execute([$usage['id']]);
    } else {
        $pdo->prepare("INSERT INTO tryon_usage (identifier, usage_date, tryons_count) VALUES (?, ?, 1)")->execute([$identifier, $today]);
    }

    // We store the job ID in session temporarily to poll it, or we could add a `job_id` column to tryon_requests
    $_SESSION["tryon_job_$requestId"] = $jobId;

    echo json_encode(['success' => true, 'request_id' => $requestId, 'status' => 'processing']);
} else {
    echo json_encode(['error' => 'Failed to save file']);
}
?>
