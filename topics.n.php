<?php
require_once 'vendor/autoload.php';
require_once 'utils/utils.php';
require_once 'lib/fetch.php';
require_once 'apis/dao/SEODAO.php';
require_once 'apis/dao/CategoryDAO.php';
require_once 'apis/services/RecoService.php';
require_once 'apis/services/CompareService.php';


$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader, array(
    //'cache' => '/tmp/compilation_cache',
));
$template = $twig->load('topics.twig.html');

$type=$_REQUEST['type'];
$ln  = null;
if (isset($_REQUEST['ln']))
	$ln = $_REQUEST['ln'];


$langs = array('hindi' => 'hi' , 'english' => 'en' , 'gujarati' => 'gu');
$lang_key = $langs[$ln];


$params['language'] = $lang_key;
$menu = Utils::getMenu($ln);
$footer = CMSUtils::getFooterLinks($params);


$dbService =  new DBService();
$content  =  $dbService->getTopics($type, $lang_key);

$url_pattern = $type;
$seoDAO  =  new SEODAO();
$seo_data = $seoDAO->getByPattern($url_pattern , $langs[$ln]);

$dao = new CategoryDAO();
$data = $dao->getDetails($type , $lang_key);
$seo_data_vals = $seoDAO->substitute($data , $seo_data);

$recoS =  new RecoService();
$similar_blog = $recoS->getRecos('blog1' , $data['value']['title']);

$comparisons = [];
if($type == 'cryptocurrencies'){
	$compS =  new CompareService();
	$comparisons = $compS->getComparisons('cryptocurrency' , null,$lang_key );
}

if($type == 'loans'){
	$compS =  new CompareService();
	$comparisons1 = $compS->getComparisons('loan' , null,$lang_key, 'personal' );
	$comparisons2 = $compS->getComparisons('loan' , null,$lang_key, 'home' );
	$comparisons3 = $compS->getComparisons('loan' , null,$lang_key, 'education' );
	$comparisons = array_merge($comparisons1,$comparisons2,$comparisons3);
}
if($type == 'banking'){
        $compS =  new CompareService();
        $comparisons = $compS->getComparisons('saving-account' , null,$lang_key );
}
/*
*/
$ta= 		array(
		'seo' => $seo_data_vals,
		'navigation' => $menu ,  
		'topics' =>$content , 
		'funds' => $mf_links , 
		'language' => $ln,
		'data' => $data,
		'similar_blog' => $similar_blog,
		'comparisons' => $comparisons,
		'footer' => $footer
		);


$debug= $_GET['debug'];
if($debug == true){
echo json_encode($ta , true);
return;
}
echo $template->render($ta);

?>
