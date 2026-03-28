<?php
require_once 'vendor/autoload.php';
require_once 'utils/utils.php';
require_once 'lib/fetch.php';
require_once 'apis/dao/SEODAO.php';


$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader, array(
    //'cache' => '/tmp/compilation_cache',
));
$type=$_REQUEST['type'];
$id=$_REQUEST['id'];
global $ln;
$ln  = 'english';
if (isset($_REQUEST['ln']))
	$ln = $_REQUEST['ln'];

$tname= 'leads.twig.html';

$template = $twig->load($tname);

$langs = array('hindi' => 'hi' , 'english' => 'en' , 'gujarati' => 'gu');
$lang_key = $langs[$ln];
$footer = CMSUtils::getFooterLinks($params);
$menu = Utils::getMenu($ln);
$url_pattern =   'leads';
$seoDAO  =  new SEODAO();
$seo_data = $seoDAO->getByPattern($url_pattern , $langs[$ln]);


try {
 $seo_data_vals = $seoDAO->substitute($template_array , $seo_data);
 $template_array['seo'] = $seo_data_vals;
 $template_array['language'] = $ln;
 $template_array['footer'] = $footer;
 $template_array['navigation'] = $menu;  
 echo $template->render($template_array);
}
catch(Exception $e){
        error_log('ERROR:' . print_r($e , true));
        http_response_code(404);
        $template_404 = $twig->load('404.html');
        echo $template_404->render();
}



?>
