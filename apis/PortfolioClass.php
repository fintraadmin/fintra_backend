<?php
require_once 'utils/utils.php';

require_once 'apis/portfolio_classes/LowRiskPortfolio.php';
require_once 'apis/portfolio_classes/ModerateLowRiskPortfolio.php';
require_once 'apis/portfolio_classes/ModerateRiskPortfolio.php';
require_once 'apis/portfolio_classes/ModerateHighRiskPortfolio.php';
require_once 'apis/portfolio_classes/HighRiskPortfolio.php';
require_once 'apis/portfolio_classes/ELSSPortfolio.php';

class PortfolioClass{

	public function fetchPortfolio($params){
		if(!isset($params['version'])  && ! version_compare($params['version'] , '4.0') >=0)
			Utils::parseParams($params);
	
		error_log("==== prams " . print_r($params , true));	
		$risk = $params['risk'];
		if(isset($risk)){
		switch($risk){
			case 1 :
				$portfolioClass = new LowRiskPortfolio($params);
				return $portfolioClass->getPortfolio();
				break;
			case 2:
				$portfolioClass = new ModerateLowRiskPortfolio($params);
				return $portfolioClass->getPortfolio();
				break;
			case 3 :
				$portfolioClass = new ModerateRiskPortfolio($params);
				return $portfolioClass->getPortfolio();
				break;
			case 4:
				$portfolioClass = new ModerateHighRiskPortfolio($params);
				return $portfolioClass->getPortfolio();
				break;
			case 5 :
				$portfolioClass = new HighRiskPortfolio($params);
				return $portfolioClass->getPortfolio();
				break;

			default:
				$data = file_get_contents('mocks/portfolio.json');
				return $data;

		}
		}
		//ELSS portfolio
		$tax = $params['tax'];
		if(isset($tax)){
				$portfolioClass = new ELSSPortfolio($params);
				return $portfolioClass->getPortfolio();

		}

	}

}


?>
