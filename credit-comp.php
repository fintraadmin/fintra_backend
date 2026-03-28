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
$template = $twig->load('creditcardcomp.twig.html');

$template_array = array();
$langs= array('english' => 'en' , 'hindi' => 'hi');

global $ln;
$id=$_REQUEST['id'];
$type = $_REQUEST['type'];
$ln  = 'en';
if (isset($_REQUEST['ln']))
	$ln = $_REQUEST['ln'];

$ids = explode('-vs-' , $id);
$id1 = $ids[0];
$id2 = $ids[1];
$canonical = 'https://fintra.co.in/'. $ln .'/credit-cards-compare/' . $id;

$url_pattern = $type . '/*';
$seoDAO  =  new SEODAO();
$seo_data = $seoDAO->getByPattern($url_pattern , $langs[$ln]);

$footer = CMSUtils::getFooterLinks($params);
$menu = Utils::getMenu($ln);

$dao =  new CreditCardDAO();
$data1 =  $dao->getByID($id1, $langs[$ln]);
$data2 =  $dao->getByID($id2, $langs[$ln]);

$error= false;
if(empty($data1) || empty($data2))
 $error = true;

$seo_data_card = array();
foreach($data1 as $key=>$val){
	$seo_data_card['card1.' . $key] = $val;
}
foreach($data2 as $key=>$val){
	$seo_data_card['card2.' . $key] = $val;
}

$seo_data_vals = $seoDAO->substitute($seo_data_card , $seo_data);
$data['seo'] = $seo_data_vals;

/*Similar Card Logic*/
$similar1 = $dao->getByCategory($data1['categories'] ,  $langs[$ln]);
if(count($similar1)< 3){
	$similar1 = $dao->getByFilters(array('bank_id' => $data1['bank_id']), $langs[$ln]);
}
$similar2 = $dao->getByCategory($data2['categories'] ,  $langs[$ln]);
if(count($similar2)< 3){
	$similar2 =$dao->getByFilters(array('bank_id' => $data2['bank_id']), $langs[$ln]);
}
$popular = $dao->getpopular($langs[$ln]);
$similar1 = array_merge($similar1 ,$popular);
$similar2 = array_merge($similar2 ,$popular);
$similar_cards1 = array();
$similar_cards2 = array();
foreach($similar1 as $sc){
	$sc['compare_url'] = '/' . $ln. '/credit-cards-compare/' . $id1 . '-vs-' . $sc['id'];
	if($sc['id'] != $id1 && $sc['id'] != $id2)
		$similar_cards1[] = $sc;
}
foreach($similar2 as $sc){
	$sc['compare_url'] = '/'. $ln . '/credit-cards-compare/' . $id2 . '-vs-' . $sc['id'];
	if($sc['id'] != $id1 && $sc['id'] != $id2)
		$similar_cards2[] = $sc;
}
shuffle($similar_cards1);
shuffle($similar_cards2);
$data['heading1'] = 'More comparisions with ' . $data1['title']  ;
$data['heading2'] = 'More comparisions with ' . $data2['title'] ; 
$data['similar'] = array_slice($similar_cards1, 0, 6);
$data['similar1'] = array_slice($similar_cards2, 0 ,6);
$data['card1'] = $data1;
$data['card2'] = $data2;
$data['language'] = $ln;
$data['footer'] = $footer;
$data['navigation'] = $menu;
$data['ids'] = $id;
$data['canonical'] = $canonical;

$debug= $_GET['debug'];
if($debug == true){
echo json_encode($data , true);
return;
}

if(!$error){
	echo $template->render($data);
}
else{
	http_response_code(404);
        $template_404 = $twig->load('404.twig.html');
        echo $template_404->render(array(
                'navigation' => $menu ,
                'footer' => $footer
        ));
}


