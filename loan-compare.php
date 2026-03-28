<?php
include_once 'RecoService.php';
require_once 'vendor/autoload.php';
require_once 'apis/dao/LoanDAO.php';
require_once 'apis/dao/SEODAO.php';
require_once 'utils/utils.php';




$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader, array(
    //'cache' => '/tmp/compilation_cache',
));
$template = $twig->load('loancompare.twig.html');

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

$url_pattern = $type . '/*';
$seoDAO  =  new SEODAO();
$seo_data = $seoDAO->getByPattern($url_pattern , $langs[$ln]);

$footer = CMSUtils::getFooterLinks($params);
$menu = Utils::getMenu($ln);

$dao =  new LoanDAO();
$data1 =  $dao->getByID($id1, $langs[$ln]);
$data2 =  $dao->getByID($id2, $langs[$ln]);

$error = false;
if(empty($data1) || empty($data2))
	$error = true;

if(!$error){
$seo_data_card = array();
foreach($data1 as $key=>$val){
	$seo_data_card['card1.' . $key] = $val;
}
foreach($data2 as $key=>$val){
	$seo_data_card['card2.' . $key] = $val;
}

$seo_data_vals = $seoDAO->substitute($seo_data_card , $seo_data);
$data['seo'] = $seo_data_vals;

$cat1 = $data1['sub_category'];
$cat2 = $data2['sub_category'];

$ids1 = $sims[$cat1];
$ids2 = $sims[$cat2];


$similar_cards1 = array();
$similar_cards2 = array();
foreach($ids1 as $idx){
	if($id1 == $idx)
		continue;
	$sf = $dao->getByID($idx ,$langs[$ln]);
	$sf['compare_url'] = '/'  . $ln . '/mutual-funds-compare/'. $id1 . '-vs-' . $idx; 
	$similar_cards1[] = $sf;
}
foreach($ids2 as $idx){
	if($id2 == $idx)
		continue;
	$sf = $dao->getByID($idx ,$langs[$ln]);
	$sf['compare_url'] = '/'  . $ln . '/mutual-funds-compare/'. $id2 . '-vs-' . $idx; 
	$similar_cards2[] = $sf;
}

usort($similar_cards1, function ($item1, $item2) {
    return $item2['direct_ret_1y'] <=> $item1['direct_ret_1y'];
});
usort($similar_cards2, function ($item1, $item2) {
    return $item2['direct_ret_1y'] <=> $item1['direct_ret_1y'];
});

$data['heading1'] = 'More comparisions with ' . $data1['name']  ;
$data['heading2'] = 'More comparisions with ' . $data2['name'] ; 
$data['similar1'] = array_slice($similar_cards1, 0, 6);
$data['similar2'] = array_slice($similar_cards2, 0 ,6);
$data['card1'] = $data1;
$data['card2'] = $data2;
$data['language'] = $ln;
$data['footer'] = $footer;
$data['navigation'] = $menu;
$data['ids'] = $id;
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

