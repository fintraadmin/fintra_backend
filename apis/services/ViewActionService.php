<?php
include_once ('BaseActionService.php');

class ViewActionService extends BaseActionService {

	 public function __construct() {
                parent::__construct();
        }

	public function get($uuid, $itemid){
		$iterator = $this->client->getIterator('Query', array(
		        'TableName'     => 'likes',
        		'ExpressionAttributeValues' => array(':v1' => array('S' =>  $uuid) , ':v2' => array('S' => $itemid . ':view') ),
        		'KeyConditionExpression' => 'userid = :v1 and itemid = :v2',
        	));
        	foreach($iterator as $item){
                	foreach($item as $key=>$value){
                        	if(isset($value['S']) && $value['S'] == 'view')
                                	return array($key=> $value['S']);
                	}
        	}
		return false;
	}	
	public function getTotal($itemid){
		$iterator = $this->client->getIterator('Query', array(
		        'TableName'     => 'likes',
        		'ExpressionAttributeValues' => array(':v1' => array('S' =>  'total_views') , ':v2' => array('S' => $itemid) ),
        		'KeyConditionExpression' => 'userid = :v1 and itemid = :v2',
        	));
        	foreach($iterator as $item){
                	foreach($item as $key=>$value){
                        	if(isset($value['N']) && $value['N'] == 'count')
                                	return array($key=> $value['N']);
                	}
        	}
		return 0;
	}

	public function put($uuid, $itemid){
		//Insert the row
		$time  = date("Y-m-d H:i:s");
		$action = 'view';	 
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
			'Key' =>  array('userid' => array('S' => 'total_views') , 'itemid' => array('S' => $itemid)),
			'ExpressionAttributeNames' => array('#f' => 'count'),
    			'ExpressionAttributeValues' => array(':v1' => array('N' =>  1)),
    			'UpdateExpression' => 'ADD #f :v1'
		));


	}

	public function remove($uuid , $itemid){

		$val = $this->client->deleteItem( array(
    			'TableName'     => 'likes',
			'Key' =>  array('userid' => array('S' => $uuid) , 'itemid' => array('S' => $itemid)),
			'ExpressionAttributeNames' => array('#f' => 'action'),
			'ConditionExpression' => '#f=:v',
    			'ExpressionAttributeValues' => array(':v' => array('S' =>  'view')),
		));
		//Increment count
		
		$val = $this->client->updateItem( array(
    			'TableName'     => 'likes',
			'Key' =>  array('userid' => array('S' => 'total_views') , 'itemid' => array('S' => $itemid)),
			'ExpressionAttributeNames' => array('#f' => 'count'),
    			'ExpressionAttributeValues' => array(':v1' => array('N' =>  -1)),
    			'UpdateExpression' => 'ADD #f :v1'
		));

	}
}
