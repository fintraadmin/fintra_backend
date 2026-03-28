<?php
include_once 'LoanAPIDAO.php';

class LoanApplicationTest {
    private $loanDao;
    #private $baseUrl = 'http://uat-api.evolutosolution.com';
    private $baseUrl = 'http://api.evolutosolution.com';
    #private $outletId = 'OUI202490896';
    private $outletId = 'OUI2025100343';
    #private $basicAuth = 'MDY4NGFkYmVkZWJjNDk2M2E5YTg3ZTk0MzQ3ZTczYjkxYzZhYTNkMzg1YWZiZWI3NzY3MDdmNGRlOTRkMWE0NzpCZzFjZWFCRjJLNEFVa3F5VmpZS05QaHpYbEc2RFVGMGZ5UDhaNFhhbjFIbEVtdi9UNUZFdDVMS2p2YVE2ay8vRFZJL3JqdG1rbGUrQ1JmdUJUSFJaa0xiOXpoWmc5RFNnTGI4N0JzT2lyL3dyZC9obER5b3dSMTE5ZnE0UUY5dVdud0hqbjlFUllhY1gyMTNZSENOYXRuRlNPUlVmS3VSYUpnQTVlaGVnUjdNUHp4aGRsOGtvTDNMNGtIVm96M3A=';
    private $basicAuth = 'NDExM2Y4NWYyMjZhZTc1NDMwZDJmZTc4MDYwMTU1ZTQxY2U1MTgwZWRmNDdhOTliMTgxOTU5Y2RjOGYzMGM2NDpzUGNhQlNOcXFYV3hEYUJGc3JrRHdHTE94STh6ZTY3YlVqejRoM1VhRVNjL3MvRXV3UTdjSG14VWNTQ1ZlSHVjdXZMRGw1Sm95dy82a2pOSGR5WG9QRHBYTzAraktMM2F0VU03UUNzVW1kVUdlbGhrN3lpQVNxcVRmN3c5OVE4OTdKTmlhUGJjYkhQeFRGQ0g2QmlxTytYMm5zN1d3eGhEYTNoelNKS0RtVmR4MU9Mb1NxY1JqYjhabXFmOUt3OTk=';
    private $testsPassed = 0;
    private $testsFailed = 0;

    public function __construct() {
        $config = [
            'baseUrl' => $this->baseUrl,
            'outletId' => $this->outletId,
            'basicAuth' => $this->basicAuth
        ];
        
        // Use factory to create DAO instance
        $this->loanDao = LoanApplicationDAOFactory::create(
            LoanApplicationDAOFactory::DEFAULT_DAO,
            $config
        );
    }

    /**
     * Run all tests
     */
    public function runTests() {
        echo "Starting Loan Application Tests...\n\n";

        // Test token generation first
        $this->testTokenGeneration();
        //$this->testTokenRequirement();
        //$this->testInvalidCredentials();

        // Generate token for subsequent tests
        try {
            $this->loanDao->generateToken();
        } catch (Exception $e) {
            echo "Failed to generate token for subsequent tests. Aborting.\n";
            return;
        }

        // Run other tests
        $this->testValidLoanEligibility();
        $this->testCibilScore();
        //$this->testInvalidEmail();
       // $this->testInvalidMobile();
       // $this->testInvalidPAN();
        //$this->testInvalidAadhaar();
        //$this->testInvalidCibilScore();
        //$this->testStoreBasicDetails();
        //$this->testInvalidBasicDetails();
        //$this->testSaveReferences();
        //$this->testInvalidReferences();
        //$this->testDocumentUpload();
        //$this->testInvalidDocument();
        //$this->testMissingParameters();
	//$this->testCheckApplicationStatus();

        $this->printTestSummary();
    }
	 /**
     * Test check application status
     */
    public function testCheckApplicationStatus() {
        try {
            $result = $this->loanDao->checkApplicationStatus('SLA00713', 'OUI202490896');
            $this->assert(
                isset($result['status']),
                'Check Application Status Test',
                'Response should contain status field'
            );
            $this->assert(
                isset($result['loan_amount']),
                'Check Application Status Test',
                'Response should contain loan_amount field'
            );
            $this->assert(
                isset($result['banks_status']),
                'Check Application Status Test',
                'Response should contain banks_status field'
            );
        } catch (Exception $e) {
            $this->assert(
                false,
                'Check Application Status Test',
                'Exception: ' . $e->getMessage()
            );
        }
    }

