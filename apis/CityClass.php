<?php

require_once 'utils/dbutils.php';

class CityClass{

	public function search($params){
		$query  = $params['q'];
		$pdo = DBUtils::getConn();

		$stmt = $pdo->prepare('SELECT * FROM cities WHERE city like ? limit 10');
		$stmt->execute(array("$query%"));
		$result = $stmt->fetchAll();
		$resp =  array();
		foreach($result as $r){
			$city = $r['city'];
			$state = $r['state'];
			$t = $city . ", ". $state;
			$resp[] = $t;
		}
		return $resp;
	}

}

