<?php
include_once 'RecoService.php';
require_once 'vendor/autoload.php';
require_once 'apis/dao/SEODAO.php';
require_once 'apis/dao/CreditCardDAO.php';
require_once 'utils/utils.php';




$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader, array(
    //'cache' => '/tmp/compilation_cache',
));
$template = $twig->load('creditcardhome.twig.html');

$template_array = array();
$langs= array('english' => 'en' , 'hindi' => 'hi');

global $ln;
$id=$_REQUEST['id'];
$type = $_REQUEST['type'];
$ln  = 'en';
if (isset($_REQUEST['ln']))
	$ln = $_REQUEST['ln'];

$url_pattern = 'credit-cards';
$footer = CMSUtils::getFooterLinks($params);
$menu = Utils::getMenu($ln);

$seoDAO  =  new SEODAO();
$seo_data = $seoDAO->getByPattern($url_pattern , $langs[$ln]);

$dao =  new CreditCardDAO();
$data =  $dao->getByID($id, $langs[$ln]);
$sections = array();
$banks_imp = array();
$banks = array('sbi','icici-bank','kotak-mahindra-bank','axis-bank','yes-bank','rbl-bank','hdfc-bank','american-express','indusind-bank','citi-bank','hsbc','tata','bank-of-baroda','bandan-bank','standard-chartered' , 'canara-bank');

$types = array('cashback' , 'rewards' , 'fuel' , 'travel' , 'business' );
foreach($types as $type){
	$similar = $dao->getByCategory($type ,  $langs[$ln]);
	$similar_cards = array();
	$similar_cards['heading'] = 'Top ' . ucwords($type) . ' Credit Cards';
	$similar_cards['url'] = '/' . $ln . '/credit-cards-category/' . $type;
	foreach($similar as $sc){
		if($sc['id'] != $id)
			$similar_cards['items'][] = $sc;
	}
	$similar_cards['items'] = array_slice($similar_cards['items'] , 0 ,3);
	$sections[$type] = $similar_cards;
}

foreach($banks as $bank){
	$similar = $dao->getByCategoryBank('', $bank ,  $langs[$ln]);
	$similar_cards = array();
	foreach($similar as $sc){
		if($sc['id'] != $id)
			$similar_cards['items'][] = $sc;
	}
	if(count($similar_cards['items']) == 0)
		continue;
	$similar_cards['items'] = array_slice($similar_cards['items'] , 0 ,3);
	$bank_title = $similar_cards['items'][0]['bank']['title'] ;
	$bank_url = $similar_cards['items'][0]['bank']['url'] ; 
	$similar_cards['heading'] = 'Top ' . $bank_title . ' Credit Cards';
	$similar_cards['url'] = '/' . $ln . '/credit-cards-category/' . $bank_url;
	//if(!in_array($bank,$banks_imp))
	//	$similar_cards['items'] = array();	
$sections[$bank] = $similar_cards;
}
$data['sections'] = $sections;


$seo_data_vals = $seoDAO->substitute($data , $seo_data);
$data['seo'] = $seo_data_vals;
$data['language'] = $ln;
$data['footer'] = $footer;
$data['navigation'] = $menu;
 
$debug= $_GET['debug'];
if($debug == true){
echo json_encode($data , true);
return;
}
echo $template->render($data);
