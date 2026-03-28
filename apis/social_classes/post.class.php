<?php
require_once 'utils/dbutils.php';
require_once 'utils/utils.php';


class Post {

	public function add($params){
		$uuid = $params['uuid'];
		if(empty($uuid)){
			return;
		}
		$update = false;
		$params['ownerid']  = $uuid;
		$fields =  array('type' , 'ownerid' , 'title' , 'description' , 'language' , 'status');
		if(isset($params['postid'])){
			$fields =  array( 'type' , 'ownerid' , 'title' , 'description' , 'language' , 'status');
			$update = true;
		}
                foreach($fields as $f){
                        $data[':'. $f ] = isset($params[$f])?  $params[$f] : null;
                }
		if(!$update){
                	$cols =  implode("," , $fields);
                	$vals = implode("," , array_keys($data));
                	$query = "insert into posts ( $cols ) values ($vals)";
		}
		else{
			foreach($fields as $f){
				$update_array[] = "$f=:" 
			}
			$query = "update table posts set $update_str where id='$postid' ";
		}
                try {
                        $st = $pdo->prepare($query);
                        $st->execute($data);
                }
                catch (PDOException $e) {
                   error_log("MYSQL Error". $e->getMessage ());
                }

	}	

	public function disable($params){


	}

	public function get($params){

	}
}


