
<?php
include_once ('apis/algorithms/functions.php');
include_once ('apis/dao/CalculatorDAO.php');

class PostOfficeTDCalculator extends BaseCalculator{
	var $principal;
	var $rate ;
	var $time;
	var $amount;

	public function output($params){
		$input  = $params['input'];
		$this->principal = $input['principal'];
		$this->format_t_r($input['time']);
		$id = $params['id'];
		$this->amount = MathFunctions::amount_compound_interest($this->principal , $this->rate , $this->time ,3);
		$this->interest = MathFunctions::compound_interest($this->principal , $this->rate , $this->time, 3);
		$data = $this->getCalculator($params);
		$output = $data['output'];
		$output = $this->render(array(
				'amount' => $this->INRFormat($this->amount) , 
				'interest' => $this->INRFormat($this->interest), 
				'principal' => $this->INRFormat($this->principal) , 
				'time' => $this->time , 
				'rate'=> $this->rate) , $output);	
		$this->table($params);
		$this->data = $output;
	}
	public function format_t_r($data){
		//if(isset($data['key']))
			{
			//$val = $data['key'];
			$val = $data;
			if($val == 'low'){
				$this->time = 1;
				$this->rate = 6.9;
			}
			if($val == 'mid'){
				$this->time = 2;
				$this->rate = 7;
			}
			if($val == 'midfielder'){
				$this->time = 3;
				$this->rate = 7;
			}
			if($val == 'winger'){
				$this->time = 5;
				$this->rate = 7.5;
			}
		}
		return 1;
	}

	public function chart($params){
		$this->addPieChart($params);
		#$this->addLineChart($params);
	}

	public function addPieChart($params){
		$this->isDoughnutChart = true;
		$datasets = array();
		$data = array('labels' => array('Interest Earned' ,  'Deposit Amount'));
		$datasets['data'] =  array($this->interest , $this->principal);
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
