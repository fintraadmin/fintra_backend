
<?php
include_once ('apis/algorithms/functions.php');
include_once ('apis/dao/CalculatorDAO.php');

class SIPCalculator extends BaseCalculator{
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
		$this->frequency = $this->format_frequency($input['frequency']);
		$id = $params['id'];
		$amount = MathFunctions::amount_compound_series($this->principal , $this->rate , $this->time , $this->frequency);
		$this->amount  = $amount;
		$data = $this->getCalculator($params);
		$this->principal_final = $this->principal * $this->time * $this->frequency;
		$this->interest =$this->amount - $this->principal_final;
		$output = $data['output'];
		$output = $this->render(array('amount' => $this->INRFormat($amount) , 
					      'interest' => $this->INRFormat($this->interest), 
					      'principal_final' => $this->INRFormat($this->principal_final) , 
					      'principal' => $this->INRFormat($this->principal) , 
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
		$data = array('labels' => array('SIP Returns' ,  'Total Investment'));
		$datasets['data'] =  array($this->interest , $this->principal_final);
		$data['datasets'] = $datasets;
		$this->pie = $data;

	}

}
