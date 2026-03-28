
<?php
include_once ('apis/algorithms/functions.php');
include_once ('apis/dao/CalculatorDAO.php');

class BankSavingCalculator extends BaseCalculator{
	var $principal;
	var $rate ;
	var $time;
	var $amount;
	var $interest;

	public function output($params){
		$input  = $params['input'];
		$this->principal = $input['principal']; //lumpsum
		$this->principal1 = $input['principal1']; // periodic
		$this->rate = $input['rate'];
		$this->time = $input['time'];
		$this->frequency = $this->format_frequency($input['frequency']);
		$id = $params['id'];
		$amount1 = MathFunctions::amount_compound_series($this->principal , $this->rate , $this->time , $this->frequency);
		$amount2 = MathFunctions::amount_compound_interest($this->principal1 , $this->rate , $this->time , $this->frequency);
		$this->amount  = $amount1 + $amount2;
		$amount = $this->INRFormat($this->amount);
		$this->principal_final = $this->principal * $this->time * $this->frequency + $this->principal1;
		$this->interest =$this->amount - $this->principal_final;
		$data = $this->getCalculator($params);
		$output = $data['output'];
		$output = $this->render(array(	'amount' => $amount , 
						'interest' => $this->INRFormat($this->interest), 
						'principal' => $this->INRFormat($this->principal) , 
						'principal1' => $this->INRFormat($this->principal1) , 
					        'principal_final' => $this->INRFormat($this->principal_final) , 
						'time' => $this->time , 
					        'frequency' => $this->frequency , 
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
		$data = array('labels' => array('Interest Earned' ,  'Total Investment'));
		$datasets['data'] =  array($this->interest , $this->principal_final);
		$data['datasets'] = $datasets;
		$this->pie = $data;

	}

}
