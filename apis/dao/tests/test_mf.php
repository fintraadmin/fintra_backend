<?php

set_include_path(get_include_path() . PATH_SEPARATOR . '/var/www/html/');
include_once 'apis/dao/MFDAO.php';

Class TestDao {
 private $dao ;


public function __construct(){
	$this->dao = new MFDAO();
}

public function getByID(){
	$fact = $this->dao->getByID('axis-overnight-fund');
	return $fact;
}

public function getFilters(){
       $facts = $this->dao->getByFilters(array('bank' => 'sbi'));
	return $facts;
}
}


$tDao = new TestDao();
$d = $tDao->getByID();


print_r($d);
