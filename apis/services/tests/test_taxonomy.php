<?php
set_include_path(get_include_path() . PATH_SEPARATOR . '/var/www/html/');


include_once '../TaxonomyService.php';


$serv =  new TaxonomyService();
$data = $serv->getBrowseTree('fact' , '558' , 'en');
print_r($data);
#$data = $serv->getBrowseTree('topic' , 'banking' , 'en');
#print_r($data);
#$data = $serv->getBrowseTree('calculator' , '11' , 'en');
#print_r($data);
