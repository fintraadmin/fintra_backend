<?php

require_once 'PortfolioBase.php';

class ModerateLowRiskPortfolio extends PortfolioBase{

        public function __construct($params){
                $this->debtPercent = 0.63;
                $this->equityPercent =  0.17;
                $this->balancedPercent =  0.20;
                parent::__construct($params);
        }

	public function deductAmt(){
		$this->debtAmount -= $this->dAmount;
	}

        public function getPortfolio(){
        	//Check if equity amount can be made zero
                $equityRem = $this->roundUp($this->equityAmount ,  500 , 300); // 

                //Check if balanced can be made zero
                $balancedRem = $this->roundUp($this->balancedAmount , 500 ,  300);

                //Check if debt can be rounded
                $debtRem = $this->roundUp($this->debtAmount , 500 , 200);

		$leftOver = $equityRem + $balancedRem + $debtRem;
		if($leftOver == 0 ){
			$equityRem = $debtRem = $balancedRem = 0;
		}

                if($equityRem <  0){
                        $this->balancedAmount = $this->balancedAmount - $equityRem;
			$equityRem  = 0;
                }
                if($balancedRem < 0){
                        $this->debtAmount = $this->debtAmount - $balancedRem;
			$balancedRem = 0;
                }
                if($debtRem <0){
                        $this->balancedAmount = $this->balancedAmount - $debtRem;
			$debtRem = 0;
                }

		if($this->debtAmount > 0)
			$this->debtAmount += $leftOver;
                
		if($this->equityAmount < 500)
                        $this->debtAmount += $this->equityAmount;
                if($this->balancedAmount < 500)
                        $this->debtAmount += $this->balancedAmount;

                $this->roundUp($this->debtAmount, 500 , 500);
                $this->roundUp($this->balancedAmount, 500 , 500);
                $this->roundUp($this->equityAmount, 500 , 500);
		return $this->loadFunds();

	}
}

