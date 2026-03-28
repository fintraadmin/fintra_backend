<?php
require_once 'apis/TopFundsClass.php';
require_once 'apis/TopicsClass.php';
require_once 'apis/ChaptersClass.php';
require_once 'apis/ChapterDetailClass.php';
require_once 'apis/LoggingClass.php';
require_once 'apis/ItemUpdateClass.php';
require_once 'apis/UserClass.php';
require_once 'apis/PortfolioClass.php';
require_once 'apis/HomepageClass.php';
require_once 'apis/RiskClass.php';
require_once 'apis/CityClass.php';
require_once 'apis/ContentClass.php';
require_once 'apis/SearchClass.php';
require_once 'apis/CompleteClass.php';
require_once 'apis/dao/CreditCardDAO.php';
error_reporting(E_ALL & ~E_NOTICE & ~E_USER_NOTICE);

$start= microtime(true);
$service = $_REQUEST['service'];
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
session_set_cookie_params(['domian' => '.fintra.co.in']);
ini_set('session.cookie_domain', '.fintra.co.in');
$body = file_get_contents('php://input');

$params =  json_decode($body , true); // JSON Body is expected
if(empty($body))
 $params = $_REQUEST;

switch($service){
	case 'home':
			$class = new HomepageClass();
			$data = $class->fetchData($params);
			break;
	case 'fetchrisk':
			$class = new RiskClass();
			$data = $class->getQuestions($params);
			break;
	case 'topics':
			$class = new TopicsClass();
			$data = $class->fetchData($params);
			break;
	case 'chapters':
			$class = new ChaptersClass();
			$data = $class->fetchData($params);
			break;
	case 'chapterdetail':
			$class = new ChapterDetailClass();
			$data = $class->fetchData($params);
			break;
	case 'topfunds':
			$class = new TopFundsClass();
			$data = $class->fetchData($params);
			break;
	case 'log':
			$class =  new LoggingClass();
			$class->logData($params);
			$data =  array('code' => 'ok');
			break;
	case 'action':
			$class =  new ItemUpdateClass();
			$class->fetchData($params);
			$data =  array('code' => 'ok');
			break;
	case 'user':
			$class =  new UserClass();
			$class->fetchData($params);
			$data =  array('code' => 'ok');
			break;
	case 'getPortfolio':
			$class =  new PortfolioClass();
			$data = $class->fetchPortfolio($params);
			break;
	case 'searchCity':
			$class = new CityClass();
			$data = $class->search($params);
			break;
	case 'getBlogs':
			$class = new ContentClass();
			$data = $class->listBlogs($params);
			break;
	case 'getContentDetails':
			$class = new ContentClass();
			$data = $class->getDetails($params);
			break;
	case 'search':
			$class =  new SearchClass();
			$data = $class->getAutoSuggestions($params);
			break;
	case 'gptcomplete':
			$class =  new CompleteClass();
			$data = $class->getSuggestions($params);
			break;
	case 'gptaction':
			break;
	case 'getlisting':
			$dao =  new CreditCardDAO();
			$data = $dao->getByPopularity('en');
			$data = array_slice($data, 0, 5);
			break;
	case 'default':
			$data = array();

}
$end = microtime(true);
$latency = round($end - $start);
$params['latency'] = $latency; 
if($service !=  'log'){
	$params['service'] = $service;
	$logclass =  new LoggingClass();
	$logclass->logData($params);
}
echo  json_encode($data);
