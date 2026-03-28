<?php
set_include_path(get_include_path() . PATH_SEPARATOR . '/var/www/html/');
include_once 'apis/dao/CalculatorDAO.php';
include_once('apis/services/CardService.php');

Class TestDao {
 private $dao ;


public function __construct(){
	$this->dao = new CalculatorDAO();
}

public function get(){
	$fact = $this->dao->getCalculatorByID(1);
	return $fact;
}

public function getFactByTopic(){
       $facts = $this->dao->getFactsByTopic('sip');
	return $facts;
}
}

$card  = New CardService();

$tDao = new TestDao();
$d = $tDao->get();
#$facts = $tDao->getFactByTopic();

$out= $card->format($d);
$resp = $card->response();

print_r($resp);
