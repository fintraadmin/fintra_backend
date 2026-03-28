<?php

require_once '../utils/memcache.php';

MemcacheUtil::setItem('hi' , 'hello');
echo MemcacheUtil::getItem('hi');
