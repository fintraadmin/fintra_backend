<?php
require_once 'DAOBase.php';


Class CalculatorDAO extends DAOBase {
	private $table = 'new_calculator';
	private $table_trans = 'new_calculator_translations';
	public $fields = array ('calculator_id' => 'id' , 'language_code' => 'language' , 'title'=> 'title' , 'template'=> 'input' ,'image'=>'image', 'url' => 'url' , 'output_template' => 'output' , 'description' => 'subtitle1' , 'subtitle' => 'subtitle' , 'seo_description' => 'seo_description' , 'output1' => 'output1' , 'button_text' => 'button_text' , 'seo_title' => 'seo_title' , 'category' => 'category' , 'calid' => 'calid' , 'faq' => 'faq', 'apply_link'=> 'apply_now');
	public $fields_cloned = array ('template'=> 'input' ,'image'=>'image', 'url' => 'url' , 'output_template' => 'output'  , 'subtitle' => 'subtitle'  , 'output1' => 'output1' , 'button_text' => 'button_text' , 'category' => 'category' , 'calid' => 'id');
	public $fields_mini = array ('calculator_id' => 'id' , 'language_code' => 'language' , 'title'=> 'title' , 'template'=> null ,'image'=>'image', 'url' => 'url' , 'output_template' => null) ;
	public $type	= 'calculator';

	public function __construct() {
		parent::__construct();
	}
	public function getType(){
		return $this->type;
	}

	public function getImage($id){

		$sql = "select name from directus_files where id  = $id ";
		$rows = parent::query($sql);
		$row = $rows[0];
		if(!empty($row['name']))
			return $row['name'];
		return '';
	}

	public function getCalculatorByID($id, $ln = 'en') {
		$memcache_key = $this->type . '.' . $id . '.' . $ln;
		#$data = MemcacheUtil::getItem($memcache_key);
		if(!empty($data)){
			$this->data = $data;
			$this->transform();
			return $data;
		}
		$sql = "select * from new_calculator c ,  new_calculator_translations ct where c.id = ct.calculator_id and c.id = '$id' and ct.language_code = '$ln' and disabled='2'";
		$facts = parent::query($sql);
		if(count($facts) == 0){
			return; 
		}
		$fact = $facts[0];
		$is_cloned = $fact['clone'];
		$cloned_id = $fact['cloned_calculator'];
		if($is_cloned ==  'yes' && isset($fact['cloned_calculator'])){
			$cloned_id = $fact['cloned_calculator'];
			$cloned_calculator = $this->getCalculatorByID($cloned_id, $ln);
			foreach($this->fields_cloned as $k1=>$k2){
				$fact[$k1] = $cloned_calculator[$k2];
			}
			$fact['output1'] = str_ireplace($cloned_calculator['title'] , $fact['title'] , $cloned_calculator['output1']);
		}
		$this->data = array();
		foreach($fact as $key=>$val){
			if(in_array($key , array_keys($this->fields) )){
				if(isset($val))
					$this->data[$key] = $val;
			}
		}
		$this->data['description'] = $this->changeURLs($this->data['description']);
		if(isset($fact['featured_image']))
			$this->data['image'] = $this->image_url($this->getImage($fact['featured_image']));
		$this->transform();
    		MemcacheUtil::setItem($memcache_key , $this->data);
		return $this->data;	
	}

	public function getCalculatorByCategory($id, $ln = 'en') {
                $memcache_key = $this->type . '.' . $id . '.' . $ln;
                #$data = MemcacheUtil::getItem($memcache_key);
                if(!empty($data)){
                        return $data;
                }
                $sql = "select id from new_calculator c where c.category='$id'  and disabled='2' and clone='no'";
                $rows = parent::query($sql);
                if(count($rows) == 0){
                        return;
                }
		$calcs = array();
                foreach($rows as $row){
			$cid = $row['id'];
			$c = $this->getCalculatorByID($cid , $ln);
			$calcs[] = $c;
                }

                MemcacheUtil::setItem($memcache_key , $calcs);
                return $calcs;
        }

	public function getCloneCalculatorByCategory($id, $ln = 'en') {
                $memcache_key = $this->type . '.' . $id . '.' . $ln;
                #$data = MemcacheUtil::getItem($memcache_key);
                if(!empty($data)){
                        return $data;
                }
                $sql = "select id from new_calculator c where c.category='$id'  and disabled='2' and clone='yes'";
                $rows = parent::query($sql);
                if(count($rows) == 0){
                        return;
                }
                $calcs = array();
                foreach($rows as $row){
                        $cid = $row['id'];
                        $c = $this->getCalculatorByID($cid , $ln);
                        $calcs[] = $c;
                }

                MemcacheUtil::setItem($memcache_key , $calcs);
                return $calcs;
        }

}


