<?php
require 'lib/vendor/autoload.php';
require 'lib/vendor/conf.ini';

use Aws\Common\Credentials\Credentials;
use Aws\Sqs\SqsClient;


class SQS  {

	public $client ;
	public function __construct(){
  		$credentials = new Credentials(ACCESS_KEY, SECRET_KEY);		
  		$this->client = SqsClient::factory(array(
  			'profile' => 'default',
  			'region'  => 'ap-south-1',
  			'credentials' => $credentials
		));
	}

	public function sendMsg($msg){
		$this->client->sendMessage(array(
    			'QueueUrl'    => 'https://sqs.ap-south-1.amazonaws.com/213501160302/actionsQueue',
    			'MessageBody' => $msg,
		));
	
	}
}