    /**
     * Test token generation
     */
    public function testTokenGeneration() {
        try {
            $token = $this->loanDao->generateToken();
	    echo "Token $token \n"; 
            $this->assert(
                !empty($token),
                'Token Generation Test',
                'Token should not be empty'
            );
        } catch (Exception $e) {
            $this->assert(
                false,
                'Token Generation Test',
                'Exception: ' . $e->getMessage()
            );
        }
    }

    /**
     * Test token requirement for API calls
     */
    public function testTokenRequirement() {
        try {
            // Create new DAO instance without generating token
            $newDao = new LoanAPIDAO($this->baseUrl, $this->outletId, $this->basicAuth);
            $params = $this->getValidParams();
            $newDao->checkEligibility($params);
            $this->assert(
                false,
                'Token Requirement Test',
                'Should have thrown an exception for missing token'
            );
        } catch (Exception $e) {
            $this->assert(
                strpos($e->getMessage(), 'No valid token available')!= false,
                'Token Requirement Test'
            );
        }
    }

    /**
     * Test invalid credentials for token generation
     */
    public function testInvalidCredentials() {
        try {
            $invalidDao = new LoanAPIDAO(
                $this->baseUrl,
                'invalid-outlet',
                'invalid-auth'
            );
            $invalidDao->generateToken();
            $this->assert(
                false,
                'Invalid Credentials Test',
                'Should have thrown an exception'
            );
        } catch (Exception $e) {
            $this->assert(
                true,
                'Invalid Credentials Test'
            );
        }
    }

    /**
     * Test helper method
     */
    private function assert($condition, $testName, $message = '') {
        if ($condition) {
            echo "✓ {$testName} passed\n";
            $this->testsPassed++;
        } else {
            echo "✗ {$testName} failed {$message}\n";
            $this->testsFailed++;
        }
    }

    /**
     * Get valid test parameters
     */
    private function getValidParams() {
        return [
            'ref_code' => 'OUI2025100343',
            'loan_type_id' => '60',
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'mobile' => '9873734310',
            'income_source' => '1',
            'income' => '35000',
            'pincode' => '201301',
            'dob' => '1999-01-27',
            'pan_no' => 'ABCDW8383F',
            'aadhaar_no' => '844712323278',
            'cibil_score' => '750',
            'loan_amount' => '50000'
        ];
    }

    /**
     * Test valid loan eligibility
     */
    public function testValidLoanEligibility() {
        try {
            $params = $this->getValidParams();
            $result = $this->loanDao->checkEligibility($params);
            $this->assert(
                is_array($result), 
                'Valid Loan Eligibility Test',
                'Response should be an array'
            );
        } catch (Exception $e) {
            $this->assert(
                false, 
                'Valid Loan Eligibility Test',
                'Exception: ' . $e->getMessage()
            );
        }
    }

    /**
     * Test invalid email format
     */
    public function testInvalidEmail() {
        try {
            $params = $this->getValidParams();
            $params['email'] = 'invalid-email';
            $this->loanDao->checkEligibility($params);
            $this->assert(
                false, 
                'Invalid Email Test',
                'Should have thrown an exception'
            );
        } catch (Exception $e) {
            $this->assert(
                strpos($e->getMessage(), 'Invalid email')!= false,
                'Invalid Email Test'
            );
        }
    }

    /**
     * Test invalid mobile number
     */
    public function testInvalidMobile() {
        try {
            $params = $this->getValidParams();
            $params['mobile'] = '123';  // Too short
            $this->loanDao->checkEligibility($params);
            $this->assert(
                false, 
                'Invalid Mobile Test',
                'Should have thrown an exception'
            );
        } catch (Exception $e) {
            $this->assert(
                strpos($e->getMessage(), 'Invalid mobile')!=false,
                'Invalid Mobile Test'
            );
        }
    }

    /**
     * Test invalid PAN number
     */
    public function testInvalidPAN() {
        try {
            $params = $this->getValidParams();
            $params['pan_no'] = 'INVALID123';
            $this->loanDao->checkEligibility($params);
            $this->assert(
                false, 
                'Invalid PAN Test',
                'Should have thrown an exception'
            );
        } catch (Exception $e) {
            $this->assert(
                strpos($e->getMessage(), 'Invalid PAN') != false,
                'Invalid PAN Test'
            );
        }
    }

    /**
     * Test invalid Aadhaar number
     */
    public function testInvalidAadhaar() {
        try {
            $params = $this->getValidParams();
            $params['aadhaar_no'] = '12345';  // Too short
            $this->loanDao->checkEligibility($params);
            $this->assert(
                false, 
                'Invalid Aadhaar Test',
                'Should have thrown an exception'
            );
        } catch (Exception $e) {
            $this->assert(
                strpos($e->getMessage(), 'Invalid Aadhaar')!= false,
                'Invalid Aadhaar Test'
            );
        }
    }

