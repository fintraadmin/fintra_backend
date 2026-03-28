<?php
// api/index.php
set_include_path(get_include_path() . PATH_SEPARATOR . '/var/www/html');
require_once 'utils/memcache.php';
require_once 'apis/dao/Database.php';
require_once 'apis/dao/CreditCardDAO.php';

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

try {
    $creditDao = new CreditCardDAO();
    $db = Database::getInstance()->getConnection(); 
    switch($service) {
        case 'update-status':
    		 $status = $params['status'];
		 $id = $params['id'];	
		if(isset($params['sub2']) && isset($params['stage']) && isset($params['sub_stage']) ){
			$status = 'Pending';
			$id = $params['sub2'];
			if(strpos($params['stage'], 'approved')){
				$status ='Approved';
			}
			if(strpos($params['stage'], 'non commissionable')){
				$status ='Approved - NC';
			}
			else if(strpos($params['stage'], 'decline')){
				$status ='Rejected';
			}
			else if(strpos($params['stage'], 'expire')){
				$status ='Expired';
			}
		 	$stmt = $db->prepare(
                		"UPDATE findipay_leads SET lead_status = ?, comments= ? WHERE id = ?"
            		);
			error_log("========= $status $id");
       			$stmt->execute([$status, $params['sub_stage'] , $id]);


		}
		else{	
		 	$stmt = $db->prepare(
                		"UPDATE findipay_leads SET lead_status = ? WHERE id = ?"
            		);
       			$stmt->execute([$status, $id]);

		}
                echo json_encode(['success' => true, 'message' => 'updated']);
		 
	    break;

	case 'submit-loan-selection':
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

?>
