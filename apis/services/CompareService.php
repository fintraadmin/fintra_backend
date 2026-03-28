<?php

spl_autoload_register(function($className) {
        include_once 'apis/dao/'.$className . '.php';
});

class CompareService{

	public function getComparisons($type, $id , $ln, $sub_type=null){

		switch($type){
		case 'cryptocurrency':
				$dao = new CryptoDAO();
				$items = $dao->getN(5);
				$len = count($items);
				$res = [];
				if(isset($id)){
					for($i=0 ; $i<$len ; $i++)
					if($items[$i]['id'] != $id){
						$r['item'] = $items[$i];
						$r['url']='/cryptocurrency-compare/'. $id . '-vs-' . $r['item']['id'];
						$res[] = $r;
					}
						
				}
				else{	
				for($i=0 ; $i<$len-1 ; $i++ )
					for($j=$i+1; $j<$len ; $j++){
						$r['item1'] = $items[$i];
						$r['item2'] = $items[$j];
						$r['url']='/cryptocurrency-compare/'. $r['item1']['id'] . '-vs-' . $r['item2']['id'];
						$res[] = $r;
					}
				}
				return $res;
				break;

		case 'loan':
                                $dao = new LoanDAO();
				if(!is_null($id)){
					$item = $dao->getByID($id, $ln);
					$loan_type = $item['sub_type'];
                                	$items = $dao->getN(5,$loan_type);
				}
				else{
					if(isset($sub_type))
						$loan_type = $sub_type;
                                	$items = $dao->getN(5, $loan_type);
				}
                                $len = count($items);
                                $res = [];
                                if(isset($id)){
                                        for($i=0 ; $i<$len ; $i++)
                                        if($items[$i]['id'] != $id){
                                                $r['item'] = $items[$i];
                                                $r['url']='/loans-compare/'. $id . '-vs-' . $r['item']['id'];
                                                $res[] = $r;
                                        }
                                 
                                }
                                else{ 
                                for($i=0 ; $i<$len-1 ; $i++ )
                                        for($j=$i+1; $j<$len ; $j++){
                                                $r['item1'] = $items[$i];
                                                $r['item2'] = $items[$j];
                                                $r['url']='/loans-compare/'. $r['item1']['id'] . '-vs-' . $r['item2']['id'];
                                                $res[] = $r;
                                        }
                                }
                                return $res;
                                break;

		case 'saving-account':
                                $dao = new SavingAccountDAO();
                                $items = $dao->getN(5);
                                $len = count($items);
                                $res = [];
                                if(isset($id)){
                                        for($i=0 ; $i<$len ; $i++)
                                        if($items[$i]['id'] != $id){
                                                $r['item'] = $items[$i];
                                                $r['url']='/saving-accounts-compare/'. $id . '-vs-' . $r['item']['id'];
                                                $res[] = $r;
                                        }
                                 
                                }
                                else{ 
                                for($i=0 ; $i<$len-1 ; $i++ )
                                        for($j=$i+1; $j<$len ; $j++){
                                                $r['item1'] = $items[$i];
                                                $r['item2'] = $items[$j];
                                                $r['url']='/saving-accounts-compare/'. $r['item1']['id'] . '-vs-' . $r['item2']['id'];
                                                $res[] = $r;
                                        }
                                }
                                return $res;
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
