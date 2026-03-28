<?php

require 'vendor/autoload.php';
require 'vendor/conf.ini';


use Aws\DynamoDb\DynamoDbClient;
use Aws\Common\Credentials\Credentials;

/*
$id = "mf1ch1";
$field = "description";
$value = "Why investing is very important. Investing helps you build wealth for future goals.";
*/

$ids = array('mf1ch1' , 'mf1ch2' , 'mf1ch3' , 'mf1ch4', 'mf1ch5' , 'mf1ch6' , 'mf1ch7');
$field =  'shareImg';
$values = array('https://images.fintra.co.in/investments-1.jpg','https://images.fintra.co.in/investments-1.jpg', 'https://images.fintra.co.in/investments-1.jpg', 'https://images.fintra.co.in/investments-1.jpg' , 'https://images.fintra.co.in/investments-1.jpg' , 'https://images.fintra.co.in/investments-1.jpg' , 'https://images.fintra.co.in/investments-1.jpg');
$num =  5;

$credentials = new Credentials(ACCESS_KEY, SECRET_KEY);
  
// Instantiate a client with the credentials from the project1 profile
$client = DynamoDbClient::factory(array(
  	'profile' => 'default',
  	'region'  => 'ap-south-1',
  	'credentials' => $credentials
  ));
for($i= 0 ; $i < $num ; $i++){
$id = $ids[$i];
$value = $values[$i];
$val = $client->updateItem( array(
	'TableName'     => 'chapters',
	'Key' =>  array('type' => array('S' => 'chapter') , 'id' => array('S' => $id)),
	'ExpressionAttributeNames' => array('#f' => $field),
	'ExpressionAttributeValues' => array(':v1' => array('S' =>  $value)),
	'UpdateExpression' => 'SET #f=:v1'
));
}
