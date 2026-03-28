<?php
include_once ('BaseActionService.php');

class ActionService extends BaseActionService {

	 public function __construct() {
                parent::__construct();
        }

	public function get($uuid, $itemid, $type){
		$iterator = $this->client->getIterator('Query', array(
		        'TableName'     => 'likes',
        		'ExpressionAttributeValues' => array(':v1' => array('S' =>  $uuid) , ':v2' => array('S' => $itemid ) ),
        		'KeyConditionExpression' => 'userid = :v1 and itemid = :v2',
        	));
        	foreach($iterator as $item){
                	foreach($item as $key=>$value){
                        	if(isset($value['S']) && $value['S'] == $type)
                                	return array($key=> $value['S']);
                	}
        	}
		return false;
	}	
	public function getTotal($itemid,$type){
		$memcache_key = 'total_'. $type . ':' . $itemid;
		$data = MemcacheUtil::getItem($memcache_key);
		if(!empty($data)){
			return $data;
		}
		$iterator = $this->client->getIterator('Query', array(
		        'TableName'     => 'likes',
        		'ExpressionAttributeValues' => array(':v1' => array('S' =>  'total_'. $type) , ':v2' => array('S' => $itemid) ),
        		'KeyConditionExpression' => 'userid = :v1 and itemid = :v2',
        	));
		$val = 0;
        	foreach($iterator as $item){
                	foreach($item as $key=>$value){
                        	if(isset($value['N']) && $key == 'count')
                                	$val =  $value['N'];
                	}
        	}
    		MemcacheUtil::setItem($memcache_key , $val);
		return $val;
	}
	public function put($uuid, $itemid, $type){
		//Insert the row
		$time  = date("Y-m-d H:i:s");
		$action = $type;	 
		$val = $this->client->updateItem( array(
    			'TableName'     => 'likes',
			'Key' =>  array('userid' => array('S' => $uuid) , 'itemid' => array('S' => $itemid)),
			'ExpressionAttributeNames' => array('#f1' => 'action', '#f2' => 'updated' , '#f3' => 'count'),
    			'ExpressionAttributeValues' => array(':v1'=> array('S'=> $action), ':v2' =>array('S'=> $time) , ':v3' =>array('N'=> 1)),
    			'UpdateExpression' => 'SET #f1=:v1 , #f2=:v2 , #f3=:v3'
		));
		//Increment count
		
		$val = $this->client->updateItem( array(
    			'TableName'     => 'likes',
			'Key' =>  array('userid' => array('S' => 'total_'. $type) , 'itemid' => array('S' => $itemid)),
			'ExpressionAttributeNames' => array('#f' => 'count'),
    			'ExpressionAttributeValues' => array(':v1' => array('N' =>  1)),
    			'UpdateExpression' => 'ADD #f :v1'
		));


	}

	public function remove($uuid , $itemid, $type){

		$val = $this->client->deleteItem( array(
    			'TableName'     => 'likes',
			'Key' =>  array('userid' => array('S' => $uuid) , 'itemid' => array('S' => $itemid)),
			'ExpressionAttributeNames' => array('#f' => 'action'),
			'ConditionExpression' => '#f=:v',
    			'ExpressionAttributeValues' => array(':v' => array('S' =>  $type)),
		));
		//Increment count
		
		$val = $this->client->updateItem( array(
    			'TableName'     => 'likes',
			'Key' =>  array('userid' => array('S' => 'total_'. $type) , 'itemid' => array('S' => $itemid)),
			'ExpressionAttributeNames' => array('#f' => 'count'),
    			'ExpressionAttributeValues' => array(':v1' => array('N' =>  -1)),
    			'UpdateExpression' => 'ADD #f :v1'
		));

	}
}
