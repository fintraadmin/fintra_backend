<?php
set_include_path(get_include_path() . PATH_SEPARATOR . '/var/www/html/');


include_once '../RecoService.php';


$serv =  new RecoService();
#$data = $serv->getRecos('fact' , '50' , 'en');
#print_r($data);

$data = $serv->getRecos('calculator' , '10' , 'en');
print_r($data);
