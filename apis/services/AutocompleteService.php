<?php
require_once 'utils/solrutils.php';
require_once 'utils/utils.php';

class AutoCompleteService{

	public function getResults($params){
		$orig_q = mb_strtolower($params['q']);
		$q =mb_strtolower( urlencode($params['q']));
		//TODO : clean the query
		$client = new SolrUtils('autocomplete');
		$client->addParams(array('q' => $q, 'defType'=>'dismax' , 'qf' => 'suggestion_edgy_txt_en^2' , 'bf' => 'sum(1,weight_i)'));
		$response = $client->getResults();
		return $this->formatAutoSuggestions($response, $orig_q);
		//return json_decode(file_get_contents('conf/searchsample.json'));	
	}

	public function formatAutoSuggestions($response,$q){
		$formatted =  array();
		$docs = $response['docs'];

		foreach($docs as $doc){
			$f = $this->transform($doc, $q);
			$formatted[] = $f;
		}

		return $formatted;
	}

	public function transform($doc, $q){

		$new_doc =  array();
		$new_doc['title'] = Utils::highlight($doc['suggestion_txt_en'] , $q);
		#$new_doc['subtitle'] =  $doc['topic'];
		$new_doc['id'] = $doc['id'];
		$new_doc['q'] = $doc['suggestion_txt_en'];
		$new_doc['img'] = $doc['image_s'];
		$new_doc['history'] = false;
		$new_doc['trending'] = true;
		$new_doc['search'] = True;
		if($doc['type_s'] == 'calculator' || $doc['type_s'] =='topic'){
			$new_doc['subtitle'] =  ucwords($doc['type_s']);
			$new_doc['url'] = $doc['url_s'];
			$new_doc['search'] = False; 
		}
		return $new_doc; 
	}



}
