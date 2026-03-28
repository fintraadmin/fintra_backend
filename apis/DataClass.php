<?php

require 'lib/vendor/autoload.php';
require 'lib/vendor/conf.ini';

use Aws\DynamoDb\DynamoDbClient;
use Aws\Common\Credentials\Credentials;



abstract class DataClass {

	public function connectDynamo(){
  		$credentials = new Credentials(ACCESS_KEY, SECRET_KEY);
		
  		$client = DynamoDbClient::factory(array(
  			'profile' => 'default',
  			'region'  => 'ap-south-1',
  			'credentials' => $credentials
		));
		return $client;
	}

	public function fetchData($params){
	
	} 

  	public function filterBylanguage($params){
	$language = $params['language'];
	$data = $params['data'];
	$fields = $params['fields'];
	$filter_data =  array();

	if($language == 'en'){
		$suffix = '';
	}
	else{
		$suffix = '_'. $language;
	}
	foreach($data as $d){
		$item = array();
		foreach($fields as $f){
			$key = $f . $suffix;
			if(isset($d[$key])){
				$item[$f] = $d[$key];
			}
			else{
				$item[$f] = $d[$f];
			}
		}
		$filter_data[] = $item;
	}
	return $filter_data;
  }

}
