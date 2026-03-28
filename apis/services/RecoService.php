<?php

spl_autoload_register(function($className) {
        include_once 'apis/dao/'.$className . '.php';
});

class RecoService{

	public function getRecos($type, $id , $ln){

		switch($type){
		case 'fact':
                                $factDao = new FactDAO();
                                $fact = $factDao->getFactByID($id, $ln);
                                $topic_id = $fact['topic'];
                                $similar = $factDao->getFactsByTopic($topic_id , $ln);
				foreach($similar as $k=>$sm){
					if($sm['id'] == $id){
						unset($similar[$k]);
					}
				}
				return array_slice($similar, 0, 6);

				break;
		case 'blog':
                                $dao = new BlogDAO();
                                $similar = $dao->getSimilarBlogs($id);
                                return array_slice($similar, 0, 6);
                                break;

		case 'blog1':
                                $dao = new BlogDAO();
                                $similar = $dao->getSimilarBlogsbyTag($id);
                                return array_slice($similar, 0, 6);
                                break;



		case 'topic':
				break;

		case 'calculator':
				$calDao = new CalculatorDAO();
                                $calc = $calDao->getCalculatorByID($id , $ln);
                                $category_id = $calc['category'];
				$similar = $calDao->getCalculatorByCategory($category_id , $ln);
				foreach($similar as $k=>$sm){
					if($sm['id'] == $id){
						unset($similar[$k]);
					}
				}
				return array_slice($similar, 0, 6);
				break;
		case 'calculatorclone':
                                $calDao = new CalculatorDAO();
                                $calc = $calDao->getCalculatorByID($id , $ln);
                                $category_id = $calc['category'];
                                $similar = $calDao->getCloneCalculatorByCategory($category_id , $ln);
                                foreach($similar as $k=>$sm){
                                        if($sm['id'] == $id){
                                                unset($similar[$k]);
                                        }
                                }
                                return array_slice($similar, 0, 12);
                                break;

				
		default:
				return array();
				break;

	}

	}


}
