<?php
/*
$url = 'http://localhost:8983/solr/live/select?q=*:*';

$d =  file_get_contents($url);

$data = json_decode($d, true);
print_r($data['response']);
*/
set_include_path(get_include_path() . PATH_SEPARATOR . '/var/www/html/');
include_once 'utils/solrutils.php';


$client = new SolrUtils('autocomplete');
$client->addParams(array('q' => 'icici', 'defType'=>'dismax' , 'qf' => 'suggestion_txt_en' , 'bf' => 'sum(1,weight_i)'));
print_r($client->getResults());

/*
$client = new SolrUtils('search');
$client->addParams(array('q' => 'title_txt_en:debt%20fund' , 'rows'=>3));
print_r($client->getResults());
*/

?>
