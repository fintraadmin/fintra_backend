<?php

Class MathFunctions{



	public static function simple_interest($principal , $rate , $time)
	{
		return round(($principal * $rate *  $time )/ 100, 2);

	}

	public static function compound_interest($principal , $rate , $time, $frequency=1){

		return round($principal * pow(1 + $rate/(100*$frequency), $time * $frequency) -$principal, 2) ;
	}

	public static function pv($principal , $rate , $time , $frequency=1){
		$r = 1 + $rate / 100;
		error_log("====== $principal , $rate , $time ");	
		return round($principal / pow($r , $time), 2) ;

	}
	public static function amount_simple_interest($principal , $rate , $time){
	
		return $principal + MathFunctions::simple_interest($principal , $rate , $time);
	}
	public static function amount_compound_interest($principal , $rate , $time , $frequency = 1){
	
		return  $principal + MathFunctions::compound_interest($principal , $rate , $time, $frequency);
	}

	public static function amount_compound_series($principal , $rate , $time , $frequency =1){
		$effective_rate =  1 + ($rate / ($frequency *100));
		$effective_time = $time *$frequency;
		$amount = (pow($effective_rate, $effective_time) - 1) / ($effective_rate -1) * $effective_rate * $principal;
		return ceil($amount);  
	}

	public static function annualized_return($initial, $final , $time){
		$tr = (($final - $initial) *100)/$initial;
		$annualized = (pow((1+$tr/100), 1/$time)-1)*100;
		return round($annualized,2 );
	}
	public static function total_return($initial, $final , $time){
		$tr = (($final - $initial) *100)/$initial;
		return round($tr ,2);			
	}
}

#var_dump(MathFunctions::amount_compound_interest(100,6,6));
