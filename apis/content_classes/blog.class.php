<?php
require_once 'vendor/autoload.php';
require_once 'utils/utils.php';

class BlogClass{

	public function listBlogs($params){
		$urls = array(
			'things-investors-should-know-before-they-buy-mutual-funds',
			'things-to-remember-before-investing-hi',
			'tax-saving-guide-80C',
			'post-office-schemes-india-2018'
		);
		$blogs = CMSUtils::getBlogsbyIDs($urls);
		$filtered_b = array();
		$fields = array('title' , 'perma_link' , 'image');
		foreach($blogs as  $b){
			$new_b =  array();
			foreach($fields as $f)	
				$new_b[$f] = $b[$f];
			$new_b['type']  = 'blog';
			$filtered_b[] = $new_b;
		}

		return $filtered_b;
	}

	public function getBlog($params){
		$perma_link = $params['perma_link'];
		$parts = explode("/", $perma_link);
		$url = $parts[count($parts) -1];
		$blog = CMSUtils::getBlog($url);
		return $blog;
		
	}
	public function getID($url){
		$parts =  explode("/", $url);
		$id = $parts[count($parts) -1];
		return $id;
	}
	public function view($params){
		CMSUtils::addViews($this->getID($params['itemid']));
	}
	public function like($params){
		CMSUtils::addLikes($this->getID($params['itemid']));
	}


}
