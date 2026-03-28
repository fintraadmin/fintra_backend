<?php
require_once 'apis/LoggingClass.php';
require_once 'apis/user_classes/Auth.php';


$service = $_REQUEST['service'];

$service  ;
$body = file_get_contents('php://input');

$params =  json_decode($body , true); // JSON Body is expected
if(empty($body))
 $params = $_REQUEST;

Utils::parseParams($params);
switch($service){
	case 'verifyuser':
			$class =  new Auth();
			$data = $class->verifyUserPhone($params);
			break;
	case 'createuser':
			$class = new Auth();
			$data = $class->createUser($params);
			break;
	case 'sendOTP':
			$class = new Auth();
			$data = $class->sendOTP($params);
			break;
	case 'default':
			$data = array();

}
if($service !=  'log'){
	$params['service'] = $service;
	$logclass =  new LoggingClass();
	$logclass->logData($params);
}
echo  json_encode($data);
