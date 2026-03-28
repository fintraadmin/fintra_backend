<?php
require_once 'DataClass.php';

class UserClass extends DataClass {

  public function fetchData($params){
	$client = $this->connectDynamo();
	$type = $params['method'];
	if(empty($params['uuid'])){
		return;
	}	
	$fields =  array('email', 'phone' , 'device_token' , 'serial' , 'os' , 'model' , 'manufacturer' ,  'name' , 'uuid' , 'updated');

	if($type == 'create'){
		$items_array = array();
		foreach($fields as $f){
			if(!empty($params[$f]))
			$items_array[$f]= array ('S' => $params[$f]);
		}	
		//Add created time
		$items_array['created'] = array('S' => date("Y-m-d H:i:s")); 
		$client->putItem(array(
		  'TableName' => 'users',
	          'Item' =>$items_array
		));

	}
	if($type == 'update'){
		$uuid = $params['uuid'];
		unset($params['uuid']);	 // remove uuid
		$params['updated'] = date("Y-m-d H:i:s");

		// Build Expressions Attribute names
		$attributes_names =  array();
		$attributes_values =  array();
		$updateExpr = array();
		$i= 0 ;
		foreach($fields as $f){
			if(!empty($params[$f])){
				$i++;
				$attribute_names['#f'.$i] = $f;
				$attributes_values[':v'.$i]= array('S' => $params[$f]);
				$updateExpr[] = '#f'.$i . '= :v'.$i;
			}
		}
		$updateStr  = 'SET ' . implode("," , $updateExpr); 
		$val = $client->updateItem( array(
    			'TableName'     => 'users',
			'Key' =>  array('uuid' => array('S' => $uuid)),
			'ExpressionAttributeNames' => $attribute_names,
    			'ExpressionAttributeValues' => $attributes_values,
    			'UpdateExpression' => $updateStr
		));
	}

	}
	

}

?>


