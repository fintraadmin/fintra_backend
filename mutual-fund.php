<?php
include_once 'RecoService.php';
require_once 'vendor/autoload.php';
require_once 'utils/cmsutils.php';
require_once 'utils/utils.php';

$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader, array(
    //'cache' => '/tmp/compilation_cache',
));
global $ln;
$id = $_REQUEST['id'];
$ln = $_REQUEST['ln'];

$template_file = 'mutual-fund.html';
if($ln == 'hindi')
	$template_file = 'mutual-fund-hi.html';
if($ln == 'punjabi')
	$template_file = 'mutual-fund-pu.html';

$template = $twig->load($template_file);
$template_array  = array();

try {
 $file_path = 'mocks/mutual-funds/' . $id;
 if(!file_exists($file_path))
	throw new Exception("Stock Not found");	
 $json_data = json_decode(file_get_contents($file_path), true);
 $template_array = tranform($json_data);
 $template_array['language']= $ln;
 $template_array['relative_url'] = '/mutual-fund/'. $id;
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
 $r = $c->getReco($template_array['industry'] , 'high');
 $recos1 = $c->output($r);
 $r = $c->getReco($template_array['industry'] , 'low');
 $recos2 = $c->output($r);
 $template_array['recos1'] = $recos1;
 $template_array['recos2'] = $recos2;

}

function tranform($json){
	global $ln;
	setlocale(LC_MONETARY, 'en_IN');
	$data = array();
	$fields = array('name' , 'primary_category' , 'sub_category' , 'regular_nav' , 'direct_nav' , 'regular_nav_prev' , 'direct_nav_prev' , 'direct_ret_1y' , 'direct_ret_3y' , 'direct_ret_5y' , 'direct_ret_10y' , 'direct_ret_sl' , 'benchmark' , 'benchmark_ret_1y' , 'aum' , 'last_updated') ;
	$translated = array('name' , 'primary_category' , 'sub_category');

	$change = (float)$json['direct_nav'] - (float)$json['direct_nav_prev'];
	$dropped = 1;
	if($change < 0)
		$dropped =  -1;
	$data['dropped'] = $dropped;
	foreach($fields as $k){
		$data[$k] = $json[$k];
	}
	
	foreach($translated as $k){
		$key = $k . '_' . $ln;
		$data[$k] = ucwords($json[$key]);
	}
	
	return $data;
}
?>
