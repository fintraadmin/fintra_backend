<?php
require_once 'vendor/autoload.php';
require_once 'utils/utils.php';
require_once 'apis/dao/SEODAO.php';
require_once 'apis/dao/CalculatorDAO.php';
require_once 'apis/dao/CategoryDAO.php';

$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader, array(
    //'cache' => '/tmp/compilation_cache',
));

$template = $twig->load('tools.twig.html');

$type=$_REQUEST['type'];
$ln  = null;
if (isset($_REQUEST['ln']))
	$ln = $_REQUEST['ln'];


	$lang_key = Utils::$language_keys[$ln];

	$menu = Utils::getMenu($ln);
	$footer = CMSUtils::getFooterLinks($params);

	$url_pattern = 'tools';
	$seoDAO  =  new SEODAO();
	$seo_data = $seoDAO->getByPattern($url_pattern , $lang_key);

	$categoryDao =  new CategoryDAO();
	$categories = $categoryDao->getList($lang_key);
	$calDao =  new CalculatorDAO();
	foreach($categories as $key=>&$cat){
		$calculators = $calDao->getCalculatorByCategory($cat['id'] , $lang_key);
		if(count($calculators) ==0 )	{
			unset($categories[$key]);
			continue;
		}	
		$cat['calculators'] = $calculators;
	}	

	$template_array = array(
		'seo' => $seo_data,
		'navigation' => $menu, 
		'categories' => $categories, 
		'footer' => $footer
	);

$debug= $_GET['debug'];
if($debug == true){
echo json_encode($template_array , true);
return;
}
	try{
		echo $template->render($template_array);
	}
	catch (Exception $e){
		http_response_code(404);
		$template_404 = $twig->load('404.html');
		echo $template_404->render();
	}
?>
