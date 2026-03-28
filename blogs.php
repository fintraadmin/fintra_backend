<?php
require_once 'vendor/autoload.php';
require_once 'utils/utils.php';
require_once 'apis/dao/SEODAO.php';

$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader, array(
    //'cache' => '/tmp/compilation_cache',
));

$template = $twig->load('blog.twig.html');

$type=$_REQUEST['type'];
$ln  = null;
if (isset($_REQUEST['ln']))
	$ln = $_REQUEST['ln'];


$url_pattern = 'blog';
$seoDAO  =  new SEODAO();
$seo_data = $seoDAO->getByPattern($url_pattern , 'en');

$page = CMSUtils::getPage(array('id' => 'blogs'));
$menu = Utils::getMenu('english');
$blogs = CMSUtils::getBlogs();
$footer = CMSUtils::getFooterLinks($params);

foreach($menu as $m){
	if($m['id'] == 'home'){
		$home = $m['caption'];
		$home_href = '/';
	}
	if($m['id'] == 'blog'){
		$title_bc = $m['caption'];
	}
} 

$applink = "https://play.google.com/store/apps/details?id=com.fintra.app&referrer=utm_source%3Dsite%26utm_medium%3Dapplink";
$msg = CMSUtils::getConfigValue('download_msg');

$template_array = array(
	'title' => $page['title'], 
	'seo_title' => $page['seo_title'], 
	'navigation' => $menu,  
	'seo_description' => $page['seo_description'], 
	'applink' =>  $applink, 
	'msg' => $msg, 
	'android_app_link' => $android_app_link, 
	'shareImg' => '' , 
	'keywords' => '',
	'title_bc' => $title_bc, 
	'home' => $home, 
	'home_href' => $home_href, 
	'content' => $page['body'],
	'similar_blog' => $blogs,
	'footer' => $footer 
);
$template_array['seo'] = $seo_data;
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
?>
