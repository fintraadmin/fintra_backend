<?php

abstract class PortfolioBase{
	
	var $debtPercent = 1;
	var $balancedPercent = 0;
	var $equityPercent = 0;
	var $debtAmount;
	var $balancedAmount;
	var $equityAmount;
	var $totalAmount;
	var $type;
	var $years;
	var $risk;
	var $tax;
	var $dAmount;

	var $maxEquity=1;
	var $maxDebt=1;
	var $maxBalanced=1;
	var $equityAmountAdjusted;
	var $debtAmountAdjusted;
	var $balancedAmountAdjusted;

	public function __construct($params){
		$this->totalAmount = $params['amount'];
		$this->type = $params['type'];
		$this->years = $params['time'];
		$this->risk = $params['risk'];
		$this->tax = $params['tax'];
                $this->debtAmount = $this->debtPercent * $this->totalAmount;
                $this->balancedAmount = $this->balancedPercent * $this->totalAmount;
                $this->equityAmount = $this->equityPercent * $this->totalAmount;
		$this->app_version = isset($params['version']) ? $params['version'] : '2.0.0' ;	
	}
	
	public function printAmounts(){
		$this->loadFunds();
	}

	public function deductAmt(){
	}

	public function decreaseAmounts(){
		$extra = 0;
		$amt = $this->debtAmount + $this->balancedAmount + $this->equityAmount;
		if($amt > $this->totalAmount){
			$extra = $amt - $this->totalAmount;
		}
		$this->dAmount = $extra;
		$this->deductAmt();
	}
	public function roundUp(&$amount , $step , $diff){
                $orig_amount = $amount;
                $d = floor($amount / $step);
                $rem = $amount % $step;
                if($rem <= $diff){  // Round Down
                        $amount = $d * $step;
                }
                if($rem > $diff){
                        $amount = ($d + 1) * $step;
                }

                return $orig_amount - $amount; //This will be added or substracted
 	}

	public function getFile(){
		if(isset($this->risk)){
			$file = $this->risk . '.csv.out';
		}
		if(isset($this->tax)){
			$file = 'elss.json';
		}
		$data = file_get_contents('mocks/' . $file);
		return $data;
	}

	public function filterbyYear($funds){
		$time = isset($this->years) ? (int)$this->years :  -100;
		$filtered = array();
		foreach($funds as $f){
			if($f['year_min'] == -1 && $f['year_max'] == -1){
				$filtered[] =$f;
			}
			if($f['year_min'] <= $time  && $time <= $f['year_max']){
				$filtered[] =$f;
			}
		}
		return $filtered;
	}

	public function decideMutipleCategories(){
		$type = $this->type;

		if(isset($this->tax) && ($this->equityAmount > 5000) && $type == 'sip')
			$this->maxEquity = 2;
		if(isset($this->tax) && ($this->equityAmount > 10000) && $type == 'lumpsum')
			$this->maxEquity = 2;
		if( ($this->risk ==5 || $this->risk ==4) &&  ($this->equityAmount > 5000) && $type == 'sip')
			$this->maxEquity = 2;
		if( ($this->risk ==1 || $this->risk ==2) &&  ($this->debtAmount > 5000) && $type == 'sip')
			$this->maxDebt = 2;
		if( $this->risk == 3  &&  ($this->debtAmount > 5000) && $type == 'sip')
			$this->maxBalanced = 2;

		if( ($this->risk ==5 || $this->risk ==4) &&  ($this->equityAmount > 50000) && $type == 'lumpsum')
			$this->maxEquity = 2;
		if( ($this->risk ==1 || $this->risk ==2) &&  ($this->debtAmount > 50000) && $type == 'lumpsum')
			$this->maxDebt = 2;
		if( $this->risk == 3  &&  ($this->debtAmount > 50000) && $type == 'lumpsum')
			$this->maxBalanced = 2;
		
	}

