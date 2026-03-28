<?php
include_once 'apis/calculators/BaseCalculator.php';
#include_once 'apis/calculators/FDCalculator.php';
spl_autoload_register(function($className) {
	include_once 'apis/calculators/'.$className . '.php';
});
 
class CalculatorService {

	function get($params){
		switch($params['id']){

			case "1":
			case "10":
			case "fd-calculator" :
				$calc = new FDCalculator();
				break;

			case "2" :
				$calc = new SICalculator();
				break; 
			case "3" :
				$calc = new CICalculator();
				break; 
			case "4" :
				$calc = new ARCalculator();
				break; 
			case "5" :
				$calc = new ARCalculator();
				break; 
			case "7" :
				$calc = new SIPCalculator();
				break; 
			case "8" :
				$calc = new StepSIPCalculator();
				break; 
			case "15" :
				$calc = new PostOfficeRDCalculator();
				break; 
			case "16" :
				$calc = new PostOfficeTDCalculator();
				break; 
			case "17" :
				$calc = new PostOfficeMISCalculator();
				break; 
			case "39" :
				$calc = new PostOfficeSSCCalculator();
				break; 
			case "40" :
				$calc = new NSCCalculator();
				break; 

			case "19" :
				$calc = new PPFCalculator();
				break; 
			case "25" :
			case "26" :
			case "27" :
			case "28" :
				$calc = new DreamCalculator();
				break; 
			case "29" :
			case "30" :
			case "31" :
			case "32" :
				$calc = new AffordabilityCalculator();
				break; 
			case "33" :
			case "23" :
			case "34" :
			case "35" :
			case "36" :
			case "41" :
				$calc = new EMICalculator();
				break; 

			case "9":
			case "rd" :
				$calc = new RDCalculator();
				break; 
			case "11" :
				$calc = new BankSavingCalculator();
				break; 
			case "12" :
				$calc = new DITCalculator();
				break; 
			case "13" :
				$calc = new FVCalculator();
				break; 
			case "86" :
				$calc = new PensionCalculator();
				break; 
			case "14" :
				$calc = new PVCalculator();
				break; 
			case "18" :
				$calc = new GratuityCalculator();
				break; 
			case "19" :
				$calc = new PPFCalculator();
				break; 
			case "20" :
			case "42" :
				$calc = new PFCalculator();
				break; 
			case "21" :
				$calc = new KVPCalculator();
				break; 
			case "22" :
				$calc = new SSYCalculator();
				break; 
			case "46" :
				$calc = new HRACalculator();
				break; 
			case "43" :
				$calc = new MTSCalculator();
				break; 
			case "44" :
				$calc = new ITCalculator();
				break; 
			case "51" :
				$calc = new IT1Calculator();
				break; 




			default:
				$calc = new DummyCalculator();
				break;
		}
			return $calc;
	}

}
