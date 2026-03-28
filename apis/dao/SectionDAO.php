<?php
require_once 'DAOBase.php';
require_once 'TopicDAO.php';
require_once 'CalculatorDAO.php';

Class SectionDAO extends DAOBase {
	private $table = 'sections';
	private $table_trans = 'sections_translations';
	public $fields = array ('section_id' => 'id' , 'language_code' => 'language' , 'title' => 'title', 'sid' => 'sid' , 'alignment' => 'alignment', 'content_type' => 'section_type');
	public $type = 'section';

	public function __construct() {
		parent::__construct();
	}
	public function getType(){
		return $this->type;
	}

	public function getSectionByID($id, $ln = 'en') {
		$memcache_key = $this->type . '.' . $id . '.' . $ln;
		#$data = MemcacheUtil::getItem($memcache_key);
		if(!empty($data)){
			return $data;
		}
		$sql = "select *, tt.id  as sid from sections t ,  sections_translations tt where t.id = tt.section_id and t.id = '$id' and tt.language_code = '$ln' ";
		$sections = parent::query($sql);
		if(count($sections) == 0){
			return; 
		}
		$section = $sections[0];
		$this->data = array();
		foreach($section as $key=>$val){
			if(in_array($key , array_keys($this->fields) )){
				$this->data[$key] = $val;
			}
		}
		$this->transform();
    		MemcacheUtil::setItem($memcache_key , $this->data);
		return $this->data;	
	}

	public function getSectionDetails($sectionid, $ln = 'en', $limit=5){

		$section_data = $this->getSectionByID($sectionid , $ln);
		$sec_id = $section_data['sid'];
		$limit_str = '';
		if($limit > 0)
			$limit_str = " limit ". $limit;
		$sec_type = $section_data['section_type'];
		if($sec_type == 'topic'){
			//$sql = "select topic_id from topic_section_mappings where section_id='$sec_id'";
			$sql = "select tsm.topic_id from topic_section_mappings tsm , topics t where tsm.topic_id = t.id and section_id='$sec_id' order by t.position" . $limit_str;
			$topic_ids = parent::query($sql);
			$topics = array();
			$topicDAO =  new TopicDAO();
			foreach($topic_ids as $tid){
				$topic_data = $topicDAO->getTopicByID($tid['topic_id'] , $ln);
				$topics[] = $topic_data;
			}
			$section_data['data'] = $topics;
		}
		else if ($sec_type == 'calculator'){
			#$sql = "select calculator_id from calculator_section_mapping where section_id='$sec_id'" . $limit_str;
			$sql = "select csm.calculator_id from calculator_section_mapping csm , new_calculator c where csm.calculator_id = c.id and section_id='$sec_id' order by c.position " . $limit_str;
			$cal_ids = parent::query($sql);
			$cals = array();
			$calDAO =  new CalculatorDAO();
			foreach($cal_ids as $cid){
				$cal_data = $calDAO->getCalculatorByID($cid['calculator_id'] , $ln);
				$cals[] = $cal_data;
			}
			$section_data['data'] = $cals;

		}
		return $section_data;
	}

}


?>
