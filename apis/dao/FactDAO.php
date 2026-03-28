<?php
require_once 'DAOBase.php';

Class FactDAO extends DAOBase {
	private $table = 'facts';
	private $table_trans = 'facts_translations';
	public $fields = array ('fact_id' => 'id' , 'language_code' => 'language' , 'title'=> 'title' , 'description'=> 'description' , 'seo_description' => 'seo_description' ,  'seo_title' => 'seo_title' , 'topic' => 'topic');
	public $fields_mini = array ('fact_id' => 'id' , 'language_code' => 'language' , 'title'=> 'title' , 'description'=> null , 'seo_description' =>  null );
	public $type	= 'fact';

	public function __construct() {
		parent::__construct();
	}
	public function getType(){
		return $this->type;
	}

	public function getFactByID($id, $ln = 'en') {
		if(empty($id))
			return;
		$memcache_key = $this->type . '.' . $id . '.' . $ln;
		#$data = unserialize(MemcacheUtil::getItem($memcache_key));
		if(!empty($data) && isset($data['id'])){
			return $data;
		}
		$sql = "select * from facts f ,  facts_translations ft where f.id = ft.fact_id and f.id = '$id' and ft.language_code = '$ln' ";
		$facts = parent::query($sql);
		if(count($facts) == 0){
			return; 
		}
		$fact = $facts[0];
		$this->data = array();
		foreach($fact as $key=>$val){
			if(in_array($key , array_keys($this->fields) )){
				$this->data[$key] = $val;
			}
		}
		$this->transform();
		$this->data['description'] = $this->changeURLs($this->data['description']);
    		MemcacheUtil::setItem($memcache_key , serialize($this->data));
		return $this->data;	
	}

	public function getFactsByTopic($topicid, $ln = 'en'){
		$memcache_key = $this->type . '.topic.' . $topicid . '.' . $ln;
		$data = MemcacheUtil::getItem($memcache_key);
		if(!empty($data)){
			return $data;
		}
		$sql = "select id from facts where topic= '$topicid' order by position";
		$fact_ids = parent::query($sql);
		$facts =  array();
		foreach($fact_ids as $fid){
			$fact = $this->getFactByID($fid['id'] ,  $ln);
			$facts[] = $fact;
		}

    		MemcacheUtil::setItem($memcache_key , $facts);
		return $facts;
	}

}


?>
