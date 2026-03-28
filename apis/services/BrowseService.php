<?php
include_once 'apis/dao/SectionDAO.php';
include_once 'SectionService.php';


class BrowseService {

	private $sections;


	public function getData($params){
		global $global_data;
		#$global_data['response_type'] = 'mini';
		$global_data['source'] = 'home';
	 
		$sectionDAO = new SectionDAO();
		$id = $params['id'];
		$ln = $params['language'];
		$data = $sectionDAO->getSectionDetails($id, $ln, -1);
		$formattted_data = $this->format($data);
		return $formattted_data;	
	}
	
	public function format($data){
		$sections = array();
		$sectionSrv = new SectionService();
		$sectionSrv->format($data);
		$sections[] = $sectionSrv->response();
		
		return $sections;
	}
}
