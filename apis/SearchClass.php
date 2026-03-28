<?php
require_once 'utils/solrutils.php';
header('Access-Control-Allow-Origin: *'); 

class SearchClass{

	public function getAutoSuggestions($params){
		$q = $params['q'];
		error_log("=== query $q");
		$client = new SolrUtils('autocomplete');
		$client->addParams(array('q' => $q, 'defType'=>'dismax' , 'qf' => 'suggestion_txt_en'));
		$response = $client->getResults();
		return $this->formatAutoSuggestions($response);
		//return json_decode(file_get_contents('conf/searchsample.json'));	
	}

	public function formatAutoSuggestions($response){
		$formatted =  array();
		$docs = $response['docs'];

		foreach($docs as $doc){
			$f = $this->transform($doc);
			$formatted[] = $f;
		}

		return $formatted;
	}

	public function transform($doc){

		$new_doc =  array();
		$new_doc['title'] = $doc['suggestion_txt_en'];
		#$new_doc['subtitle'] =  $doc['topic'];
		$new_doc['id'] = $doc['id'];
		$new_doc['img'] = '';
		return $new_doc; 
	}



}
