<?php
require_once 'DAOBase.php';

Class SEODAO extends DAOBase {
	private $table = 'seo';
	private $table_trans = 'seo_translations';
	public $type	= 'seo';

	public function __construct() {
		parent::__construct();
	}
	public function getType(){
		return $this->type;
	}
	
	private function getID($pattern){
		$parts = explode('/' , $pattern);
		$seq = [];
		foreach($parts as $part){
			if($part != '*' && $part != '')
				$seq[] = $part;
		}	
		$regex= implode('%' , $seq);
	}

	public function getByPattern($pattern, $ln = 'en') {
		if(empty($pattern))
			return;
		$memcache_key = $this->type . '.' . $pattern . '.' . $ln;
		$data = unserialize(MemcacheUtil::getItem($memcache_key));
		if(!empty($data) && isset($data['pattern'])){
			return $data;
		}
		$sql = "select * from  seo s ,  seo_translations st where s.id = st.seo_id and s.url = '$pattern' and st.language_code = '$ln' ";
		$facts = parent::query($sql);
		if(count($facts) == 0){
			return; 
		}
		$fact = $facts[0];
		$this->data = array();
		foreach($fact as $key=>$val){
				$this->data[$key] = $val;
		}
    		MemcacheUtil::setItem($memcache_key , serialize($this->data));
		return $this->data;	
	}

	public function substitute($pattern , $seo_data_fields){
	
		if(empty($seo_data_fields))
			return $seo_data_fields;	
		foreach($seo_data_fields as $seo_key=>$template){
			foreach($pattern as $key=>$value){
				$find = '{{' . $key . '}}';
				$template = str_replace($find , $value, $template);
			}
			$seo_data_fields[$seo_key] = $template;
		}
		return $seo_data_fields;
	}
}


?>
