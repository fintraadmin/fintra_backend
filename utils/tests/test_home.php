<?php

set_include_path(get_include_path() . PATH_SEPARATOR . '/var/www/html/');
include_once 'apis/dao/HomepageDAO.php';

Class TestDao {
 private $dao ;


public function __construct(){
	$this->dao = new HomepageDAO();
}

public function getDetails(){
	$data = $this->dao->getHomepage('en');
	print_r($data);
}

}

$tDao = new TestDao();
$tDao->getDetails();
