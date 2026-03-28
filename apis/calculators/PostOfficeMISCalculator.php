
<?php
include_once ('apis/algorithms/functions.php');
include_once ('apis/dao/CalculatorDAO.php');

class PostOfficeMISCalculator extends BaseCalculator{
	var $principal;
	var $rate ;
	var $time;
	var $amount;

	public function output($params){
		$input  = $params['input'];
		$this->principal = $input['principal'];
		$this->time = 5;
		$this->rate = 7.4;
		$id = $params['id'];
		$this->total_interest = MathFunctions::simple_interest($this->principal , $this->rate , $this->time); 
		$this->monthly_interest = ceil($this->total_interest / (12 * $this->time)); 
		$data = $this->getCalculator($params);
		$output = $data['output'];
		$output = $this->render(array(
				'amount' => $this->INRFormat($this->total_interest) , 
				'interest' => $this->INRFormat($this->monthly_interest), 
				'principal' => $this->INRFormat($this->principal) , 
				'time' => $this->time , 
				'rate'=> $this->rate) , $output);	
		$this->table($params);
		$this->data = $output;
	}

	public function chart($params){
		$this->addPieChart($params);
		#$this->addLineChart($params);
	}

	public function addPieChart($params){
		$this->isDoughnutChart = true;
		$datasets = array();
		$data = array('labels' => array('Interest Earned' ,  'Deposit Amount'));
		$datasets['data'] =  array($this->total_interest , $this->principal);
		$data['datasets'] = $datasets;
		$this->pie = $data;

	}

	public function addLineChart($params){
		$this->isLineChart = true;
		$datasets = array();
		$series1 = array();
		$series2 = array();
		for($i=1; $i <= $this->time ; $i++){
                        $row =  array();
                        $year = $i;
			$datasets['labels'][] = $year;
                        $principal = $this->principal * $i;
                        $amount = MathFunctions::amount_compound_interest($principal , $this->rate , $year);
			$series1[] = $principal;
			$series2[] = $amount;
                }
		$datasets['series1']['label'] = 'Principal';
		$datasets['series2']['label'] = 'Principal';
		$datasets['series1']['data'] = $series1;
		$datasets['series2']['data'] = $series2;
		$this->line = $datasets;
	}

	public function table($params){
		$this->isTable = true;
		$columns =  array ('Year' , 'Principal' , 'Amount');
		$rows = array();
		for($i=1; $i <= $this->time ; $i++){
			$row =  array();
			$year = $i;
			$principal = $this->principal * $i;
			$amount = MathFunctions::amount_compound_interest($principal , $this->rate , $year);
			$row =  array('year' => "$year" , 'principal' => $this->INRFormat($principal) ,'amount'=> $this->INRFormat($amount));
			$rows[] =$row;
		}
		$this->cols = $columns;
		$this->rows = $rows;
	}


}
