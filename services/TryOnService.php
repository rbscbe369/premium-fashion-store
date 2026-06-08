<?php

class TryOnService {
    private $endpoint;
    private $apiKey;

    public function __construct() {
        $config = require __DIR__ . '/../includes/config.php';
        $this->endpoint = $config['idm_vton']['endpoint'];
        $this->apiKey = $config['idm_vton']['api_key'];
    }

    /**
     * Sends the try-on request to IDM-VTON API
     */
    public function queueTryOnJob($personImageUrl, $garmentImageUrl) {
        if ($this->apiKey === 'demo_key' || empty($this->apiKey)) {
            // Simulated job queue
            // Return a dummy job ID
            return 'simulated_job_' . uniqid();
        }

        // Real API Implementation (e.g. Replicate IDM-VTON endpoint)
        $ch = curl_init($this->endpoint);
        
        $payload = json_encode([
            "version" => "c871bb9b046607b680449ecbae55fd8c6d945e0a1948644bf2361b3d021d3ff4", // IDM-VTON model version
            "input" => [
                "image" => $personImageUrl,
                "garment" => $garmentImageUrl,
                "description" => "A high fashion studio shot."
            ]
        ]);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . $this->apiKey,
            "Content-Type: application/json"
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        return $data['id'] ?? null; // The Job ID
    }

    /**
     * Polls the job status
     */
    public function checkJobStatus($jobId) {
        if (strpos($jobId, 'simulated_job_') === 0) {
            // Simulated status
            // We will just return "processing" randomly, or just return success
            // But since PHP is stateless, we will just say it is complete and return null, 
            // the status API wrapper will handle returning the mocked image.
            return ['status' => 'succeeded', 'output' => null]; 
        }

        $url = "https://api.replicate.com/v1/predictions/" . $jobId;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . $this->apiKey,
            "Content-Type: application/json"
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        
        if (isset($data['status'])) {
            return [
                'status' => $data['status'], // 'starting', 'processing', 'succeeded', 'failed'
                'output' => $data['output'] ?? null // The generated image URL
            ];
        }

        return ['status' => 'failed', 'output' => null];
    }
}
?>
