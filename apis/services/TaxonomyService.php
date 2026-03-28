<?php
spl_autoload_register(function($className) {
        include_once 'apis/dao/'.$className . '.php';
});

class TaxonomyService{

	public function getBrowseTree($type, $id, $ln){

		switch($type){
			
			case 'fact':
				$factDao = new FactDAO();
				$fact = $factDao->getFactByID($id, $ln);
				$topic_id = $fact['topic'];
				$topicDao = new TopicDAO();
				$topic = $topicDao->getTopicByID($topic_id , $ln);
				$category_id = $topic['category'];
				$categoryDao =  new CategoryDAO();
				$category = $categoryDao->getByID1($category_id ,  $ln);

				$nodes = array();

				$node = array();
				$node['title'] = $fact['title'];
				$node['url'] = $fact['url'];

				$nodes[] = $node;

				$node = array();
				$node['title'] = $topic['title'];
				$node['url'] = $topic['url'];
				$node['category'] = $topic['category'];
				$nodes[] = $node;
				$node = array();
				$node['title'] = $category['title'];
				$node['url'] = $category['url'];
				$nodes[] = $node;

				return $nodes;
			break;

			case 'topic':

				$topicDao = new TopicDAO();
                                $topic = $topicDao->getTopicByID($id , $ln);
                                $category_id = $topic['category'];
                                $categoryDao =  new CategoryDAO();
                                $category = $categoryDao->getByID1($category_id ,  $ln);

                                $nodes = array();
                                
                                $node = array();
                                $node['title'] = $topic['title'];
                                $node['url'] = $topic['url'];
                                $nodes[] = $node; 
                                $node = array();
                                $node['title'] = $category['title'];
                                $node['url'] = $category['url'];
                                $nodes[] = $node;
                                
                                return $nodes;

			break;


			case 'calculator':
				$calDao = new CalculatorDAO();
                                $calc = $calDao->getCalculatorByID($id , $ln);
                                $category_id = $calc['category'];
                                $categoryDao =  new CategoryDAO();
                                $category = $categoryDao->getByID1($category_id ,  $ln);

                                $nodes = array();

                                $node = array();
                                $node['title'] = $calc['title'];
                                $node['url'] = $calc['url'];
                                $nodes[] = $node;
                                $node = array();
                                $node['title'] = $category['title'];
                                $node['url'] = $category['url'];
                                $nodes[] = $node;

                                return $nodes;
	

			break;
			default:
				return [];

		}

		


	}


}




?>
