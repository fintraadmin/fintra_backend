<?php
require_once 'DataClass.php';

class ChaptersClass extends DataClass {

  public function fetchData($params){
	$client = $this->connectDynamo();
	$id = $params['parent_id'];
	$language = $params['language'];
	
	$memcache_key = 'topics-' . $id . '-' . $language;
	$data = MemcacheUtil::getItem($memcache_key);
	if(!empty($data)){
		return $data;
	} 		
			
	$iterator = $client->getIterator('Query', array(
    	'TableName'     => 'chapters',
	"IndexName" => "parent_id-id-index",
    	'ExpressionAttributeValues' => array(':v2' => array('S' => $id) ),
    	'KeyConditionExpression' => 'parent_id = :v2',
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
	$fields =  array('id' , 'about' , 'title' , 'image');
	$final =  $this->filterBylanguage(array('language' => $language ,'data' =>  $res , 'fields' => $fields));
        MemcacheUtil::setItem($memcache_key , $final);
	return $final;	
  }
	


}

?>


