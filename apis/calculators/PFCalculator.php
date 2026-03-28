
<?php
include_once ('apis/algorithms/functions.php');
include_once ('apis/dao/CalculatorDAO.php');

class PFCalculator extends BaseCalculator{
	var $principal;
	var $rate ;
	var $time;
	var $amount;
	var $interest;

	public function output($params){
		$input  = $params['input'];
		$this->principal = $input['principal'];
		$this->principal1 = $input['principal1'];
		$this->rate = 8.5;
		$contribution = $input['contribution'];
		$this->rate1 = $input['rate1'];
		$this->rate2 = $input['rate2'];
		$this->rate2 = 12;
		if($contribution['key']=='min'){
			$monthly =  1800;
		}
		else{
			$monthly = ($this->principal + $this->principal1)* ($this->rate1 + $this->rate2) /100 ;
		}
		$this->time = $input['time'];
		$this->frequency = 12;
		$id = $params['id'];
		$employee_monthly = ($this->principal + $this->principal1) * $this->rate1  / 100;
		$employer_monthly = ($this->principal + $this->principal1) * $this->rate1 / 100;
		$monthly = $employee_monthly + $employer_monthly;
		$amount = MathFunctions::amount_compound_series($monthly , $this->rate , $this->time, $this->frequency);
		$amount1 = MathFunctions::amount_compound_series($employee_monthly , $this->rate , $this->time, $this->frequency);
		$amount2 = MathFunctions::amount_compound_series($employer_monthly , $this->rate , $this->time, $this->frequency);
		$investment = $monthly * 12 * $this->time;
		$this->employee_contribution = $employee_monthly * 12 * $this->time;
		$this->employer_contribution = $employer_monthly * 12 * $this->time;
		$this->investment = $investment;
		$this->interest = $amount - $investment;
		$data = $this->getCalculator($params);
		$output = $data['output'];
		$output = $this->render(array(
					'amount' => $this->INRFormat($amount) , 
					'amount1' => $this->INRFormat($amount1) , 
					'amount2' => $this->INRFormat($amount2) , 
					'interest' => $this->INRFormat($this->interest), 
					'total' => $this->INRFormat($investment) , 
					'employee_total' => $this->INRFormat($this->employee_contribution) , 
					'employer_total' => $this->INRFormat($this->employer_contribution) ,
					'principal' => $this->INRFormat($this->principal) ,
					'principal1' => $this->INRFormat($this->principal1) ,
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
		$data = array('labels' => Utils::translate(array('INTEREST','EMPLOYEE_CONTRIBUTION','EMPLOYER_CONTRIBUTION') , $params['language']));
		$datasets['data'] =  array($this->interest , $this->employee_contribution , $this->employer_contribution);
		$data['datasets'] = $datasets;
		$this->pie = $data;

	}

}
