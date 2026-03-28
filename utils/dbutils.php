<?php
require_once 'conf/db.conf';

class DBUtils {

	public static $pdo;

	public static function getConn($dbname= DBNAME){
		if(isset(DBUtils::$pdo))
			return DBUtils::$pdo;
		$opt = array(
  			PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    			PDO::ATTR_EMULATE_PREPARES   => false,
		);
		$dsn = 'mysql:host='.DBHOST.';dbname='. $dbname . ';charset=utf8' ;
		#error_log("dsn  :$dsn ");
		DBUtils::$pdo =  new PDO($dsn , DBUSER, DBPASS ,$opt);
		return DBUtils::$pdo;
	}

}


?>
