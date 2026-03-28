<?php
require_once 'vendor/autoload.php';
require_once 'lib/fetch.php';
require_once 'utils/utils.php';
include_once 'RecoService.php';

$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader, array(
    //'cache' => '/tmp/compilation_cache',
));

$template = $twig->load('chapter.html');
global $ln;
$id=$_REQUEST['title'];
$ln  = null;
if (isset($_REQUEST['ln']))
	$ln = $_REQUEST['ln'];

if(empty($id))
	$id = 'mf1ch1';
// Fetch data
if(empty($ln))
	$ln  = 'english';
$caption = 'Mutual Funds';
$href = '/' . $ln . '/mutual-funds';
$href_tools = '/' . $ln . '/tools';
$captions_tools = 'Calculators';
if($ln == 'hindi'){
 $caption = 'म्यूच्यूअल फंड्स'; 
 $captions_tools = 'कैलकुलेटर';
}

$menu = array();
$menu[] = array('caption' => $caption , 'href' => $href); 
$menu[] = array('caption' => $captions_tools , 'href' => $href_tools); 
$menu = Utils::getMenu($ln);

$dbService =  new DBService();
$langs = array('hindi' => 'hi' , 'english' => 'en', 'gujarati' => 'gu');
$lang_key = $langs[$ln];
$content  =  $dbService->getChapterByIDTitle($id,$lang_key);
$title = $content['title'];
$details = $content['content'];
$description = $content['description'];
$keywords = $content['keywords'];
$image = $content['shareImg'];
$next_article = isset($content['next']) ?  "https://fintra.co.in/" . $ln . "/mutual-funds/" . $content['next']['id']: null;
$prev_article = isset($content['prev']) ?  "https://fintra.co.in/" . $ln . "/mutual-funds/" . $content['prev']['id'] : null;
$next_article_title = isset($content['next']) ?   $content['next']['title']: null;
$prev_article_title = isset($content['prev']) ?   $content['prev']['title'] : null;
$applink = "https://play.google.com/store/apps/details?id=com.fintra.app&referrer=utm_source%3Dsite%26utm_medium%3Dapplink";
if(empty($description)){
	$description = "Investing into mutual funds , equity funds , SIP made easy.";
}
$home_bc_href = '/english';
$home_bc = 'Home';
$level_1_bc = 'Mutual Funds';
$level_1_href = '/english/mutual-funds';
$msg_hi ='अपने फ़ोन पर ऐप डाउनलोड करने के लिए नीचे लिंक पर क्लिक करें और आसानी से म्यूच्यूअल फंडस के बारें में अधिक जानकारी प्राप्त करें |  ';$msg = 'Download the app now by clicking on Google Play link below. Learn everything about mutual funds on Fintra app.';

$msg_gu = 'નીચેની લિંકને ક્લિક કરીને મ્યુચ્યુઅલ ફંડ્સમાં રોકાણ શરૂ કરવા માટે હવે ફિક્કી એપ ડાઉનલોડ કરો.';


if($lang_key == 'hi'){
	$title = $content['title_hi'];
	$details = $content['content_hi'];
	if(!empty($content['description_hi']))
		$description = $content['description_hi'];
	$msg = $msg_hi;
	$home_bc_href = '/hindi';
	$home_bc = 'होम';
	$level_1_bc = 'म्यूच्यूअल फंड्स';
	$level_1_href = '/hindi/mutual-funds';
}
if($lang_key == 'gu'){
	if(!empty($content['title_gu']))
		$title = $content['title_gu'];
	if(!empty($content['content_gu']))
		$details = $content['content_gu'];
	if(!empty($content['description_gu']))
		$description = $content['description_gu'];
	if(empty($content['description_gu'])){
		$words = explode(' ', $content['content_gu']);
		$words = array_slice($words, 150);
		$description =  implode(' ', $words);
	}
	$msg = $msg_gu;
	$home_bc_href = '/gujarati';
	$home_bc = 'होम';
	$level_1_bc = 'म्यूच्यूअल फंड्स';
	$level_1_href = '/gujarati/mutual-funds';
	
}

$android_app_link = "android-app://com.fintra.app/fintra/" . $ln . "/mutual-funds/" . $id ;
$template_array = array('title' => $title, 'navigation' => $menu , 'details' => $details , 'description' => $description , 'keywords' => $keywords , 'applink' =>  $applink , 'msg' => $msg , 'shareImg' => $image , 'android_app_link' => $android_app_link, 'next' => $next_article , 'prev' => $prev_article , 'home' => $home_bc , 'home_href' => $home_bc_href , 'level1' => $level_1_bc , 'level1href' => $level_1_href, 'prev_title' => $prev_article_title , 'next_title' => $next_article_title);
addRecos($template_array);
if(!empty($title)){
	echo $template->render($template_array);
}
else{
	http_response_code(404);
	$template_404 = $twig->load('404.html');
	echo $template_404->render();
}

//Log 
$params = array();
$params['platform'] = 'site';
$params['page'] = $id;
$params['referrer'] = $_REQUEST['referrer'];
$params['language'] = $ln;

$headers = array('HTTP_X_FORWARDED_FOR' , 'REQUEST_TIME' , 'HTTP_USER_AGENT' , 'SERVER_NAME');
        foreach ($headers as $h)
        {
                $params[$h] = $_SERVER[$h];
        }
error_log('DATA:' . json_encode($params));

function addRecos(&$template_array){
 #Recos
 global $ln;
 $c =  new StockRecoService($ln);
 $r = $c->getReco('nifty50High' , 'high');
 $recos1 = $c->output($r);
 $r = $c->getReco('nifty50Low' , 'low');
 $recos2 = $c->output($r);
 $template_array['recos1'] = $recos1;
 $template_array['recos2'] = $recos2;

}
