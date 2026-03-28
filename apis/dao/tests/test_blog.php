<?php

set_include_path(get_include_path() . PATH_SEPARATOR . '/var/www/html/');
include_once 'apis/dao/BlogDAO.php';

Class TestDao {
 private $dao ;


public function __construct(){
	$this->dao = new BlogDAO();
}

public function getByID($id){
	$fact = $this->dao->getByID($id);
	return $fact;
}

public function getSimilar($id){
       $facts = $this->dao->getSimilarBlogs($id);
	return $facts;
}
}

$id =  '';
$tDao = new TestDao();
$blog = $tDao->getByID($id);
$sim = $tDao->getSimilar($id);

print_r($blog);
#$out= $card->format($fact);
#$resp = $card->response();
foreach($sim as $s){
	echo $s['title'] . "\n";
	echo $s['image'] . "\n";
}
