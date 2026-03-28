<?php
require_once 'DAOBase.php';

Class TopicDAO extends DAOBase {
	private $table = 'topics';
	private $table_trans = 'topics_translations';
	public $fields = array ('topic_id'=> 'id' , 'language_code' => 'language' , 'title' => 'title' , 'image' => 'image' , 'seo_title' => 'seo_title' , 'seo_description' => 'seo_description' , 'category' => 'category');
	public $type = 'topic';

	public function __construct() {
		parent::__construct();
	}
	public function getType(){
		return $this->type;
	}

	public function getTopicByID($id, $ln = 'en') {
		$memcache_key = $this->type . '.' . $id . '.' . $ln;
		#$data = MemcacheUtil::getItem($memcache_key);
		if(!empty($data)){
			$this->data = $data;
			$this->transform();
			return $data;
		}
		$sql = "select * from topics t ,  topics_translations tt where t.id = tt.topic_id and t.id = '$id' and tt.language_code = '$ln' ";
		$topics = parent::query($sql);
		if(count($topics) == 0){
			return; 
		}
		$topic = $topics[0];
		$this->data = array();
		foreach($topic as $key=>$val){
			if(in_array($key , array_keys($this->fields) )){
				$this->data[$key] = $val;
			}
		}
		$this->transform();
    		MemcacheUtil::setItem($memcache_key , $this->data);
		return $this->data;	
	}

	public function getTopicDetails($topicid, $ln = 'en'){
	}

}


?>
