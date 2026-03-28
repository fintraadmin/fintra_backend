<?php
require_once 'DAOBase.php';
require_once 'CalculatorDAO.php';
require_once 'TopicDAO.php';

Class CategoryDAO extends DAOBase {
	private $table = 'categories';
	private $table_trans = 'categories_translations';
	public $fields = array ('category_id'=> 'id'  , 'title' => 'title' , 'image' => 'image' );
	public $type = 'category';

	public function __construct() {
		parent::__construct();
	}
	public function getType(){
		return $this->type;
	}

	public function getByID($id, $ln = 'en') {
		$memcache_key = $this->type . '.' . $id . '.' . $ln;
		$data = MemcacheUtil::getItem($memcache_key);
		if(!empty($data)){
			$this->data = $data;
			$this->transform();
			return $data;
		}
		$sql = "select c.*, ct.* , f.name from categories c ,  categories_translations ct, directus_files f where c.id = ct.category_id and c.url = '$id' and ct.language = '$ln' and f.id = c.featured_image ";
		$topics = parent::query($sql);
		if(count($topics) == 0){
			return; 
		}
		$topic = $topics[0];
		$this->data = array();
		foreach($topic as $key=>$val){
				$this->data[$key] = $val;
		}
		$this->data['uuid'] = $id;
		$this->data['language'] = Utils::$language_keys_rev[$ln];
		$this->data['image'] = $this->image_url($this->data['name']);
		$this->transform();
    		MemcacheUtil::setItem($memcache_key , $this->data);
		return $this->data;	
	}

	public function getByID1($id, $ln = 'en') {
                $memcache_key = $this->type . '.' . $id . '.1.' . $ln;
                $data = MemcacheUtil::getItem($memcache_key);
                if(!empty($data)){
                        $this->data = $data;
                        $this->transform();
                        return $data;
                }
                $sql = "select c.*, ct.* , f.name from categories c ,  categories_translations ct, directus_files f where c.id = ct.category_id and c.id = '$id' and ct.language = '$ln' and f.id = c.featured_image ";
                $topics = parent::query($sql);
                if(count($topics) == 0){
                        return;
                }
                $topic = $topics[0];
                $this->data = array();
                foreach($topic as $key=>$val){
                                $this->data[$key] = $val;
                }
                $this->data['language'] = Utils::$language_keys_rev[$ln];
                $this->data['image'] = $this->image_url($this->data['name']);
                $this->transform();
                MemcacheUtil::setItem($memcache_key , $this->data);
                return $this->data;
        }


	public function getList($ln = 'en'){
		$memcache_key = $this->type . '.all.' . $ln;
		#$data = MemcacheUtil::getItem($memcache_key);
		if(!empty($data)){
			$this->data = $data;
			$this->transform();
			return $data;
		}
		$sql = "select * from categories c ,  categories_translations ct where c.id = ct.category_id  and ct.language = '$ln' ";
		$rows = parent::query($sql);
		$list =  array();
		foreach($rows as  $row){
				$list[] = $this->getByID($row['url'], $ln);
		}
    		MemcacheUtil::setItem($memcache_key , $list);
		return $list;	
	}

	public function getDetails($id , $ln ='en'){
		$memcache_key = $id . '.details.' . $ln;
		#$data = MemcacheUtil::getItem($memcache_key);
		if(!empty($data)){
			return $data;
		}

		$sql = "select t.id from topics t , categories c where  c.url = '$id' and t.category=c.id";
		$rows = parent::query($sql);
		$list =  array();
		$topicDao =  new TopicDAO();
		foreach($rows as  $row){
				$list['topics'][] = $topicDao->getTopicByID($row['id'] , $ln);
		}

		$sql = "select c.id from new_calculator c , categories cc where cc.url = '$id' and  c.category = cc.id";
		$rows1 = parent::query($sql);
		$calDao = new CalculatorDAO();
		foreach($rows1 as  $row){
				$list['calculators'][] = $calDao->getCalculatorByID($row['id'], $ln);
		}
		$list['value'] = $this->getByID($id, $ln);	
    		MemcacheUtil::setItem($memcache_key , $list);
		return $list;	

	}
	
	public function get_url(){
		return SERVER_DNS  . '/' . $this->data['language'] . '/' . $this->data['url']; 		
	}

}


?>
