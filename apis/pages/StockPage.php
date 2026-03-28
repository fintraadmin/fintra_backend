<?php


class StockPage implements BasePage{
	var $data;

	public function __construct($params){
		$dao  = new StockDAO();
		$data = $dao->getByID($params['id']  , $params['ln']);	
	}


	public function getWidgets(){
		//heading
		$widget =  new Widget();
		$widget->setType = 'p';
		$widget->setValues = 
		
	}
}

?>
