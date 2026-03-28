
<?php
include_once ('apis/algorithms/functions.php');
include_once ('apis/dao/CalculatorDAO.php');

class DreamCalculator extends BaseCalculator{
	var $principal;
	var $rate ;
	var $time;
	var $amount;
	var $interest;

	public function output($params){
		$input  = $params['input'];
		$this->principal = $input['principal'];  // Price of asset
		$this->principal1 = $input['principal1']; // Monthly Amount
		$this->principal2 = $input['principal2']; // Initial Saving
		$this->rate = $input['rate'];
		$id = $params['id'];

		$data = $this->getCalculator($params);
		$this->time  = $this->calculation();
		$output = $data['output'];
		$output = $this->render(array(
						'principal' => $this->INRFormat($this->principal),
						'principal1' => $this->INRFormat($this->principal1),
						'principal2' => $this->INRFormat($this->principal2),
						'rate' => $this->rate,
						'time' => $this->timeFormat($this->time) ) , $output);	
		$this->data = $output;

	}

	public function calculation(){
		$frequency =  12;
		$rate = $this->rate;
		$eff_rate = $rate/ ($frequency * 100);
		$factor = ($this->principal + ($this->principal1/$eff_rate) )/( ($this->principal1/$eff_rate) + $this->principal2);
		$time = log($factor) / ( log($eff_rate +1));
		return $time/12; 
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
