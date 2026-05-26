<?php
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['product_id'])) {
    echo json_encode(['error' => 'Product ID is required']);
    exit;
}

$product_id = $data['product_id'];

// Simulate AI Model Processing Time
sleep(2);

// Mock response for "View on Model"
$response = [
    'success' => true,
    'message' => 'Model image generated successfully',
    'data' => [
        'original_product_id' => $product_id,
        'model_image_url' => 'assets/images/placeholder_model.jpg'
    ]
];

echo json_encode($response);
?>
