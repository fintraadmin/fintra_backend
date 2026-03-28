<?php
// tests/FileSystemTest.php

require_once 'apis/FileSystemUtility.php';

class FileSystemTest {
    private $fsUtil;
    private $testResults = [];
    private $testFiles = [];
    private $debug = true; // Debug flag
    
    public function __construct() {
        $this->debug("Initializing FileSystem Test Suite");
        $this->fsUtil = new FileSystemUtility();
        $this->initializeTestFiles();
    }
    
    private function debug($message, $type = 'INFO') {
        if ($this->debug) {
            $timestamp = date('Y-m-d H:i:s');
            echo "\n[$timestamp][$type] $message";
        }
    }
    
    private function initializeTestFiles() {
        $this->debug("Creating test files...");
        
        // Create test PDF file
        $pdfContent = "%PDF-1.4\nTest PDF content";
        $this->createTestFile('test.pdf', $pdfContent, 'application/pdf');
        $this->debug("Created test PDF file");
        
        // Create test image file
        $imageContent = base64_decode('R0lGODlhAQABAIAAAAUEBAAAACwAAAAAAQABAAACAkQBADs=');
        $this->createTestFile('test.jpg', $imageContent, 'image/jpeg');
        $this->debug("Created test image file");
        
        // Create oversized file
        $largeContent = str_repeat('A', 3 * 1024 * 1024); // 3MB
        $this->createTestFile('large.pdf', $largeContent, 'application/pdf');
        $this->debug("Created oversized test file (3MB)");
    }
    
    private function createTestFile($filename, $content, $type) {
        $tmpFile = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($tmpFile, $content);
        
        $this->testFiles[$filename] = [
            'name' => $filename,
            'type' => $type,
            'size' => strlen($content),
            'tmp_name' => $tmpFile,
            'error' => UPLOAD_ERR_OK
        ];
        
        $this->debug("Created temporary file: $tmpFile ($filename)");
    }
    
    public function runTests() {
        $this->debug("\nStarting test execution...", "START");
        
        $this->testValidFileUpload();
        $this->testOversizedFile();
        $this->testInvalidFileType();
        $this->testFileAccess();
        $this->testFileDelete();
        
        $this->debug("\nAll tests completed.", "END");
        $this->printResults();
        $this->cleanup();
    }
    
    private function testValidFileUpload() {
        $this->debug("\nRunning: Valid File Upload Test", "TEST");
        try {
            $file = $this->testFiles['test.pdf'];
            $this->debug("Attempting to upload: {$file['name']} ({$file['size']} bytes)");
            
            $result = $this->fsUtil->uploadFile($file, 'TEST123', 'document');
            $this->debug("File uploaded successfully. Path: $result");
            
            $this->assert(
                'Valid File Upload',
                strpos($result, 'documents/TEST123/document_') !== false &&
                file_exists($this->fsUtil->getFilePath($result)),
                'Should successfully upload valid file'
            );
        } catch (Exception $e) {
            $this->debug("Upload failed: " . $e->getMessage(), "ERROR");
            $this->assert('Valid File Upload', false, 'Failed: ' . $e->getMessage());
        }
    }
    
    private function testOversizedFile() {
        $this->debug("\nRunning: Oversized File Test", "TEST");
        try {
            $file = $this->testFiles['large.pdf'];
            $this->debug("Attempting to upload oversized file: {$file['size']} bytes");
            
            $this->fsUtil->uploadFile($file, 'TEST123', 'document');
            $this->assert('Oversized File', false, 'Should reject oversized file');
        } catch (Exception $e) {
            $this->debug("Expected error received: " . $e->getMessage());
            $this->assert(
                'Oversized File',
                $e->getMessage() === 'File size exceeds 2MB limit',
                'Should throw correct error message'
            );
        }
    }
    
