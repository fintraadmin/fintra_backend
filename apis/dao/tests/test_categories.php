<?php

set_include_path(get_include_path() . PATH_SEPARATOR . '/var/www/html/');
include_once 'apis/dao/CategoryDAO.php';
include_once 'apis/dao/CalculatorDAO.php';
include_once 'apis/dao/TopicDAO.php';

Class TestDao {
 private $dao ;


public function __construct(){
	$this->dao = new CategoryDAO();
}

public function getByID(){
	$fact = $this->dao->getByID('mutual-funds');
	return $fact;
}

public function getList(){
       $facts = $this->dao->getList('en');
	return $facts;
}
public function getDetails(){
       $facts = $this->dao->getDetails('income-tax' , 'en');
	return $facts;
}

}


$tDao = new TestDao();
$card = $tDao->getByID();
#print_r($card);

$cards = $tDao->getList();
print_r($cards);

$d = $tDao->getDetails();
#print_r($d);
