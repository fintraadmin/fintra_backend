<?php

// include and register Twig auto-loader
//include './vendor/autoload.php';
require_once 'vendor/autoload.php';
require_once 'conf/hi.inc';

$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader, array(
    //'cache' => '/tmp/compilation_cache',
));

// Language mappings
$lang_keys = array('hindi' => 'hi' , 'english' =>  'en');
$lang= $_REQUEST['ln'];
if(empty($lang))
	$lang  = 'english';
$template = $twig->load('index.html');


$title = "Best App for Mutual Funds";
$lang_key = $lang_keys[$lang];

$caption = 'Mutual Funds';
$href = '/' . $lang . '/mutual-funds';
$href_tools = '/' . $lang . '/tools';
$captions_tools = 'Calculators';
if($lang == 'hindi'){
 $caption = 'म्यूच्यूअल फंड्स'; 
 $captions_tools = 'कैलकुलेटर';
}

$menu = array();
$menu[] = array('caption' => $caption , 'href' => $href); 
$menu[] = array('caption' => $captions_tools , 'href' => $href_tools); 

$applink = "https://play.google.com/store/apps/details?id=com.fintra.app&referrer=utm_source%3Dsite%26utm_medium%3Dapplink";
$introduction = $terms[$lang_key]['title'];
$description  = $terms[$lang_key]['description'];

//SEO Fields
$seo_title = $terms[$lang_key]['seo_title'];
$seo_description = $terms[$lang_key]['seo_description'];
$seo_keywords = $terms[$lang_key]['seo_keywords'];
$android_app_link = "android-app://com.fintra.app/fintra/learn";

echo $template->render(array('title' => $seo_title, 'navigation' => $menu , 'introduction' => $introduction , 'details' => $description , 'applink' => $applink , 
				'page_description' => $seo_description ,  'keywords' => $seo_keywords , 'android_app_link' => $android_app_link , 'homeImg' => 'https://images.fintra.co.in/app_homepage.png'
				));



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