    /**
     * Test invalid CIBIL score
     */
    public function testCibilScore() {
        try {
            $params = $this->getValidParams();
            $params['pan_no'] = 'BWWPC4509F';  // Out of range
            $params['name'] = 'SUNIL DADARAO CHAVAN';  // Out of range
            $params['phone'] = '9022188084';  // Out of range
            $params['dob'] = '1984-07-01';  // Out of range
            $params['ref_code'] = 'OUI2025100343';  // Out of range
            $r = $this->loanDao->checkCreditScore($params);
	    echo "output cibil\n";
            print_r($r);
        }
	catch (Exception $e) {
            $this->assert(
                strpos($e->getMessage(), 'Invalid Aadhaar')!= false,
                'Invalid Aadhaar Test'
            );
        }

    }

    /**
     * Test missing parameters
     */
    public function testMissingParameters() {
        try {
            $params = [
                'ref_code' => 'OUI202490896'
                // Missing other required parameters
            ];
            $this->loanDao->checkEligibility($params);
            $this->assert(
                false, 
                'Missing Parameters Test',
                'Should have thrown an exception'
            );
        } catch (Exception $e) {
            $this->assert(
                strpos($e->getMessage(), 'Missing required parameters')!=false,
                'Missing Parameters Test'
            );
        }
    }

    /**
     * Print test summary
     */
    /**
     * Test valid basic details storage
     */
    public function testStoreBasicDetails() {
        try {
            $params = [
                'ref_code' => 'OUI202490896',
                'name' => 'John Doe',
                'mobile' => '9873734310',
                'email' => 'john.doe@example.com',
                'dob' => '1996-01-01',
                'city' => '',
                'pincode' => '121003',
                'income_source' => 1,
                'monthly_income' => '50000',
                'loan_amount' => '50000',
                'pan' => 'ABCDW8383F',
                'aadhaar' => '844712323278',
                'loan_type_id' => '60',
                'preferred_banks' => '[139,236]'
            ];

            $result = $this->loanDao->storeBasicDetails($params);
            $this->assert(
                isset($result['application_id']),
                'Store Basic Details Test',
                'Response should contain application_id'
            );
        } catch (Exception $e) {
            $this->assert(
                false,
                'Store Basic Details Test',
                'Exception: ' . $e->getMessage()
            );
        }
    }

    /**
     * Test invalid basic details
     */
    public function testInvalidBasicDetails() {
        try {
            $params = [
                'ref_code' => 'OUI202490896',
                'name' => 'John Doe',
                // Missing other required fields
            ];
            
            $this->loanDao->storeBasicDetails($params);
            $this->assert(
                false,
                'Invalid Basic Details Test',
                'Should have thrown an exception'
            );
        } catch (Exception $e) {
            $this->assert(
                strpos($e->getMessage(), 'Missing required parameters')!=false,
                'Invalid Basic Details Test'
            );
        }

        // Test invalid preferred_banks format
        try {
            $params = $this->getValidParams();
            $params['preferred_banks'] = 'invalid-json';
            
            $this->loanDao->storeBasicDetails($params);
            $this->assert(
                false,
                'Invalid Preferred Banks Format Test',
                'Should have thrown an exception'
            );
        } catch (Exception $e) {
            $this->assert(
                strpos($e->getMessage(), 'must be a valid JSON array')!=false,
                'Invalid Preferred Banks Format Test'
            );
        }
    }

    /**
     * Test valid references saving
     */
    public function testSaveReferences() {
        try {
            $params = [
                'application_id' => 'SLA00715',
                'ref_code' => 'OUI202490896',
                'reference1' => [
                    'name' => 'Joy',
                    'relationship' => 'Friend',
                    'email' => 'joy@example.com',
                    'phone' => '9876543210',
                    'address' => 'H-781, Infinity Tower, Sector 60, Noida'
                ],
                'reference2' => [
                    'name' => 'Henry',
                    'relationship' => 'Brother',
                    'email' => 'henry@example.com',
                    'phone' => '9876543211',
                    'address' => 'A-21, Residency Tower, Sector 3, Delhi'
                ]
            ];

            $result = $this->loanDao->saveReferences($params);
	    print_r($result);
            $this->assert(
                isset($result['status']) && $result['status'] === 'success',
                'Save References Test',
                'Response should indicate success'
            );
        } catch (Exception $e) {
            $this->assert(
                false,
                'Save References Test',
                'Exception: ' . $e->getMessage()
            );
        }
    }

