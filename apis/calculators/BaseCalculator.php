<?php
include_once ('apis/dao/CalculatorDAO.php');


abstract class BaseCalculator{
	public  $isTable = false;
	public  $isDoughnutChart = false;
	public  $isLineChart = false;
	public  $rows = array ();
	public  $cols = array();
	public  $pie = array();
	public  $line = array();
	public  $data = '';
	public  $values = array();

	public function INRFormat($amount){
		setlocale(LC_MONETARY, 'en_IN');
		return money_format('%!.0n', floor($amount));
	}

	public function getCalculator($params){
		$id = $params['id'];
		$ln = $params['language'];
		if(empty($ln))
			$ln = 'en';
		$this->id = $id;
		$DAO =  new CalculatorDAO();
		$data = $DAO->getCalculatorByID($id,$ln);
		$this->likes = $data['total_like'];
		$this->views = $data['total_view'];
		$this->url = $data['url'];
		$this->output1 = $data['output1'];
		if(isset($data['button_text']))
			$this->button_text = $data['button_text'];
		return $data;
	}
	public function timeFormat($time){
		$years = floor($time);
		$months = ceil(($time - $years) *12);
		if($months == 12){
			$months = 0;
			$years +=1;
		}
		if($years == 0){
			if($months == 1)
				$format_time = $months . ' month';
			else
				$format_time = $months . ' months';
				
		}
		else{
			$year_str = 'years';
			if($years == 1)
				$year_str = 'year';
			$months_str = 'months';
			if($months == 1)
				$months_str = 'month';
			if($months > 0)
				$format_time = $years . ' ' . $year_str . ' ' .  $months . ' ' . $months_str;
			else
				$format_time = $years . ' ' . $year_str ;
		}
		return $format_time;
	}

	public function render($pattern , $template){
		$this->values = $pattern;

		foreach($pattern as $key=>$value){
			$find = '{{' . $key . '}}';
			$template = str_replace($find , $value, $template);
		}

		return $template;
	}

	public function chartColorCodes(){
		if($this->isDoughnutChart){
			$forground =  array('#ca0400' , '#00C6CA'   , '#2AC700');
			$background =  array('#f4cccc' , '#ccf3f4'  , '#B2FAA0');
			$num = count($this->pie['labels']);

			$this->pie['colors'] = array_slice($forground, 0, $num);
			$this->pie['colors1'] = array_slice($background, 0, $num);
		}
	}

	abstract public function output($params);

	abstract public function chart($params);

	#public function table($params);
	public function format_metro($data){
		if(isset($data['key'])){
			$val = $data['key'];
			if($val == 'low')
				return true;

		}
		return false;
	}
	public function format_frequency($data){
		if(isset($data['key']))
			$freq = $data['key'];
		else
			$freq = $data;
		if($freq){
			$val = $freq;
			if($val == 'low')
				return 12;
			if($val == 'mid')
				return 4;
			if($val == 'midfielder')
				return 2;
			if($val == 'winger')
				return 1;
		}
		return 12;
	}
	public function enrich($data){
		$r =  str_replace("cms.fintra.co.in/tables/facts","fintra.co.in/fact",$data);
		$r1=  str_replace("cms.fintra.co.in/tables/new_calculator","fintra.co.in/calculator",$r);
		$r2=  str_replace("cms.fintra.co.in/tables/topics","fintra.co.in/topic",$r1);
		return $r2;
	}

	public function actions(){
		$actions = array();
		$actions[]= array('text'=> 'Apply Now' ,  'link' => 'https://fintra.co.in/hindi/leads?ref=' . $this->id);
		$actions[]= array('text'=> 'Ask Us' ,  'link' => 'https://fintra.co.in/hindi/leads?ref=' . $this->id);
		return $actions;
	}
	public function response($params){
		$this->output($params);
		$this->chart($params);
		$this->chartColorCodes();
		$response = array(

		'isDoughnutChart' => $this->isDoughnutChart,
		'isLineChart' => $this->isLineChart,
		'isTable' => $this->isTable,
		'data' => $this->enrich($this->data),
		'rows' => $this->rows,
		'cols' => $this->cols,
		'pie' => $this->pie,
		'line' => $this->line,
		'likes' => $this->likes,
		'views' => $this->views,
		'type' => 'calculator',
		'values' => $this->values,
		'id' => $this->id,
		'url' => $this->url
		//'output1' => $this->enrich($this->output1)
		);
		$response['actions'] = $this->actions();
		if(isset($this->button_text)){
			$response['button_text'] = $this->button_text;
			$response['button_url'] = 'https://fintra.co.in/calculator/49';
		}
		
		return $response;
	}

}
