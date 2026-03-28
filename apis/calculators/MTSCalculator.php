
<?php
include_once ('apis/algorithms/functions.php');
include_once ('apis/dao/CalculatorDAO.php');

class MTSCalculator extends BaseCalculator{
	var $principal;
	var $rate ;
	var $time;
	var $amount;
	var $interest;

	public function output($params){
		$input  = $params['input'];
		$this->income = $input['income'];  // basic
		$this->age = $this->format_age($input['age']);
		$this->isresident = $this->format_age($input['resident']);
		$id = $params['id'];
		$data = $this->getCalculator($params);

		$this->time  = $this->calculation();
		$output = $data['output'];
		$output = $this->render(array(	
						'income'=> $this->INRFormat($this->income),
						'tax'=> $this->INRFormat($this->tax),
						't1'=> $this->INRFormat($this->t1),
						't2'=> $this->INRFormat($this->t2),
						't3'=> $this->INRFormat($this->t3),
						't4'=> $this->INRFormat($this->t4),
						't5'=> $this->INRFormat($this->t5),
						't6'=> $this->INRFormat($this->t6),
						't7'=> $this->INRFormat($this->t7),
						't8'=> $this->INRFormat($this->t8),
						't9'=> $this->INRFormat($this->t9),
						't10'=> $this->INRFormat($this->t10),
						'i1'=> $this->INRFormat($this->i1),
						'i2'=> $this->INRFormat($this->i2),
						'i3'=> $this->INRFormat($this->i3),
						'i4'=> $this->INRFormat($this->i4),
						'i5'=> $this->INRFormat($this->i5),
						'i6'=> $this->INRFormat($this->i6),
						'i7'=> $this->INRFormat($this->i7),
						'i8'=> $this->INRFormat($this->i8),
						'i9'=> $this->INRFormat($this->i9),
						'i10'=> $this->INRFormat($this->i10),
						'resident' => $this->isresident ? 'Yes' : 'No' ) , $output);	
		$this->data = $output;

	}
	public function format_age($data){
		if(isset($data['key'])){
			$val = $data['key'];
			if($val == 'mid')
				return '1';
			if($val == 'midfielder')
				return '2';

		}
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
		$slab3 = 1000000;
		$slab4 = 5000000;
		$t1 = 0.05;
		$t2 = 0.2;
		$t3 = 0.3;
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
		else if($this->income <= $slab4){
			$tax = $t2 * ($slab3 - $slab2) + $t1 * ($slab2 -$slab1) + $t3 * ($this->income - $slab3);
		}
		//add surcharge;
		$this->tax = $tax;
		$this->maxVals();
	}
	public function maxVals(){
		$this->t1 = 31200;	
		$this->t2 = 31200;	
		$this->t3 = 31200;	
		$this->t4 = 31200;	
		$this->t5 = 31200;	
		$this->t6 = 31200;	
		$this->t7 = 31200;	
		$this->t8 = 31200;	
		$this->t9 = 31200;	
		$this->t10 = 31200;	
		$this->i1 = 150000;	
		$this->i2 = 150000;	
		$this->i3 = 150000;	
		$this->i4 = 150000;	
		$this->i5 = 150000;	
		$this->i6 = 150000;	
		$this->i7 = 150000;	
		$this->i8 = 150000;	
		$this->i9 = 150000;	
		$this->i10 = 150000;	
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
