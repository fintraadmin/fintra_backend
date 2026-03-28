<?php

class LoggingClass  {

  public function logData($params){

	$headers = array('HTTP_X_FORWARDED_FOR' , 'REQUEST_TIME' , 'HTTP_USER_AGENT' , 'SERVER_NAME');
	foreach ($headers as $h) 
       	{	
		$params[$h] = $_SERVER[$h];
	} 
	error_log('DATA:' . json_encode($params));
  }
	


}

?>


