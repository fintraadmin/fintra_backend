<?php
require_once 'DAOBase.php';

Class LoanDAO extends DAOBase {
	public $type	= 'loans';
	public $mini_fields = array('id' , 'url' , 'title');

	public function __construct() {
		parent::__construct();
	}
	public function getType(){
		return $this->type;
	}

	public function getByID($id, $ln = 'en') {
		$sql = "select * from loans l, loans_translations lt where l.id=lt.loan_id and l.url='$id' and lt.language_code='$ln'"; 
		error_log("sql $sql ");

		$facts = parent::query($sql);
		if(count($facts) == 0){
			return; 
		}
		$fact = $facts[0];
		$fact['id'] = $fact['url'];
		$fact['sub_type'] = $fact['type'];
		$this->data = array();
		foreach($fact as $key=>$val){
				$this->data[$key] = $val;
		}
		$this->transform();
		return $this->data;	
	}

	public function getN($n,$type=null, $ln = 'en'){
                $memcache_key = $this->type . '.n.' . $n . '.'.$type . $ln;
                #$data = MemcacheUtil::getItem($memcache_key);
                if(!empty($data)){
                        return $data;
                }
                $n = min($n, 5);
                $sql = "select url from loans where type='$type' limit $n";
                if(is_null($type))
			$sql = "select url from loans  ORDER BY RAND() limit $n";
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
	public function getByAffliate($aid, $ln = 'en'){
		$memcache_key = $this->type . '.loan.'.$aid .$ln;
		$data = MemcacheUtil::getItem($memcache_key);
		if(!empty($data) && $this->cacheOn){
			return $data;
		}
		
		$sql = "select url as id from loans where affliate_link is not null limit 10";
		$ids = parent::query($sql);
		$cards =  array();
		foreach($ids as $id){
			$card = $this->getByID($id['id'] ,  $ln);
			$cards[] = $card;
		}

    		MemcacheUtil::setItem($memcache_key , $cards);
		return $cards;
	}


}


?>
