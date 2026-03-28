<?php
include_once 'RecoService.php';
require_once 'vendor/autoload.php';
require_once 'utils/cmsutils.php';
require_once 'utils/utils.php';
require_once 'apis/dao/SEODAO.php';
require_once 'apis/dao/FactDAO.php';
require_once 'apis/services/DetailService.php';
require_once 'apis/services/TaxonomyService.php';
require_once 'apis/services/RecoService.php';

$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader, array(
    //'cache' => '/tmp/compilation_cache',
));
$template = $twig->load('fact.twig.html');

global $ln;
$id=$_REQUEST['id'];
$type = $_REQUEST['type'];
$ln  = null;

if (isset($_REQUEST['ln']))
	$ln = $_REQUEST['ln'];

$lang_key = Utils::$language_keys[$ln];
$params['language'] = $lang_key;

$menu = Utils::getMenu($ln);
$footer = CMSUtils::getFooterLinks($params);

$ts = new TaxonomyService();
$nodes = $ts->getBrowseTree('fact', $id , $lang_key);

$url_pattern = 'fact/*';
$seoDAO  =  new SEODAO();
$seo_data = $seoDAO->getByPattern($url_pattern , $lang_key);

$dao = new FactDAO();
$data = $dao->getFactByID($id , $lang_key);
$seo_data_vals = $seoDAO->substitute($data , $seo_data);

$recoS =  new RecoService();
$similar_facts = $recoS->getRecos('fact' , $id , $lang_key);

if(!empty($data['title'])){
echo $template->render(array(
		'seo' => $seo_data_vals,
		'navigation' => $menu , 
		'nodes' => $nodes, 
		'language' => $ln,
		'data' => $data,
		'similar_facts' => $similar_facts,
		'footer' => $footer
		));
}
else{
http_response_code(404);
        $template_404 = $twig->load('404.twig.html');
        echo $template_404->render(array(
		'navigation' => $menu , 
		'footer' => $footer
	));
}

function addRecos(&$template_array){
 #Recos
 global $ln;
 $c =  new StockRecoService($ln);
 $r = $c->getReco('nifty50High' , 'high');
 $recos1 = $c->output($r);
 $r = $c->getReco('nifty50Low' , 'low');
 $recos2 = $c->output($r);
 $template_array['recos1'] = $recos1;
 $template_array['recos2'] = $recos2;

}
?>

