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

$tname= 'stock-indices.twig.html';
if($ln == 'hindi')
 $tname= 'stock-indices.hi.twig.html';
if($ln == 'punjabi')
 $tname= 'stock-indices-pu.html';

$template = $twig->load($tname);

$langs = array('hindi' => 'hi' , 'english' => 'en' , 'gujarati' => 'gu');
$lang_key = $langs[$ln];

$url_pattern =   $id;
$seoDAO  =  new SEODAO();
$seo_data = $seoDAO->getByPattern($url_pattern , $langs[$ln]);
$footer = CMSUtils::getFooterLinks($params);
$menu = Utils::getMenu($ln);

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
	$info = $json['info'];
	$fields = array('identifier' => 'name' , 'lastPrice' => 'lastPrice' , 'dayHigh' => 'dayHigh' , 'dayLow' => 'dayLow' , 'lastUpdateTime' => 'lastUpdateTime' , 'pChange' => 'pChange' , 'previousClose' => 'previousClose' , 'yearHigh' => 'yearHigh' , 'yearLow' => 'yearLow' , 'open' => 'open' , 'change' => 'change');
	foreach($fields as $k1=>$k2){
		$data[$k2] = $info[$k1];
	}
	$data['change'] = round($data['change'] , 2);
	if($ln =='hindi'){
		$data['name'] = $info['nameHindi'];
	}
	$data['dropped'] = 1;
	if($data['previousClose'] > $data['lastPrice'])
		$data['dropped'] =  -1;
	$data['title'] = $data['name'] . ' NSE stocks data';

	if($ln == 'hindi'){
		foreach($json['links'] as &$l){
			$l['name'] = $l['name_hindi'];
		}
	} 
	if($ln == 'punjabi'){
		foreach($json['links'] as &$l){
			$l['name'] = $l['name_punjabi'];
		} 
	}
	$data['list'] = $json['links'];
	return $data;	
}

?>
