<?php

// include and register Twig auto-loader
//include './vendor/autoload.php';
require_once 'vendor/autoload.php';
require_once 'utils/utils.php';
require_once 'utils/memcache.php';
require_once 'apis/dao/SEODAO.php';
require_once 'apis/dao/CategoryDAO.php';

$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader, array(
//    'cache' => '/tmp/compilation_cache',
));

// Language mappings
$lang_keys = array('hindi' => 'hi' , 'english' =>  'en' , 'gujarati' => 'gu');
$lang= $_REQUEST['ln'];
if(empty($lang))
	$lang  = 'english';
$template = $twig->load('home.twig.html');

$lang_key = $lang_keys[$lang];
$memcache_key = 'home-' . $lang_key;
$template_array = MemcacheUtil::getItem($memcache_key);

$url_pattern = 'home';

$applink = "https://play.google.com/store/apps/details?id=com.fintra.app&referrer=utm_source%3Dsite%26utm_medium%3Dapplink";

if(empty($template_array)){
	$seoDAO  =  new SEODAO();
	$seo_data = $seoDAO->getByPattern($url_pattern , $lang_key);
	$menu = Utils::getMenu($lang);

	$categoryDao =  new CategoryDAO();
	$categories = $categoryDao->getList($lang_key);
	$params['language'] = $lang_key;
	$footer = CMSUtils::getFooterLinks($params);
	$blogs = CMSUtils::getBlogs();
	shuffle($blogs);
	$blogs = array_splice($blogs, 0 , 6);

	$config_fields = array('home_h2_1' , 'home_h2_1_desc' ,'home_h2_2' , 'home_h2_2_desc', 'home_feature_1_title' , 'home_feature_1_desc', 'home_feature_2_title' , 'home_feature_2_desc', 'home_feature_3_title' , 'home_feature_3_desc', 'home_feature_4_title' , 'home_feature_4_desc' , 'home_sections'); 	

	foreach($config_fields as $key){
		$configs[$key] = CMSUtils::getConfigValue($key , $lang_key);
	}	
	$configs['home_sections'] = json_decode($configs['home_sections'] , true);	
	$template_array = array('title' => $seo_title, 
				'navigation' => $menu, 
				'introduction' => $introduction, 
				'details' => $description,
				'categories' => $categories, 
				'applink' => $applink, 
				'page_description' => $seo_description, 
				'keywords' => $seo_keywords, 
				'android_app_link' => $android_app_link,
				'seo' => $seo_data,
				'language' => $lang,
				'blogs' => $blogs,
				'configs' => $configs, 
				'hom' => 'https://images.fintra.co.in/app_homepage.png', 
				'footer' => $footer);
	MemcacheUtil::setItem($memcache_key , $template_array);

}
$debug= $_GET['debug'];
if($debug == true){
echo json_encode($template_array , true);
return;
}
 		
echo $template->render($template_array); 

//Log 
$params = array();
$params['platform'] = 'site';
$params['page'] = 'index';
$params['language'] = $lang;
$params['referrer'] = $_REQUEST['referrer'];

$headers = array('HTTP_X_FORWARDED_FOR' , 'REQUEST_TIME' , 'HTTP_USER_AGENT' , 'SERVER_NAME');
        foreach ($headers as $h)
        {
                $params[$h] = $_SERVER[$h];
        }
error_log('DATA:' . json_encode($params));

