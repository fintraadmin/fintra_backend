<?php


set_include_path(get_include_path() . PATH_SEPARATOR . '/var/www/html/');

include_once('apis/services/HomepageService.php');


class TestService{

	private $service;

	public function __construct(){
		$this->service = new HomepageService();
	}

	public function get(){
		print_r($this->service->getData(array()));
	}

}
$test =  new TestService();
$test->get();
