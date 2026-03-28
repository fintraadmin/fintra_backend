<?php
require_once 'DAOBase.php';

Class MutualFundDAO extends DAOBase {
	private $table = 'facts';
	private $table_trans = 'facts_translations';
	public $fields = array ('scheme_name' => 'id' , 'language' => 'language' , 'scheme_name'=> 'title' ,  'sub_category' => 'description' );
	public $type	= 'mutual_fund';

	public function __construct() {
		parent::__construct();
	}
	public function getType(){
		return $this->type;
	}

	public function getFactByID($id, $ln = 'en') {
		$sql = "select * , 'en' as language from mutual_funds_data where scheme_name= '$id' ";
		error_log("sql $sql ");
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
		return $this->data;	
	}

	public function getFactsByTopic($topicid, $ln = 'en'){
		$sql = "select id from facts where topic= '$topicid'";
		$fact_ids = parent::query($sql);
		$facts =  array();
		foreach($fact_ids as $fid){
			$fact = $this->getFactByID($fid['id'] ,  $ln);
			$facts[] = $fact;
		}

		return $facts;
	}

}


?>
