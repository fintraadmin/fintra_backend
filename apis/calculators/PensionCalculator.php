
<?php
include_once ('apis/algorithms/functions.php');
include_once ('apis/dao/CalculatorDAO.php');

class PensionCalculator extends BaseCalculator{
	var $principal;
	var $time;

	public function output($params){
		$input  = $params['input'];
		$this->principal = $input['principal'];
		$this->time = $input['time'];
		$this->stepup = 10;
		$this->rate =  12;
		$this->rate1 =  7.5;
		$this->rate2 =  9.5;
		$id = $params['id'];
		$this->inflation = 5;
		$this->inflation1 = 2;
		$this->time1 =  25; // 25 years post retirment
		$this->frequency = 12; //working at monthyl level
		$p1 = MathFunctions::amount_compound_interest($this->principal , $this->inflation , $this->time);
		$p2 = MathFunctions::amount_compound_series($p1 , $this->inflation1 , $this->time1 , $this->frequency);
		$this->amount = $p2 ;
		$data = $this->getCalculator($params);
		$output = $data['output'];
		$this->sip = $this->calculation($this->rate);
		$this->fd = $this->calculation($this->rate1);
		$this->mix = $this->calculation($this->rate2);
		$output = $this->render(array(
			'amount' =>$this->INRFormat( $this->amount) , 
			'principal' =>$this->INRFormat( $this->principal) , 
			'time' => $this->time ,
			'time1' => $this->time1 ,
			'rate' => $this->rate ,
			'rate1' => $this->rate1 ,
			'rate2' => $this->rate2 ,
			'inflation' => $this->inflation ,
			'inflation1' => $this->inflation1 ,
			'fd'=> $this->INRFormat($this->fd),
			'mix'=> $this->INRFormat($this->mix),
			'sip'=> $this->INRFormat($this->sip)
			) , $output);	
		$this->data = $output;
	}
	public function chart($params){
		$this->addPieChart($params);
		#$this->addLineChart($params);
	}

	public function calculation($r){
		$i1 = $r;
		$i2 = $this->time;
		$i3 = $this->principal;
		$i4 = $this->stepup;
		$i5 = $this->amount;
		$f1 =  1200;
		$v1 = 1 + $i1/$f1;
		$v2 =  1 + $i4/100;
		$v3 = $v2 / pow($v1 , 12);
		$v4 = 12 *  ($i2 - 1);
		$v5 = pow($v1, $v4);
		$v6 = $v5 *  (pow($v3 , $i2) -1);
		$v7 = $v3 -1;
		$v8 = $v6 / $v7;
		$v9 = pow($v1, 12) -1;
		$v10 = $v8 * $v9;
		$v11 = ($v1 * $i5 - $i5)/$v1;
		$v12 = $v11/ $v10;
		return $v12; 

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
