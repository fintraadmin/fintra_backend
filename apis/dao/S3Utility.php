<?php
// utils/S3Utility.php

require 'lib/vendor/autoload.php';
require 'lib/vendor/conf.ini';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Aws\Common\Credentials\Credentials;

class S3Utility {
    private $s3Client;
    private $bucket;
    
    public function __construct() {
        $this->bucket = 'fintrafiles';
  	$credentials = new Credentials(ACCESS_KEY, SECRET_KEY);
        
        $this->s3Client = S3Client::factory(array(
  	    'profile' => 'default',
            'version' => 'latest',
            'region'  => 'ap-south-1',
	    'signature' => 'v2',
  	    'credentials' => $credentials
        ));
    }
    
    public function uploadFile($file, $leadId, $documentType) {
        try {
            // Validate file
            $this->validateFile($file);
            
            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $key = "documents/{$leadId}/{$documentType}_" . time() . ".{$extension}";
            
            // Upload to S3
            $result = $this->s3Client->putObject([
                'Bucket' => $this->bucket,
                'Key'    => $key,
                'Body'   => fopen($file['tmp_name'], 'rb'),
                'ACL'    => 'private',
                'ContentType' => $file['type']
            ]);
            
            return $key;
        } catch (AwsException $e) {
            error_log("S3 Upload Error: " . $e->getMessage());
            throw new Exception("Failed to upload file to storage");
        }
    }
    
    private function validateFile($file) {
        // Check file size (2MB limit)
        if ($file['size'] > 2 * 1024 * 1024) {
            throw new Exception("File size exceeds 2MB limit");
        }
        
        // Check file type
        $allowedTypes = [
            'image/jpeg',
            'image/png',
            'application/pdf',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];
        
        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception("Invalid file type");
        }
        
        // Check if file was successfully uploaded
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("File upload failed");
        }
    }
    
    public function getSignedUrl($key, $expiry = 3600) {
        try {
            $cmd = $this->s3Client->getCommand('GetObject', [
                'Bucket' => $this->bucket,
                'Key'    => $key
            ]);
            
            $request = $this->s3Client->createPresignedRequest($cmd, "+{$expiry} seconds");
            return (string) $request->getUri();
        } catch (AwsException $e) {
            error_log("S3 Signed URL Error: " . $e->getMessage());
            throw new Exception("Failed to generate document access link");
        }
    }
}
