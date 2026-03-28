<?php
// api/index.php
set_include_path(get_include_path() . PATH_SEPARATOR . '/var/www/html');
require_once 'utils/memcache.php';
require_once 'apis/dao/Database.php';
require_once 'apis/dao/LoanApplicationDAO.php';
require_once 'apis/dao/DocumentDAO.php';
include_once 'apis/dao/LoanAPIDAO.php';
//require_once 'apis/dao/S3Utility.php';

header('Content-Type: application/json');

// Get request method and path

$start= microtime(true);
$service = $_REQUEST['service'];
$aid=$_REQUEST['aid'];
$cid=$_REQUEST['cid'];
$type=$_REQUEST['product'];

$body = file_get_contents('php://input');

$params =  json_decode($body , true); // JSON Body is expected

if(empty($body))
 $params = $_REQUEST;

#$baseUrl = 'http://uat-api.evolutosolution.com';
$baseUrl = 'http://api.evolutosolution.com';
#$outletId = 'OUI202490896';
$outletId = 'OUI2025100343';
#$basicAuth = 'MDY4NGFkYmVkZWJjNDk2M2E5YTg3ZTk0MzQ3ZTczYjkxYzZhYTNkMzg1YWZiZWI3NzY3MDdmNGRlOTRkMWE0NzpCZzFjZWFCRjJLNEFVa3F5VmpZS05QaHpYbEc2RFVGMGZ5UDhaNFhhbjFIbEVtdi9UNUZFdDVMS2p2YVE2ay8vRFZJL3JqdG1rbGUrQ1JmdUJUSFJaa0xiOXpoWmc5RFNnTGI4N0JzT2lyL3dyZC9obER5b3dSMTE5ZnE0UUY5dVdud0hqbjlFUllhY1gyMTNZSENOYXRuRlNPUlVmS3VSYUpnQTVlaGVnUjdNUHp4aGRsOGtvTDNMNGtIVm96M3A=';
$basicAuth = 'NDExM2Y4NWYyMjZhZTc1NDMwZDJmZTc4MDYwMTU1ZTQxY2U1MTgwZWRmNDdhOTliMTgxOTU5Y2RjOGYzMGM2NDpzUGNhQlNOcXFYV3hEYUJGc3JrRHdHTE94STh6ZTY3YlVqejRoM1VhRVNjL3MvRXV3UTdjSG14VWNTQ1ZlSHVjdXZMRGw1Sm95dy82a2pOSGR5WG9QRHBYTzAraktMM2F0VU03UUNzVW1kVUdlbGhrN3lpQVNxcVRmN3c5OVE4OTdKTmlhUGJjYkhQeFRGQ0g2QmlxTytYMm5zN1d3eGhEYTNoelNKS0RtVmR4MU9Mb1NxY1JqYjhabXFmOUt3OTk=';
$config = [
            'baseUrl' => $baseUrl,
            'outletId' => $outletId,
            'basicAuth' => $basicAuth
];
try {
    $loanDao = new LoanApplicationDAO();
    $documentDao = new DocumentDAO();
   // Use factory to create DAO instance
    $loanAPIDao = LoanApplicationDAOFactory::create(
            LoanApplicationDAOFactory::DEFAULT_DAO,
            $config
        );
    
    $loanAPIDao->generateToken();
    switch($service) {
        case 'submit-personal-info':
            
            $data = json_decode(file_get_contents('php://input'), true);
            //validatePersonalInfo($data);
	    //check Eligibility
	    if($data['income_source'] == '2'){ //Business
	    	$data['loan_type_id']= 61;
	    }
	    if($data['income_source'] == '1'){ //Personal
	    	$data['loan_type_id']= 60;
	    }
            $data['pan'] = strtoupper($data['pan']);
            $data['pan_no'] = $data['pan'];
            $data['aadhaar_no'] = $data['aadhaar'];
            $data['ref_code'] = $outletId;
            $data['fname'] = $data['name'];
            $data['phone'] = $data['mobile'];
	    error_log("====". json_encode($data, true)); 
	    //$data['cibil_score']= 650;
	    //$credit_score= $data['cibil_score'];
	    try{
	    	$credit = $loanAPIDao->checkCreditScore($data);
		$credit_score = $credit['score'];
		error_log("==== cibil fetch success for ". $data['pan'] );
		error_log("==== cibil fetch details success". json_encode($credit, true) );
	    }
	    catch(Exception $e){
		error_log("==== cibil fetch exception for ". $data['pan'] );
		error_log("==== cibil fetch details error ". json_encode($data, true) );
		$credit_score = 400;
	    }
	    error_log("===== cibil $credit_score");
	    $data['cibil_score']= $credit_score;
	    $data['credit_score']= $credit_score;
            $result = $loanAPIDao->checkEligibility($data);
	    error_log("====banks ". json_encode($result, true));
	    // get eligible banks and store in memcache
	    $banks = $result;
	    if(empty($banks)){
		$b = array();
		$b['id'] = '119';
		$b['bank'] = 'Yes Bank';
		$b["loan_amount"]= "50K-5000K";
		$b["tenure"]= "12-60 months";
		$b["bank_logo"]= "Yes Bank.png";
		$banks[] =$b;
            }
	    foreach($banks as &$b){
		$b['logo_url'] = 'https://d3e0ld6arspcd6.cloudfront.net/evoluto/bank_logo/' . rawurlencode($b['bank_logo']);
	    }
	    $memcache_key = rand();
	    $memcache_key_banks = $memcache_key . '-banks';   
    	    MemcacheUtil::setItem($memcache_key , serialize($data));
    	    MemcacheUtil::setItem($memcache_key_banks , serialize($banks));
            /*$data['preferred_banks'] = 119;
            $data['monthly_income'] = $data['income'];
            $result = $loanAPIDao->storeBasicDetails($data);
	    $application_id = $result['application_id'];
	    $data['application_id'] = $application_id;
            $leadId = $loanDao->createApplication($data);
            echo json_encode(['success' => true, 'lead_id' => $leadId]);
            */
            echo json_encode(['success' => true, 'lead_id' => $memcache_key]);

	    break;

	case 'submit-loan-selection':
	   $data = json_decode(file_get_contents('php://input'), true);
            $data['preferred_banks'] = implode(",", $data['selected_providers']);
	    $key = $data['lead_id']; 
	    //get data from memcache
	    $data = unserialize(MemcacheUtil::getItem($key));
	    error_log("======= data" . json_encode($data, true));
            $data['monthly_income'] = $data['income'];
            $result = $loanAPIDao->storeBasicDetails($data);
	    $application_id = $result['application_id'];
	    $data['application_id'] = $application_id;
            $leadId = $loanDao->createApplication($data);
	    $postData = $data;
	    $postData['Lead_ID'] = $leadId;
	    $postData['agentId'] = $aid;
	    #$callback_url = 'https://findiuat-coreapi.tsiindia.co.in/api/Fintra/FintraLoanWebhook';
	    $callback_url = 'https://findipay.in:4443/api/Fintra/FintraLoanWebhook';
	    $response = callCallbackUrl($callback_url, $postData);
            echo json_encode(['success' => true, 'lead_id' => $leadId]);
	   error_log("===== loan selection $leadId" . json_encode($data, true));	
	   break;
        case 'upload-document':
            error_log("===== uploading document");  
            if (!isset($_FILES['document']) || !isset($_POST['document_type']) || !isset($_POST['lead_id'])) {
                throw new Exception('Missing required fields');
            }
            $ext = pathinfo($_FILES['document']['name'], PATHINFO_EXTENSION);
	    $local_file = '/tmp/'. $_POST['lead_id'] . '-' . $_POST['document_type'] . '.' . $ext;
	    move_uploaded_file($_FILES['document']['tmp_name'] , $local_file); 
            $docId = $documentDao->saveDocument(
                $_POST['lead_id'],
                $_POST['document_type'],
                $_FILES['document'],
		$local_file
            );
		if($_POST['document_type'] == 'pan'){
			$doc_type = 'PAN CARD';
		}
		if($_POST['document_type'] == 'aadhaar'){
			$doc_type = 'AADHAAR CARD';
		}
		if($_POST['document_type'] == 'bank'){
			$doc_type = 'LAST 6-MONTH BANK STATEMENT';
		}
		if($_POST['document_type'] == 'address'){
			$doc_type = 'ADDRESS PROOF';
		}
		if($_POST['document_type'] == 'waddress'){
			$doc_type = 'OFFICE ADDRESS PROOF';
		}
		if($_POST['document_type'] == 'witr'){
			$doc_type = '3 YEAR ITR';
		}
		if($_POST['document_type'] == 'salary'){
			$doc_type = '3-MONTH SALARY SLIP';
		}
	     $d = $loanDao->getApplication($_POST['lead_id']);
	     $application_id = $d['application_id'];
             $params = [
                'doc_file' => $local_file,
                'ref_code' => $outletId,
                'application_id' => $application_id,
                'doc_type' => $doc_type,
                'doc_no' =>$docId
            ];
	    error_log("==== prams" . json_encode($params , true));
            $result = $loanAPIDao->uploadDocument($params);
 
            echo json_encode(['success' => true, 'document_id' => $docId]);
            break;

        case 'document-status':
            $data = json_decode(file_get_contents('php://input'), true);
            $documents = $documentDao->getAllRequiredDocuments($data['lead_id']);
            echo json_encode(['success' => true, 'documents' => $documents]);
            break;
	case 'update-status':
	    error_log("====== updating status ");
            $data = json_decode(file_get_contents('php://input'), true);
	    $application_id = $data['application_id'];
	    $status = $data['status'];
	    $remark = $data['remark'];
	    $loanDao->updateStatusbyApp($status, $remark , $application_id);
            echo json_encode(['success' => true]);
	
	break;

	case 'check-cibil':
            $data = json_decode(file_get_contents('php://input'), true);
	    $credit = $loanAPIDao->checkCreditScore($data);
	    echo json_encode($credit);
	break;	
	case 'update-cibil':
            $data = json_decode(file_get_contents('php://input'), true);
	    $lead_id = $data['lead_id'];
	    $d = $loanDao->getApplication($lead_id);
	    $d['pan_no'] = $d['pan'];
	    $d['phone'] = $d['mobile'];
	    $d['ref_code']= $outletId;
	    error_log("=======  details " .  json_encode($d , true));
	    $credit = $loanAPIDao->checkCreditScore($d);
	    $score = $credit['score'];
	    error_log("======= $lead_id , $score");
	    $loanDao->updateCreditScore($score, $lead_id);
            echo json_encode(['success' => true, 'score' => $score]);
	break;	

        case 'finalize-application':
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['lead_id'])) {
                throw new Exception('Lead ID is required');
            }
            
            // Check if all documents are uploaded
	    /*
            $documents = $documentDao->getAllRequiredDocuments($data['lead_id']);
            $allUploaded = array_reduce($documents, function($carry, $item) {
                return $carry && $item['uploaded'];
            }, true);
            
            if (!$allUploaded) {
                throw new Exception('All required documents must be uploaded');
            }
            */
            // Update application status and generate application ID
            $applicationId = 'APP' . time() . rand(1000, 9999);
            $loanDao->updateStatus($data['lead_id'], 'initiated');
            
            echo json_encode([
                'success' => true, 
                'application_id' => $applicationId
            ]);
            break;

        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
    }

} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    $json = $e->getMessage();
    http_response_code(500);
    if (is_string($e->getMessage())) {
        $json = json_decode($e->getMessage(), true);
    }
    echo json_encode(['success' => false, 'errors' => $json]);
}

