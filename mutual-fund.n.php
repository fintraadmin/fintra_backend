<?php
include_once 'RecoService.php';
require_once 'vendor/autoload.php';
require_once 'utils/cmsutils.php';
require_once 'utils/utils.php';
require_once 'apis/dao/MFDAO.php';
require_once 'apis/dao/SEODAO.php';

$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader, array(
    //'cache' => '/tmp/compilation_cache',
));
global $ln;
$id = $_REQUEST['id'];
$ln = $_REQUEST['ln'];
$langs= array('english' => 'en' , 'hindi' => 'hi', 'punjabi' => 'pu');

$template_file = 'mutual-fund.twig.html';
if($ln == 'hindi')
	$template_file = 'mutual-fund.hi.twig.html';
if($ln == 'punjabi')
	$template_file = 'mutual-fund-pu.html';

$template = $twig->load($template_file);
$template_array  = array();

$footer = CMSUtils::getFooterLinks($params);
$menu = Utils::getMenu($ln);
$url_pattern =   'mutual-fund/*';
$seoDAO  =  new SEODAO();
$seo_data = $seoDAO->getByPattern($url_pattern , $langs[$ln]);

try {
 $file_path = 'mocks/mutual-funds/' . $id;
 if(!file_exists($file_path))
	throw new Exception("Stock Not found");	
 $json_data = json_decode(file_get_contents($file_path), true);
 $template_array = tranform($json_data);
 $seo_data_vals = $seoDAO->substitute($template_array , $seo_data);
 $template_array['seo'] = $seo_data_vals;
 $template_array['language']= $ln;
 $template_array['relative_url'] = '/mutual-fund/'. $id;
 $template_array['footer'] = $footer;
 $template_array['navigation'] = $menu;  
 $template_array['heading1'] = 'More comparisions with ' . $template_array['name']  ;
 addRecos($template_array);
 echo $template->render($template_array);
}
catch(Exception $e){
        error_log('ERROR:' . print_r($e , true));
        http_response_code(404);
        $template_404 = $twig->load('404.twig.html');
        echo $template_404->render(array(
                'navigation' => $menu ,
                'footer' => $footer
        ));
}

function addRecos(&$template_array){
 #Recos
 global $ln;
 $dao =  new MFDAO();
 $sims = json_decode(file_get_contents('mocks/mf_recos.json'), true);
 $cat1 = $template_array['sub_category1'];
 $id1 = $template_array['id'];
 $ids1 = $sims[$cat1];


 $similar_cards1 = array();
 foreach($ids1 as $idx){
        if($id1 == $idx)
                continue;
        $sf = $dao->getByID($idx , 'en');
        $sf['compare_url'] = '/'  . $ln . '/mutual-funds-compare/'. $id1 . '-vs-' . $idx;
        $similar_cards1[] = $sf;
 }
 $template_array['similar1'] = $similar_cards1;
}

function tranform($json){
	global $ln;
	setlocale(LC_MONETARY, 'en_IN');
	$data = array();
	$fields = array('name' , 'primary_category' , 'sub_category' , 'regular_nav' , 'direct_nav' , 'regular_nav_prev' , 'direct_nav_prev' , 'direct_ret_1y' , 'direct_ret_3y' , 'direct_ret_5y' , 'direct_ret_10y' , 'direct_ret_sl' , 'benchmark' , 'benchmark_ret_1y' , 'aum' , 'last_updated' , 'id') ;
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
		$data[$k .'1'] = $json[$k];
	}
	
	return $data;
}
?>
