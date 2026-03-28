<?php
require_once 'DAOBase.php';

Class BlogDAO extends DAOBase {
	private $table = 'blog';
	public $type	= 'blog';

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
		$sql = "select b.*, f.name as featured_image from blogs b,  directus_files f where b.url='$id' and b.draft='no' and  f.id = b.featured_image  ";
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
		$this->data['image'] = $this->image_url($this->data['featured_image']);
    		MemcacheUtil::setItem($memcache_key , serialize($this->data));
		return $this->data;	
	}
	public function get_url(){
		return SERVER_DNS  . '/blog/' . $this->data['url']; 		
	}

	public function getSimilarBlogs($id){
		$memcache_key = $this->type . '.simblogs.' . $id;
		#$data = MemcacheUtil::getItem($memcache_key);
		if(!empty($data)){
			return $data;
		}
		$blog = $this->getByID($id);
		$url = $blog['url'];
		$tags = implode("','" ,  explode("," , $blog['tags']));
		$sql = "select tags, title, url from blogs where tags in ('$tags') and url!='$id' order by created desc limit 8";
		$rows = parent::query($sql);
		$similar  = array();
		foreach($rows as $row){
			$title = $row['title'];
			$tags = $row['tags'];
			$burl = $row['url'];
			$score = 0.9 * similar_text($title , $blog['title']) + 0.1 * similar_text($tags , $blog['tags']);
			$similar[$burl] = $score;
		}
		arsort($similar);
			$results =  array();
			foreach($similar as $simid=>$score){
				$b = $this->getByID($simid);
				$results[] = $b;
			}
    		MemcacheUtil::setItem($memcache_key , $results);
		return $results;
	}


	public function getSimilarBlogsbyTag($tag){
                $memcache_key = $this->type . '.simblogs1.' . $id;
                #$data = MemcacheUtil::getItem($memcache_key);
                if(!empty($data)){
                        return $data;
                }
                $sql = "select tags, title, url from blogs where find_in_set('$tag', tags)<>0  order by created desc limit 8";
                $rows = parent::query($sql);
                $similar  = array();
                foreach($rows as $row){
                        $title = $row['title'];
                        $tags = $row['tags'];
                        $burl = $row['url'];
                        $score = 0.9 * similar_text($title , $blog['title']) + 0.1 * similar_text($tags , $blog['tags']);
                        $similar[$burl] = $score;
                }
                arsort($similar);
                        $results =  array();
                        foreach($similar as $simid=>$score){
                                $b = $this->getByID($simid);
                                $results[] = $b;
                        }
                MemcacheUtil::setItem($memcache_key , $results);
                return $results;
        }

}


?>