    private function testInvalidFileType() {
        $this->debug("\nRunning: Invalid File Type Test", "TEST");
        try {
            $invalidFile = $this->testFiles['test.pdf'];
            $invalidFile['type'] = 'application/exe';
            $this->debug("Attempting to upload file with invalid type: {$invalidFile['type']}");
            
            $this->fsUtil->uploadFile($invalidFile, 'TEST123', 'document');
            $this->assert('Invalid File Type', false, 'Should reject invalid file type');
        } catch (Exception $e) {
            $this->debug("Expected error received: " . $e->getMessage());
            $this->assert(
                'Invalid File Type',
                strpos($e->getMessage(), 'Invalid file type') !== false,
                'Should throw correct error message'
            );
        }
    }
    
    private function testFileAccess() {
        $this->debug("\nRunning: File Access Test", "TEST");
        try {
            $file = $this->testFiles['test.pdf'];
            $this->debug("Uploading test file for access check");
            
            $path = $this->fsUtil->uploadFile($file, 'TEST123', 'document');
            $this->debug("File uploaded to: $path");
            
            $fullPath = $this->fsUtil->getFilePath($path);
            $this->debug("Checking file existence at: $fullPath");
            
            $tempUrl = $this->fsUtil->getTemporaryUrl($path);
            $this->debug("Generated temporary URL: $tempUrl");
            
            $this->assert(
                'File Access',
                file_exists($fullPath),
                'Should be able to access uploaded file'
            );
        } catch (Exception $e) {
            $this->debug("Access test failed: " . $e->getMessage(), "ERROR");
            $this->assert('File Access', false, 'Failed: ' . $e->getMessage());
        }
    }
    
    private function testFileDelete() {
        $this->debug("\nRunning: File Deletion Test", "TEST");
        try {
            $file = $this->testFiles['test.pdf'];
            $this->debug("Uploading test file for deletion");
            
            $path = $this->fsUtil->uploadFile($file, 'TEST123', 'temp_doc');
            $this->debug("File uploaded to: $path");
            
            $result = $this->fsUtil->deleteFile($path);
            $this->debug("File deletion " . ($result ? "successful" : "failed"));
            
            $this->assert(
                'File Deletion',
                $result && !file_exists($this->fsUtil->getFilePath($path)),
                'Should successfully delete file'
            );
        } catch (Exception $e) {
            $this->debug("Deletion failed: " . $e->getMessage(), "ERROR");
            $this->assert('File Deletion', false, 'Failed: ' . $e->getMessage());
        }
    }
    
    private function assert($testName, $condition, $message) {
        $status = $condition ? "PASSED" : "FAILED";
        $this->debug("Test '$testName': $status - $message", $status);
        
        $this->testResults[] = [
            'name' => $testName,
            'passed' => $condition,
            'message' => $message
        ];
    }
    
    private function printResults() {
        $this->debug("\nTest Results Summary:", "SUMMARY");
        echo "\n-----------------------------";
        
        $passed = 0;
        $failed = 0;
        
        foreach ($this->testResults as $result) {
            $status = $result['passed'] ? "PASSED" : "FAILED";
            echo sprintf(
                "\n%s: %s\n%s\n",
                $result['name'],
                $status,
                $result['message']
            );
            
            $result['passed'] ? $passed++ : $failed++;
        }
        
        echo "\n-----------------------------";
        echo "\nTotal Tests: " . count($this->testResults);
        echo "\nPassed: $passed";
        echo "\nFailed: $failed";
        echo "\n-----------------------------\n";
    }
    
    private function cleanup() {
        $this->debug("\nCleaning up test files...", "CLEANUP");
        foreach ($this->testFiles as $file) {
            if (file_exists($file['tmp_name'])) {
                unlink($file['tmp_name']);
                $this->debug("Removed: {$file['tmp_name']}");
            }
        }
        $this->debug("Cleanup completed");
    }
}

// Run the tests
$test = new FileSystemTest();
$test->runTests();
?>
