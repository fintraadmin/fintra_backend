<?php

require_once 'vendor/autoload.php';
require_once 'apis/services/TaxonomyService.php';
require_once 'utils/cmsutils.php';
require_once 'utils/utils.php';
require_once 'apis/dao/SEODAO.php';
require_once 'apis/dao/PromptDAO.php';


session_start();
//session_reset();
session_regenerate_id();

$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader, array(
    //'cache' => '/tmp/compilation_cache',
));
global $ln;
$id=$_REQUEST['id'];
$type = $_REQUEST['type'];
$ln  = $_REQUEST['ln'];

$lang_key = Utils::$language_keys[$ln];
$params['language'] = $lang_key;

$memcache_key = 'gpt-' . $lang_key . '-' . $id;
#$template_array = MemcacheUtil::getItem($memcache_key);
if(empty($template_array)){
$menu = Utils::getMenu($ln);
$footer = CMSUtils::getFooterLinks($params);

$pDao = new PromptDAO();
$prompts = $pDao->getN($lang_key);
error_log("====== ". json_encode($prompts, true));
$url_pattern = 'fintragpt';
$seoDAO  =  new SEODAO();
$seo_data = $seoDAO->getByPattern($url_pattern , $lang_key);
$data = array();
$seo_data_vals = $seoDAO->substitute($data , $seo_data);

$ts = new TaxonomyService();
$nodes = $ts->getBrowseTree('calculator', $id , $lang_key);

$template_array = array(
		'id' => $id,
		'seo' => $seo_data_vals,
		'navigation' => $menu ,  
		'nodes' => $nodes, 
		'language' => $lang_key,
		'data' => $data,
		'prompts' => $prompts,
		'language_name' => $ln);


}
$debug= $_GET['debug'];
if($debug == true){
echo json_encode($template_array , true);
return;
}
$template = $twig->load('gpt.twig.html');
echo $template->render($template_array);

?>

