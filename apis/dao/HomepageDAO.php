<?php
require_once 'DAOBase.php';
require_once 'SectionDAO.php';

Class HomepageDAO extends DAOBase {
	private $table = 'sections';
	private $table_trans = 'sections_translations';
	public $fields = array ('language' , 'id' , 'config' );
	public $type = 'home';	

	public function __construct() {
		parent::__construct();
	}
	public function getType(){
		return $this->type;
	}

	public function getHomepage($ln = 'en') {
		$sql = "select * from homepage where language='$ln'";
		$rows = parent::query($sql);
		if(count($rows) == 0){
			return; 
		}
		$row = $rows[0];
		$homepage_data = array();
		foreach($row as $key=>$val){
			if(in_array($key , $this->fields )){
				$homepage_data[$key] = $val;
			}
		}
		$homepage_id = $homepage_data['id'];
		$sections = $this->getHomepageSections($homepage_id , $ln); 
		return array(
				'sections'=>$sections,
				'config' => $homepage_data['config']
			);
	}

	public function getHomepageSections($id, $ln = 'en'){
		#$sql = "select section_id from homepage_section_mappings where homepage_id='$id'";
		$sql = "select h.section_id from homepage_section_mappings h, sections s where s.id=h.section_id  and  homepage_id='$id' order by s.position"; 
		$section_ids = parent::query($sql);
		$sections = array();
		$sectionDAO =  new SectionDAO();
		$section_data = array();
		foreach($section_ids as $sid){
			$data = $sectionDAO->getSectionDetails($sid['section_id'] , $ln);
			$section_data[] = $data;
		}
		return $section_data;
	}

}


?>
