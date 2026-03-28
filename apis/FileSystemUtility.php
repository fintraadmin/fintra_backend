<?php
// utils/FileSystemUtility.php

class FileSystemUtility {
    private $baseUploadPath;
    private $allowedTypes;
    private $maxFileSize;
    
    public function __construct() {
        // Set base upload directory - you can modify this as needed
        $this->baseUploadPath = dirname(__DIR__) . '/uploads';
        
        // Set allowed file types
        $this->allowedTypes = [
            'image/jpeg',
            'image/png',
            'application/pdf',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];
        
        // Set max file size (2MB)
        $this->maxFileSize = 2 * 1024 * 1024;
        
        // Ensure base upload directory exists
        $this->initializeUploadDirectory();
    }
    
    /**
     * Upload a file to local filesystem
     */
    public function uploadFile($file, $leadId, $documentType) {
        try {
            // Validate file
            $this->validateFile($file);
            
            // Create lead directory if it doesn't exist
            $leadPath = $this->baseUploadPath . '/documents/' . $leadId;
            if (!is_dir($leadPath)) {
                if (!mkdir($leadPath, 0755, true)) {
                    throw new Exception("Failed to create directory structure");
                }
            }
            
            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = $documentType . '_' . time() . '.' . $extension;
            $filePath = $leadPath . '/' . $filename;
            
            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                throw new Exception("Failed to move uploaded file");
            }
            
            // Return relative path from base upload directory
            return 'documents/' . $leadId . '/' . $filename;
            
        } catch (Exception $e) {
            error_log("File Upload Error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Validate uploaded file
     */
    private function validateFile($file) {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("File upload failed with error code: " . $file['error']);
        }
        
        // Check file size
        if ($file['size'] > $this->maxFileSize) {
            throw new Exception("File size exceeds 2MB limit");
        }
        
        // Check file type
        if (!in_array($file['type'], $this->allowedTypes)) {
            throw new Exception("Invalid file type: " . $file['type']);
        }
        
        // Additional security check for file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $this->allowedTypes)) {
            throw new Exception("Invalid file content type");
        }
    }
    
    /**
     * Get full file path
     */
    public function getFilePath($relativePath) {
        $fullPath = $this->baseUploadPath . '/' . $relativePath;
        
        if (!file_exists($fullPath)) {
            throw new Exception("File not found");
        }
        
        return $fullPath;
    }
    
    /**
     * Create a temporary URL for file access
     */
    public function getTemporaryUrl($relativePath, $expiryMinutes = 60) {
        $fullPath = $this->getFilePath($relativePath);
        
        // Generate a temporary token
        $token = bin2hex(random_bytes(16));
        
        // Store token with expiry in session or database
        $_SESSION['file_tokens'][$token] = [
            'path' => $relativePath,
            'expiry' => time() + ($expiryMinutes * 60)
        ];
        
        // Return URL with token
        return '/download.php?token=' . $token;
    }
    
    /**
     * Delete a file
     */
    public function deleteFile($relativePath) {
        try {
            $fullPath = $this->getFilePath($relativePath);
            
            if (!unlink($fullPath)) {
                throw new Exception("Failed to delete file");
            }
            
            return true;
        } catch (Exception $e) {
            error_log("File Delete Error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Initialize upload directory
     */
    private function initializeUploadDirectory() {
        if (!is_dir($this->baseUploadPath)) {
            if (!mkdir($this->baseUploadPath, 0755, true)) {
                throw new Exception("Failed to create upload directory");
            }
        }
        
        // Create .htaccess to prevent direct access
        $htaccess = $this->baseUploadPath . '/.htaccess';
        if (!file_exists($htaccess)) {
            file_put_contents($htaccess, "Deny from all");
        }
    }
}
?>
