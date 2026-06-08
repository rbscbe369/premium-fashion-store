<?php
// Configuration for API Keys and Settings
// DO NOT HARDCODE REAL KEYS IN PUBLIC REPOSITORIES

return [
    'cloudinary' => [
        'cloud_name' => getenv('CLOUDINARY_CLOUD_NAME') ?: 'demo',
        'api_key'    => getenv('CLOUDINARY_API_KEY') ?: 'demo_key',
        'api_secret' => getenv('CLOUDINARY_API_SECRET') ?: 'demo_secret',
    ],
    'idm_vton' => [
        'endpoint'   => getenv('IDM_VTON_ENDPOINT') ?: 'https://api.replicate.com/v1/models/yisol/idm-vton/predictions',
        'api_key'    => getenv('IDM_VTON_API_KEY') ?: 'demo_key',
    ],
    'tryon' => [
        'guest_limit'      => 2,
        'registered_limit' => 10,
    ]
];
?>
