<?php

class CloudinaryService {
    private $cloudName;
    private $apiKey;
    private $apiSecret;

    public function __construct() {
        $config = require __DIR__ . '/../includes/config.php';
        $this->cloudName = $config['cloudinary']['cloud_name'];
        $this->apiKey = $config['cloudinary']['api_key'];
        $this->apiSecret = $config['cloudinary']['api_secret'];
    }

    /**
     * Upload an image to Cloudinary (Simulated if keys are missing)
     */
    public function uploadImage($localFilePath, $folder) {
        if ($this->apiKey === 'demo_key' || empty($this->apiKey)) {
            // Simulated upload: Just return a local URL to keep the app working offline
            $relativePath = str_replace($_SERVER['DOCUMENT_ROOT'] . '/fashion-store/', '', $localFilePath);
            // Replace backslashes for URLs
            $relativePath = str_replace('\\', '/', $relativePath);
            return 'http://localhost/fashion-store/' . $relativePath;
        }

        // Real Implementation using Cloudinary REST API
        $url = "https://api.cloudinary.com/v1_1/{$this->cloudName}/image/upload";
        
        $cfile = new CURLFile($localFilePath);
        $timestamp = time();
        // Signature: SHA-1 of timestamp+folder+api_secret
        $signature = sha1("folder={$folder}&timestamp={$timestamp}{$this->apiSecret}");

        $postFields = [
            'file' => $cfile,
            'api_key' => $this->apiKey,
            'timestamp' => $timestamp,
            'signature' => $signature,
            'folder' => $folder
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 200) {
            $data = json_decode($response, true);
            return $data['secure_url'] ?? null;
        }

        return null; // Failed
    }
}
?>
