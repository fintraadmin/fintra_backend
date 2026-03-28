<?php
spl_autoload_register(function($className) {
        include_once 'apis/dao/'.$className . '.php';
});
spl_autoload_register(function($className) {
        include_once 'apis/services/'.$className . '.php';
});
include_once 'RecoService.php';
require_once 'vendor/autoload.php';
require_once 'apis/dao/SEODAO.php';
require_once 'utils/utils.php';


$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader, array(
    //'cache' => '/tmp/compilation_cache',
));
$template = $twig->load('compare.twig.html');

$template_array = array();
$langs= array('english' => 'en' , 'hindi' => 'hi');

global $ln;
$id=$_REQUEST['id'];
$type = $_REQUEST['type'];
$ln  = 'en';
if (isset($_REQUEST['ln']))
	$ln = $_REQUEST['ln'];

$ids = explode('-vs-' , $id);
$id1 = $ids[0];
$id2 = $ids[1];

$url_pattern = $type . '/*';
$seoDAO  =  new SEODAO();
$seo_data = $seoDAO->getByPattern($url_pattern , $langs[$ln]);

$footer = CMSUtils::getFooterLinks($params);
$menu = Utils::getMenu($ln);

if($type == 'cryptocurrency-compare'){
	$dao = new CryptoDAO();
	$ctype = 'cryptocurrency';
}
if($type == 'loans-compare'){
	$dao = new LoanDAO();
	$ctype = 'loan';
}
if($type == 'saving-accounts-compare'){
	$dao = new SavingAccountDAO();
	$ctype = 'saving-account';
}
$data1 =  $dao->getByID($id1, $langs[$ln]);
$data2 =  $dao->getByID($id2, $langs[$ln]);


$error = false;
if(empty($data1) || empty($data2))
	$error = true;

if(!$error){
$seo_data_card = array();
foreach($data1 as $key=>$val){
	$seo_data_card['item1.' . $key] = $val;
}
foreach($data2 as $key=>$val){
	$seo_data_card['item2.' . $key] = $val;
}

$seo_data_vals = $seoDAO->substitute($seo_data_card , $seo_data);
$data['seo'] = $seo_data_vals;

//Create Mappings for comparators
$lines = explode("\n" , $data['seo']['data']);
foreach($lines as $line){
	$parts = explode("," , $line);
	$heading = $parts[0];
	$f = $parts[1];
	$field1 = $data1[$f];
	$field2 = $data2[$f];
	$data['fields'][] = array('heading' => $heading , 'field1' => $field1 , 'field2' => $field2);
}
$lang_key = $langs[$ln];
$cServ =  new CompareService();
$similar1['items'] = $cServ->getComparisons($ctype , $data1['id'] ,  $lang_key);
$similar1['item1'] = $data1;
$similar2['items'] = $cServ->getComparisons($ctype , $data2['id'] ,  $lang_key);
$similar2['item1'] = $data2;

$data['heading1'] = 'More comparisions with ' . $data1['title']  ;
$data['heading2'] = 'More comparisions with ' . $data2['title'] ; 
$data['similar1'] = $similar1;
$data['similar2'] = $similar2;
$data['item1'] = $data1;
$data['item2'] = $data2;
$data['language'] = $ln;
$data['footer'] = $footer;
$data['navigation'] = $menu;
$data['ids'] = $id;
echo $template->render($data);
}
else{
	http_response_code(404);
        $template_404 = $twig->load('404.twig.html');
        echo $template_404->render(array(
                'navigation' => $menu ,
                'footer' => $footer
        ));
}

