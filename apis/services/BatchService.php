<?php
set_include_path(get_include_path() . PATH_SEPARATOR . '/var/www/html/');

include_once ('BaseActionService.php');
include_once 'utils/memcache.php';

class BatchService extends BaseActionService {

	 public function __construct() {
                parent::__construct();
        }

	public function getTotal(){
		$types =  array('like' , 'view' , 'calculate');

		foreach($types as $type){
			$memcache_key = 'total_'  . $type;
			$iterator = $this->client->getIterator('Query', array(
			        'TableName'     => 'likes',
        			'ExpressionAttributeValues' => array(':v1' => array('S' =>  'total_'. $type) ),
        			'KeyConditionExpression' => 'userid = :v1',
        		));
        		foreach($iterator as $item){
				$count  = 0 ;
                		foreach($item as $key=>$value){
                	        	if(isset($value['N']) && $key == 'count')
                	                	$count =  $value['N'];
                	        	if(isset($value['S']) && $key == 'itemid')
                	                	$itemid =  $value['S'];
                		}
				$memcache_key_id = $memcache_key . ':' . $itemid ;
				$memcache_key_val = $count;
				echo "$memcache_key_id : $memcache_key_val  \n";
    				MemcacheUtil::setItem($memcache_key_id , $memcache_key_val);
        		}
			}
	}
}

$c =  new BatchService();
$c->getTotal();
