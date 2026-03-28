<?php
include_once ('utils/SolrClient.php');

class SolrUtils{
	
 private $client;

 public function __construct($section='autocomplete'){
		$this->client = new SolrClient($section);
 }

 public function addParams($arr){
	foreach($arr as $key=>$val){
		$this->client->addParams($key, $val);
	}

 }

 public function getResults(){
	return $this->client->getResponse();
 }

}


