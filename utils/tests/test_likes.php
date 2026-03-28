<?php

set_include_path(get_include_path() . PATH_SEPARATOR . '/var/www/html/');
include_once('apis/services/LikeService.php');

Class Test {
 private $class ;


public function __construct(){
        $this->class = new LikeService();
}

public function get(){
        $d = $this->class->get('testuuid' , 'testitemid');
        return $d;
}

public function put(){
        $this->class->put('testuuid' , 'testitemid');
}
public function remove(){
        $this->class->remove('testuuid' , 'testitemid');
}
}

$t = new Test();
$v = $t->get();
print_r($v);
#$t->put();
$v = $t->remove();
print_r($v);
