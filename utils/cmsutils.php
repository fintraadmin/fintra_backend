<?php
require 'vendor/autoload.php';
require 'conf/db.conf';
require_once 'utils/memcache.php';
ini_set('session.cookie_domain', '.fintra.co.in');

class CMSUtils {
	static $config = [
    	'database' => [
        	'hostname' => DBHOST,
        	'username' => DBUSER,
        	'password' => DBPASS,
        	'database' => 'fintracms',
    	],
    	'filesystem' => [
        	'root' => '/tmp/storage/uploads'
    	]
	];

	static $client = null;

	static $rev_lang_key = array('en' => 'english' , 'hi' => 'hindi' , 'gu' => 'gujarati');

	public static function init(){
		if(isset(static::$client))
			return;
		static::$client = \Directus\SDK\ClientFactory::create(static::$config);
	}

	public static function _formatCalc($trans){
		$response = array();
		$lang =  $trans['language_code'];
		$title = $trans['title'];
		$des = $trans['description'];
		$seo_des = $trans['seo_description'];
		$msg = $trans['download_msg'];
		$image = $trans['image'];
		$id = $trans['calculator_id'];
		$response =  array(
					'title' => $title,
					'description' => $des,
					'id' => $id,
					'image' => $image,
					'seo_description' => $seo_des,
					'msg' => $msg,
					'language' => $lang,
					'href' => '/' . static::$rev_lang_key[$lang] . '/tools/' . $id 
			);
		return $response;
	}
	public static function getCalculators($params){
		CMSUtils::init();
		$response = array();
		$ln =  isset($params['language']) ? $params['language'] : 'en';
		$calculators  = static::$client->getItems('calculators_translations' ,  array('filters'=> array('language_code' => $ln)));
		foreach($calculators as $cal){
			$data = $cal->getData();
			$response[] = CMSUtils::_formatCalc($data);
		}
		return $response;
	}


	public static function getCalculatorDetails($params){
		CMSUtils::init();
		$ln =  isset($params['language']) ? $params['language'] : 'en';
		$id =  $params['id'];
		if(empty($id))
			return array();
		$params = ['filters' => ['calculator_id' =>  $id , 'language_code' => $ln]];	
		$calculators  = static::$client->getItems('calculators_translations' ,  $params);
		if(count($calculators)> 0){
			foreach($calculators as $calculator){
				$data = $calculator->getData();
				return CMSUtils::_formatCalc($data);
			}
		}
		return array();
	}

	public static function getBlogLink($url){
		CMSUtils::init();
                $response = array();
                $blogs  = static::$client->getItems('blogs' ,  array('filters'=> array('url' => $url )));
                foreach($blogs as $blog){
                        $data = $blog->getData();
			$link = '/blog/' . $data['url'];
			$title = $data['title'];
		}
		return array('href' => $link , 'title' => $title);
	}

	public static function getImageLink($url){
                CMSUtils::init();
                $response = array();
		$link = null;
                $files  = static::$client->getItems('directus_files' ,  array('filters'=> array('id' => $url )));
                foreach($blogs as $blog){
                        $data = $blog->getData();
                        $link = 'https://images.fintra.co.in/cms/' . $data['name'];
                }
		return $link;
        }


	public static function getFooterLinks($params){
		$memcache_key = 'footer-' . $params['language'];
		$footer = MemcacheUtil::getItem($memcache_key);
		if(empty($footer)){
		#$blogs =  CMSUtils::getBlogs(10);
		#$calculators = CMSUtils::getCalculators($params); 

		$footer = array();

		$f_blogs = array();
		$f_calculators = array();
		foreach($blogs as $blog){
			$url = $blog['perma_link'];
			$title = $blog['title'];
			$content = $blog['body'];
			$f_blogs[] = array('url' => $url , 'title' => $title, 'content' => $content);
		}
		foreach($calculators as $c){
			$url = $c['href'];
			$title = $c['title'];
			$f_calculators[] = array('url' => $url , 'title' => $title);
		}

		#$footer['Blogs'] = $f_blogs;
		#$footer['Calculators'] = $f_calculators;
		$footer['privacy_policy'] = '/privacy_policy.html';
                $footer['instagram'] = 'https://www.instagram.com/fintra_finance/';
                $footer['youtube'] = 'https://www.youtube.com/channel/UC13B009CymX4ClgOgXPuiYw';
                $footer['linkedin'] = 'https://www.linkedin.com/company/1345703';
                $footer['facebook'] = 'https://www.facebook.com/Fintra-714955548629332';
                $footer['twitter'] = 'https://twitter.com/FintraSupport';
                $footer['google_play'] = 'https://play.google.com/store/apps/details?id=com.fintra.app&referrer=utm_source%3Dsite%26utm_medium%3Dapplink';

		MemcacheUtil::setItem($memcache_key , $footer);
		}
		return $footer;
	}

