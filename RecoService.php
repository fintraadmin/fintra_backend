<?php

require_once 'vendor/autoload.php';
class StockRecoService{
	var $data ;
	var $lang;

	public function __construct($ln='english'){
		$file_path = 'mocks/stocks/reco.json';
		$this->data = json_decode(file_get_contents($file_path), true);
		$this->lang = $ln;
	}
	public function getReco($id, $type){
		if($id== 'nifty50High' || $id == 'nifty50Low')
			$list = $this->data[$id];
		else if($type == 'high')
			$list = $this->data['highSector'][$id];
		else if($type == 'low')
			$list = $this->data['lowSector'][$id];
		if(empty($list)  && $type == 'high'){
			$list = $this->data['nifty50High'];
		}
		if(empty($list)  && $type == 'low'){
			$list = $this->data['nifty50Low'];
		}
		$data = array();
		foreach($list as $l){
			$data[] = $this->getDetails($l);
		}
		return array('recos' => $data , 'id' => $id , 'type' => $type);
	}

	public function  getDetails($id){
		$file_path = 'mocks/stocks/' . $id . '.json';
 		$json_data = json_decode(file_get_contents($file_path), true);
 		return $this->tranform($json_data, $id);
	}

	public function tranform($json, $id){
        	$ln = $this->lang;
        	setlocale(LC_MONETARY, 'en_IN');
        	$data = array();
        	$data['company_title'] = $json['info']['companyName'];
        	$data['company_name'] = $json['info']['companyName'];
        	if (!empty($json['language'])){
        	        $data['company_name'] = $json['language']['shortName'];

        	        if($ln == 'hindi'){
        	                $data['company_title'] = $json['language']['nameHindi'];
        	                $data['company_name'] = $json['language']['shortNameHindi'];
        	        }
        	        if($ln == 'punjabi'){
        	                $data['company_title'] = $json['language']['namePunjabi'];
        	                $data['company_name'] = $json['language']['shortNamePunjabi'];
        	        }
        	}
        	$data['industry'] = $json['info']['industry'];
		$data['ltp'] = $json['priceInfo']['lastPrice'];
		$data['pChange'] = round($json['priceInfo']['pChange'] , 2);
		$data['priceChange'] = round($json['priceInfo']['change'] , 2);
		$data['open_price'] = $json['priceInfo']['open'];
		$data['intradayHigh'] = $json['priceInfo']['intraDayHighLow']['max'];
		$data['intradayLow'] = $json['priceInfo']['intraDayHighLow']['min'];
		$data['url'] = '/' . $ln . '/stock/' . $id;
		$data['dropped'] = 1;
		if($data['pChange'] < 0){
			$data['dropped'] = -1;
		}
		return $data;
	}
	public function output($reco){
		
		$loader = new Twig_Loader_Filesystem('templates');
		$twig = new Twig_Environment($loader, array(
    			//'cache' => '/tmp/compilation_cache',
		));
		$template_file = 'module-reco.html';
		if($this->lang == 'hindi')
			$template_file = 'module-reco-hi.html';
		$template = $twig->load($template_file);
 		return $template->render($reco);
	}
}
/*
$c =  new StockRecoService('hindi');
$r = $c->getReco('PRINTING & STATIONERY' , 'high');
echo $c->output($r);
*/
