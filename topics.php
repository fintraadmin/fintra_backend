<?php
require_once 'vendor/autoload.php';
require_once 'utils/utils.php';
require_once 'lib/fetch.php';


$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader, array(
    //'cache' => '/tmp/compilation_cache',
));
$template = $twig->load('topic.html');
//$template = $twig->load('topics.twig.html');
$type=$_REQUEST['type'];
$ln  = null;
if (isset($_REQUEST['ln']))
	$ln = $_REQUEST['ln'];


$langs = array('hindi' => 'hi' , 'english' => 'en' , 'gujarati' => 'gu');
$lang_key = $langs[$ln];
$dbService =  new DBService();
$content  =  $dbService->getTopics($type, $lang_key);
$page_title = 'Mutual Funds - Fintra';
$page_des = 'Learn about investing in mutual funds,how to invest in mutual funds,mutual fund basics and do investment planning.';

$caption = 'Mutual Funds';
$href = '/' . $ln . '/mutual-funds';
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
	$page_title = 'फिंतरा - म्यूचुअल फंड्स विस्तार में समझें'; 
	$page_des = 'म्यूचुअल फंड में निवेश करने के बारे में जानें, म्यूचुअल फंड में कैसे निवेश करें, म्यूचुअल फंड की मूल बातें और निवेश की योजना बनाएं'; 
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
	$page_title = 'મુતુળ ફંડ્સ - ફિંત્રા';
	$page_des = 'મ્યુચ્યુઅલ ફંડ્સમાં રોકાણ કરવા વિશે જાણો, મ્યુચ્યુઅલ ફંડ્સમાં કેવી રીતે રોકાણ કરવું, મ્યુચ્યુઅલ ફંડ બેઝિક્સ અને ઇન્વેસ્ટમેંટ પ્લાનિંગ કરવું';
}

$android_app_link = "android-app://com.fintra.app/fintra/" . $ln . "/mutual-funds"  ;
$applink = "https://play.google.com/store/apps/details?id=com.fintra.app&referrer=utm_source%3Dsite%26utm_medium%3Dapplink";

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

if(!empty($page_title)){
	echo $template->render(array('title' => $page_title,'navigation' => $menu ,  'description' => $page_des  , 'applink' =>  $applink , 'msg' => $msg , 'android_app_link' => $android_app_link, 'topics' =>$content , 'shareImg' => '' , 'keywords' => '','title_bc' => $title_bc , 'home' => $home , 'home_href' => $home_href, 'funds' => $mf_links , 'language' => $ln));
}
else{
	http_response_code(404);
	$template_404 = $twig->load('404.html');
	echo $template_404->render();
}
?>
