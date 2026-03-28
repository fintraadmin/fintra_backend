<?php
ini_set('memory_limit' , '32M');
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
	public function geturl($id, $title,$type, $language_code=null){
		$map['en'] = 'english';
		$map['hi'] = 'hindi';
		$map['gu'] = 'gujarati';
		if (isset($language_code)) 
			$url3 = 'https://fintra.co.in/' . $map[$language_code] . '/' . $type . '/' . $id;
		else
			$url3 = 'https://fintra.co.in/' . $type . '/' . $id;
		$url = array();
		$url['href'] = $url3;  
		$url['title'] = $title; 
		if (isset($language_code)) 
			$url['language'] = $map[$language_code]; 
		return $url; 
	}

	public function getCalculators(){
		$sql = "select c.id,t.title, language_code from new_calculator c, new_calculator_translations t where  c.id =t.calculator_id";
		$ids = $this->query($sql);
		$urls = array();
		foreach($ids as $id){
			$urls[] = $this->geturl($id['id'] , $id['title'], 'calculator' , $id['language_code'] );
		}
		return $urls;	
	}
	public function getFacts(){
		$sql = "select f.id, title , language_code , from facts f , facts_translations t where t.fact_id=f.id";
		$ids = $this->query($sql);
		$urls = array();
		foreach($ids as $id){
			$urls[] = $this->geturl($id['id'] , $id['title'] , 'fact', $id['language_code']);
		}	
		return $urls;	
	}

	public function getTopics(){
		$sql = "select t.id, title, language_code from topics t , topics_translations tt where  tt.topic_id=t.id and tt.title is not null";
                $ids = $this->query($sql);
                $urls = array();
                foreach($ids as $id){
                        $urls[] = $this->geturl($id['id'] , $id['title'] , 'topic' , $id['language_code']);
                }
                return $urls;

	}
}

$s = new SiteMap();
$cals = $s->getCalculators();
#$facts = $s->getFacts();
$topics = $s->getTopics();
$myfile = fopen("calculator.json", "w");
foreach($cals as $cal){
	fwrite($myfile,  json_encode($cal). "\n");
}
fclose($myfile);
$myfile = fopen("topics.json", "w");
foreach($topics as $topic){
	fwrite($myfile, json_encode($topic) . "\n");
}
fclose($myfile);
