<?php
include_once './support/constants.php';

define('CACHE_TYPE', 'memcache');
function cache_data_store()
{
	$memcached = new Memcached();
	$memcached->addServer('db.flourishlib.com', 11211);
	return $memcached;	
}