<?php
include_once 'apis/dao/HomepageDAO.php';
include_once 'SectionService.php';


class HomepageService {

	private $sections;


	public function getData($params){
		global $global_data;
		//$global_data['response_type'] = 'mini';
		$global_data['source'] = 'home';
		$homeDAO = new HomepageDAO();
		$ln = $params['language']; 
		if(empty($ln)){
			$ln = 'en';
		}
		$data = $homeDAO->getHomepage($ln);
		$formattted_sections = $this->format($data['sections']);
		$config = json_decode($data['config'], true);
		return array('config' => $config , 'sections' => $formattted_sections);	
		#return $formattted_sections;
	}
	
	public function format($data){
		$sections = array();
		$sectionSrv = new SectionService();
		foreach($data as $d){
			$sectionSrv->format($d);
			$sections[] = $sectionSrv->response();
		}

		return $sections;
	}
}
