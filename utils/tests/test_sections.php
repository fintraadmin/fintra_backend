<?php

set_include_path(get_include_path() . PATH_SEPARATOR . '/var/www/html/');
include_once 'apis/dao/SectionDAO.php';

Class TestDao {
 private $dao ;


public function __construct(){
	$this->dao = new SectionDAO();
}

public function getSectionDetails(){
	$fact = $this->dao->getSectionDetails('section_1' , 'en');
	print_r($fact);
}

}

$tDao = new TestDao();
$tDao->getSectionDetails();
