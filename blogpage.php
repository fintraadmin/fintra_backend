<?php
include_once 'RecoService.php';
require_once 'vendor/autoload.php';
require_once 'utils/utils.php';
require_once 'apis/services/RecoService.php';

$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader, array(
    //'cache' => '/tmp/compilation_cache',
));

$template = $twig->load('blogpage_n.html');

$id=$_REQUEST['id'];


$menu = Utils::getMenu('english');
$blog = CMSUtils::getBlog($id);
$footer = CMSUtils::getFooterLinks($params);

foreach($menu as $m){
	if($m['id'] == 'home'){
		$home = $m['caption'];
		$home_href = '/';
	}
	if($m['id'] == 'blog'){
		$level1 = $m['caption'];
		$level1href =  $m['href'];
	}
} 

$recoS =  new RecoService();
$similar = $recoS->getRecos('blog' , $id);
$applink = "https://play.google.com/store/apps/details?id=com.fintra.app&referrer=utm_source%3Dsite%26utm_medium%3Dapplink";
$msg = CMSUtils::getConfigValue('download_msg');
$myDateTime = strtotime($blog['modified']);
$created = date('F j, Y', $myDateTime);
$created1 = date('Y-m-d', $myDateTime);
$canonical = 'https://fintra.co.in/blog/' . $id;
$template_array = array(
	'title' => $blog['title'], 
	'seo_title' => $blog['seo_title'],
	'id' => 'https://fintra.co.in/blog/' . $id,  
	'navigation' => $menu,  
	'seo_description' => $blog['seo_description'], 
	'applink' =>  $applink, 
	'msg' => $msg, 
	'android_app_link' => $android_app_link, 
	'shareImg' => $blog['featured_image'] , 
	'keywords' => '',
	'featured_image' => $blog['featured_image'],
	'home' => $home, 
	'home_href' => $home_href,
	'level1'  => $level1,
	'related' => $blog['related'],
	'level1href' => $level1href,
	'content' => str_replace('<img' , '<img class="img-fluid" ' , $blog['body']),
	'created' => $created,
	'created1' => $created1,
	'similar' => $similar,
	'canonical' => $canonical,
	'footer' => $footer
);

 	addRecos($template_array);
$debug= $_GET['debug'];
if($debug == true){
echo json_encode($template_array , true);
return;
}
	//print_r($template_array);
	try{
		echo $template->render($template_array);
	}
	catch (Exception $e){
		//http_response_code(404);
		//$template_404 = $twig->load('404.html');
		//echo $template_404->render();
	}

function addRecos(&$template_array){
 #Recos
 $ln = 'english';
 $c =  new StockRecoService($ln);
 $r = $c->getReco('nifty50High' , 'high');
 $recos1 = $c->output($r);
 $r = $c->getReco('nifty50Low' , 'low');
 $recos2 = $c->output($r);
 $template_array['recos1'] = $recos1;
 $template_array['recos2'] = $recos2;

}

?>
