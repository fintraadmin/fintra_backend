<?php
require_once 'vendor/autoload.php';
require_once 'apis/dao/LoanApplicationDAO.php';

$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader, array(
    //'cache' => '/tmp/compilation_cache',
));


$template = $twig->load('tracking.twig.html');


$aid=$_REQUEST['aid'];
$cid=$_REQUEST['cid'];
$lead_id=$_REQUEST['lead_id'];
$tracking_url = "listapplications?cid=$cid&aid=$aid";
if($cid != 'findipay' || empty($aid) || empty($lead_id)){
	$template = $twig->load('error.twig.html');
	echo $template->render();
	return;		
}

try {
    $appDao = new LoanApplicationDAO();


    $result = $appDao->getApplication($lead_id);
    if($result['doc_uploaded'] == 0){
	$result['doc_status'] = 'Documents not Uploaded';
	$result['doc_link'] = 'landing_loan/document-upload?step=3&lead_id='. $result['lead_id']  .'&reupload=1';
    }
    else{

	$result['doc_status'] = 'Documents Uploaded';
    }
    error_log("=====". json_encode($result, true));
    $template_array = $result;
    $template_array['trackingurl']= $tracking_url; 
    echo $template->render($template_array);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch applications'
    ]);
}
?>

