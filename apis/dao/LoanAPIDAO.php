<?php
include_once 'LoanAPIInterface.php';

class LoanAPIDAO implements LoanApplicationDAOInterface {
    private $baseUrl;
    private $token;
    private $headers;
    private $outletId;
    private $basicAuth;

    /**
     * Constructor to initialize the DAO with base URL and credentials
     */
    public function __construct($baseUrl, $outletId, $basicAuth) {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->outletId = $outletId;
        $this->basicAuth = $basicAuth;
        $this->token = null;
        $this->headers = ['Content-Type: application/json'];
    }

    /**
     * Generate security token for API access
     */
    public function generateToken() {
        $authHeaders = [
            'outletid: ' . $this->outletId,
            'Authorization: Basic ' . $this->basicAuth,
            'Content-Type: application/json'
        ];

        try {
            $response = $this->makeApiCall('/v1/authentication/', 'POST', [], $authHeaders);
            if (!isset($response['data']['token'])) {
                throw new Exception('Token not found in authentication response');
            }

            $this->token = $response['data']['token'];
            $this->headers = [
                'token: ' . $this->token,
                'Content-Type: application/json'
            ];

            return $this->token;
        } catch (Exception $e) {
            throw new Exception("Token generation failed: " . $e->getMessage());
        }
    }

    /**
     * Check loan eligibility
     */
    public function checkEligibility($params) {
        $this->ensureValidToken();

        // Required parameters for eligibility check
        $requiredParams = [
            'ref_code', 'loan_type_id', 'name', 'email', 'mobile',
            'income_source', 'income', 'pincode', 'dob', 'pan_no',
            'aadhaar_no', 'cibil_score', 'loan_amount'
        ];

        // Validate required parameters
        //$this->validateParams($params, $requiredParams);

        try {
            $response = $this->makeApiCall('/v1/loan/checkEligibility', 'POST', $params);
	    //error_log("=====". json_encode($response, true));
            return $this->processResponse($response);
	    //return $response;
        } catch (Exception $e) {
            throw new Exception( $e->getMessage());
        }
    }

    public function checkCreditScore($params) {
        $this->ensureValidToken();
	$name = explode(" " ,  $params['name']);
	$params['fname'] = $name[0];
	$params['lname'] = $name[1];
        // Required parameters for eligibility check
        $requiredParams = [
             'pan_no' ,  'ref_code', 'dob', 'fname' , 'phone', 'lname'
        ];

        // Validate required parameters
        //$this->validateParams($params, $requiredParams);
	$p1 = array();
	foreach($requiredParams  as $p){
		$p1[$p] = $params[$p];
	}

        try {
            $response = $this->makeApiCall('/v1/loan/checkCreditScore', 'POST', $p1);
            return $this->processResponse($response);
            //return $response;
        } catch (Exception $e) {
            throw new Exception( $e->getMessage());
        }
    }


    /**
     * Store basic loan details
     */
    public function storeBasicDetails($params) {
        $this->ensureValidToken();

        $requiredParams = [
            'ref_code', 'name', 'mobile', 'email', 'dob', 'pincode',
            'income_source', 'monthly_income', 'loan_amount', 'pan',
            'aadhaar', 'loan_type_id', 'preferred_banks'
        ];

        //$this->validateBasicDetailsParams($params, $requiredParams);

        try {
            $response = $this->makeApiCall('/v1/loan/storeBasicDetails', 'POST', $params);
            return $this->processResponse($response);
        } catch (Exception $e) {
            throw new Exception("Store basic details failed: " . $e->getMessage());
        }
    }

    /**
     * Save loan references
     */
    public function saveReferences($params) {
        $this->ensureValidToken();
        //$this->validateReferenceParams($params);

        try {
            $response = $this->makeApiCall('/v1/loan/saveRefrences', 'POST', $params);
            return $this->processResponse($response);
        } catch (Exception $e) {
            throw new Exception("Save references failed: " . $e->getMessage());
        }
    }

    /**
     * Upload loan document
     */
    public function uploadDocument($params) {
        $this->ensureValidToken();
        //$this->validateDocumentParams($params);

        try {
            $response = $this->makeMultipartApiCall('/v1/loan/saveDocs', $params);
	    //print_r($response);
            return $this->processResponse($response);
        } catch (Exception $e) {
            throw new Exception("Document upload failed: " . $e->getMessage());
        }
    }

