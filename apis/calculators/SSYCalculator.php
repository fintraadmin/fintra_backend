
<?php
include_once ('apis/algorithms/functions.php');
include_once ('apis/dao/CalculatorDAO.php');

class SSYCalculator extends BaseCalculator{
	var $principal;
	var $rate ;
	var $time;
	var $amount;
	var $interest;

	public function output($params){
		$input  = $params['input'];
		$this->principal = $input['principal'];
		$this->rate = 8;
		$this->age = $input['time'];
		#$this->time = max(15 - $this->age, 0);
		$this->time = 14;
		$this->extra_time = 7;
		$this->frequency = $this->format_frequency($input['frequency']);
		$id = $params['id'];
		$this->amount1 = MathFunctions::amount_compound_series($this->principal , $this->rate , $this->time , $this->frequency);
		$this->amount = MathFunctions::amount_compound_interest($this->amount1 , $this->rate , $this->extra_time);
		$this->investment = $this->principal * $this->time * $this->frequency;
		$this->interest = $this->amount - $this->investment;
		$data = $this->getCalculator($params);
		$output = $data['output'];
		$output = $this->render(array(
					'amount' => $this->INRFormat($this->amount) , 
					'investment' => $this->INRFormat($this->investment) , 
					'interest' => $this->INRFormat($this->interest), 
					'principal' => $this->INRFormat($this->principal) , 
					'time' => $this->age , 
					'total_time' => $this->time +$this->extra_time , 
					'rate'=> $this->rate) , $output);	
		if($this->age > 10){
			$output = 'Age is greater than 10 years.Not eligble for this scheme.';
		}
		$this->data = $output;

	}
	public function chart($params){
		if($this->age <=10)
			$this->addPieChart($params);
		#$this->addLineChart($params);
	}

	
	public function addPieChart($params){
		$this->isDoughnutChart = true;
		$datasets = array();
		$data = array('labels' => array('Interest' ,  'Total Investement'));
		$datasets['data'] =  array($this->interest , $this->investment);
		$data['datasets'] = $datasets;
		$this->pie = $data;

	}

}
