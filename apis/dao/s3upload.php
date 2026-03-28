<?php

class S3MultipartUpload {
    private $awsAccessKey;
    private $awsSecretKey;
    private $bucket;
    private $region;
    private $chunkSize = 5242880; // 5MB minimum chunk size

    public function __construct($accessKey, $secretKey, $bucket, $region = 'us-east-1') {
        $this->awsAccessKey = $accessKey;
        $this->awsSecretKey = $secretKey;
        $this->bucket = $bucket;
        $this->region = $region;
    }

    public function upload($filePath, $s3Key) {
        // Initialize multipart upload
        $uploadId = $this->initiateMultipartUpload($s3Key);
        if (!$uploadId) {
            return false;
        }

        // Split file into chunks and upload
        $parts = [];
        $handle = fopen($filePath, 'rb');
        $partNumber = 1;

        while (!feof($handle)) {
            $chunk = fread($handle, $this->chunkSize);
            $etag = $this->uploadPart($s3Key, $uploadId, $partNumber, $chunk);
            
            if (!$etag) {
                $this->abortMultipartUpload($s3Key, $uploadId);
                fclose($handle);
                return false;
            }

            $parts[] = [
                'PartNumber' => $partNumber,
                'ETag' => $etag
            ];
            $partNumber++;
        }

        fclose($handle);

        // Complete the multipart upload
        return $this->completeMultipartUpload($s3Key, $uploadId, $parts);
    }

    private function initiateMultipartUpload($key) {
        $date = gmdate('Ymd\THis\Z');
        $shortDate = gmdate('Ymd');
        
        $url = "https://{$this->bucket}.s3.{$this->region}.amazonaws.com/{$key}?uploads";
        
        $headers = $this->generateHeaders('POST', $key, '', $shortDate, $date, ['uploads' => '']);
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return false;
        }

        $xml = simplexml_load_string($response);
        return (string)$xml->UploadId;
    }

    private function uploadPart($key, $uploadId, $partNumber, $data) {
        $date = gmdate('Ymd\THis\Z');
        $shortDate = gmdate('Ymd');
        
        $url = "https://{$this->bucket}.s3.{$this->region}.amazonaws.com/{$key}";
        $query = [
            'partNumber' => $partNumber,
            'uploadId' => $uploadId
        ];
        
        $headers = $this->generateHeaders('PUT', $key, md5($data), $shortDate, $date, $query);
        
        $ch = curl_init($url . '?' . http_build_query($query));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $etag = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        return ($httpCode === 200) ? trim($etag, '"') : false;
    }

    private function completeMultipartUpload($key, $uploadId, $parts) {
        $date = gmdate('Ymd\THis\Z');
        $shortDate = gmdate('Ymd');
        
        $url = "https://{$this->bucket}.s3.{$this->region}.amazonaws.com/{$key}";
        $query = ['uploadId' => $uploadId];
        
        $xml = "<CompleteMultipartUpload>";
        foreach ($parts as $part) {
            $xml .= "<Part>";
            $xml .= "<PartNumber>{$part['PartNumber']}</PartNumber>";
            $xml .= "<ETag>{$part['ETag']}</ETag>";
            $xml .= "</Part>";
        }
        $xml .= "</CompleteMultipartUpload>";
        
        $headers = $this->generateHeaders('POST', $key, md5($xml), $shortDate, $date, $query);
        
        $ch = curl_init($url . '?' . http_build_query($query));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode === 200;
    }

    private function abortMultipartUpload($key, $uploadId) {
        $date = gmdate('Ymd\THis\Z');
        $shortDate = gmdate('Ymd');
        
        $url = "https://{$this->bucket}.s3.{$this->region}.amazonaws.com/{$key}";
        $query = ['uploadId' => $uploadId];
        
        $headers = $this->generateHeaders('DELETE', $key, '', $shortDate, $date, $query);
        
        $ch = curl_init($url . '?' . http_build_query($query));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        curl_exec($ch);
        curl_close($ch);
    }

    private function generateHeaders($method, $key, $contentMd5, $shortDate, $date, $query = []) {
        $scope = "{$shortDate}/{$this->region}/s3/aws4_request";
        $credential = "{$this->awsAccessKey}/{$scope}";
        
        $canonicalRequest = $this->createCanonicalRequest($method, $key, $contentMd5, $date, $query);
        $stringToSign = $this->createStringToSign($date, $scope, $canonicalRequest);
        $signature = $this->calculateSignature($shortDate, $stringToSign);
        
        $authorization = "AWS4-HMAC-SHA256 Credential={$credential},"
            . "SignedHeaders=content-md5;host;x-amz-content-sha256;x-amz-date,"
            . "Signature={$signature}";

        return [
            "Authorization: {$authorization}",
            "Content-MD5: {$contentMd5}",
            "Host: {$this->bucket}.s3.{$this->region}.amazonaws.com",
            "x-amz-content-sha256: " . hash('sha256', ''),
            "x-amz-date: {$date}"
        ];
    }

    private function createCanonicalRequest($method, $key, $contentMd5, $date, $query) {
        $canonicalUri = '/' . ltrim($key, '/');
        $canonicalQueryString = http_build_query($query);
        $canonicalHeaders = "content-md5:{$contentMd5}\n"
            . "host:{$this->bucket}.s3.{$this->region}.amazonaws.com\n"
            . "x-amz-content-sha256:" . hash('sha256', '') . "\n"
            . "x-amz-date:{$date}\n";
        
        return implode("\n", [
            $method,
            $canonicalUri,
            $canonicalQueryString,
            $canonicalHeaders,
            "content-md5;host;x-amz-content-sha256;x-amz-date",
            hash('sha256', '')
        ]);
    }

    private function createStringToSign($date, $scope, $canonicalRequest) {
        return implode("\n", [
            'AWS4-HMAC-SHA256',
            $date,
            $scope,
            hash('sha256', $canonicalRequest)
        ]);
    }

    private function calculateSignature($shortDate, $stringToSign) {
        $dateKey = hash_hmac('sha256', $shortDate, 'AWS4' . $this->awsSecretKey, true);
        $regionKey = hash_hmac('sha256', $this->region, $dateKey, true);
        $serviceKey = hash_hmac('sha256', 's3', $regionKey, true);
        $signingKey = hash_hmac('sha256', 'aws4_request', $serviceKey, true);
        
        return hash_hmac('sha256', $stringToSign, $signingKey);
    }
}

// Usage example:
// Load credentials from environment variables
$uploader = new S3MultipartUpload(
    getenv('AWS_ACCESS_KEY_ID'),
    getenv('AWS_SECRET_ACCESS_KEY'),
    getenv('AWS_BUCKET') ?: 'fintrafiles',
    getenv('AWS_REGION') ?: 'ap-south-1'
);

$result = $uploader->upload('/tmp/LOAN17381647732918-aadhaar.pdf', 'destination/key.zip');
if ($result) {
    echo "Upload successful";
} else {
    echo "Upload failed";
}
