<?php
require_once 'utils/dbutils.php';
require_once 'utils/utils.php';


class User{
	
	var $fields;

	public function init($fields){
		foreach($fields as $key=>$val){
			$this->fields[$key] = $val;
		}
	}

	public function getUser($params){
		if(!empty($params['uuid'])){
			$query = "select * from users where uuid=" . $params['uuid'];
		}
		else if(!empty($params['phone'])){
			$query = "select * from users where phone='" . $params['phone'] . "'";
		}
		else if(!empty($params['email'])){
			$query = "select * from users where email='" . $params['email'] . "'";
		}
		$pdo = DBUtils::getConn();
		$st = $pdo->prepare($query);
		$st->execute();

		$result = $st->fetch(PDO::FETCH_ASSOC);
		$this->init($result);
	}

	public function createUser($params){
		$pdo = DBUtils::getConn();
		$uuid = Utils::gen_uuid();
		$params['uuid'] = $uuid;
                $data = array();
                $fields =  array('uuid' , 'name' , 'about' , 'image' , 'email' , 'phone' , 'last_deviceid' , 'last_platform' , 'dob' , 'city' ,  'gender' , 'language');
                foreach($fields as $f){
                        $data[':'. $f ] = isset($params[$f])?  $params[$f] : null;
                }
                $cols =  implode("," , $fields);
                $vals = implode("," , array_keys($data));
                $query = "insert into users ( $cols ) values ($vals)";
		try { 
                	$st = $pdo->prepare($query);
                	$st->execute($data);
		}
		catch (PDOException $e) {
 		   error_log("MYSQL Error". $e->getMessage ());
		}
	}

	public function mark_phone_valid($params){
		$phone = $params['phone'];
		$pdo = DBUtils::getConn();

		$query = "update users set is_phone_verified=1 where phone=:phone";
		try { 
                	$st = $pdo->prepare($query);
                	$st->execute(array(':phone' => $phone));
		}
		catch (PDOException $e) {
 		   error_log("MYSQL Error". $e->getMessage ());
		}
	}
	public function checkIfUserExists($params){
		 if(!empty($params['uuid'])){
                        $query = "select * from users where uuid=" . $params['uuid'];
                }
                else if(!empty($params['phone'])){
                        $query = "select * from users where phone='" . $params['phone'] . "'";
                }
                else if(!empty($params['email'])){
                        $query = "select * from users where email='" . $params['email'] . "'";
                }
                $pdo = DBUtils::getConn();
                $st = $pdo->prepare($query);
                $st->execute();

                $result = $st->fetch(PDO::FETCH_ASSOC);
		if(!empty($result))
			return true;
		return false;
	}

}
/*
$u =  new User();
$p['phone'] = '+919167071530';
$u->getUser($p);
print_r($u->fields);
*/
