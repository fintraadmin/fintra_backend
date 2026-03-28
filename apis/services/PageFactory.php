<?php

class PageFactory {
	var $page;


	public function getPage($params){

		switch($params['type']){


			case 'stock':
				$page =  new StockPage($params);

			default:
				$page =  new 404Page();

		}
		return $page;

	}


}
