<?php
set_include_path(get_include_path() . PATH_SEPARATOR . '/var/www/html/');
include_once 'apis/services/AWSQueueService.php';
$s =  new SQS();
$msg =  array();
$msg['uuid'] = 'uuid3';
$msg['type'] = 'view';
$msg['itemid'] = 'fact:1';

$s->sendMsg(json_encode($msg));
