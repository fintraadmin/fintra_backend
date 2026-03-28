<?php

require_once 'PortfolioBase.php';

class ELSSPortfolio extends PortfolioBase{

        public function __construct($params){
                $this->debtPercent = 0;
                $this->equityPercent =  1;
                $this->balancedPercent =  0;
                parent::__construct($params);
        }

	public function getFile(){
		$data = file_get_contents('mocks/elss.json');
	        return $data;	
	}

        public function getPortfolio(){
		 //Check if equity amount can be made zero
                $equityRem = $this->roundUp($this->equityAmount ,  500 , 200); // 

                //Check if balanced can be made zero
                $balancedRem = $this->roundUp($this->balancedAmount , 500 ,  300);

                //Check if debt can be rounded
                $debtRem = $this->roundUp($this->debtAmount , 500 , 300);


                if($debtRem <  0  && $balancedRem > 0){
                        $this->balancedAmount = $this->balancedAmount - $debtRem;
                }
                if($debtRem <=  0  && $balancedRem <= 0){
                        $this->equityAmount = $this->equityAmount - $debtRem;
                        $this->equityAmount = $this->equityAmount - $balancedRem;
                }
                if($this->debtAmount < 500)
                        $this->equityAmount += $this->debtAmount;
                if($this->balancedAmount < 500)
                        $this->equityAmount += $this->balancedAmount;
                if($debtRem < 500)
                        $this->equityAmount += $debtRem;
                if($balancedRem < 500)
                        $this->equityAmount += $balancedRem;
                if($equityRem < 500)
                        $this->equityAmount += $equityRem;
                $this->roundUp($this->debtAmount, 500 , 500);
                $this->roundUp($this->balancedAmount, 500 , 500);
                $this->roundUp($this->equityAmount, 500 , 500);
		return $this->loadFunds();

        }
}

