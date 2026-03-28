<?php

class S3Uploader {
    private $accessKey;
    private $secretKey;
    private $bucket;
    private $region;
    private $endpoint;
    private $lastError;

    public function __construct($accessKey, $secretKey, $bucket, $region = 'us-east-1') {
        $this->accessKey = $accessKey;
        $this->secretKey = $secretKey;
        $this->bucket = $bucket;
        $this->region = $region;
        $this->endpoint = "s3.{$this->region}.amazonaws.com";
    }

    public function upload($filePath, $s3Key) {
        // Validate file
        if (!file_exists($filePath)) {
            $this->lastError = "File not found: {$filePath}";
            return false;
        }

        if (!is_readable($filePath)) {
            $this->lastError = "File not readable: {$filePath}";
            return false;
        }

        try {
            $contentType = mime_content_type($filePath);
            if ($contentType === false) {
                $this->lastError = "Could not determine mime type for file";
                return false;
            }

            $date = gmdate('Ymd\THis\Z');
            $shortDate = gmdate('Ymd');
            
            // Prepare request headers
            $headers = [
                'Host' => "{$this->bucket}.{$this->endpoint}",
                'Date' => $date,
                'Content-Type' => $contentType,
                'x-amz-date' => $date,
                'x-amz-content-sha256' => hash_file('sha256', $filePath)
            ];
            
            // Generate signature
            $canonicalRequest = $this->createCanonicalRequest('PUT', "/{$s3Key}", '', $headers, $filePath);
            $stringToSign = $this->createStringToSign($shortDate, $this->region, $canonicalRequest);
            $signature = $this->calculateSignature($shortDate, $this->region, $stringToSign);
            
            // Prepare authorization header
            $scope = "{$shortDate}/{$this->region}/s3/aws4_request";
            $signedHeaders = 'content-type;host;x-amz-content-sha256;x-amz-date';
            $authHeader = "AWS4-HMAC-SHA256 Credential={$this->accessKey}/{$scope},SignedHeaders={$signedHeaders},Signature={$signature}";
            
            // Make request
            $ch = curl_init("https://{$this->bucket}.{$this->endpoint}/{$s3Key}");
            if ($ch === false) {
                $this->lastError = "Failed to initialize cURL";
                return false;
            }

            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, file_get_contents($filePath));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_VERBOSE, true);
            
            // Capture curl debug output
            $verbose = fopen('php://temp', 'w+');
            curl_setopt($ch, CURLOPT_STDERR, $verbose);

            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: {$authHeader}",
                "Content-Type: {$contentType}",
                "Host: {$this->bucket}.{$this->endpoint}",
                "x-amz-date: {$date}",
                "x-amz-content-sha256: " . hash_file('sha256', $filePath)
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($response === false) {
                $this->lastError = "cURL error: " . curl_error($ch);
                rewind($verbose);
                $this->lastError .= "\nVerbose log:\n" . stream_get_contents($verbose);
                fclose($verbose);
                curl_close($ch);
                return false;
            }

            if ($httpCode !== 200) {
                $this->lastError = "HTTP error {$httpCode}: {$response}";
                rewind($verbose);
                $this->lastError .= "\nVerbose log:\n" . stream_get_contents($verbose);
                fclose($verbose);
                curl_close($ch);
                return false;
            }

            fclose($verbose);
            curl_close($ch);
            return true;

        } catch (Exception $e) {
            $this->lastError = "Exception: " . $e->getMessage();
            return false;
        }
    }

    public function getLastError() {
        return $this->lastError;
    }

    private function createCanonicalRequest($method, $uri, $queryString, $headers, $filePath) {
        $canonicalHeaders = '';
        foreach ($headers as $key => $value) {
            $canonicalHeaders .= strtolower($key) . ":{$value}\n";
        }

        return implode("\n", [
            $method,
            $uri,
            $queryString,
            $canonicalHeaders,
            'content-type;host;x-amz-content-sha256;x-amz-date',
            hash_file('sha256', $filePath)
        ]);
    }

    private function createStringToSign($shortDate, $region, $canonicalRequest) {
        return implode("\n", [
            'AWS4-HMAC-SHA256',
            $shortDate . 'T000000Z',
            "{$shortDate}/{$region}/s3/aws4_request",
            hash('sha256', $canonicalRequest)
        ]);
    }

    private function calculateSignature($shortDate, $region, $stringToSign) {
        $dateKey = hash_hmac('sha256', $shortDate, "AWS4{$this->secretKey}", true);
        $regionKey = hash_hmac('sha256', $region, $dateKey, true);
        $serviceKey = hash_hmac('sha256', 's3', $regionKey, true);
        $signingKey = hash_hmac('sha256', 'aws4_request', $serviceKey, true);
        
        return hash_hmac('sha256', $stringToSign, $signingKey);
    }
}

// Usage example:
// Load credentials from environment variables
$uploader = new S3Uploader(
    getenv('AWS_ACCESS_KEY_ID'),
    getenv('AWS_SECRET_ACCESS_KEY'),
    getenv('AWS_BUCKET') ?: 'fintrafiles',
    getenv('AWS_REGION') ?: 'ap-south-1'
);

//$success = $uploader->upload('/path/to/local/file.jpg', 'destination/in/s3/file.jpg');
$success = $uploader->upload('/tmp/LOAN17381647732918-aadhaar.pdf', 'destination/LOAN17381647732918-aadhaar.pdf');
if (!$success) {
    echo "Upload failed: " . $uploader->getLastError();
} else {
    echo "Upload successful";
}
