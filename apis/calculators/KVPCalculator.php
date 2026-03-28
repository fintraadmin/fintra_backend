
<?php
include_once ('apis/algorithms/functions.php');
include_once ('apis/dao/CalculatorDAO.php');

class KVPCalculator extends BaseCalculator{
	var $principal;
	var $rate ;
	var $time;
	var $amount;
	var $interest;

	public function output($params){
		$input  = $params['input'];
		$this->principal = $input['principal'];
		$this->rate = 7.5;
		//$this->time = $input['time'];
		$id = $params['id'];
		$this->amount  = $this->principal * 2;
		$this->interest = $this->principal;
		$data = $this->getCalculator($params);
		$output = $data['output'];
		$output = $this->render(array(
					'amount' => $this->INRFormat($this->amount) , 
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
		$data = array('labels' => Utils::translate(array('INTEREST' ,  'TOTAL_INVESTMENT'),$params['language'] ));
		$datasets['data'] =  array($this->interest , $this->principal);
		$data['datasets'] = $datasets;
		$this->pie = $data;

	}

}
