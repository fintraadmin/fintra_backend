<?php
require_once 'utils/utils.php';
require_once 'DataClass.php';
require_once 'utils/memcache.php';
require_once 'ContentClass.php';

class HomepageClass extends DataClass {

  public function fetchData($params){
	Utils::parseParams($params);
	$app_version =  '1.0.2';
	if(isset($params['version'])){
		$app_version = $params['version'];
	}	
	
	$this->client = $this->connectDynamo();
	$language = $params['language'];
	if(empty($language))
		$language = 'en';

	$this->language = $language;
	$memcache_key = 'homepage-' . $language . $app_version;
	//$data = MemcacheUtil::getItem($memcache_key);
	if(!empty($data)){
		return $data;
	}

	$data = array();
	$blogs  = array();
	$topics = $this->getTopics();
	$calculators = $this->getCalculators('calculators');
	$calculators1 = $this->getCalculators('calculators1');
	if(version_compare($app_version , '2.5') >=0) {// for all such apps
		$blogs = $this->getBlogs();
	}
	$main_topics= $this->initTopics();
	foreach($calculators as $topic){
		$category = $topic['category'];
		if(isset($topic['min_version']) && version_compare($topic['min_version'] ,  $app_version) >= 0)
			continue; 
                foreach($main_topics as &$mt){
                        if($mt['header'] == $category){
				$mt['header_title'] = $topic['header'];
                                if(empty($mt['data']))
                                        $mt['data'] = array();
                                $mt['data'][] = $topic;
                        }
                }
        }
	if(count($calculators1) > 0  && version_compare($app_version , '2.5') >=0)
		$this->addModule($main_topics , $calculators1);
	foreach($topics as $topic){
		$category = $topic['category'];
		foreach($main_topics as &$mt){
			if($mt['header'] == $category){
				$mt['header_title'] = $topic['header'];
				if(empty($mt['data']))
					$mt['data'] = array();
				$mt['data'][] = $topic;
			}
		}
	}

	
	if(count($blogs) > 0){
		$this->addBlogModule($main_topics , $blogs);
	}
	$data = $main_topics;
	 		
    	$response = array();
	$response['items'] = $data;
	$this->fillOther($response);

    MemcacheUtil::setItem($memcache_key , $response);
	//return $data;	
	return $response;
  }
  public function initTopics(){
	$topics = array('calculators', 'mutual_fund' , 'mutual_fund_invest');
	$data = array();
	foreach($topics as $topic){
		$item = array();
		$item['header'] = $topic;
		$item['data'] = array();
		$data[] = $item;
	}
	return $data;
  }

  public function getBlogs(){
	$class = new ContentClass();
	$data = $class->listBlogs($params);
	return $data;
  }

  public function addBlogModule(&$modules , $blogs){
	$blog_module = array();
	$blog_module['header'] = 'blog';
	$blog_module['header_title'] = 'Blogs - Read and Learn Daily';
	$blog_module['data'] = $blogs;
	$modules[] = $blog_module;
  }

  public function addModule(&$modules , $data){
	$module = array();
	$module['header'] = $data[0]['category'];
	$module['header_title'] = $data[0]['header'];
	foreach($data as &$d){
		if($d['type'] == 'calculator1')
			$d['type'] = 'calculator';
	}
	$module['data'] = $data;
	$modules[] = $module;
	error_log("====== added module ");
  }

  public function getTopics(){
	$iterator = $this->client->getIterator('Query', array(
        'TableName'     => 'topics',
    	'ExpressionAttributeNames' => array('#t' => 'type'),
        'ExpressionAttributeValues' => array(':v1' => array('S' =>  'topic')),
        'KeyConditionExpression' => '#t = :v1',
    )); 
    $res =  array();
    foreach ($iterator as $item) {
      $data  = array();
          foreach($item as $key=>$value){
        if(isset($value['S']))
            $data[$key] = $value['S'];
        if(isset($value['N']))
            $data[$key] = $value['N'];
      }   
      $res[] = $data;
    }   
    //Sort by sequence
    usort($res, function ($item1, $item2) {
         return $item1['sequence'] <=> $item2['sequence'];
    }); 
    $fields = array('title' , 'about' , 'view' , 'time' , 'numChapters' , 'id' , 'image' , 'category' , 'type' , 'header');
    $final =  $this->filterBylanguage(array('language' => $this->language ,'data' =>  $res , 'fields' => $fields));

	return $final;	
  }

  public function fillOther(&$d){
	$d['whatsapp'] = 'https://api.whatsapp.com/send?phone=+919619392341';
    	//$d['phone'] = '+919619392341';
	$d['fdrate'] = 6.5;
	$d['risk_chapter']= 'pf1ch1';
	$d['fund_chapter']= 'pf1ch3';
	$d['show_ads']= '1';
	$d['show_update_banner']= '0';
	$d['update_msg_id']= 'rn1ch1';
  }

  public function getCalculators($category){
	$iterator = $this->client->getIterator('Query', array(
	    'TableName'     => 'topics',
    	'ExpressionAttributeNames' => array('#t' => 'type'),
        'ExpressionAttributeValues' => array(':v1' => array('S' =>  'calculator')),
        'KeyConditionExpression' => '#t = :v1',
    )); 
    $res =  array();
    foreach ($iterator as $item) {
      $data  = array();
      $flag =  false;
          foreach($item as $key=>$value){
        if(isset($value['S']))
            $data[$key] = $value['S'];
        if(isset($value['N']))
            $data[$key] = $value['N'];
	    if ($key == 'category')
		if ($data[$key] != $category)
			$flag = true;
      }
      if(!$flag) 
      	$res[] = $data;
    }  
    //filter disabled 
    $filter_res = array();
    foreach($res as $r){
	if(isset($r['disabled']) && $r['disabled'] == 'yes'){
		continue;
	}
	$filter_res[] = $r;
    } 
    $res = $filter_res;
    //Sort by sequence
    usort($res, function ($item1, $item2) {
         return $item1['sequence'] <=> $item2['sequence'];
    }); 
    $fields = array('title' , 'id' , 'image' , 'type' , 'category' , 'header' , 'calculations' , 'min_version');
    $final =  $this->filterBylanguage(array('language' => $this->language ,'data' =>  $res , 'fields' => $fields));
	return $final;	

  }
	

}

?>


