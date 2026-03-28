<?php
require_once 'utils/utils.php';
require_once 'DataClass.php';
require_once 'ActionsClass.php';
require_once 'content_classes/blog.class.php';


class ItemUpdateClass extends DataClass {

  public function fetchData($params){
	$client = $this->connectDynamo();
	$action = $params['action'];
	$flag = true;
	if($action == 'calculate'){
		$actionClass =  new ActionsClass();
		$actionClass->fetchData($params);
		return;
	}
	$id = $params['itemid'];
	if($params['type'] == 'blog'){
		$bc = new BlogClass();
		if ($action == 'like'){
			$bc->like($params);
		}	
		if ($action == 'view'){
			$bc->view($params);
		}
		$flag = false;
	}
	if($params['type'] == 'topic'){
	 $table = 'topics';
	 $type = 'topic';
	}
	else if ($params['type'] == 'chapter'){
	 $table = 'chapters';
	 $type = 'chapter';
        }
	 else if ($params['type'] == 'calculator'){
         $table = 'topics';
         $type = 'calculator';
        }
	if ($flag == true)
	if($action == 'like' || $action == 'view' || $action == 'dislike'){
		$num = 1; 
		$attribute = 'like'; //attribute name is like
		if($action == 'dislike'){
			$num = -1;
		}
		if($action ==  'view'){
			$attribute = 'view';
		}	
		
		$val = $client->updateItem( array(
    			'TableName'     => $table,
			'Key' =>  array('type' => array('S' => $type) , 'id' => array('S' => $id)),
			'ExpressionAttributeNames' => array('#f' => $attribute),
    			'ExpressionAttributeValues' => array(':v1' => array('N' =>  $num)),
    			'UpdateExpression' => 'ADD #f :v1'
		));

	}
	//Create an entry in likes table for the user
	$client->putItem(array(
		'TableName' => 'likes',
		'Item' => array('action' =>  array('S' =>  $action) , 'itemid' => array('S' => $params['itemid']) , 'updated' => array('S' => date("Y-m-d H:i:s")) ,  'userid' => array('S' => $params['uuid'])),
		
	));
  }
	


}

?>


