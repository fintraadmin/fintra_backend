<?php
/*
$url = 'http://localhost:8983/solr/live/select?q=*:*';

$d =  file_get_contents($url);

$data = json_decode($d, true);
print_r($data['response']);
*/
set_include_path(get_include_path() . PATH_SEPARATOR . '/var/www/html/');
include_once 'utils/solrutils.php';


$q = 'ICICI%20Fund';
$client = new SolrUtils('search');
$client->addParams(array('q' => $q, 'defType'=>'dismax' , 'qf' => 'title_txt_en' , 'rows' => 3));
print_r($client->getResults());

?>
