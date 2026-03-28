
<?php
require_once 'utils/utils.php';


class RiskClass{

  public function getQuestions($params){
		Utils::parseParams($params);
		$language = $params['language'];
		
		if($language == 'hi'){
			$data = file_get_contents('mocks/risk_hi.json'); 
		}
		else if($language == 'gu'){
			$data = file_get_contents('mocks/risk_gu.json'); 
		}
		else{
			$data = file_get_contents('mocks/risk.json'); 
		}
		return json_decode($data, true);
  }


}

?>
