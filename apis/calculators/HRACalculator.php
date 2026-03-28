
<?php
include_once ('apis/algorithms/functions.php');
include_once ('apis/dao/CalculatorDAO.php');

class HRACalculator extends BaseCalculator{
	var $principal;
	var $rate ;
	var $time;
	var $amount;
	var $interest;

	public function output($params){
		$input  = $params['input'];
		$this->income = $input['income'];  // basic
		$this->income2 = $input['income2'];  // da
		$this->income3 = $input['income3'];  // commission
		$this->income4 = $input['income4'];  // HRA
		$this->income5 = $input['income5'] * 12;  // rent
		$this->ismetro = $this->format_metro($input['metro']);
		$id = $params['id'];
		$data = $this->getCalculator($params);

		$this->time  = $this->calculation();
		$output = $data['output'];
		$output = $this->render(array(	
						'income'=> $this->INRFormat($this->income),
						'income2'=> $this->INRFormat($this->income2),
						'income3'=> $this->INRFormat($this->income3),
						'income4'=> $this->INRFormat($this->income4),
						'income5'=> $this->INRFormat($this->income5),
						'hra1'=> $this->INRFormat($this->hra1),
						'hra2'=> $this->INRFormat($this->hra2),
						'metro' => $this->ismetro ? 'Yes' : 'No' ) , $output);	
		$this->data = $output;

	}

	public function calculation(){

		$a1 = $this->income5  -  0.1 * ($this->income + $this->income2 + $this->income3);
		$a2 = $this->income4;
		if($this->ismetro){
			$a3 = 0.5* ($this->income + $this->income2 + $this->income3);
		}
		else{
			$a3 = 0.4* ($this->income + $this->income2 + $this->income3);
		}
		$this->hra1 = min($a1, $a2 , $a3);
		$this->hra2 = $this->income4 - $this->hra1;
		
	}
	public function chart($params){
		#$this->addPieChart($params);
		#$this->addLineChart($params);
	}

	
	public function addPieChart($params){
		$this->isDoughnutChart = true;
		$datasets = array();
		$data = array('labels' => array('Interest' ,  'Principal'));
		$datasets['data'] =  array($this->interest , $this->principal);
		$data['datasets'] = $datasets;
		$this->pie = $data;

	}

}
