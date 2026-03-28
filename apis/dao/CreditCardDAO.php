<?php
require_once 'DAOBase.php';

Class CreditCardDAO extends DAOBase {
	private $table = 'credit_cards';
	private $table_trans = 'credit_cards__translations';
	public $fields = array ('url' => 'id' , 'language_code' => 'language' );
	public $fields_mini = array ('url' => 'id' , 'language_code' => 'language' );
	public $type	= 'credit-card';
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
		$sql = "select * from  credit_cards c ,  credit_cards_translations ct where c.id = ct.card_id and c.url = '$id' and ct.language_code = '$ln' ";
		$facts = parent::query($sql);
		if(count($facts) == 0){
			return; 
		}
		$fact = $facts[0];
		$this->data = array();
		foreach($fact as $key=>$val){
				$this->data[$key] = $val;
		}
		$this->data['type'] = $this->type;
		$this->data['stars_full'] = floor($this->data['rating']);
		$this->data['stars_half'] = ceil($this->data['rating']) - floor($this->data['rating']);
		$this->data['stars_empty'] = 5 - ($this->data['stars_full'] + $this->data['stars_half']);
		$this->data['joining_fees_format'] = $this->INRFormat($this->data['joining_fees']);
		$this->data['renewal_fees_format'] = $this->INRFormat($this->data['renewal_fees']);
		$this->data['bank'] = $this->getBankByID($this->data['bank_id'] , $ln);
		$this->data['bank_name'] = $this->data['bank']['title'];
		$this->data['network'] = ucwords($this->data['network']);
		$this->data['key_features'] = array();
		$this->data['key_features'][] = array('title'=> 'Free Lounge Access', 'value'=>true);
		$this->data['key_features'][] = array('title'=> 'Fuel Surcharge Waiver', 'value'=>true);

		$this->transform();
    		MemcacheUtil::setItem($memcache_key , serialize($this->data));
		return $this->data;	
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

	public function getByAffliate($aid, $ln = 'en'){
		$memcache_key = $this->type . '.creditcard.'.$aid .$ln;
		$data = MemcacheUtil::getItem($memcache_key);
		if(!empty($data) && $this->cacheOn){
			return $data;
		}
		
		$sql = "select url as id from credit_cards where findipaylink is not null   order by priority limit 15";
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
	public function getByPopularity( $ln = 'en'){
		$memcache_key = $this->type . '.creditcard.' . $category . '.pop.' . $ln;
		$data = MemcacheUtil::getItem($memcache_key);
		if(!empty($data) && $this->cacheOn){
			return $data;
		}
		$sql = "select url as id from credit_cards order by popularity desc";
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
	/*
	public function updateStatus($leadId, $status) {
    		$conn =	
	}*/

}


