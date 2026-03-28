<?php
require_once 'utils/dbutils.php';
require_once 'utils/utils.php';
require_once 'utils/memcache.php';
require_once 'apis/services/ActionService.php';
define('IMAGES_CDN' , 'https://images.fintra.co.in/cms/');

abstract Class DAOBase {

	private $conn;
	//private $type = 'default';
	public $data ;

	public function __construct() {
    		$this->conn = DBUtils::getConn('fintracms') ;
	}
	public function changeURLs($data){
		$r =  str_replace("cms.fintra.co.in/tables/facts","fintra.co.in/english/fact",$data);
		$r1=  str_replace("cms.fintra.co.in/tables/new_calculator","fintra.co.in/english/calculator",$r);
		$r2=  str_replace("cms.fintra.co.in/tables/topics","fintra.co.in/english/topic",$r1);
		return $r2;
	}

	public function toJSON($response){
		return json_encode($response);
	}

	public function INRFormat($amount){
                setlocale(LC_MONETARY, 'en_IN');
                return money_format('%!.0n', floor($amount));
        }
	public function image_url($name){
		return IMAGES_CDN .  $name;
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
	
	public function get_url(){
		$type = $this->data['type'];
		$id = $this->data['id'];
		$language = $this->data['language'];
		//if($type== 'calculator' && ($id == '47' || $id == '48')){
		if($type== 'calculator' && $id == '48'){
			$id = $this->data['url'];	
			$this->data['id'] = $id;
		}
		$utils = new Utils();
		$url = $utils->encodeURL($type,  $id , $language);
		return $url;
	}

	public function addSocialSignals(){
		$signals = array('view' , 'like' , 'calculate' , 'share');
		#$serv = new ActionService();
		foreach($signals as $signal){
			$memcache_key = 'total_' . $signal . ':' . $this->data['type'] . ':' . $this->data['id'] ;
			$data = MemcacheUtil::getItem($memcache_key);
			$this->data['total_' . $signal] = (int)$data;
		}
		if($this->data['total_view'] < $this->data['total_like']){
			$this->data['total_view'] += $this->data['total_like'];
		}
	}
	abstract public function getType();

	public function transform(){
		global $global_data;
		if($global_data['response_type'] == 'mini'){
			$fields = $this->fields_mini;
		}
		else{
			$fields = $this->fields;
		}
		$this->data['type'] = $this->type;
		foreach($fields as $k=>$v){
			if($v ==  null){
				unset($this->data[$k]);
				continue;	
			}
			$val = $this->data[$k];
			$this->data[$v] = $val;
			if($k != $v)
				unset($this->data[$k]);
		}
		$this->data['url'] = $this->get_url();
		$this->data['time'] = Utils::read_time($this->data['description']);
		$this->addSocialSignals();
	}
}


?>
