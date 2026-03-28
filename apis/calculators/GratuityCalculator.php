
<?php
include_once ('apis/algorithms/functions.php');
include_once ('apis/dao/CalculatorDAO.php');

class GratuityCalculator extends BaseCalculator{
	var $principal;
	var $rate ;
	var $time;
	var $amount;
	var $interest;

	public function output($params){
		$input  = $params['input'];
		$this->principal = $input['principal'];
		$this->principal1 = $input['principal1'];
		$this->time = $input['time'];
		$id = $params['id'];
		$data = $this->getCalculator($params);
		$output = $data['output'];
		if($this->time >= 5){
			$amount = ($this->principal + $this->principal1) * 15 * $this->time / 26;
			$amount1 = ($this->principal + $this->principal1) * 15 * $this->time / 30;
			$amount = floor($amount);
		}
		else{
			$amount = 0;
			$amount1 = 0;
		}
		$output = $this->render(array(
						'salary' => $this->INRFormat($this->principal + $this->principal1),
						'amount1' => $this->INRFormat($amount1),
						'time' => $this->time,
						'amount' => $this->INRFormat($amount) ) , $output);	
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
