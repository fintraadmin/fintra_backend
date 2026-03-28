<?php
require_once 'DAOBase.php';

Class CryptoDAO extends DAOBase {
	public $type	= 'cryptocurrencies';
	public $mini_fields = array('id' , 'url' , 'title');
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
		if(!empty($data) && isset($data['id'])){
			return $data;
		}
		$sql = "select * from Cryptocurrency t ,  cryptocurrency_translations tt where t.id = tt.crypto_id and t.url = '$id' and tt.language = '$ln' ";
		$facts = parent::query($sql);
		if(count($facts) == 0){
			return; 
		}
		$fact = $facts[0];
		$fact['id'] = $fact['url'];
		$this->data = $fact;
		$this->transform();
    		MemcacheUtil::setItem($memcache_key , serialize($this->data));
		return $this->data;	
	}

	public function getN($n, $ln = 'en'){
		$memcache_key = $this->type . '.n.' . $n . '.' . $ln;
		$data = MemcacheUtil::getItem($memcache_key);
		if(!empty($data)){
			return $data;
		}
		$n = min($n, 5);
		$sql = "select url from Cryptocurrency limit $n";
		$fact_ids = parent::query($sql);
		$facts =  array();
		foreach($fact_ids as $fid){
			$fact = $this->getByID($fid['url'] ,  $ln);
			foreach($fact as $k=>$v){
				if(!in_array($k, $this->mini_fields)){
					unset($fact[$k]);	
				}
			}
			$facts[] = $fact;
		}

		
    		MemcacheUtil::setItem($memcache_key , $facts);
		return $facts;
	}

}


?>
