
<?php
include_once ('apis/algorithms/functions.php');
include_once ('apis/dao/CalculatorDAO.php');

class SICalculator extends BaseCalculator{
	var $principal;
	var $rate ;
	var $time;
	var $amount;
	var $interest;

	public function output($params){
		$input  = $params['input'];
		$this->principal = $input['principal'];
		$this->rate = $input['rate'];
		$this->time = $input['time'];
		$id = $params['id'];
		$amount = MathFunctions::amount_simple_interest($this->principal , $this->rate , $this->time);
		$this->amount  = $amount;
		$this->interest =MathFunctions::simple_interest($this->principal , $this->rate , $this->time);
		$data = $this->getCalculator($params);
		$output = $data['output'];
		$output = $this->render(array(
						'amount' =>$this->INRFormat($amount) , 
						'interest' => $this->INRFormat($this->interest), 
						'principal' => $this->INRFormat($this->principal) , 
						'time' => $this->time , 
						'rate'=> $this->rate) , $output);	
		$this->data = $output;

	}
	public function chart($params){
		$this->addPieChart($params);
		#$this->addLineChart($params);
	}

	
	public function addPieChart($params){
		$this->isDoughnutChart = true;
		$datasets = array();
		$data = array('labels' => array(Utils::translate('INTEREST', $params['language']) ,  Utils::translate('PRINCIPAL' , $params['language'])));
		$datasets['data'] =  array($this->interest , $this->principal);
		$data['datasets'] = $datasets;
		$this->pie = $data;

	}

}
