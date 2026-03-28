
<?php
include_once ('apis/algorithms/functions.php');
include_once ('apis/dao/CalculatorDAO.php');

class ITCalculator extends BaseCalculator{
	var $principal;
	var $rate ;
	var $time;
	var $amount;
	var $interest;

	public function output($params){
		$input  = $params['input'];
		$this->income = $input['income'];  // basic
		$this->age = $this->format_age($input['age']);
		$this->isresident = $this->format_resident($input['resident']);
		$id = $params['id'];
		$data = $this->getCalculator($params);

		$this->time  = $this->calculation();
		$output = $data['output'];
		$output = $this->render(array(	
						'income'=> $this->INRFormat($this->income),
						'tax'=> $this->INRFormat($this->tax),
						'age'=> $this->agegroup,
						'cess'=> $this->INRFormat($this->cess),
						'surcharge'=> $this->INRFormat($this->surcharge),
						'totaltax'=> $this->INRFormat($this->totaltax),
						'taxpercent' => $this->taxpercent,
						'resident' => $this->isresident ? 'Resident' : 'Non-Resident' ) , $output);	
		$this->data = $output;

	}
	public function format_age($data){
		if(isset($data['key'])){
			$val = $data['key'];
			if($val == 'mid'){
				$this->agegroup = '60 - 80';
				return '1';
			}
			if($val == 'midfielder'){
				$this->agegroup = '80+';
				return '2';
			}

		}
		$this->agegroup = '0-60';
		return '0'; //0 - till 60 , 1 -  60-80 , 2 80+
	}
	public function format_resident($data){
		if(isset($data['key'])){
			$val = $data['key'];
			if($val == 'low')
				return true;

		}
		return false;
	}

	public function calculation(){
		$slab1 = 250000;
		$slab2 = 500000;
		$relief = 500000;
		$slab3 = 1000000;
		$slab4 = 5000000;
		$cess_percent = 0.04;
		$t1 = 0.05;
		$t2 = 0.2;
		$t3 = 0.3;
		$tax = 0;
		if($this->age == '1'){
			$slab1 = 300000;
		}
		if($this->income <= $slab1){
			$tax = 0;
		}
		else if($this->income <= $slab2){
			$tax = $t1 * ($this->income - $slab1);
		}
		else if($this->income <= $slab3){
			$tax = $t1 * ($slab2 - $slab1) + $t2 * ($this->income - $slab2);
		}
		else if($this->income > $slab3){
			$tax = $t2 * ($slab3 - $slab2) + $t1 * ($slab2 -$slab1) + $t3 * ($this->income - $slab3);
		}
		//add surcharge;
		if($this->income <= $relief){
			$tax = 0;
		}
		//cess
		$cess = $tax * $cess_percent;
		
		$tt = $tax + $cess;
		//surcharge
		if($this->income >= 5130000 && $this->income <= 10000000){
			$surcharge = 0.1 * $tt;
		}
		if($this->income >= 10000000 && $this->income <= 20000000){
			$surcharge = 0.15 * $tt;
		}
		if($this->income >= 20000000 && $this->income <= 50000000){
			$surcharge = 0.25 * $tt;
		}
		if($this->income >  50000000){
			$surcharge = 0.37 * $tt;
		}
		$this->tax = $tax;
		$this->cess = $cess;
		$this->surcharge = $surcharge;
		$this->totaltax = $surcharge + $cess + $tax;
		$this->taxpercent = round($this->totaltax / $this->income *100 ,2);
		
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