	public function checkApplicationStatus($loanApplicationId, $refCode) {
        $this->ensureValidToken();

        if (empty($loanApplicationId) || empty($refCode)) {
            throw new Exception('Loan application ID and reference code are required');
        }

        try {
            $params = [
                'loan_application_id' => $loanApplicationId,
                'ref_code' => $refCode
            ];

            $response = $this->makeApiCall(
                '/v1/loan/status',
                'GET',
                $params
            );
	    //print_r($response);
            return $this->processResponse($response);
        } catch (Exception $e) {
            throw new Exception("Status check failed: " . $e->getMessage());
        }
    }
    /**
     * Make API call with multipart/form-data
     */
    private function makeMultipartApiCall($endpoint, $data) {
        $curl = curl_init();
        $url = $this->baseUrl . $endpoint;
        
        if (!file_exists($data['doc_file'])) {
            throw new Exception("File not found: " . $data['doc_file']);
        }

        $cfile = new \CURLFile(
            $data['doc_file'],
            $this->getMimeType($data['doc_file']),
            basename($data['doc_file'])
        );

        $formData = [
            'doc_file' => $cfile,
            'ref_code' => $data['ref_code'],
            'application_id' => $data['application_id'],
            'doc_type' => $data['doc_type'],
            'doc_no' => $data['doc_no']
        ];

        $curlOptions = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $formData,
            CURLOPT_HTTPHEADER => [
                'token: ' . $this->token
            ]
        ];

        curl_setopt_array($curl, $curlOptions);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            throw new Exception("cURL Error: " . $err);
        }

        return json_decode($response, true);
    }

    /**
     * Make regular API call
     */
    private function makeApiCall($endpoint, $method, $data = [], $customHeaders = null) {
        $curl = curl_init();
        $url = $this->baseUrl . $endpoint;
        $curlOptions = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER =>1,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $customHeaders ?? $this->headers
        ];

        if (!empty($data)) {
            $curlOptions[CURLOPT_POSTFIELDS] = json_encode($data);
        }
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt_array($curl, $curlOptions);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            throw new Exception("cURL Error: " . $err);
        }

        return json_decode($response, true);
    }

    /**
     * Get MIME type of file
     */
    private function getMimeType($filePath) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filePath);
        finfo_close($finfo);
        return $mimeType;
    }

    /**
     * Validate document upload parameters
     */
    private function validateDocumentParams($params) {
        $required = ['doc_file', 'ref_code', 'application_id', 'doc_type', 'doc_no'];
        
        foreach ($required as $field) {
            if (!isset($params[$field]) || empty($params[$field])) {
                throw new Exception("Missing required field: {$field}");
            }
        }

        if (!file_exists($params['doc_file'])) {
            throw new Exception("File not found: " . $params['doc_file']);
        }

        // Validate file size (max 5MB)
        $maxSize = 5 * 1024 * 1024;
        if (filesize($params['doc_file']) > $maxSize) {
            throw new Exception("File size exceeds maximum limit of 5MB");
        }

        $allowedTypes = [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/jpg'
        ];
        
        $mimeType = $this->getMimeType($params['doc_file']);
        if (!in_array($mimeType, $allowedTypes)) {
            throw new Exception("Invalid file type. Allowed types: PDF, JPEG, PNG");
        }

        $allowedDocTypes = [
            'PAN CARD',
            'AADHAR CARD',
            'SALARY SLIP',
            'BANK STATEMENT'
        ];

        if (!in_array($params['doc_type'], $allowedDocTypes)) {
            throw new Exception("Invalid document type. Allowed types: " . implode(', ', $allowedDocTypes));
        }
    }

    /**
     * Ensure valid token exists
     */
    private function ensureValidToken() {
        if (empty($this->token)) {
            throw new Exception('No valid token available. Call generateToken() first.');
        }
    }

    /**
     * Process API response
     */
     private function processResponse($response) {
        if (!is_array($response)) {
            throw new Exception("Invalid API response format");
        }

        if (isset($response['success']) && !$response['success']) {

	    //fetch arrors as string
	    $errors = array();
	    $errors[] = $response['message'] ?? 'API request failed';
	    if(isset($response['data'])){
		 foreach ($response['data'] as $key => $valueArray) {
            	// Get the first value from each array
            	if (is_array($valueArray) && !empty($valueArray)) {
                	$errors[] = $valueArray[0];
            	}
            }
	    }
            throw new Exception(json_encode($errors));
        }

        if (isset($response['data'])) {
            return $response['data'];
        }

        return $response;
    }
    // Other validation methods...
}
