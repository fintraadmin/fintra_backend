<?php
require_once 'DataClass.php';

class ChapterDetailClass extends DataClass {

  public function fetchData($params){
	$client = $this->connectDynamo();
	$id = $params['id'];
	$language = $params['language'];
        $langsVal = array('hi' => 'hindi' , 'en' => 'english' , 'gu' => 'gujarati');
	$linklang= $langsVal[$language];
	if(strlen($id) < 8){		
		$iterator = $client->getIterator('Query', array(
    		'TableName'     => 'chapters',
		'ExpressionAttributeNames' => array('#t' => 'type'),
    		'ExpressionAttributeValues' => array(':v1' => array('S' =>  'chapter') , ':v2' => array('S' => $id) ),
    		'KeyConditionExpression' => '#t = :v1 and id = :v2',
	));
	}
	else {
		$iterator = $client->getIterator('Query', array(
    		'TableName'     => 'chapters',
		'IndexName' => 'id_title-index' ,
    		'ExpressionAttributeValues' => array(':v2' => array('S' => $id) ),
    		'KeyConditionExpression' => 'id_title = :v2',
	));
	}
	$res =  array();
	foreach ($iterator as $item) {
	  $data  = array();
    	  foreach($item as $key=>$value){
		if(isset($value['S']))
			$data[$key] = $value['S'];
		if(isset($value['N']))
			$data[$key] = $value['N'];
	  }
	  //Add perma link
	  $category = 'mutual-funds';
	  if($data['category'] ==  'bitcoin'){
		$category = 'bitcoin';
	  }
	  $data['perma_link'] = 'https://fintra.co.in/'. $linklang .'/' . $category .  '/' . $data['id_title'];
	  $data['isLiked'] = $this->isLiked($params , $client) ? "1" : "0";
	  $data['previous'] = $this->getPrevNext($data, true);
	  $data['next'] = $this->getPrevNext($data ,  false);
	  $res[] = $data;
	}
	$fields =  array('id' , 'id_title' , 'title' , 'image' , 'sequence' , 'content' , 'like' , 'perma_link' , 'isLiked' , 'previous' , 'next');
	return $this->filterBylanguage(array('language' => $language ,'data' =>  $res , 'fields' => $fields));
  }

  public function getPrevNext($params, $isPrev){
	$client = $this->connectDynamo();
	$sequence = $params['sequence'];
	$parent_id = $params['parent_id'];
	if($isPrev)
		$seq = $params['sequence'] - 1;	
	else
		$seq = $params['sequence'] + 1;

	$iterator = $client->getIterator('Query', array(
    	'TableName'     => 'chapters',
	'IndexName' => 'parent_id-sequence-index-copy' ,
	'ExpressionAttributeNames' => array('#s' => 'sequence'),
    	'ExpressionAttributeValues' => array(':v2' => array('S' => $parent_id) , ':v3' => array('N' => $seq) ),
    	'KeyConditionExpression' => 'parent_id = :v2 and #s = :v3 ',
	));

	foreach ($iterator as $item) {
          $data  = array();
          foreach($item as $key=>$value){
                if(isset($value['S']))
                        $data[$key] = $value['S'];
                if(isset($value['N']))
                        $data[$key] = $value['N'];
          }
	}
	if(isset($data['id']))
		return $data['id'];	
  }

  public function isLiked($params, $client){
	$uuid = $params['uuid'];
	$itemid = $params['id'];
	if(empty($uuid))
		return false;
	$iterator = $client->getIterator('Query', array(
    	'TableName'     => 'likes',
    	'ExpressionAttributeValues' => array(':v1' => array('S' =>  $uuid) , ':v2' => array('S' => $itemid) ),
    	'KeyConditionExpression' => 'userid = :v1 and itemid = :v2',
	));
	foreach($iterator as $item){
		foreach($item as $key=>$value){
			if(isset($value['S']) && $value['S'] == 'like')
				return true;
		}
	}
	return false;

  }
	


}

?>


