<?php

set_include_path(get_include_path() . PATH_SEPARATOR . '/var/www/html/');
include_once 'apis/dao/CryptoDAO.php';
include_once('apis/services/CardService.php');

Class TestDao {
 private $dao ;


public function __construct(){
	$this->dao = new CryptoDAO();
}

public function getByID(){
	$fact = $this->dao->getByID('bitcoin');
	return $fact;
}

public function getN(){
       $facts = $this->dao->getN(4);
	return $facts;
}
}


$tDao = new TestDao();
$card = $tDao->getByID();
print_r($card);
$card = $tDao->getN();
print_r($card);
