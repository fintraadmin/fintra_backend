<?php
include_once 'RecoService.php';
require_once 'vendor/autoload.php';
require_once 'utils/cmsutils.php';
require_once 'utils/utils.php';
require_once 'apis/services/DetailService.php';

$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader, array(
    //'cache' => '/tmp/compilation_cache',
));
$template = $twig->load('calculators.html');

global $ln;
$id=$_REQUEST['id'];
$type = $_REQUEST['type'];
$ln  = null;

if (isset($_REQUEST['ln']))
	$ln = $_REQUEST['ln'];

$lang_key = Utils::$language_keys[$ln];

try{
	//$menu = Utils::getMenu($lang_key);
	$serv =  new DetailService();
	$params = array();
	$params['language'] = $lang_key;
	$params['type'] = $type;
	$params['id'] = $id;
	$calculator = $serv->getData($params);
	$calculator = $calculator[0];
	$url = $calculator['url'];
	$can_url = null;
	if(empty($ln) && !empty($url)){
		$can_url = $url;
	}

	//throw error for missing fact
	if(empty($calculator['title']) & empty($calculator['content'])) {
   		 throw new Exception("Empty fact");
  	}
	if(empty($ln))
		$ln = 'english';
	$menu = Utils::getMenu($ln);
	$applink = "https://play.google.com/store/apps/details?id=com.fintra.app&referrer=utm_source%3Dsite%26utm_medium%3Dapplink_new";
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
		'seo_title' => isset($calculator['seo_title']) ? $calculator['seo_title'] : $calculator['title'], 
		'navigation' => $menu,  
		'seo_description' => $calculator['seo_description'], 
		'applink' =>  $applink, 
		'msg' => $calculator['msg'],
		'title' => $calculator['title'], 
		'content' => $type =='fact' ? $calculator['content'] : $calculator['subtitle1'],
		'shareImg' => isset($calculator['image']) ? $calculator['image'] : '',
	        'keywords' => '',
		'home' => $home_bc, 
		'home_href' => $home_bc_href, 
		'level1' => $level_1_bc,
		'canonical_url' => $can_url, 
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

