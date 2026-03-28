<?php

set_include_path(get_include_path() . PATH_SEPARATOR . '/var/www/html/');
include_once 'apis/dao/FactDAO.php';
include_once('apis/services/CardService.php');

Class TestDao {
 private $dao ;


public function __construct(){
	$this->dao = new FactDAO();
}

public function getFactByID(){
	$fact = $this->dao->getFactByID(1);
	return $fact;
}

public function getFactByTopic(){
       $facts = $this->dao->getFactsByTopic('sip');
	return $facts;
}
}

$card  = New CardService();

$tDao = new TestDao();
$fact = $tDao->getFactByID();
$facts = $tDao->getFactByTopic();

$out= $card->format($fact);
$resp = $card->response();

print_r($resp);
