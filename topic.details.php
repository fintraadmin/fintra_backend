<?php
require_once 'vendor/autoload.php';
require_once 'utils/utils.php';
require_once 'lib/fetch.php';
require_once 'apis/services/DetailService.php';
require_once 'apis/dao/SEODAO.php';
require_once 'apis/dao/CategoryDAO.php';
require_once 'apis/services/TaxonomyService.php';


$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader, array(
    //'cache' => '/tmp/compilation_cache',
));
$template = $twig->load('topic.detail.twig.html');
$type=$_REQUEST['type'];
$id=$_REQUEST['id'];
$ln  = null;
if (isset($_REQUEST['ln']))
	$ln = $_REQUEST['ln'];


$langs = array('hindi' => 'hi' , 'english' => 'en' , 'gujarati' => 'gu');
$lang_key = $langs[$ln];
$params['language'] = $lang_key;
$params['type'] = 'topic';
$params['id'] = $id;

$serv =  new DetailService();
$data = $serv->getData($params);

$ts = new TaxonomyService();
$nodes = $ts->getBrowseTree('topic', $id , $lang_key);


$menu = Utils::getMenu($ln);
$footer = CMSUtils::getFooterLinks($params);
	
$url_pattern = 'topic/*';
$seoDAO  =  new SEODAO();
$seo_data = $seoDAO->getByPattern($url_pattern , $langs[$ln]);
$seo_data_vals = $seoDAO->substitute($data , $seo_data);


if(true){
	echo $template->render(
		array(
		'seo' => $seo_data_vals,
		'navigation' => $menu ,  
		'language' => $ln,
		'data' => $data,
		'nodes' => $nodes, 
		'footer' => $footer
		));
}
else{
	http_response_code(404);
	$template_404 = $twig->load('404.html');
	echo $template_404->render();
}
?>
