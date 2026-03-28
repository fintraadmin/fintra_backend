<?php
include_once 'RecoService.php';
require_once 'vendor/autoload.php';
require_once 'utils/cmsutils.php';
require_once 'utils/utils.php';
require_once 'apis/dao/SEODAO.php';
require_once 'apis/dao/CalculatorDAO.php';
require_once 'apis/services/DetailService.php';
require_once 'apis/services/TaxonomyService.php';
require_once 'apis/services/RecoService.php';

$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader, array(
    //'cache' => '/tmp/compilation_cache',
));
$template = $twig->load('calculator.twig.html');
global $ln;
$id=$_REQUEST['id'];
$type = $_REQUEST['type'];
$ln  = null;

if (isset($_REQUEST['ln']))
	$ln = $_REQUEST['ln'];

$lang_key = Utils::$language_keys[$ln];
$params['language'] = $lang_key;

$memcache_key = 'calculator-' . $lang_key . '-' . $id;
#$template_array = MemcacheUtil::getItem($memcache_key);
if(empty($template_array)){
$menu = Utils::getMenu($ln);
$footer = CMSUtils::getFooterLinks($params);

$url_pattern = 'calculator/*';
$seoDAO  =  new SEODAO();
$seo_data = $seoDAO->getByPattern($url_pattern , $lang_key);

$ts = new TaxonomyService();
$nodes = $ts->getBrowseTree('calculator', $id , $lang_key);

$dao = new CalculatorDAO();
$data = $dao->getCalculatorByID($id , $lang_key);
$seo_data_vals = $seoDAO->substitute($data , $seo_data);

$recoS =  new RecoService();
$similar = $recoS->getRecos('calculator' , $id , $lang_key);
$similar2 = $recoS->getRecos('blog1' , 'credit card');

$similar1 = $recoS->getRecos('calculatorclone' , $id , $lang_key);

$data['input'] = json_decode($data['input'] , true);
if(isset($data['calid']))
	$id = $data['calid'];
$template_array = array(
		'id' => $id,
		'seo' => $seo_data_vals,
		'navigation' => $menu ,  
		'language_name' => $ln,
		'nodes' => $nodes, 
		'language' => $lang_key,
		'data' => $data,
		'similar' => $similar,
		'similar1' => $similar1,
		'similar_blog' => $similar2,
		'footer' => $footer
		);

	MemcacheUtil::setItem($memcache_key , $template_array);
}
$debug= $_GET['debug'];
if($debug == true){
echo json_encode($template_array , true);
return;
}
if(!empty($template_array['data']['title'])){

	echo $template->render($template_array); 
}
else{
http_response_code(404);
        $template_404 = $twig->load('404.twig.html');
        echo $template_404->render(array(
		'navigation' => $menu , 
		'footer' => $footer
	));
}
?>

