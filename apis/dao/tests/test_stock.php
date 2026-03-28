<?php

set_include_path(get_include_path() . PATH_SEPARATOR . '/var/www/html/');
include_once 'apis/dao/StockDAO.php';
include_once('apis/services/CardService.php');

Class TestDao {
 private $dao ;


public function __construct(){
	$this->dao = new StockDAO();
}

public function getByID(){
	$fact = $this->dao->getByID('cipla-limited');
	return $fact;
}

public function getFilters(){
       $facts = $this->dao->getByFilters(array('bank' => 'sbi'));
	return $facts;
}
}


$tDao = new TestDao();
$card = $tDao->getByID();


print_r($card);