	public function filterFundsByCategory($funds){
		$seen_cg= array();
		$seen = array();
		$filtered = array();
		foreach($funds  as $f){
			$f_cat = $f['category'];
			if($f_cat == "equity"){
				$limit = $this->maxEquity;
			}
			if($f_cat == "debt"){
				$limit = $this->maxDebt;
			}
			if($f_cat == "balanced"){
				$limit = $this->maxBalanced;
			}
			$f_grp = $f['group'];
			$key = $f_cat . '-' . $f_grp;
			if(count($seen_cg[$f_cat]) < $limit){
				if(!in_array($key , $seen_cg[$f_cat])){
					$seen_cg[$f_cat][] = $key;
				}
			}
			if(in_array($key, $seen_cg[$f_cat])){
				$filtered[] = $f;
			}
		}

		if(count($seen_cg["equity"]) > 1){
			$cat1 = $seen_cg["equity"][0];
			$cat2 = $seen_cg["equity"][1];
			$amount1 = $this->equityAmountAdjusted[0];
			$amount2 = $this->equityAmountAdjusted[1];
		}
		if(count($seen_cg["debt"]) > 1){
			$cat1 = $seen_cg["debt"][0];
			$cat2 = $seen_cg["debt"][1];
			$amount1 = $this->debtAmountAdjusted[0];
			$amount2 = $this->debtAmountAdjusted[1];
		}
		if(count($seen_cg["balanced"]) > 1){
			$cat1 = $seen_cg["balanced"][0];
			$cat2 = $seen_cg["balanced"][1];
			$amount1 = $this->balancedAmountAdjusted[0];
			$amount2 = $this->balancedAmountAdjusted[1];
		}
		if(isset($cat1) && isset($cat2)){
			foreach($filtered as &$f){
				if($f["category"] . '-' . $f['group'] == $cat1){
					$f['amount'] = $amount1;
				}
				if($f["category"] . '-' . $f['group'] == $cat2){
					$f['amount'] = $amount2;
				}

			}
		}
		return $filtered;
	}

	public function selectFundinGroup(&$funds){
		$old_group = -1;
		foreach($funds as &$f){
			$new_group = $f['group'];
			if($old_group != $new_group){
				$f["selected"] =  true;
				$old_group = $new_group;
			}
		}
	}

	public function decorateData(&$funds){
		foreach($funds as &$f){
			$f['category'] = ucwords($f['category']);
			$years = array('1' ,'2' , '3' , '5' ,'7', '10');
			foreach($years as $y){
				if(isset($f['return_'. $y]))
					unset($f['return_' . $y]);
			}
		}
		
	}
	public function updateReturns(&$funds){
		if(version_compare($this->app_version  , '2.0.2') === -1)
			return;
                $duration = $this->years;
                if($duration <=1){
                        $window = '1';
                }
                else if($duration <= 2){
                        $window = '2';
                }
                else if($duration <= 3){
                        $window = '3';
                }
                else if($duration <= 5){
                        $window = '5';
                }
                else if($duration <= 7){
                        $window = '7';
                }
                else if($duration <= 10){
                        $window = '10';
                }                 
                else {
                        $window = '10';
                }
                foreach($funds as &$fund){
                        if(isset($fund['return_' . $window])){
                                $window_returns = $fund['return_' . $window];
                                $fund['return'] = $window_returns; // override the returns
				$fund['year'] = $window;
                        }                         
                }                                 
        }                  
	public function adjustAmountSplits(){
		$this->equityAmountAdjusted = array();
		$this->debtAmountAdjusted = array();
		$this->balancedAmountAdjusted = array();
		if($this->maxEquity > 1){
			$mod = ($this->equityAmount)%1000;
			$split = ($this->equityAmount - $mod) / 2;
			$this->equityAmountAdjusted[0] = $split + $mod;
			$this->equityAmountAdjusted[1] = $split;
		} 
		if($this->maxDebt > 1){
			$mod = ($this->debtAmount)%1000;
			$split = ($this->debtAmount - $mod) / 2;
			$this->debtAmountAdjusted[0] = $split + $mod;
			$this->debtAmountAdjusted[1] = $split;
		} 
		if($this->maxBalanced > 1){
			$mod = ($this->balancedAmount)%1000;
			$split = ($this->balancedAmount - $mod) / 2;
			$this->balancedAmountAdjusted[0] = $split + $mod;
			$this->balancedAmountAdjusted[1] = $split;
		} 
	}

	public function loadFunds(){
		$this->decreaseAmounts();
		$this->decideMutipleCategories();
		$this->adjustAmountSplits();
		$data = $this->getFile();
		$funds_data =  json_decode($data , true);
		$filtered_funds = $this->filterbyYear($funds_data);
		//$funds = $funds_data["options"];
		$funds = $filtered_funds;
		$new_funds =  array();
		foreach($funds as $f){
		 if($f["category"] == "equity"){
			$f["amount"] = $this->equityAmount;
			if($this->equityAmount > 0)
				$new_funds[] = $f;
		 }
		 if($f["category"] == "debt" || $f["category"] == "gilt"){
			$f["amount"] = $this->debtAmount;
			if($this->debtAmount > 0)
				$new_funds[] = $f;
		 }
		 if($f["category"] == "balanced"){
			$f["amount"] = $this->balancedAmount;
			if($this->balancedAmount > 0)
				$new_funds[] = $f;
		}
		}
		$this->selectFundinGroup($new_funds);
		$data =  array();
		$ff = $this->filterFundsByCategory($new_funds);
		$this->updateReturns($ff);
		$this->decorateData($ff);
		$data["options"] = $ff;
		return $data;
	}


}


