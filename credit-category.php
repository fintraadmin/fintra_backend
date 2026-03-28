<?php
include_once 'RecoService.php';
require_once 'vendor/autoload.php';
require_once 'apis/dao/CreditCardDAO.php';
require_once 'apis/dao/SEODAO.php';
require_once 'utils/utils.php';


$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader, array(
    //'cache' => '/tmp/compilation_cache',
));
$template = $twig->load('creditcardcat.twig.html');

$template_array = array();
$langs= array('english' => 'en' , 'hindi' => 'hi');

global $ln;
$id=$_REQUEST['id'];
$type = $_REQUEST['type'];
$sub_type = null;
$data =  array();

if(isset($_REQUEST['subtype']))
	$sub_type = $_REQUEST['subtype'];

$ln  = 'en';
if (isset($_REQUEST['ln']))
	$ln = $_REQUEST['ln'];
$canonical = 'https://fintra.co.in/'. $ln .'/'. $type .'/' . $id ;
if(!empty($sub_type))
	$canonical = 'https://fintra.co.in/'. $ln .'/'. $type .'/' . $id . '/' . $sub_type;
$types = array('travel' , 'rewards' , 'business' , 'fuel' , 'cashback');

$data['pagetype'] = 'bank';
if(in_array($id, $types)){
	$data['pagetype'] = 'category';
}

if($data['pagetype'] == 'bank')
	$url_pattern = $type . '/*';
else
	$url_pattern = $type . '/' . $id;

if(isset($sub_type))
	$url_pattern .= '/' . $sub_type;


$seoDAO  =  new SEODAO();
$seo_data = $seoDAO->getByPattern($url_pattern , $langs[$ln]);

$footer = CMSUtils::getFooterLinks($params);
$menu = Utils::getMenu($ln);

$dao =  new CreditCardDAO();

$sections = array();


$details =  array();
#If Bank; then get details
if($data['pagetype'] ==  'bank'){
	$details =  $dao->getBank($id, $langs[$ln]);
	$data['banksections'] =  true;	
}
$data = array_merge($data , $details);

$seo_data_vals = $seoDAO->substitute($data , $seo_data);
$data['seo'] = $seo_data_vals;

$data['issubtype'] = False;
$section_mapping = array(
	'customer-care' => 'customer_care_main',
	'apply' => 'apply',
	'redeem' => 'redeem' ,
	'bill-payment' => 'bill_payment',
	'statement' => 'statement',
	'statement-benefits' => 'statement_benefits',
	'moratorium' => 'moratorium',
	'call' => 'call',
	'login' => 'login',
	'faq' => 'faq',
	'reward-points' => 'reward_points',
	'contact' => 'address',
	'customer-care-email' => 'customer_care_email',
	'bill-payment-offline' => 'bill_paymeny_offline',
	'bill-payment-online' => 'bill_paymeny_online',
	'faq-redeem' => 'faq_redeem',
	'faq-statement' => 'faq_statement',
	'faq-customer-care' => 'faq_customer_care',
	'faq-moratorium' => 'faq_moratorium'
	
);
$details = $dao->getCategory($id , $langs[$ln]);
if(empty($details['customer_care_main']))
	$data['banksections'] =  false;


if(isset($sub_type)){
	$data['subtype'] = $sub_type;
	$data['issubtype'] = True;
	$data['data'] = $details[$section_mapping[$sub_type]];
	//$data['data'] = $details['redeem']; //Todo
}

$bank = $id;

if($data['pagetype'] == 'bank'){
   foreach($types as $type){
        $similar = $dao->getByCategoryBank($type , $bank,  $langs[$ln]);
        $similar_cards = array();
        foreach($similar as $sc){
                if($sc['id'] != $id)
                        $similar_cards[] = $sc;
        }
	$sections[$type]['items'] = array_slice($similar_cards, 0, 25);
	$sections[$type]['heading'] = ucwords($type);
  }
}
else{
        $similar = $dao->getByCategory($id,  $langs[$ln]);
        $similar_cards = array();
        foreach($similar as $sc){
                if($sc['id'] != $id)
                        $similar_cards[] = $sc;
        }
	$sections[$type]['items'] = array_slice($similar_cards, 0, 50);
}


$data['sections'] = $sections;
$data['language'] = $ln;
$data['id'] = $id;
$data['footer'] = $footer;
$data['navigation'] = $menu;
$data['canonical'] = $canonical;
$debug= $_GET['debug'];
if($debug == true){
echo json_encode($data , true);
return;
}

echo $template->render($data);
