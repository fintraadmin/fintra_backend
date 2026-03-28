<?php
require_once 'DAOBase.php';

Class PromptDAO extends DAOBase {
	private $table = 'prompts';
	public $type	= 'prompt';

	public function __construct() {
		parent::__construct();
	}
	public function getType(){
		return $this->type;
	}

	public function getByID($id, $ln = 'en') {
		if(empty($id))
			return;
		$memcache_key = $this->type . '.' . $id . '.' . $ln;
		#$data = unserialize(MemcacheUtil::getItem($memcache_key));
		if(!empty($data) && isset($data['id'])){
			return $data;
		}
		$sql = "select * from prompts p ,  prompts_translation pt where p.id = pt.prompt_id and p.uuid = '$id' and pt.language_code = '$ln'";
		$facts = parent::query($sql);
		if(count($facts) == 0){
			return; 
		}
		$fact = $facts[0];
		$this->data = array();
		foreach($fact as $key=>$val){
				$this->data[$key] = $val;
		}
		$this->transform();
    		MemcacheUtil::setItem($memcache_key , serialize($this->data));
		return $this->data;	
	}


	 public function getN($ln = 'en') {
                $memcache_key = $this->type . '.' . $id . '.' . $ln;
                #$data = unserialize(MemcacheUtil::getItem($memcache_key));
                if(!empty($data) && isset($data['id'])){
                        return $data;
                }
                $sql = "select * from prompts p ,  prompts_translation pt where p.id = pt.prompt_id and  pt.language_code = '$ln' and p.featured=1";
		error_log("===== $sql");
                $facts = parent::query($sql);
                if(count($facts) == 0){
                        return;
                }
                $data = array();
                foreach($facts as $fact){
                                $data[] = $fact;
                }
                #$this->transform();
                #MemcacheUtil::setItem($memcache_key , serialize($this->data));
                return $data;
        }

	public function get_url(){
		return SERVER_DNS  . '/blog/' . $this->data['url']; 		
	}

	public function getSimilar($id){
	}


	public function getSimilarBlogsbyTag($tag){
        }

}


?>
