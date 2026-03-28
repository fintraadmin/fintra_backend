
<?php
include_once ('apis/algorithms/functions.php');
include_once ('apis/dao/CalculatorDAO.php');

class TRCalculator extends BaseCalculator{
	var $principal;
	var $rate ;
	var $time;
	var $amount;
	var $interest;

	public function output($params){
		$input  = $params['input'];
		$this->principal1 = $input['principal1'];
		$this->principal2 = $input['principal2'];
		$this->time = $input['time'];
		$id = $params['id'];
		$this->rate = MathFunctions::total_return($this->principal1 ,$this->principal2, $this->time);
		$data = $this->getCalculator($params);
		$this->amount = $this->principal2 - $this->principal1;
		$output = $data['output'];
		$output = $this->render(array('amount' => $this->INRFormat($this->amount) , 'rate' => $this->rate ) , $output);	
		$this->data = $output;

	}
	public function chart($params){
		#$this->addPieChart($params);
		#$this->addLineChart($params);
	}

	
	public function addPieChart($params){
		$this->isDoughnutChart = true;
		$datasets = array();
		$data = array('labels' => array('Interest' ,  'Principal'));
		$datasets['data'] =  array($this->interest , $this->principal);
		$data['datasets'] = $datasets;
		$this->pie = $data;

	}

}
