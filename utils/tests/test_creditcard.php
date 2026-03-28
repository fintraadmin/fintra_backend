<?php

set_include_path(get_include_path() . PATH_SEPARATOR . '/var/www/html/');
include_once 'apis/dao/CreditCardDAO.php';
include_once('apis/services/CardService.php');

Class TestDao {
 private $dao ;


public function __construct(){
	$this->dao = new CreditCardDAO();
}

public function getByID(){
	$fact = $this->dao->getByID('citi-premiermiles-credit-card');
	return $fact;
}

public function getFilters(){
       $facts = $this->dao->getByFilters(array('bank' => 'sbi'));
	return $facts;
}
}

$card  = New CardService();

$tDao = new TestDao();
$card = $tDao->getByID();
$cards = $tDao->getFilters();

#$out= $card->format($fact);
#$resp = $card->response();

print_r($cards);
