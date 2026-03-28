<?php
require_once 'DataClass.php';

class ActionsClass extends DataClass {

  public function fetchData($params){
	$client = $this->connectDynamo();
	$table = 'actions';
	$action = $params['action'];
	$id = $params['itemid'];
	$uuid = $params['uuid'];
	if(empty($id) ||  empty($uuid)){
		return;
	}
	$params['updated'] = date("Y-m-d H:i:s");
	unset($params['uuid']);
	unset($params['id']);
	$i=0;
	foreach($params as $key=>$val){
                        if(!empty($params[$key])){
                                $i++;
                                $attribute_names['#f'.$i] = $key;
                                $attributes_values[':v'.$i]= array('S' => (string)$params[$key]);
                                $updateExpr[] = '#f'.$i . '= :v'.$i;
                        }
                }
	$updateStr  = 'SET ' . implode("," , $updateExpr); 

	$val = $client->updateItem( array(
    			'TableName'     => $table,
			'Key' =>  array('uuid' => array('S' => $uuid) , 'id' => array('S' => $id)),
			'ExpressionAttributeNames' => $attribute_names,
    			'ExpressionAttributeValues' => $attributes_values,
    			'UpdateExpression' => $updateStr
		));
	//Update calculation count
	if($action == 'calculate'){
		$val = $client->updateItem( array(
    			'TableName'     => 'topics',
			'Key' =>  array('type' => array('S' => 'calculator') , 'id' => array('S' => $id)),
			'ExpressionAttributeNames' => array('#f' => 'calculations'),
    			'ExpressionAttributeValues' => array(':v1' => array('N' =>  1)),
    			'UpdateExpression' => 'ADD #f :v1'
		));
	}

  }
}
	



?>


