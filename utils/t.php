<?php
set_include_path(get_include_path() . PATH_SEPARATOR . '/var/www/html/');
include 'utils/utils.php';


$uid =  Utils::gen_uuid() ;
$token = Utils::encodeJWT(array('id' => $uid));
echo $token."\n";
if(Utils::decodeJWT($token))
	echo "success\n";
?> 
