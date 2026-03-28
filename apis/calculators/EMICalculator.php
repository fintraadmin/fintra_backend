
<?php
include_once ('apis/algorithms/functions.php');
include_once ('apis/dao/CalculatorDAO.php');

class EMICalculator extends BaseCalculator{
	var $principal;
	var $rate ;
	var $time;
	var $amount;
	var $interest;

	public function output($params){
		$input  = $params['input'];
		$this->principal = $input['principal'];  // Price of asset
		$this->rate = $input['rate'];
		$this->time = $input['time'];
		$this->frequency = 12;
		$id = $params['id'];
		$data = $this->getCalculator($params);		
		$this->emi  = $this->calculation();
		$this->interest = $this->emi * $this->frequency * $this->time  - $this->principal;
		$output = $data['output'];
		$output = $this->render(array(
						'time' => $this->time,
						'rate' => $this->rate,
						'emi' => $this->INRFormat(ceil($this->emi)) , 'principal' => $this->INRFormat($this->principal) , 'interest'=> $this->INRFormat(ceil($this->interest)) , 'amount'=> $this->INRFormat(ceil($this->interest + $this->principal))) , $output);	
		$this->data = $output;

	}

	public function calculation(){
		$frequency =  $this->frequency;
		$rate = $this->rate;
		$eff_rate = $rate/ ($frequency * 100);
		$eff_time = $this->time * $frequency;
		$emi = ($this->principal * $eff_rate * pow(1 + $eff_rate,$eff_time))/ (pow(1+$eff_rate, $eff_time) -1);
		return $emi; 
	}
	public function chart($params){
		$this->addPieChart($params);
		#$this->addLineChart($params);
	}

	
	public function addPieChart($params){
		$this->isDoughnutChart = true;
		$datasets = array();
		$data = array('labels' => array('Interest' ,  'Loan Amount'));
		$datasets['data'] =  array($this->interest , $this->principal);
		$data['datasets'] = $datasets;
		$this->pie = $data;

	}

}