function processResponse($response) {
        if (!is_array($response)) {
            throw new Exception("Invalid API response format");
        }

        if (isset($response['success']) && !$response['success']) {
            throw new Exception($response['message'] ?? 'API request failed');
        }

        if (isset($response['data'])) {
            return $response['data'];
        }

        return $response;
    }


function validatePersonalInfo($data) {
    $required = ['name', 'email', 'mobile', 'pan', 'aadhaar'];
    $errors = [];
    
    foreach ($required as $field) {
        if (empty($data[$field])) {
            $errors[] = ucfirst($field) . " is required";
        }
    }
    
    if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (!empty($data['mobile']) && !preg_match("/^[0-9]{10}$/", $data['mobile'])) {
        $errors[] = "Invalid mobile number";
    }
    
    if (!empty($data['pan']) && !preg_match("/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/", $data['pan'])) {
        $errors[] = "Invalid PAN format";
    }
    
    if (!empty($data['aadhaar']) && !preg_match("/^[0-9]{12}$/", $data['aadhaar'])) {
        $errors[] = "Invalid Aadhaar number";
    }
    
    if (!empty($errors)) {
        throw new Exception(implode(", ", $errors));
    }
}
function callCallbackUrl($url, $postData) {
    // Initialize cURL session
    $ch = curl_init($url);


    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the response instead of printing it
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData)); // Set POST data

    $headers = ['x-client-code: fintra', 'Content-Type: application/json',];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    // Execute the request
    $response = curl_exec($ch);

    // Check for errors
    if($response === false) {
        // If there's an error, you can handle it here
        echo 'cURL error: ' . curl_error($ch);
    }

    $info = curl_getinfo($ch);

    error_log("====== response $response");
    error_log("======= info ".  json_encode($info , true));
    // Close cURL session
    curl_close($ch);

    // Return the response
    return $response;
}

?>
