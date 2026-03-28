<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_USER_NOTICE);
set_include_path(get_include_path() . PATH_SEPARATOR . '/var/www/html');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

include_once('services/DetailService.php');
include_once('services/HomepageService.php');
include_once('services/BrowseService.php');
include_once('services/AutocompleteService.php');
include_once('services/SearchService.php');
include_once('services/CalculatorService.php');
include_once('services/GPTService.php');
include_once('services/LikeService.php');

$start= microtime(true);
$service = $_REQUEST['service'];

$body = file_get_contents('php://input');

$params =  json_decode($body , true); // JSON Body is expected
if(empty($body))
 $params = $_REQUEST;

$global_data ;
/*
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
*/
switch($service){
	case 'home':
		$serv =  new HomepageService();
		$data = $serv->getData($params);
		break;

	case 'browse':
		$serv =  new BrowseService();
		$data = $serv->getData($params);
		break;

	case 'list':
		$serv = new DetailService();
		$params['type'] = 'topic';
		$data = $serv->getData($params);
		break;
	case 'detail':
	case 'details':
	case 'fetch':
		$serv = new DetailService();
		$params['type'] = 'fact';
		$data = $serv->getData($params);
		break;
	case 'suggestions':
		$serv =  new AutoCompleteService();
		$data = $serv->getResults($params);
		break;

	case 'search':
		$serv =  new SearchService();
		#$params['q'] = 'debt';
		$data = $serv->getResults($params);
		break;

	case 'form' :
		$serv = new DetailService();
		$params['type'] = 'calculator';
		$data = $serv->getData($params);
		break;

	case 'like':
		$serv = new LikeService();
		$data = $serv->add($params);
		break;		
	case 'isLiked':
		$serv = new LikeService();
		$data = $serv->get($params);
		break;	
	case 'calculator' :
	        $serv = new CalculatorService();
		$calc = $serv->get($params);
		$data = $calc->response($params);
		break;
	case 'gpt' :
	        $serv = new GPTService();
		$data = $serv->getData($params);
		break;


	default:
		return;

}
echo  json_encode($data);


?>
