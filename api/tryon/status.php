<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';
require_once '../../services/TryOnService.php';
session_start();

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'Missing request ID']);
    exit;
}

$requestId = (int)$_GET['id'];

// Verify ownership (optional but recommended)
$userId = $_SESSION['user_id'] ?? null;
$sessId = session_id();

$stmt = $pdo->prepare("SELECT * FROM tryon_requests WHERE id = ? AND (user_id = ? OR session_id = ?)");
$stmt->execute([$requestId, $userId, $sessId]);
$request = $stmt->fetch();

if (!$request) {
    echo json_encode(['error' => 'Request not found or unauthorized']);
    exit;
}

if ($request['status'] === 'completed') {
    echo json_encode(['success' => true, 'status' => 'completed', 'result_url' => $request['result_image_url']]);
    exit;
}

// Poll IDM-VTON
$jobId = $_SESSION["tryon_job_$requestId"] ?? null;

if ($jobId) {
    $tryon = new TryOnService();
    $statusData = $tryon->checkJobStatus($jobId);
    
    if ($statusData['status'] === 'succeeded' || strpos($jobId, 'simulated') !== false) {
        // If simulated, fake a delay using the updated_at timestamp.
        $secondsPassed = time() - strtotime($request['updated_at']);
        if (strpos($jobId, 'simulated') !== false && $secondsPassed < 4) {
            echo json_encode(['success' => true, 'status' => 'processing']);
            exit;
        }

        // Get the mock image based on product ID if simulated
        $resultUrl = $statusData['output'];
        if (!$resultUrl) {
            $imageMap = [
                1 => 'assets/images/models/model_1.jpg',
                2 => 'assets/images/models/model_2.jpg',
                3 => 'assets/images/models/model_3.jpg',
                4 => 'assets/images/models/model_4.jpg',
            ];
            $relativePath = isset($imageMap[$request['product_id']]) ? $imageMap[$request['product_id']] : 'assets/images/models/model_1.jpg';
            $resultUrl = 'http://localhost/fashion-store/' . $relativePath;
        }

        // Update DB
        $stmt = $pdo->prepare("UPDATE tryon_requests SET status = 'completed', result_image_url = ? WHERE id = ?");
        $stmt->execute([$resultUrl, $requestId]);
        
        echo json_encode(['success' => true, 'status' => 'completed', 'result_url' => $resultUrl]);
        exit;
    } else if ($statusData['status'] === 'failed') {
        $pdo->prepare("UPDATE tryon_requests SET status = 'failed' WHERE id = ?")->execute([$requestId]);
        echo json_encode(['success' => true, 'status' => 'failed']);
        exit;
    } else {
        echo json_encode(['success' => true, 'status' => 'processing']);
        exit;
    }
}

echo json_encode(['error' => 'Job tracking lost']);
?>
