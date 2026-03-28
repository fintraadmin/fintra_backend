<?php
require_once 'vendor/autoload.php';
require_once 'apis/dao/CCApplicationDAO.php';
require_once 'apis/dao/CreditCardDAO.php';

$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader, array(
    //'cache' => '/tmp/compilation_cache',
));


$template = $twig->load('c_list_application.twig.html');


$aid=$_REQUEST['aid'];
$cid=$_REQUEST['cid'];
/*
if($cid != 'findipay' || empty($aid) ){
	$template = $twig->load('error.twig.html');
	echo $template->render();
	return;		
}
*/
try {
    $appDao = new CCApplicationDAO();
    
    $result = $appDao->getApplicationsByComp($cid);
    $applications = $result['applications'];
    /*foreach($applications as &$a){
    }*/
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

