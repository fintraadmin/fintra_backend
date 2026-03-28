<?php

require 'lib/vendor/autoload.php';
require 'lib/vendor/conf.ini';


use Aws\DynamoDb\DynamoDbClient;
use Aws\Common\Credentials\Credentials;


class DBService {

  var $client;
 
  function __construct(){
  $credentials = new Credentials(ACCESS_KEY, SECRET_KEY);
  
  // Instantiate a client with the credentials from the project1 profile
  $this->client = DynamoDbClient::factory(array(
  	'profile' => 'default',
  	'region'  => 'ap-south-1',
  	'credentials' => $credentials
  ));
  }
  public function getChapters(){
        $iterator = $this->client->getIterator('Scan', array(
        'TableName'     => 'chapters',
	'ProjectionExpression' => 'id,id_title,title'
        ));

	$items =  array();
        foreach ($iterator as $item) {
          $data  = array();
          foreach($item as $key=>$value){
                if(isset($value['S']))
                        $data[$key] = $value['S'];
                if(isset($value['N']))
                        $data[$key] = $value['N'];
          }
	  $items[] = $data;
        }
        return $items;
  }
 
function getTopLevel(){
	$links = array("en" => "https://fintra.co.in/english" , "hi" =>  "https://fintra.co.in/hindi" , "gu" => "https://fintra.co.in/gujarati");	
	$str = '';
	foreach($links as $key => $link){
		$str .= '<url>' . "\n";
		$str .= '<loc>' . $link . '</loc>' . "\n";
		foreach($links as $k=>$sl){
			$str .= "\t" . '<xhtml:link rel="alternate" hreflang="'. $k . '"' . "\n";
                        $str .= "\t" . 'href="' . $sl . '"/>' . "\n";
		}
		$str .= '</url>' . "\n";
	}
	return $str;
}	

function getTopicLevel(){
	$links = array("en" => "https://fintra.co.in/english/mutual-funds" , "hi" =>  "https://fintra.co.in/hindi/mutual-funds" , "gu" => "https://fintra.co.in/gujarati/mutual-funds");	
	$str = '';
	foreach($links as $key => $link){
		$str .= '<url>' . "\n";
		$str .= '<loc>' . $link . '</loc>' . "\n";
		foreach($links as $k=>$sl){
			$str .= "\t" . '<xhtml:link rel="alternate" hreflang="'. $k . '"' . "\n";
                        $str .= "\t" . 'href="' . $sl . '"/>' . "\n";
		}
		$str .= '</url>' . "\n";
	}
	return $str;
}
	
function getHreflangs($id){

	$langs = array('hindi' => 'hi' ,  'english' => 'en' , 'gujarati' => 'gu');
	#$langs = array( 'english' => 'en');
		$str = '';
		foreach($langs as $lang=>$key){
			$str .= "\t" . '<xhtml:link rel="alternate" hreflang="'. $key . '"' . "\n";
			$link = "https://fintra.co.in/$lang/mutual-funds/" . $id;
			$str .= "\t" . 'href="' . $link . '"/>' . "\n";
		}
	return $str;

}
function genSitemap($data){
	$langs = array('hindi' => 'hi' ,  'english' => 'en' , 'gujarati' => 'gu');
	#$langs = array('english' => 'en');
	$start = '<?xml version="1.0" encoding="UTF-8"?> 
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml"> ' . "\n";
	$str = $start ;
     	$str .= $this->getTopLevel();
     	$str .= $this->getTopicLevel();
	foreach($data as $e){
		foreach($langs as $l=>$v){
			if(!isset($e['id_title']))
				continue;
			$str .=  '<url>' . "\n";
			$str .= '<loc>' . 'https://fintra.co.in/' . $l . '/mutual-funds/' . $e['id_title'] .  '</loc>' . "\n" ;
			$str .= $this->getHreflangs($e['id_title']); 
			$str .= '</url>' . "\n";
		}
	}
	$str .= '</urlset>' . "\n";
 return $str;
}
 
}
$dbs = new DBService();
$links = $dbs->getChapters();
print_r($dbs->genSitemap($links));
?>
