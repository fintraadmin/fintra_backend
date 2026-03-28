
<?php
include_once ('apis/algorithms/functions.php');
include_once ('apis/dao/CalculatorDAO.php');

class AffordabilityCalculator extends BaseCalculator{
	var $principal;
	var $rate ;
	var $time;
	var $amount;
	var $interest;

	public function output($params){
		$input  = $params['input'];
		$this->principal = $input['principal'];  // Loan EMI
		$this->principal1 = $input['principal1']; // Initial Amount
		$this->principal2 = $input['principal2']; // Resale Amount
		#$this->principal2 = 0; // Resale Amount
		$this->rate = $input['rate'];
		$this->time = $input['time'];
		$this->frequency =  12;
		$id = $params['id'];
		$data = $this->getCalculator($params);

		$this->amount  = $this->calculation();
		$this->interest = $this->principal * $this->time * $this->frequency - $this->amount;
		$this->price = $this->amount + $this->principal1 + $this->principal2;
		$output = $data['output'];
		$output = $this->render(array(
						'price' => $this->INRFormat($this->price) , 
						'rate' => $this->rate , 
						'time' => $this->INRFormat($this->time) , 
						'principal' => $this->INRFormat($this->principal) , 
						'principal1' => $this->INRFormat($this->principal1) , 
						'principal2' => $this->INRFormat($this->principal2) , 
						'amount'=> $this->INRFormat($this->amount) , 
						'interest' => $this->INRFormat($this->interest)) , $output);	
		$this->data = $output;

	}

	public function calculation(){
		$frequency =  $this->frequency;
		$rate = $this->rate;
		$r = 1 + ($rate/ ($frequency * 100));
		$r1 = $rate/ ($frequency * 100);
		$t = $this->time * $this->frequency;
		$p = $this->principal;
		$a = $p * (pow($r, $t) -1)/($r1* pow($r,$t));
		return ceil($a); 
	}
	public function chart($params){
		$this->addPieChart($params);
		#$this->addLineChart($params);
	}

	
	public function addPieChart($params){
		$this->isDoughnutChart = true;
		$datasets = array();
		$data = array('labels' => array('Loan Interest' ,  'Loan Amount', 'Downpayment'));
		$datasets['data'] =  array($this->interest , $this->amount , $this->principal1);
		$data['datasets'] = $datasets;
		$this->pie = $data;

	}

}
