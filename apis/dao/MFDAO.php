<?php
require_once 'DAOBase.php';
require_once 'apis/calculators/BaseCalculator.php';
require_once 'apis/calculators/SIPCalculator.php';
require_once 'apis/calculators/CICalculator.php';

Class MFDAO extends DAOBase {
	private $table = 'credit_cards';
	private $table_trans = 'credit_cards__translations';
	public $fields = array ('url' => 'id' , 'language_code' => 'language' );
	public $fields_mini = array ('url' => 'id' , 'language_code' => 'language' );
	public $type	= 'mf';
	var $cacheOn =  false;

	public function __construct() {
		parent::__construct();
	}
	public function getType(){
		return $this->type;
	}

	public function getByID($id, $ln = 'en') {
		if(empty($id))
			return;
		$memcache_key = $this->type . '.' . $id . '.' . $ln;
		$data = unserialize(MemcacheUtil::getItem($memcache_key));
		if(!empty($data) && isset($data['id']) && $this->cacheOn){
			return $data;
		}
 		$file_path = '/var/www/html/mocks/mutual-funds/' . $id;
		 if(!file_exists($file_path))
				return null;
 		$this->data = json_decode(file_get_contents($file_path), true);
		$this->data['rating'] = 'N/A';
		$this->addFields();
    		MemcacheUtil::setItem($memcache_key , serialize($this->data));
		return $this->data;	
	}

	public function addFields(){
		$calc = new SIPCalculator();
		$calc1 = new CICalculator();
		if(isset($this->data['direct_ret_1y']) && $this->data['direct_ret_1y'] != 'NA'){
			$params['input']['principal'] = 10000;
			$params['input']['rate'] = $this->data['direct_ret_1y']; 
			$params['input']['time'] = 1;
			$response = $calc->response($params);
			$this->data['sip1yr'] = $response['values']['amount']; 
			$params['input']['principal'] = 100000;
			$response1 = $calc1->response($params);
			$this->data['lump1yr'] = $response1['values']['amount']; 
		}
		if(isset($this->data['direct_ret_2y']) && $this->data['direct_ret_2y'] != 'NA'){
			$params['input']['principal'] = 10000;
			$params['input']['rate'] = $this->data['direct_ret_2y'];
			$params['input']['time'] = 2;
			$response = $calc->response($params);
			$this->data['sip2yr'] = $response['values']['amount']; 
			$params['input']['principal'] = 100000;
			$response1 = $calc1->response($params);
			$this->data['lump2yr'] = $response1['values']['amount']; 
		}
		if(isset($this->data['direct_ret_3y']) && $this->data['direct_ret_3y'] != 'NA'){
			$params['input']['principal'] = 10000;
			$params['input']['rate'] = $this->data['direct_ret_3y'];
			$params['input']['time'] = 3;
			$response = $calc->response($params);
			$this->data['sip3yr'] = $response['values']['amount']; 
			$params['input']['principal'] = 100000;
			$response1 = $calc1->response($params);
			$this->data['lump3yr'] = $response1['values']['amount']; 
		}
		if(isset($this->data['direct_ret_5y']) && $this->data['direct_ret_5y'] != 'NA'){
			$params['input']['principal'] = 10000;
			$params['input']['rate'] = $this->data['direct_ret_5y'];
			$params['input']['time'] = 5;
			$response = $calc->response($params);
			$this->data['sip5yr'] = $response['values']['amount']; 
			$params['input']['principal'] = 100000;
			$response1 = $calc1->response($params);
			$this->data['lump5yr'] = $response1['values']['amount']; 
		}

	}

	public function getByFilters($filters, $ln = 'en'){
		$memcache_key = $this->type . '.creditcard.' . serialize($filters) . '.' . $ln;
		$data = MemcacheUtil::getItem($memcache_key);
		if(!empty($data) && $this->cacheOn){
			return $data;
		}
		$parts =  array();
		foreach($filters as $key => $val){
			$txt = $key . ' = ' . "'$val'";
			$parts[] = $txt;
		}
		$condition =  implode(' and ' , $parts);
		$sql = "select url as id from credit_cards where " . $condition . "  order by rating desc";
		$ids = parent::query($sql);
		$cards =  array();
		foreach($ids as $id){
			$card = $this->getByID($id['id'] ,  $ln);
			$cards[] = $card;
		}

    		MemcacheUtil::setItem($memcache_key , $cards);
		return $cards;
	}


	public function getpopular($ln = 'en'){
                $memcache_key = $this->type . '.creditcardpop' . $ln;
                //$data = MemcacheUtil::getItem($memcache_key);
                if(!empty($data) && $this->cacheOn){
                        return $data;
                }
		$ids = array('hdfc-regalia-credit-card', 'hdfc-diners-club-black' , 'hdfc-millennia-credit-card', 'hdfc-moneyback-credit-card', 'icici-sapphiro-credit-card');
                foreach($ids as $id){
                        $card = $this->getByID($id ,  $ln);
                        $cards[] = $card;
                }

                MemcacheUtil::setItem($memcache_key , $cards);
                return $cards;
        }

	public function getByCategory($category, $ln = 'en'){
		$memcache_key = $this->type . '.creditcard.' . $category . '.' . $ln;
		$data = MemcacheUtil::getItem($memcache_key);
		if(!empty($data) && $this->cacheOn){
			return $data;
		}
		$sql = "select url as id from credit_cards where categories like '%$category%' order by rating desc";
		$ids = parent::query($sql);
		$cards =  array();
		foreach($ids as $id){
			$card = $this->getByID($id['id'] ,  $ln);
			$cards[] = $card;
		}

    		MemcacheUtil::setItem($memcache_key , $cards);
		return $cards;
	}
	public function getByCategoryBank($category, $bank, $ln = 'en'){
		$memcache_key = $this->type . '.creditcard.' . $category . '.'.$bank . '-' . $ln;
		$data = MemcacheUtil::getItem($memcache_key);
		if(!empty($data) && $this->cacheOn){
			return $data;
		}
		$sql = "select c.url as id from credit_cards c , banks b  where c.categories like '%$category%' and b.url='$bank' and c.bank_id=b.id order by c.rating desc";
		if(empty($category))
		$sql = "select c.url as id from credit_cards c , banks b  where  b.url='$bank' and c.bank_id=b.id order by c.rating desc";
		$ids = parent::query($sql);
		$cards =  array();
		foreach($ids as $id){
			$card = $this->getByID($id['id'] ,  $ln);
			$cards[] = $card;
		}

    		MemcacheUtil::setItem($memcache_key , $cards);
		return $cards;
	}
	public function getCategory($bank, $ln = 'en'){
		$memcache_key = $this->type . '.creditcardcat.' . $category . '.'.$bank . '-' . $ln;
		$data = MemcacheUtil::getItem($memcache_key);
		if(!empty($data) && $this->cacheOn){
			return $data;
		}
		$sql = "select * from bank_credit_cards  bc ,  bank_credit_cards_translations bct, banks b where b.url='$bank' and b.id=bc.bank_id and bc.id = bct.bank_id and bct.language_code='$ln'";
		$facts = parent::query($sql);
		if(count($facts) == 0){
			return; 
		}
		$fact = $facts[0];
		$data = array();
		foreach($fact as $key=>$val){
				$data[$key] = $val;
		}

    		MemcacheUtil::setItem($memcache_key , $fact);
		return $data;
	}
	public function getBank($bank, $ln = 'en'){
		$memcache_key = $this->type . '.creditcardbank.' . '.'.$bank . '-' . $ln;
		$data = MemcacheUtil::getItem($memcache_key);
		if(!empty($data) && $this->cacheOn){
			return $data;
		}
		$sql = "select * from banks b ,  bank_translations bt where b.url ='$bank' and b.id=bt.bank_id and bt.language_code='$ln'";
		$facts = parent::query($sql);
		if(count($facts) == 0){
			return; 
		}
		$fact = $facts[0];
		$data = array();
		foreach($fact as $key=>$val){
				$data[$key] = $val;
		}

    		MemcacheUtil::setItem($memcache_key , $fact);
		return $data;
	}
	public function getBankByID($bank, $ln = 'en'){
		$memcache_key = $this->type . '.creditcardbankID.' . '.'.$bank . '-' . $ln;
		$data = MemcacheUtil::getItem($memcache_key);
		if(!empty($data) && $this->cacheOn){
			return $data;
		}
		$sql = "select * from banks b ,  bank_translations bt where b.id ='$bank' and b.id=bt.bank_id and bt.language_code='$ln'";
		$facts = parent::query($sql);
		if(count($facts) == 0){
			return; 
		}
		$fact = $facts[0];
		$data = array();
		foreach($fact as $key=>$val){
				$data[$key] = $val;
		}

    		MemcacheUtil::setItem($memcache_key , $fact);
		return $data;
	}
}


