
<?php
include_once ('apis/algorithms/functions.php');
include_once ('apis/dao/CalculatorDAO.php');

class PPFCalculator extends BaseCalculator{
	var $principal;
	var $rate ;
	var $time;
	var $amount;
	var $interest;

	public function output($params){
		$input  = $params['input'];
		$this->principal = $input['principal'];
		$this->rate = 7.1;
		$this->time = $input['time'];
		$this->frequency = $this->format_frequency($input['frequency']);
		$id = $params['id'];
		$amount = MathFunctions::amount_compound_series($this->principal , $this->rate , $this->time, $this->frequency);
		$this->amount  = $amount;
		$this->investment = $this->principal * $this->time * $this->frequency;
		$this->interest =$this->amount - $this->investment;
		$data = $this->getCalculator($params);
		$output = $data['output'];
		$output = $this->render(array(
					'amount' => $this->INRFormat($this->amount) , 
					'investment' => $this->INRFormat($this->investment) , 
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
		$data = array('labels' => array('Interest' ,  'Total Investment'));
		$datasets['data'] =  array($this->interest , $this->investment);
		$data['datasets'] = $datasets;
		$this->pie = $data;

	}

}
