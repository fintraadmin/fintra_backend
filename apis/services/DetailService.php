<?php

include_once 'apis/dao/FactDAO.php';
include_once 'apis/dao/CalculatorDAO.php';
include_once 'apis/dao/TopicDAO.php';
include_once 'apis/dao/CreditCardDAO.php';
include_once 'CardService.php';
include_once 'AWSQueueService.php';


class DetailService {


	public function getData($params){
		global $global_data;
		$DAO = new FactDAO();
		$ln = $params['language']; 
		if(empty($ln)){
			$ln = 'en';
		}
		$type = $params['type'];
		$id = $params['id'];
		$uuid = $params['uuid'];
		$data  =  array();
		$response = array();
		if($type == 'topic'){
			$global_data['response_type'] = 'mini';
			$data = $DAO->getFactsByTopic($id,$ln);
			$topicDAO = new TopicDAO();
			$td[] = $topicDAO->getTopicByID($id, $ln);
			$formattted_data = $this->format($data);
			$formattted_topic_data = $this->format($td);
			$response = $formattted_topic_data[0];
			$response['list'] = $formattted_data;
			
		}
		if($type == 'fact'){
			$data[] = $DAO->getFactByID($id,$ln);
			$response = $this->format($data);
		}
		if($type == 'credit-card'){
			$data[] = $DAO->getCardByID($id,$ln);
			$response = $this->format($data);
		}
		if($type == 'calculator'){
			//$id = 1;
			$DAO =  new CalculatorDAO();
			$data[] = $DAO->getCalculatorByID($id,$ln);
			$response = $this->format($data);
		}
		//TODO : replace with uuid
		if(isset($uuid) && $uuid!= "0"){
			$this->addView($uuid , $type , $id);
		}
		return $response;	
	}

	public function addView($uuid, $type , $id){
		$serv = new SQS();
		$id = $type . ':'  . $id;
		$msg =  array();
		$msg['uuid'] = $uuid;
		$msg['itemid'] = $id ;
		$msg['type'] = 'view';
		$serv->sendMsg(json_encode($msg));
	}
	public function format($data){
		$list = array();
		$cardSrv = new CardService();
		foreach($data as $d){
			$cardSrv->format($d);
			$list[] = $cardSrv->response();
		}

		return $list;
	}
	



}

