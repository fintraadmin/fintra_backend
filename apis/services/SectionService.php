
<?php
require_once 'CardService.php';

class SectionService {

	private $icon;
	private $title;
	private $layout;
	private $cards;
	private $id;
	private $type;


	public function __construct(){

	}

	public function format($data){
		$type = $data['type'];	

		switch($type){

			case 'section':
				$this->icon = $data['icon'];
				$this->title = $data['title'];
				$this->layout = $data['alignment'];
				$this->id = $data['id'];
				$this->type = $data['type'];
				$this->cards =  array();
				$this->url = $data['url'];
				foreach($data['data'] as $d){
					$card  = new CardService();
					$card->format($d);
					if(!$card->isBad())
						$this->cards[] = $card->response();
				}
				break;
			default:
				$this->title = 'Demo';
				$this->image = '';

		}

	}
		
	public function response(){	
		$json = array();
		$json['icon'] = $this->icon;
		$json['title']= $this->title;
		$json['layout'] = $this->layout;
		$json['cards'] = $this->cards;
		$json['url'] = $this->url;
		return $json;
	}
}

?>
