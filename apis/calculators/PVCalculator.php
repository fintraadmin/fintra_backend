
<?php
include_once ('apis/algorithms/functions.php');
include_once ('apis/dao/CalculatorDAO.php');

class PVCalculator extends BaseCalculator{
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
		$this->amount = MathFunctions::pv($this->principal , $this->rate , $this->time);
		$data = $this->getCalculator($params);
		$output = $data['output'];
		$output = $this->render(array(
					'amount' => $this->INRFormat($this->amount) ,  
					'principal' => $this->INRFormat($this->principal) , 
					'time' => $this->time , 
					'rate'=> $this->rate) , $output);	
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
