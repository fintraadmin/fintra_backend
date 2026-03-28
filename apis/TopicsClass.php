<?php
require_once 'DataClass.php';
require_once 'utils/memcache.php';

class TopicsClass extends DataClass {

  public function fetchData($params){
	
	$client = $this->connectDynamo();
	$language = $params['language'];
	if(empty($language))
		$language = 'en';

	$app_version =  '1.0.2';
	if(isset($params['version'])){
		$app_version = $params['version'];
	}	
	$memcache_key = 'topics-' . $language;
	$data = MemcacheUtil::getItem($memcache_key);
	if(!empty($data)){
		return $data;
	} 		
	$iterator = $client->getIterator('Query', array(
    	'TableName'     => 'topics',
	'ExpressionAttributeNames' => array('#t' => 'type'),
    	'ExpressionAttributeValues' => array(':v1' => array('S' =>  'topic')),
    	'KeyConditionExpression' => '#t = :v1',
	));
	$res =  array();
	foreach ($iterator as $item) {
	  $data  = array();
    	  foreach($item as $key=>$value){
		if(isset($value['S']))
			$data[$key] = $value['S'];
		if(isset($value['N']))
			$data[$key] = $value['N'];
	  }
	  $res[] = $data;
	}
	$filter_res =  array();
	foreach($res as $r){
		$min_version = $r['min_version'];
		if(isset($min_version))
			if(version_compare($min_version ,  $app_version) > 0)
				continue; 

		$filter_res[] = $r;
	}
	$res = $filter_res;
	//Sort by sequence
	usort($res, function ($item1, $item2) {
   		 return $item1['sequence'] <=> $item2['sequence'];
	});
	$fields = array('title' , 'about' , 'view' , 'time' , 'numChapters' , 'id' , 'image');
	$final =  $this->filterBylanguage(array('language' => $language ,'data' =>  $res , 'fields' => $fields));
        MemcacheUtil::setItem($memcache_key , $final);
	return $final;	
  }
	

}

?>


