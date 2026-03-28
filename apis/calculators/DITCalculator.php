
<?php
include_once ('apis/algorithms/functions.php');
include_once ('apis/dao/CalculatorDAO.php');

class DITCalculator extends BaseCalculator{
	var $principal;
	var $rate ;
	var $time;
	var $amount;

	public function output($params){
		$input  = $params['input'];
		$this->principal = $input['principal'];
		$this->rate = $input['rate'];
		//$this->time = $input['time'];
		$id = $params['id'];
		$this->amount1  = 2* $this->principal;
		$this->amount2  = 3* $this->principal;
		$this->amount3  = 5* $this->principal;
		$time1 = log(2)/ log(1 + $this->rate/100);
		$time2 = log(3)/ log(1 + $this->rate/100);
		$time3 = log(5)/ log(1 + $this->rate/100);
		$data = $this->getCalculator($params);
		$output = $data['output'];
		$output = $this->render(array(
				'amount1' => $this->INRFormat($this->amount1), 
				'amount2' => $this->INRFormat($this->amount2), 
				'amount3' => $this->INRFormat($this->amount3), 
				'interest' => $interest, 
				'principal' => $this->INRFormat($this->principal) , 
				'time1' => $this->timeFormat($time1) , 
				'time2' => $this->timeFormat($time2) , 
				'time3' => $this->timeFormat($time3) , 
				'rate'=> $this->rate) , $output);	
		$this->table($params);
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
		$datasets['data'] =  array($this->principal , $this->amount);
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
