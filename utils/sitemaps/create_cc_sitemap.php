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
	public function getccurl($id, $type){
		$url1 = 'https://fintra.co.in/english/' . $type . '/' . $id;
		$url2 = 'https://fintra.co.in/hindi/' . $type . '/' . $id;
		//$url3 = 'https://fintra.co.in/' . $type . '/' . $id;
		$url = array();
		//$url['x-default'] = $url3;  
		$url['en'] = $url1;  
		$url['hi'] = $url2; 
		return $url; 
	}
	public function getCC(){
		$sql = "select url from credit_cards where url is not null";
		$ids = $this->query($sql);
		$urls = array();
		foreach($ids as $id){
			$urls[] = $this->getccurl($id['url'] , 'credit-card');
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
$cc = $s->getCC();

$map = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$map .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";
foreach($cc as $cal){
	$map .= $s->getLine($cal);
}

$map .= '</urlset>';

echo  $map;
