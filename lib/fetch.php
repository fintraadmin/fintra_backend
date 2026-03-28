<?php

require 'lib/vendor/autoload.php';
require 'lib/vendor/conf.ini';


use Aws\DynamoDb\DynamoDbClient;
use Aws\Common\Credentials\Credentials;


class DBService {

  var $client;
 
  function __construct(){
  $credentials = new Credentials(ACCESS_KEY, SECRET_KEY);
  
  // Instantiate a client with the credentials from the project1 profile
  $this->client = DynamoDbClient::factory(array(
  	'profile' => 'default',
  	'region'  => 'ap-south-1',
  	'credentials' => $credentials
  ));
  }

  public function getTopics($type, $lang){
        $iterator = $this->client->getIterator('Query', array(
        	'TableName'     => 'topics',
			'ExpressionAttributeNames' => array('#t' => 'type'),
        	'ExpressionAttributeValues' => array( ':v2' => array('S' => 'topic')), // this is topics right now
        	'KeyConditionExpression' => '#t = :v2',
        ));
		$topics =  array();
        foreach ($iterator as $item) {
          $data  = array();
          foreach($item as $key=>$value){
                if(isset($value['S']))
                        $data[$key] = $value['S'];
                if(isset($value['N']))
                        $data[$key] = $value['N'];
          }
			$topics[] = $data;
		}
		$fields = array('id');
		$fields_l = array( 'title');
		$topics = $this->filterFields($topics , $fields , $fields_l , $lang);
		//iterate over topics
		foreach($topics as &$t){
			// fetch chapters in a topics
			$chpts = $this->getChaptersByTopic($t['id'],$lang);
			$t['chapters' ] = $chpts;
		}
		return $topics;
  }

  public function filterFields($data , $filters , $filters_l , $lang){
	$resp =  array();
	$fields = $filters;
	$fields_l = $filters_l;
	foreach($data as  $d){
		$i = array();
		foreach($fields as $f){
			$i[$f] = $d[$f];
		}
		foreach($fields_l as $f){
			$f_l = $f . "_" . $lang;
			if($lang == 'en') // en is default
				$f_l = $f;
			$i[$f] = $d[$f_l];
		}
		$resp[] = $i;
	}
	return $resp;

  }

  public function getChaptersByTopic($id,$lang){
        $iterator = $this->client->getIterator('Query', array(
        'TableName'     => 'chapters',
		'IndexName' => 'parent_id-sequence-index-copy',
        'ExpressionAttributeValues' => array( ':v2' => array('S' => $id)),
        'KeyConditionExpression' => 'parent_id = :v2',
        ));

	$chapters = array();
        foreach ($iterator as $item) {
          $data  = array();
          foreach($item as $key=>$value){
                if(isset($value['S']))
                        $data[$key] = $value['S'];
                if(isset($value['N']))
                        $data[$key] = $value['N'];
          }
		//add url link
	  $this->addChapterUrl($data,$lang);
	  $chapters[] = $data;
	}
	return  $this->filterFields($chapters , array('id_title', 'link') , array('title') , $lang);
  }
  public function addChapterUrl(&$data,$lang){
	$id = $data['id_title'];
	$langs = array('hi' => 'hindi' , 'en' => 'english' , 'gu' => 'gujarati');
	$l = $langs[$lang];
	$link = '/' . $l . '/mutual-funds/' . $id;
	$data['link'] = $link;
  }
  public function getChapterByIDTitle($id, $lang){
        $iterator = $this->client->getIterator('Query', array(
        'TableName'     => 'chapters',
		'IndexName' => 'id_title-index',
        'ExpressionAttributeValues' => array( ':v2' => array('S' => $id)),
        'KeyConditionExpression' => 'id_title = :v2',
        ));

        $data  = array();
        foreach ($iterator as $item) {
          foreach($item as $key=>$value){
                if(isset($value['S']))
                        $data[$key] = $value['S'];
                if(isset($value['N']))
                        $data[$key] = $value['N'];
          }
        }
	$data['lang'] = $lang;
	$data['prev'] = $this->getPrevNext($data , true);
	$data['next'] = $this->getPrevNext($data , false);
	
        return $data;
  }
 

  public function getChapterByID($id){
	$iterator = $this->client->getIterator('Query', array(
    	'TableName'     => 'chapters',
	'ExpressionAttributeNames' => array('#t' => 'type'),
    	'ExpressionAttributeValues' => array(':v1' => array('S' =>  'chapter') , ':v2' => array('S' => $id)),
    	'KeyConditionExpression' => '#t = :v1 AND id = :v2',
	));

	$data  = array();
	foreach ($iterator as $item) {
    	  foreach($item as $key=>$value){
		if(isset($value['S']))
			$data[$key] = $value['S'];
		if(isset($value['N']))
			$data[$key] = $value['N'];
	  }
	}
	$data['prev'] = $this->getPrevNext($data , true);
	$data['next'] = $this->getPrevNext($data , false);
	return $data;
  }
  public function getPrevNext($params, $isPrev){
	$client = $this->client;
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
	if(!isset($params['lang']) || $params['lang'] =='en')
		$title_k = 'title';
	else
		$title_k = 'title_' . $params['lang'];
	if(isset($data['id_title']) && isset($data[$title_k]))
		return array('id' => $data['id_title'] , 'title'=>$data[$title_k]) ;	
  }
}
/* 
$dbs = new DBService();
$dbs->getChapterByID('mf1ch1');
*/
?>
