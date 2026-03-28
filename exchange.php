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

$tname= 'stock-exchange.twig.html';
if($ln == 'hindi')
 $tname= 'stock-exchange.hi.twig.html';
if($ln == 'punjabi')
 $tname= 'stock-exchange-punjabi.html';

$template = $twig->load($tname);

$langs = array('hindi' => 'hi' , 'english' => 'en' , 'gujarati' => 'gu');
$lang_key = $langs[$ln];
$footer = CMSUtils::getFooterLinks($params);
$menu = Utils::getMenu($ln);
$url_pattern =   'nse';
$seoDAO  =  new SEODAO();
$seo_data = $seoDAO->getByPattern($url_pattern , $langs[$ln]);

if($id == 'nse')
	$id = 'nifty';

try {
 $file_path = 'mocks/stocks/' . $id . '.json';
 if(!file_exists($file_path))
        throw new Exception("Stock Not found");
 $json_data = json_decode(file_get_contents($file_path), true);
 $template_array = tranform($json_data);
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


function tranform($json){
	global $ln;
        setlocale(LC_MONETARY, 'en_IN');
        $data = array();

	if($ln == 'hindi'){
		foreach($json as &$l){
			if(isset($l['language']['nameHindi']))
				$l['name'] = $l['language']['nameHindi'];
		} 
	}
	if($ln == 'punjabi'){
		foreach($json as &$l){
			if(isset($l['language']['namePunjabi']))
				$l['name'] = $l['language']['namePunjabi'];
		} 
	}
	$data['list'] = $json;
	return $data;	
}

?>
