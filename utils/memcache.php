<?php

class MemcacheUtil {

static $m;

public static function connect(){
	MemcacheUtil::$m = new Memcached();
	MemcacheUtil::$m->addServer('localhost', 11211);
	#MemcacheUtil::$m->setOption(Memcached::OPT_SERIALIZER, Memcached::SERIALIZER_PHP);
}

public static function getItem($key){
	if(!MemcacheUtil::$m){
		MemcacheUtil::connect();
	}
	return MemcacheUtil::$m->get($key);
}

public static function setItem($key , $value){
	if(!MemcacheUtil::$m){
		MemcacheUtil::connect();
	}
	MemcacheUtil::$m->set($key , $value,  3600);
}

}

?>

