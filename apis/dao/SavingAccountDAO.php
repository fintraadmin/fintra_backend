<?php
require_once 'DAOBase.php';

Class SavingAccountDAO extends DAOBase {
	public $type	= 'saving-account';
	public $mini_fields = array('id' , 'url' , 'title');

	public function __construct() {
		parent::__construct();
	}
	public function getType(){
		return $this->type;
	}

	public function getByID($id, $ln = 'en') {
		$sql = "select * from saving_account l, saving_account_translations lt where l.id=lt.account_id and l.url='$id' and lt.language_code='$ln'"; 
		error_log("sql $sql ");

		$facts = parent::query($sql);
		if(count($facts) == 0){
			return; 
		}
		$fact = $facts[0];
		$fact['id'] = $fact['url'];
		$this->data = array();
		foreach($fact as $key=>$val){
				$this->data[$key] = $val;
		}
		$this->transform();
		return $this->data;	
	}


	public function getN($n, $ln = 'en'){
                $memcache_key = $this->type . '.n.' . $n . '.' . $ln;
                $data = MemcacheUtil::getItem($memcache_key);
                if(!empty($data)){
                        return $data;
                }
                $n = min($n, 5);
                $sql = "select url from saving_account limit $n";
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
