<?php
include_once 'RecoService.php';
require_once 'vendor/autoload.php';
require_once 'utils/cmsutils.php';
require_once 'utils/utils.php';


$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader, array(
    //'cache' => '/tmp/compilation_cache',
));
$template = $twig->load('calculators.html');

global $ln;
$id=$_REQUEST['type'];
$ln  = null;


if (isset($_REQUEST['ln']))
	$ln = $_REQUEST['ln'];

$lang_key = Utils::$language_keys[$ln];

try{
	$menu = Utils::getMenu($lang_key);
	$calculator = CMSUtils::getCalculatorDetails(array('language' => $lang_key , 'id' => $id));
	$menu = Utils::getMenu($ln);
	$applink = "https://play.google.com/store/apps/details?id=com.fintra.app&referrer=utm_source%3Dsite%26utm_medium%3Dapplink";
	foreach($menu as $m){
	        if($m['id'] == 'home'){
        	        $home_bc = $m['caption'];
                	$home_bc_href = $m['href'];
        	}
        	if($m['id'] == 'tools'){
                	$level_1_bc = $m['caption'];
			$level_1_href = $m['href'];
        	}
	}

	
	$template_array = array(
		'seo_title' =>   $calculator['title'], 
		'navigation' => $menu,  
		'seo_description' => $calculator['seo_description'], 
		'applink' =>  $applink, 
		'msg' => $calculator['msg'],
		'title' => $calculator['title'], 
		'content' => $calculator['description'],
		'shareImg' => $calculator['image'],
	        'keywords' => '',
		'home' => $home_bc, 
		'home_href' => $home_bc_href, 
		'level1' => $level_1_bc, 
		'level1href' => $level_1_href
	);
	
 	addRecos($template_array);
	echo $template->render($template_array);
}
catch(Exception $e){
	error_log('ERROR:' . print_r($e , true));
	http_response_code(404);
	$template_404 = $twig->load('404.html');
	echo $template_404->render();
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

