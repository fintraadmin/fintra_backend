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

  
$credentials = new Credentials(ACCESS_KEY, SECRET_KEY);
// Instantiate a client with the credentials from the project1 profile
$client = DynamoDbClient::factory(array(
  	'profile' => 'default',
  	'region'  => 'ap-south-1',
  	'credentials' => $credentials
  ));

$parent_ids = array('MF1' ,'MF2' , 'MF3' , 'PF1' , 'PF2' , 'PF3');

foreach($parent_ids as $id){
$iterator = $client->getIterator('Query', array(
        'TableName'     => 'chapters',
                'IndexName' => 'parent_id-sequence-index-copy',
        'ExpressionAttributeValues' => array( ':v2' => array('S' => $id)),
        'KeyConditionExpression' => 'parent_id = :v2',
        ));
$keys = array('title', 'content');

foreach ($iterator as $item) {
          $data  = array();
          foreach($item as $key=>$value){
		if(!in_array($key , $keys))
			continue;
                if(isset($value['S']))
                        $data[$key] = $value['S'];
                if(isset($value['N']))
                        $data[$key] = $value['N'];
          }
	 foreach($keys as $k)
		echo $data[$k]. "\n";

	echo "\n";
	
}
}



