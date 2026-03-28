<?php

class SolrClient {
 var $url = '';	

 public function __construct($section){
	$ini_array = parse_ini_file("conf/solrconf.ini", true);
	$ini_array = $ini_array[$section];
	$this->url = 'http://'. $ini_array['SOLR_HOST'] . ':' . $ini_array['SOLR_PORT'] .  '/solr/' . $ini_array['SOLR_CORE']  . '/select?wt=json';
 }

 public function addParams($key , $val){
	if(!empty($key) && !empty($val))
		$this->url = $this->url . '&' .  $key . '=' . $val;
  }

 public function getResponse(){
	$d =  file_get_contents($this->url);
	$data = json_decode($d, true);
	return $data['response'];
 }


}

?>
