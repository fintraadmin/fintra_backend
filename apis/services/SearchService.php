<?php
require_once 'utils/solrutils.php';
include_once 'apis/dao/FactDAO.php';
include_once 'apis/dao/CalculatorDAO.php';
include_once 'apis/dao/MutualFundDAO.php';
include_once 'apis/dao/FixedDepositDAO.php';
include_once 'CardService.php';

class SearchService{

	public function getResults($params){
		$q = urlencode($params['q']);
		$client = new SolrUtils('search');
		$client->addParams(array('q' => $q, 'defType'=>'dismax' , 'qf' => 'title_txt_en' , 'rows' => 10 , 'bf' => 'sum(1,weight_i)'));
		$response = $client->getResults();
		//check if empty docs then do a secodn search
		$docs = $response['docs'];
		if(count($docs) < 1){
			$client->addParams(array('q' => $q, 'defType'=>'dismax' , 'qf' => 'description_txt_en' , 'rows' => 10));
			$response = $client->getResults();
		}
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
		$id = $doc['iid_s'];
		$ln = 'en';
		$type = $doc['type'];
		if($type =='mutual_fund'){
			$DAO = new MutualFundDAO();
			$data = $DAO->getFactByID($id,$ln);

		}
		else if($type == 'fact'){
			$DAO = new FactDAO();
			$data = $DAO->getFactByID($id,$ln);
		}
		else if($type == 'calculator'){
			$DAO = new CalculatorDAO();
			$data = $DAO->getCalculatorByID($id,$ln);
		}
		else if($type == 'fixed_deposit'){
			$DAO = new FixedDepositDAO();
			$data = $DAO->getByID($id,$ln);
		}
		else if($type == 'topic'){
			$DAO = new CalculatorDAO();
			$data = $DAO->getCalculatorByID($id,$ln);
		}
		$cardSrv = new CardService();
		$cardSrv->format($data);
		$new_doc = $cardSrv->response();
		return $new_doc; 
	}



}
