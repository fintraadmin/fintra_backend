<?php
require_once 'vendor/autoload.php';
require_once 'utils/utils.php';
require_once 'lib/fetch.php';
require_once 'apis/dao/SEODAO.php';
require_once 'apis/dao/CategoryDAO.php';


$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader, array(
    //'cache' => '/tmp/compilation_cache',
));
$template = $twig->load('mf.twig.html');

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
$url_pattern = 'mutual-funds';
$seoDAO  =  new SEODAO();
$seo_data = $seoDAO->getByPattern($url_pattern , $langs[$ln]);

$dao = new CategoryDAO();
$data = $dao->getByID('mutual-funds' , $lang_key);
$seo_data_vals = $seoDAO->substitute($data , $seo_data);

/* Mutual Funds Specific */
$d = file_get_contents('mocks/mutual-funds-list.json');
$json =  json_decode($d, true);
$mf_links = array();
foreach($json as $j){
	$link = array();
	if(isset($j[$ln])){
		$link['text'] = ucwords($j[$ln]);
		$link['url'] = '/' . $ln . '/mutual-fund/' . $j['id'];
		$mf_links[] = $link;
	}
}

echo $template->render(array(
		'seo' => $seo_data_vals,
		'navigation' => $menu ,  
		'topics' =>$content , 
		'home' => $home , 
		'home_href' => $home_href, 
		'funds' => $mf_links , 
		'language' => $ln,
		'data' => $data,
		'footer' => $footer
		));
?>
