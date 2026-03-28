<?php
require_once 'vendor/autoload.php';
require_once 'apis/dao/LoanApplicationDAO.php';

$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader, array(
    //'cache' => '/tmp/compilation_cache',
));


$template = $twig->load('list_application.twig.html');


$aid=$_REQUEST['aid'];
$cid=$_REQUEST['cid'];

if($cid != 'findipay' || empty($aid) ){
	$template = $twig->load('error.twig.html');
	echo $template->render();
	return;		
}
try {
    $appDao = new LoanApplicationDAO();
    
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $filters = [
        'status' => $_GET['status'] ?? null,
        'search' => $_GET['search'] ?? null
    ];
    
    $result = $appDao->getApplications($aid, $cid);
    $applications = $result['applications'];
    foreach($applications as &$a){
		$a['url']= "tracking?cid=$cid&aid=$aid&lead_id=". $a['lead_id'];
		if($a['doc_uploaded'] == 0){
			$a['doc_status'] = 'Pending';
		}
		else{

			$a['doc_status'] = 'Uploaded';
		}
			 
    }
    $template_array =  [
        'success' => true,
        'applications' => $applications
    ];
    echo $template->render($template_array);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch applications'
    ]);
}

?>

