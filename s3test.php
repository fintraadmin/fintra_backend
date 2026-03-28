<?php
// tests/s3_utility_test.php

require_once 's3utils.php';

class SimpleS3Test {
    private $s3Utility;
    private $testResults = [];
    
    public function __construct() {
        $this->s3Utility = new S3Utility();
    }
    
    public function runTests() {
        // Run all test cases
        $this->testFileUpload();
        $this->testInvalidFileSize();
        $this->testInvalidFileType();
        
        // Print results
        $this->printResults();
    }
    
    private function testFileUpload() {
        try {
            // Create test file
            $testFile = [
                'name' => 'test.pdf',
                'type' => 'application/pdf',
                'size' => 1024 * 1024, // 1MB
                'tmp_name' => $this->createTempFile(),
                'error' => 0
            ];
            
            $result = $this->s3Utility->uploadFile($testFile, 'TEST123', 'pan');
            
            // Check if result contains expected path format
            $this->assert(
                'File Upload Test',
                strpos($result, 'documents/TEST123/pan_') !== false,
                'Should return valid S3 path'
            );
        } catch (Exception $e) {
            $this->assert('File Upload Test', false, $e->getMessage());
        }
    }
    
    private function testInvalidFileSize() {
        try {
            $largeFile = [
                'name' => 'large.pdf',
                'type' => 'application/pdf',
                'size' => 3 * 1024 * 1024, // 3MB (exceeds limit)
                'tmp_name' => $this->createTempFile(),
                'error' => 0
            ];
            
            $this->s3Utility->uploadFile($largeFile, 'TEST123', 'pan');
            $this->assert('File Size Test', false, 'Should reject large files');
        } catch (Exception $e) {
            $this->assert(
                'File Size Test',
                $e->getMessage() === 'File size exceeds 2MB limit',
                'Should throw correct error message'
            );
        }
    }
    
    private function testInvalidFileType() {
        try {
            $invalidFile = [
                'name' => 'test.exe',
                'type' => 'application/exe',
                'size' => 1024,
                'tmp_name' => $this->createTempFile(),
                'error' => 0
            ];
            
            $this->s3Utility->uploadFile($invalidFile, 'TEST123', 'pan');
            $this->assert('File Type Test', false, 'Should reject invalid file types');
        } catch (Exception $e) {
            $this->assert(
                'File Type Test',
                $e->getMessage() === 'Invalid file type',
                'Should throw correct error message'
            );
        }
    }
    
    private function assert($testName, $condition, $message) {
        $this->testResults[] = [
            'name' => $testName,
            'passed' => $condition,
            'message' => $message
        ];
    }
    
    private function createTempFile() {
        $tmpFile = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($tmpFile, 'Test content');
        return $tmpFile;
    }
    
    private function printResults() {
        echo "\nTest Results:\n";
        echo "------------\n";
        
        foreach ($this->testResults as $result) {
            echo sprintf(
                "%s: %s\n%s\n\n",
                $result['name'],
                $result['passed'] ? 'PASSED' : 'FAILED',
                $result['message']
            );
        }
    }
}

// Run tests
$test = new SimpleS3Test();
$test->runTests();
?>
