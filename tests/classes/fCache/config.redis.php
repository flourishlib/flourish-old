<?php
include_once './support/constants.php';

define('CACHE_TYPE', 'redis');
function cache_data_store()
{
	$redis = new Redis();
	$redis->connect('db.flourishlib.com');
	return $redis;	
}