    /**
     * Test invalid references
     */
    public function testInvalidReferences() {
        // Test missing reference
        try {
            $params = [
                'application_id' => 'LA123456',
                'ref_code' => 'OUI202490896',
                'reference1' => [
                    'name' => 'Joy',
                    'relationship' => 'Friend',
                    'email' => 'joy@example.com',
                    'phone' => '9876543210',
                    'address' => 'Test Address'
                ]
                // Missing reference2
            ];
            
            $this->loanDao->saveReferences($params);
            $this->assert(
                false,
                'Missing Reference Test',
                'Should have thrown an exception'
            );
        } catch (Exception $e) {
            $this->assert(
                strpos($e->getMessage(), 'Missing required field')!=false,
                'Missing Reference Test'
            );
        }

        // Test invalid email format
        try {
            $params = [
                'application_id' => 'LA123456',
                'ref_code' => 'OUI202490896',
                'reference1' => [
                    'name' => 'Joy',
                    'relationship' => 'Friend',
                    'email' => 'invalid-email',
                    'phone' => '9876543210',
                    'address' => 'Test Address'
                ],
                'reference2' => [
                    'name' => 'Henry',
                    'relationship' => 'Brother',
                    'email' => 'henry@example.com',
                    'phone' => '9876543211',
                    'address' => 'Test Address'
                ]
            ];
            
            $this->loanDao->saveReferences($params);
            $this->assert(
                false,
                'Invalid Email Format Test',
                'Should have thrown an exception'
            );
        } catch (Exception $e) {
            $this->assert(
                strpos($e->getMessage(), 'Invalid email format')!=false,
                'Invalid Email Format Test'
            );
        }
    }

    /**
     * Test valid document upload
     */
    public function testDocumentUpload() {
        try {
            // Create a temporary test file
            //$tempFile = tempnam(sys_get_temp_dir(), 'test_doc_.pdf');
            //file_put_contents($tempFile, 'Test content');
	    $tempFile = '/tmp/test.pdf';
            $params = [
                'doc_file' => $tempFile,
                'ref_code' => 'OUI202490896',
                'application_id' => 'SLA00713',
                'doc_type' => 'PAN CARD',
                'doc_no' => 'ABC1234'
            ];

            $result = $this->loanDao->uploadDocument($params);
	    print_r($result);
            $this->assert(
                isset($result['status']) && $result['status'] === 'success',
                'Document Upload Test',
                'Response should indicate success'
            );

            // Clean up
            unlink($tempFile);
        } catch (Exception $e) {
            $this->assert(
                false,
                'Document Upload Test',
                'Exception: ' . $e->getMessage()
            );
        }
    }

    /**
     * Test invalid document upload
     */
    public function testInvalidDocument() {
        // Test missing file
        try {
            $params = [
                'doc_file' => '/non/existent/file.pdf',
                'ref_code' => 'OUI202490896',
                'application_id' => 'SLA00713',
                'doc_type' => 'PAN CARD',
                'doc_no' => 'ABC1234'
            ];
            
            $this->loanDao->uploadDocument($params);
            $this->assert(
                false,
                'Missing File Test',
                'Should have thrown an exception'
            );
        } catch (Exception $e) {
            $this->assert(
                strpos($e->getMessage(), 'File not found')!=false,
                'Missing File Test'
            );
        }

        // Test invalid document type
        try {
            // Create a temporary test file
            $tempFile = tempnam(sys_get_temp_dir(), 'test_doc_');
            file_put_contents($tempFile, 'Test content');

            $params = [
                'doc_file' => $tempFile,
                'ref_code' => 'OUI202490896',
                'application_id' => 'SLA00713',
                'doc_type' => 'INVALID_TYPE',
                'doc_no' => 'ABC1234'
            ];
            
            $this->loanDao->uploadDocument($params);
            $this->assert(
                false,
                'Invalid Document Type Test',
                'Should have thrown an exception'
            );

            // Clean up
            unlink($tempFile);
        } catch (Exception $e) {
            $this->assert(
                strpos($e->getMessage(), 'Invalid document type')!=false,
                'Invalid Document Type Test'
            );
        }
    }

    private function printTestSummary() {
        echo "\nTest Summary:\n";
        echo "-------------\n";
        echo "Tests Passed: {$this->testsPassed}\n";
        echo "Tests Failed: {$this->testsFailed}\n";
        echo "Total Tests: " . ($this->testsPassed + $this->testsFailed) . "\n";
    }
}

// Usage:
// Include your DAO class

// Create and run tests
$tester = new LoanApplicationTest();
$tester->runTests();
