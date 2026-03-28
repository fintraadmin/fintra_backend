<?php
require_once 'vendor/autoload.php';
require_once 'apis/dao/CreditCardDAO.php';

$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader, array(
    //'cache' => '/tmp/compilation_cache',
));


$template = $twig->load('landing1.twig.html');


$aid=$_REQUEST['aid'];
$cid=$_REQUEST['cid'];
$qa = $_REQUEST['qa'];

$product=$_REQUEST['product'];

if(empty($aid) ){
	$template = $twig->load('error.twig.html');
	echo $template->render();
	return;		
}
if(isset($qa)){

$template = $twig->load('landing1.twig.html');
}

//get credit cards 
$dao =  new CreditCardDAO();
$cards =  $dao->getByAffliate('findipay');
error_log("======card" . json_encode($cards, true));

//Build landing page url
foreach($cards as &$card){
	$card['a_link'] = "https://fintra.co.in/leadgen?cid=$cid&aid=$aid&pid=".$card['id'];
}
$template_array['cards'] = $cards;
if($product == 'cc'){
$template_array['tracking_url']= "cc_tracking?cid=$cid&aid=$aid";
echo $template->render($template_array);
}
if($product == 'saving'){
$template = $twig->load('landing.saving.twig.html');
$template_array['a_link']= "https://fintra.co.in/leadgen?cid=$cid&aid=$aid&pid=indusind-saving";
echo $template->render($template_array);
}
?>

