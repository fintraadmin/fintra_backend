<?php
include_once 'RecoService.php';
require_once 'vendor/autoload.php';
require_once 'apis/dao/CreditCardDAO.php';
require_once 'apis/dao/SEODAO.php';
require_once 'utils/memcache.php';
require_once 'utils/utils.php';

$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader, array(
    //'cache' => '/tmp/compilation_cache',
));
$template = $twig->load('creditcard.twig.html');

$template_array = array();
$langs= array('english' => 'en' , 'hindi' => 'hi');

global $ln;
$id=$_REQUEST['id'];
$type = $_REQUEST['type'];
$ln  = 'english';
$section = null;
if (isset($_REQUEST['ln']))
	$ln = $_REQUEST['ln'];

if (isset($_REQUEST['section']))
	$section = $_REQUEST['section'];

$canonical = 'https://fintra.co.in/'. $ln .'/'. $type .'/' . $id;
$memcache_key = 'cc-' . $ln . '-' . $id;
$data = MemcacheUtil::getItem($memcache_key);
if(empty($data)){
$data = array();

$url_pattern = $type . '/*';
if(isset($section))
	$url_pattern .= '/' . $section;
$seoDAO  =  new SEODAO();
$seo_data = $seoDAO->getByPattern($url_pattern , $langs[$ln]);

$footer = CMSUtils::getFooterLinks($params);
$menu = Utils::getMenu($ln);
$dao =  new CreditCardDAO();
$data =  $dao->getByID($id, $langs[$ln]);

$seo_data_vals = $seoDAO->substitute($data , $seo_data);
$data['seo'] = $seo_data_vals;

/*Similar Card Logic*/
$similar = $dao->getByFilters(array('bank_id' => $data['bank_id']) ,  $langs[$ln]);
$similar_cards = array();
foreach($similar as $sc){
	if($sc['id'] != $id)
		$similar_cards[] = $sc;
}

$data['isSectionPage'] = 0;
$sections = array('fees' , 'offers' , 'features' , 'review');
if(in_array($section , $sections)){
	$data['isSectionPage'] = 1;
	$data['section'] = $section;
	$data['section_data'] = $data[$section];
	if(!empty($data['seo']['data']))
		$data['section_data'] = $data['seo']['data'];
}

$config_fields = array(
	'credit_card_page' => array('type' => 'json' )
);

foreach($config_fields as $key => $fields){
		$v = CMSUtils::getConfigValue($key , $langs[$ln] );
		if($fields['type'] == 'json')
			$v = json_decode($v , true);
		$data[$key] = $v;
	}	

 


$similar2 = $dao->getByCategory($data['categories'] ,  $langs[$ln]);
$similar3 = $dao->getByPopularity( $langs[$ln]);
foreach($similar2 as $sc){
	$sc['compare_url'] = '/'. $ln . '/credit-cards-compare/' . $id . '-vs-' . $sc['id'];
	if($sc['id'] != $id)
		$similar_cards2[] = $sc;
}
foreach($similar3 as $sc){
	$sc['compare_url'] = '/'. $ln . '/credit-cards-compare/' . $id . '-vs-' . $sc['id'];
	if($sc['id'] != $id)
		$similar_cards3[] = $sc;
}
shuffle($similar_cards2);
shuffle($similar_cards);

$data['similar'] = array_slice($similar_cards,0,6);
$data['similar1'] = array_slice($similar_cards2, 0 ,12);
$data['similar2'] = array_slice($similar_cards3, 0 ,12);
$data['heading1'] = $data['credit_card_page']['more_cards']  . ' - ' . $data['bank']['title'];
$data['heading2'] = $data['credit_card_page']['compare_cards']  .' '. $data['title'];
$data['heading3'] = $data['credit_card_page']['compare_cards_1']  .' '. $data['title'];
$data['language'] = $ln;
$data['footer'] = $footer;
$data['navigation'] = $menu;
$data['canonical'] = $canonical;
	
MemcacheUtil::setItem($memcache_key , $data);
}
$debug= $_GET['debug'];
if($debug == true){
echo json_encode($data , true);
return;
}
echo $template->render($data);
