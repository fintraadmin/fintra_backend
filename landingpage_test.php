<?php
require_once 'vendor/autoload.php';
require_once 'apis/dao/CreditCardDAO.php';
require_once 'apis/dao/LoanApplicationDAO.php';
require_once 'utils/memcache.php';

$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader, array(
    //'cache' => '/tmp/compilation_cache',
));




$aid=$_REQUEST['aid'];
$cid=$_REQUEST['cid'];
$type=$_REQUEST['product'];

$step=$_REQUEST['step'];
if(empty($step))
	$step=1;

$template = $twig->load('landing_test.twig.html');
if($step==2){
	$template = $twig->load('landing_test2.twig.html');
	$key = $_REQUEST['lead_id'] . '-banks';
	$banks = unserialize(MemcacheUtil::getItem($key));
	error_log("====== bb " . json_encode($banks, true));
	$providers = $banks;
}
if($step==3)
	$template = $twig->load('landing_test3.twig.html');
if($step==4)
	$template = $twig->load('landing_test4.twig.html');
if($step==1 && empty($aid) ){
	$template = $twig->load('error.twig.html');
	echo $template->render();
	return;		
}

if ($type == "business-loan"){
 $income_source= '2';
}

if ($type == "personal-loan"){
 $income_source = '1';
}
//Build landing page url
foreach($cards as &$card){
	$card['a_link'] = "https://fintra.co.in/leadgen?cid=$cid&aid=$aid&pid=".$card['id'];
}
$template_array['cards'] = $cards;
/*
$providers = array();
$p['id']=1;
$p['logo_url']='https://allvectorlogo.com/img/2019/05/aditya-birla-capital-ltd-logo.png';
$p['name']='Aditya Birla';
$p['interest_rate']='13';
$p['tenure']='1';
$p['processing_fee']='1000';
$providers[]= $p;
$p=array();
$p['id']=1;
$p['logo_url']='https://upload.wikimedia.org/wikipedia/commons/7/7b/IDFC_First_Bank_logo.jpg';
$p['name']='IDFC';
$p['interest_rate']='12';
$p['tenure']='1';
$p['processing_fee']='1000';
$providers[]= $p;
*/
if($step ==2 || $step ==3 || $step==4){
 $template_array['lead_id'] = $_REQUEST['lead_id'];
 $appDao = new LoanApplicationDAO();
 $result = $appDao->getApplication($_REQUEST['lead_id']);
 $cid=$result['publisher_id'];
 $aid = $result['merchant_id'];
 $income_source = $result['income_source'];
 if($income_source == '2'){
	$type = 'personal_loan';
 }
 else{
	$type = 'business-loan';
 }
 error_log("==== cid $cid aid $aid");
}
$template_array['reupload'] = false;
if($_REQUEST['reupload'] == 1){
$template_array['reupload'] = true;
}
$template_array['aid'] = $aid;
$template_array['cid']= $cid;
$template_array['providers'] = $providers;
$template_array['loan_type'] = $income_source;
$template_array['trackingurl'] = '/english/listapplications' . "?cid=$cid&aid=$aid";
$template_array['home_url'] = "/english/landing_loan?cid=$cid&aid=$aid&product=$type";
echo $template->render($template_array);

?>

