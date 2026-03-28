<?php
set_include_path(get_include_path() . PATH_SEPARATOR . '/var/www/html/');
require_once 'utils/dbutils.php';


class SiteMap {

	public function __construct() {
    		$this->conn = DBUtils::getConn('fintracms') ;
	}

	 public function query($sql){
                $STH = $this->conn->query($sql);
                $STH->setFetchMode(PDO::FETCH_ASSOC);

                $results = array();
                while($row = $STH->fetch()) {
                        $results[] = $row;
                }
                return $results;
        }
	public function geturl($id, $type){
		$url1 = 'https://fintra.co.in/english/' . $type . '/' . $id;
		$url2 = 'https://fintra.co.in/hindi/' . $type . '/' . $id;
		//$url3 = 'https://fintra.co.in/' . $type . '/' . $id;
		$url = array();
		//$url['x-default'] = $url3;  
		$url['en'] = $url1;  
		$url['hi'] = $url2; 
		return $url; 
	}

	public function getCalculators(){
		$sql = "select id from new_calculator";
		$ids = $this->query($sql);
		$urls = array();
		foreach($ids as $id){
			$urls[] = $this->geturl($id['id'] , 'calculator');
		}
		return $urls;	
	}
	public function getFacts(){
		$sql = "select id from facts";
		$ids = $this->query($sql);
		$urls = array();
		foreach($ids as $id){
			$urls[] = $this->geturl($id['id'] , 'fact');
		}	
		return $urls;	
	}

	public function getTopics(){
                $sql = "select id from topics";
                $ids = $this->query($sql);
                $urls = array();
                foreach($ids as $id){
                        $urls[] = $this->geturl($id['id'] , 'fact');
                }
                return $urls;
        }
	public function getTopics(){
		$sql = "select url from credit_cards";
		$ids = $this->query($sql);
		$urls = array();
		foreach($ids as $id){
			$urls[] = $this->getccurl($id['id'] , 'fact');
		}
		return $urls;
	}


	public function getLine($url){
		$str = '';
		foreach($url as $key=>$val){
			$str .= "<url>\n";
			$str .= "<loc>" . $val . "</loc>\n";  
			//foreach($url as $subkey=>$subval){
			//   $str.= '<xhtml:link rel="alternate" hreflang="'. $subkey . '" href="' . $subval . '"/>' . "\n";
			//}
			$str .="</url>\n";
		}
		return $str;
	}
}

$s = new SiteMap();
$cals = $s->getCalculators();
$facts = $s->getFacts();

$map = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$map .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";
foreach($cals as $cal){
	$map .= $s->getLine($cal);
}
foreach($facts as $fact){
	$map .= $s->getLine($fact);
}

$map .= '</urlset>';

echo  $map;
