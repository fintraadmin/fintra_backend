<?php
require_once 'vendor/autoload.php';
require_once 'apis/dao/CCApplicationDAO.php';
require_once 'apis/dao/CreditCardDAO.php';

$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader, array(
    //'cache' => '/tmp/compilation_cache',
));


$template = $twig->load('cc_tracking.twig.html');


$aid=$_REQUEST['aid'];
$cid=$_REQUEST['cid'];
/*if($cid != 'findipay' || empty($aid) || empty($cid)){
	$template = $twig->load('error.twig.html');
	echo $template->render();
	return;		
}
*/
try {
    $appDao = new CCApplicationDAO();
    $cDao = new CreditCardDAO();
    $result = $appDao->getApplicationByAgent($aid);
    foreach($result as &$r){
	$pid = $r['productid'];
	$card = $cDao->getByID($pid);
	$r['card_name'] = $card['title']; 
	$r['card_image'] = $card['image'];
	
	if(empty($r['lead_status'])){
		$r['lead_status'] = 'New';
	}
	$r['class'] = strtolower($r['lead_status']);
    }
    //error_log("=====". json_encode($result, true));
    $template_array['applications'] = $result;
    echo $template->render($template_array);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch applications'
    ]);
}
?>

