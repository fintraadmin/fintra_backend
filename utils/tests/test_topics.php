<?php

set_include_path(get_include_path() . PATH_SEPARATOR . '/var/www/html/');
include_once 'apis/dao/TopicDAO.php';
include_once('apis/services/CardService.php');

Class TestDao {
 private $dao ;


public function __construct(){
	$this->dao = new TopicDAO();
}

public function getTopicByID(){
	$fact = $this->dao->getTopicByID('debt_mutual_fund' , 'en');
	return $fact;
}

public function getFactByTopic(){
       $facts = $this->dao->getFactsByTopic('sip');
	return $facts;
}
}

$card  = New CardService();
$tDao = new TestDao();
$topic = $tDao->getTopicByID();
$out= $card->format($topic);
$resp = $card->response();

print_r($resp);
