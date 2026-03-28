<?php
require_once 'vendor/autoload.php';
require_once 'utils/utils.php';
require_once 'lib/fetch.php';
require_once 'apis/services/DetailService.php';


$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader, array(
    //'cache' => '/tmp/compilation_cache',
));
$template = $twig->load('factlist.html');
$type=$_REQUEST['type'];
$id=$_REQUEST['id'];
$ln  = null;
if (isset($_REQUEST['ln']))
	$ln = $_REQUEST['ln'];


$langs = array('hindi' => 'hi' , 'english' => 'en' , 'gujarati' => 'gu');
$lang_key = $langs[$ln];
	
$serv =  new DetailService();
$params = array();
$params['language'] = $lang_key;
$params['type'] = $type;
$params['id'] = $id;
$data = $serv->getData($params);
$facts = $data['list'];
$page_title = $data['title'];
$page_des = $data['seo_description'];
$caption = 'Topics';
$href = '/' . $ln . '/topics';
$href_tools = '/' . $ln . '/tools';
$captions_tools = 'Calculators';
if($ln == 'hindi'){
 $caption = 'म्यूच्यूअल फंड्स'; 
 $captions_tools = 'कैलकुलेटर';
}
if($ln == 'gujarati'){
 $caption='મુતુળ ફંડ્સ';
 $captions_tools = 'કૅલ્ક્યુલેટેરસ';
}

$menu = array();
$menu[] = array('caption' => $caption , 'href' => $href); 
$menu[] = array('caption' => $captions_tools , 'href' => $href_tools); 

$menu = Utils::getMenu($ln);
$title_bc = 'Mutual Funds';
$home = 'Home';
$home_href = '/english';
$msg_hi ='अपने फ़ोन पर ऐप डाउनलोड करने के लिए नीचे लिंक पर क्लिक करें और आसानी से म्यूच्यूअल फंडस के बारें में अधिक जानकारी प्राप्त करें |  ';$msg = 'Download the app now by clicking on Google Play link below. Learn everything about mutual funds on Fintra app.';
if($lang_key == 'hi'){
	$msg = $msg_hi;
	$home = 'होम';
	$title_bc = 'म्यूच्यूअल फंड्स';
	$home_href= '/hindi';
}
$msg_gu ='નીચેની Google Play લિંક પર ક્લિક કરીને એપ્લિકેશનને ડાઉનલોડ કરો ફિન્ન્ટ્રા એપ્લિકેશન પર મ્યુચ્યુઅલ ફંડ્સ વિશે બધું જાણો.';
if($lang_key=='gu'){
	$home = 'હોમ';
	$title_bc = 'મુતુળ ફંડ્સ ';
	$home_href= '/gujarati';
	$msg = $msg_gu;
}

$android_app_link = "android-app://com.fintra.app/fintra/" . $ln . "/mutual-funds"  ;
$applink = "https://play.google.com/store/apps/details?id=com.fintra.app&referrer=utm_source%3Dsite%26utm_medium%3Dapplink";

if(!empty($page_title)){
	echo $template->render(array('title' => $page_title,'navigation' => $menu ,  'description' => $page_des  , 'applink' =>  $applink , 'msg' => $msg , 'android_app_link' => $android_app_link, 'topics' =>$facts , 'shareImg' => '' , 'keywords' => '','title_bc' => $title_bc , 'home' => $home , 'home_href' => $home_href));
}
else{
	http_response_code(404);
	$template_404 = $twig->load('404.html');
	echo $template_404->render();
}
?>