	public static function _formatBlog(&$blog){
		$editor = $blog['editor'];
		$blog['editor_name'] = $editor['first_name'] . ' ' . $editor['last_name'];
		$blog['perma_link'] = '/blog/' . $blog['url'];
		$blog['featured_image'] = 'https://images.fintra.co.in/cms/'.  $blog['featured_image']['name'];
		$blog['image'] = $blog['featured_image'];
		$blog['url'] = 'https://fintra.co.in' . $blog['perma_link'];
		unset($blog['editor']);
		$related = array();
		for($i=1; $i<=3; $i++){
			if(isset($blog['link' . $i])){
				 $url = $blog['link' . $i];
				 $parts =  explode('/', $url);
				 $u = $parts[count($parts) - 1];
				 $link = CMSUtils::getBlogLink($u);
				 if(!empty($link))
					$related[] = $link;
			}
		}
		$blog['related'] = $related;
		return $blog;

	}
	public static function getBlogs($limit=40){
		CMSUtils::init();
		$response = array();
		$blogs  = static::$client->getItems('blogs' ,  array('order'=> array('created' => 'DESC') , 'limit' => $limit, 'filters' => array('draft'=>'no')));
		foreach($blogs as $blog){
			$data = $blog->getData();
			$response[] = CMSUtils::_formatBlog($data);
		}
		return $response;	
	}

	public static function getBlogsbyIDs($ids){
		$response =  array();
		foreach($ids as $id){
			$response[] = CMSUtils::getBlog($id);
		}
		return $response;
	}

	public static function getBlog($id){
                CMSUtils::init();
                $response = array();
                $blogs  = static::$client->getItems('blogs' ,  array('filters'=> array('url' => $id )));
                foreach($blogs as $blog){
                        $data = $blog->getData();
                        $response = CMSUtils::_formatBlog($data);
                }
                return $response;
        }

	public static function addLikes($id){
		$conn = new PDO("mysql:host=". DBHOST .";dbname=fintracms", DBUSER, DBPASS);
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$sql= "UPDATE blogs set likes=likes+1 where url='$id'";
		$stmt = $conn->prepare($sql);
		$stmt->execute();
	}
	public static function addViews($id){
		$conn = new PDO("mysql:host=". DBHOST .";dbname=fintracms", DBUSER, DBPASS);
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$sql= "UPDATE blogs set views=views+1 where url='$id'";
		$stmt = $conn->prepare($sql);
		$stmt->execute();
	}
	public static function getConfigValue($key , $lang='en'){
                CMSUtils::init();
		$r = '';
                $vals  = static::$client->getItems('config_translations' ,  array('filters'=> array('orig_id' => $key , 'language_code' => $lang)));
		foreach($vals as $val){
			$data = $val->getData();
			$r = $data['value'];
		}

		return $r;
	}

	public static function getMenus($params){
		CMSUtils::init();
		$ln =  isset($params['language']) ? $params['language'] : 'en';
		$menus =  static::$client->getItems('menus_translations' ,  array('filters'=> array('language_code' => $ln) , 'order' => array('sequence' => 'ASC')));
		$response = array();
		foreach($menus as $menu){
			$data = $menu->getData();
			$response[$data['menu_id']] =  array('menu_name' => $data['name'] , 'url' => $data['url'] , 'is_translated' => $data['is_translated']);
		}
		return $response;
	}

	 public static function getPage($params){
                CMSUtils::init();
                $ln =  isset($params['language']) ? $params['language'] : 'en';
		$id =  $params['id'];
                $pages =  static::$client->getItems('pages_translations' ,  array('filters'=> array('language_code' => $ln ,  'pages_id' => $id)));
                $response = array();
                foreach($pages as $page){
                        $data = $page->getData();
                        $response =  array('seo_title' => $data['seo_title'] , 'url' => $data['url'] , 'seo_description' => $data['seo_description'] , 'body' => $data['body'] , 'title' => $data['title']);
                }
                return $response;
        }

}
#print_r(CMSUtils::getBlogs());
#print_r(CMSUtils::getCalculators(array('language' => 'hi' , 'id' => 'calculators')));
?>
