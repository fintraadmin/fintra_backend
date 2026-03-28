<?php
require_once 'content_classes/blog.class.php';

class  ContentClass{
	public function listBlogs($params){
		$bc = new BlogClass();
		return $bc->listBlogs($params);
	}	

	public function getDetails($params){
		$type = $params['type'];

		switch($type){

		case 'blog':
			$bc = new BlogClass();
			$b = $bc->getBlog($params);
			$b['perma_link']= 'https://fintra.co.in' .$b['perma_link'] ;
			return $b;
		default:
			return array();
		}

	}
}

?>
