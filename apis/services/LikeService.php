<?php


include_once 'AWSQueueService.php';
include_once 'ActionService.php';

class LikeService{

	public function add($params){
		$uuid = $params['uuid'];
		$type = $params['type'];
		$id = $params['id'];
		$id = $type . ':'  . $id;
		$msg =  array();
		$msg['uuid'] = $uuid;
		$msg['itemid'] = $id ;
		$msg['type'] = 'like';
		if(isset($params['like']) && $params['like'] === False)
			$msg['remove'] = True;
		$serv = new SQS();
		$serv->sendMsg(json_encode($msg));
	}
	public function get($params){
		$uuid = $params['uuid'];
		$type = $params['type'];
		$id = $params['id'];
		if(empty($id) || empty($type) ||  empty($uuid))
			return false;
		$action = $params['action'];
		$id = $type . ':'  . $id . ':like'  ;
		$serv =  new ActionService();
		$data = $serv->get($uuid, $id , 'like');
		if ($data)
			return true;
		return false;
	}
}
