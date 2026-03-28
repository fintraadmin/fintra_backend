
<?php
include_once ('apis/algorithms/functions.php');
include_once ('apis/dao/CalculatorDAO.php');

class StepSIPCalculator extends BaseCalculator{
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
		$this->step = $input['rate2'];
		$id = $params['id'];
		$data = $this->getCalculator($params);
		$output = $data['output'];
		$this->calculation();
		$output = $this->render(array(
			'principal_final' => $this->INRFormat($this->principal_final) , 
			'amount' =>$this->INRFormat( $this->amount) , 
			'interest' => $this->INRFormat($this->interest), 
			'principal' => $this->principal , 
			'time' => $this->time ,
			'rate2' => $this->step ,
			'frequency' => $this->frequency ,  
			'rate'=> $this->rate) , $output);	
		$this->data = $output;
	}
	public function chart($params){
		$this->addPieChart($params);
		#$this->addLineChart($params);
	}

	public function calculation(){
		$r  =  1 +  $this->rate / ($this->frequency *100);
		$r1 = $this->rate;
		$t = $this->time;
		$t1 = $this->time  * $this->frequency;
		$s = $this->step;
		$p = $this->principal; 
		$f = $this->frequency;
		$pf = ($this->frequency * $this->principal *(pow((1 + ($this->step/100)),$this->time)  -1)  * 100 ) / $this->step;
		$a = $p* $f * 100 * ($r)*(pow((1+$s/100),$t)-pow($r,$t1))*(pow($r,$f)-1)/(((1+$s/100)-pow($r,$f))*$r1) ;
		$this->principal_final = ceil($pf);
		$this->amount = ceil($a);
		$this->interest = $a - $pf;
	}	
	public function addPieChart($params){
		$this->isDoughnutChart = true;
		$datasets = array();
		$data = array('labels' => array('SIP Returns' , 'Total Investment' ));
		$datasets['data'] =  array($this->interest , $this->principal_final );
		$data['datasets'] = $datasets;
		$this->pie = $data;

	}

}
