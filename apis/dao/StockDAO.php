<?php 
require_once 'DAOBase.php';
define('SITE_ROOT', '/var/www/html/');

class StockDAO extends DAOBase {
	public $type	= 'stock';


	public function getByID($id, $ln ='en'){
 		$file_path = SITE_ROOT . 'mocks/stocks/' . $id . '.json';
 		if(!file_exists($file_path)){
			return null;
		}

 		$json_data = json_decode(file_get_contents($file_path), true);
		$json_data['id']= $id;
		return $this->tranform($json_data, $ln);
	}	

	public function getType(){
		return $this->type;
	}
	
	private function tranform($json, $ln){
	
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
				$data['company_title'] = $json['language']['namePunjabi'] . '(' .  $data['company_title']  .')';
				$data['company_name'] = $json['language']['shortNamePunjabi'] . '(' .  $data['company_name']  .')';
			}
		}
		$data['id'] = $json['id'];
		$data['industry'] = $json['info']['industry'];
		$data['last_updated'] = $json['metadata']['lastUpdateTime'];
		$date=date_create($data['last_updated']);
		$data['peratio'] = $json['metadata']['pdSymbolPe'];
		$data['peratiosector'] = $json['metadata']['pdSectorPe'];
		$data['sectorindex'] = $json['metadata']['pdSectorInd'];
		$data['ltp'] = $json['priceInfo']['lastPrice'];
		$data['open_price'] = $json['priceInfo']['open'];
		$data['previousClose'] = $json['priceInfo']['previousClose'];
		$data['pChange'] = round($json['priceInfo']['pChange'] , 2);
		$data['priceChange'] = $json['priceInfo']['change'];
		$data['intradayHigh'] = $json['priceInfo']['intraDayHighLow']['max'];
		$data['intradayLow'] = $json['priceInfo']['intraDayHighLow']['min'];
		$data['weekHigh'] = $json['priceInfo']['weekHighLow']['max'];
		$data['weekLow'] = $json['priceInfo']['weekHighLow']['min'];
		$data['weekHighDate'] = $json['priceInfo']['weekHighLow']['maxDate'];
		$data['weekLowDate'] = $json['priceInfo']['weekHighLow']['minDate'];
		$data['volume'] = money_format('%!.0n',  $json['marketDeptOrderBook']['tradeInfo']['totalTradedVolume']);
		$data['dropped'] = 1;
		$weekChangeHigh = -1 * round((($data['weekHigh'] - $data['ltp'])/($data['ltp']+0.001)) *100, 2);
		$weekChangeLow = round((($data['ltp'] - $data['weekLow'])/($data['ltp']+ 0.001)) *100, 2);
		$data['weekChangeHigh'] = $weekChangeHigh;
		$data['weekChangeLow'] = $weekChangeLow;
		$data['title'] = $data['company_name'] . ' share price NSE latest';
		if($ln == 'hindi'){
			$data['title'] = $data['company_name'] . '  शेयर की एनएसई पर कीमत';
		}
		if($data['pChange'] < 0){
			$data['dropped'] = -1;
		}
		return $data;
	}

}
?>
