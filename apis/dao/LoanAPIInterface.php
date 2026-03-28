<?php

interface LoanApplicationDAOInterface {
    /**
     * Generate security token for API access
     * @return string Generated token
     * @throws Exception If token generation fails
     */
    public function generateToken();

    /**
     * Check loan eligibility
     * @param array $params Array containing all required parameters
     * @return array Response from the API
     * @throws Exception If required parameters are missing or API call fails
     */
    public function checkEligibility($params);

    /**
     * Store basic loan details
     * @param array $params Array containing all required parameters
     * @return array Response from the API
     * @throws Exception If required parameters are missing or API call fails
     */
    public function storeBasicDetails($params);

    /**
     * Save loan references
     * @param array $params Array containing application_id, ref_code, and reference details
     * @return array Response from the API
     * @throws Exception If required parameters are missing or API call fails
     */
    public function saveReferences($params);

    /**
     * Upload loan document
     * @param array $params Array containing document details and file path
     * @return array Response from the API
     * @throws Exception If required parameters are missing or API call fails
     */
    public function uploadDocument($params);

     /**
     * Check loan application status
     * @param string $loanApplicationId Loan application ID
     * @param string $refCode Reference code
     * @return array Application status details
     */
    public function checkApplicationStatus($loanApplicationId, $refCode);
}

/**
 * Factory class for creating DAO instances
 */
class LoanApplicationDAOFactory {
    // DAO types
    const DEFAULT_DAO = 'default';
    const MOCK_DAO = 'mock';

    /**
     * Create a DAO instance
     */
    public static function create($type, $config) {
        switch ($type) {
            case self::DEFAULT_DAO:
                return new LoanAPIDAO(
                    $config['baseUrl'] ?? '',
                    $config['outletId'] ?? '',
                    $config['basicAuth'] ?? ''
                );
            
            case self::MOCK_DAO:
                return new MockLoanApplicationDAO(
                    $config['baseUrl'] ?? '',
                    $config['outletId'] ?? '',
                    $config['basicAuth'] ?? ''
                );

            default:
                throw new Exception("Invalid DAO type specified: {$type}");
        }
    }
}

/**
 * Mock implementation for testing
 */
class MockLoanApplicationDAO implements LoanApplicationDAOInterface {
    private $baseUrl;
    private $outletId;
    private $basicAuth;
    private $token;

    public function __construct($baseUrl, $outletId, $basicAuth) {
        $this->baseUrl = $baseUrl;
        $this->outletId = $outletId;
        $this->basicAuth = $basicAuth;
        $this->token = null;
    }

    public function generateToken() {
        $this->token = 'mock-token-' . uniqid();
        return $this->token;
    }

    public function checkEligibility($params) {
        $this->validateParams($params);
        
        return [
            'status' => 'success',
            'eligible' => true,
            'message' => 'Mock eligibility check successful'
        ];
    }

    public function storeBasicDetails($params) {
        $this->validateBasicDetailsParams($params);
        
        return [
            'status' => 'success',
            'message' => 'Application created successfully',
            'application_id' => 'APP' . time()
        ];
    }

    public function saveReferences($params) {
        $this->validateMockReferenceParams($params);
        
        return [
            'status' => 'success',
            'message' => 'References saved successfully',
            'application_id' => $params['application_id']
        ];
    }

    public function uploadDocument($params) {
        $this->validateMockDocumentParams($params);
        
        return [
            'status' => 'success',
            'message' => 'Document uploaded successfully',
            'application_id' => $params['application_id'],
            'doc_type' => $params['doc_type'],
            'doc_url' => 'mock_url/documents/' . basename($params['doc_file'])
        ];
    }

    // Validation methods remain the same but without type hints
    private function validateParams($params) {
        // Implementation...
    }

    private function validateBasicDetailsParams($params) {
        // Implementation...
    }

    private function validateMockReferenceParams($params) {
        // Implementation...
    }

    private function validateMockDocumentParams($params) {
        // Implementation...
    }
   public function checkApplicationStatus($loanApplicationId, $refCode) {
        if (empty($loanApplicationId) || empty($refCode)) {
            throw new Exception('Loan application ID and reference code are required');
        }
        
        return [
            'application_id' => $loanApplicationId,
            'status_code' => 0,
            'loan_amount' => 50000,
            'status' => 'INITIATED',
            'remarks' => null,
            'banks_status' => [
                [
                    'bank_id' => null,
                    'bank_name' => '',
                    'bank_logo' => '',
                    'bank_status_code' => null,
                    'bank_status' => ''
                ]
            ]
        ];
    }
    
}
