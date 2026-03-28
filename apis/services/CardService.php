<?php
include_once 'apis/services/ActionService.php';


class CardService {

	private $image;
	private $title;
	private $subtitle;
	private $id;
	private $type;
	private $content;

	public function __construct(){

	}

	public function format($data){
		$type = $data['type'];	

		switch($type){
			case 'mutual_fund':
			case 'fixed_deposit':
			case 'fact':
				$this->id = $data['id'];
				$this->type= $data['type'];
				$this->title = $data['title'];
				$this->content = isset($data['description']) ? $this->enrich($data['description']) : '';
				$this->image = $data['image'];
				$this->url = $data['url'];
				$this->subtitle = $this->addSubtitle($data);
				$this->totalLikes = $data['total_like'];
				$this->totalViews = $data['total_view'];
				$this->seo_description = $data['seo_description'];
				$this->seo_title = $data['seo_title'];
				break;
			case 'calculator':
				$this->id = $data['id'];
				$this->type= $data['type'];
				$this->title = $data['title'];
				$this->content = json_decode($this->enrich($data['input']));
				$this->image = $data['image'];
				$this->url = $data['url'];
				$this->subtitle = $data['subtitle'];
				$this->subtitle1 =  $this->enrich($data['subtitle1']);
				$this->totalLikes = $data['total_like'];
				$this->totalViews = $data['total_view'];
				$this->seo_description = $data['seo_description'];
				$this->seo_title = $data['seo_title'];
				break;
			case 'topic':
				$this->id = $data['id'];
				$this->type= $data['type'];
				$this->title = $data['title'];
				$this->subtitle = $data['subtitle'];
				$this->image = $data['image'];
				$this->url = $data['url'];
				$this->totalViews = $data['total_view'];
				$this->seo_description = $data['seo_description'];
				$this->seo_title = $data['seo_title'];
				break;
			default:
				$this->id = 1;
				$this->image = '';

		}

	}

	public function enrich($data){
		$r =  str_replace("cms.fintra.co.in/tables/facts","fintra.co.in/english/fact",$data);
		$r1=  str_replace("cms.fintra.co.in/tables/new_calculator","fintra.co.in/english/calculator",$r);
		$r2=  str_replace("cms.fintra.co.in/tables/topics","fintra.co.in/english/topic",$r1);
		return $r2;
	}
	public function addSubtitle($data){
		$subtitle = $data['time'] . ' mins read';
		return $subtitle;
	}

	public function isBad(){
		if (empty($this->id) || empty($this->type) || empty($this->title))
			return true;

		return false;
	}
	
	public function format_number($num){
		$d =  $num;
		if($num/1000 > 1.1)
			$d = round($num/1000 , 1) . 'K';

		return $d;

	}	
	public function response(){
		global $global_data;
		$json = array();
		$json['image'] = $this->image;
		if($global_data['source'] != 'home'){
			$json['title']= $this->title;
		}
		$json['content']= $this->content;
		$json['seo_description']= isset($this->seo_description) ? $this->seo_description : null ;
		$json['seo_title']= isset($this->seo_title) ? $this->seo_title : null ;

		$json['subtitle'] = '';
		if(isset($this->totalLikes) && $this->totalLikes >= 1){
			$json['subtitle'] .= '<p>' . $this->totalLikes . ' Likes';
		}

		if(isset($this->totalViews) && $this->totalViews >= 1){
			if(strlen($json['subtitle']) > 5)
				$json['subtitle'] .= ' &middot; ' . $this->format_number($this->totalViews) . ' Views</p>';
			else
				$json['subtitle'] .= '<p>' . $this->format_number($this->totalViews) . ' Views</p>';
		}
		else{
			if(strlen($json['subtitle'] > 5))
				$json['subtitle'] .= '</p>';
		}

		
		$json['subtitle1'] = isset($this->subtitle1) ? $this->subtitle1 : '' ;
		$json['id'] = $this->id;
		$json['type'] = $this->type;
		$json['url'] = $this->url;
		$json['views'] = isset($this->totalViews) ? $this->totalViews : null;
		$json['likes'] = isset($this->totalLikes) ? $this->totalLikes :  null;
		$json['isLiked'] = isset($this->isLiked) ? $this->isLiked : false;
		$json['isViewed'] = isset($this->isViewed) ? $this->isViewed : false;
		return $json;
	}
}

?>
