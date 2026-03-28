<?php
require_once 'vendor/autoload.php';
require_once 'utils/utils.php';
require_once 'apis/dao/SEODAO.php';
require_once 'apis/dao/CategoryDAO.php';

$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader, array(
    //'cache' => '/tmp/compilation_cache',
));

$template = $twig->load('topicsnew.twig.html');

$type=$_REQUEST['type'];
$ln  = null;
if (isset($_REQUEST['ln']))
	$ln = $_REQUEST['ln'];


	$lang_key = Utils::$language_keys[$ln];

	$menu = Utils::getMenu($ln);
	$footer = CMSUtils::getFooterLinks($params);

	$url_pattern = 'topics';
	$seoDAO  =  new SEODAO();
	$seo_data = $seoDAO->getByPattern($url_pattern , $lang_key);

	$categoryDao =  new CategoryDAO();
	$categories = $categoryDao->getList($lang_key);
	foreach($categories as $key=>&$cat){
		$details = $categoryDao->getDetails($cat['uuid'] , $lang_key);
		if(count($details['topics']) ==0 )	{
			unset($categories[$key]);
			continue;
		}	
		$cat['topics'] = $details['topics'];
	}	

	$template_array = array(
		'seo' => $seo_data,
		'navigation' => $menu, 
		'categories' => $categories, 
		'footer' => $footer
	);


	try{
		echo $template->render($template_array);
	}
	catch (Exception $e){
		http_response_code(404);
		$template_404 = $twig->load('404.html');
		echo $template_404->render();
	}
?>
