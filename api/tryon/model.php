<?php
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['product_id'])) {
    echo json_encode(['error' => 'Product ID is required']);
    exit;
}

$productId = (int)$data['product_id'];

// Simulate AI processing time
sleep(2);

// Map product ID to pre-generated model image
// ID 1: T-Shirt
// ID 2: Denim Jacket
// ID 3: Sneakers
// ID 4: Dress
$imageMap = [
    1 => 'assets/images/models/model_1.jpg',
    2 => 'assets/images/models/model_2.jpg',
    3 => 'assets/images/models/model_3.jpg',
    4 => 'assets/images/models/model_4.jpg',
];

$modelImage = isset($imageMap[$productId]) ? $imageMap[$productId] : 'assets/images/models/model_1.jpg'; // default to model_1

echo json_encode([
    'success' => true, 
    'data' => [
        'model_image_url' => $modelImage
    ]
]);
?>
