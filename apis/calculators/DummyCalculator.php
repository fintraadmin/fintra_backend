
<?php
include_once ('apis/algorithms/functions.php');
include_once ('apis/dao/CalculatorDAO.php');

class DummyCalculator extends BaseCalculator{
	var $principal;
	var $rate ;
	var $time;
	var $amount;
	var $interest;

	public function output($params){
		$input  = $params['input'];
		$id = $params['id'];
		$DAO =  new CalculatorDAO();
		$data = $DAO->getCalculatorByID($id);
		$output = $data['output'];
		$output = $this->render(array('amount' => $amount , 'interest' => $this->interest, 'principal' => $this->principal , 'time' => $this->time , 'rate'=> $this->rate) , $output);	
		$this->data = $output;

	}
	public function chart($params){
	}

}
