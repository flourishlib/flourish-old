<?php
define('CACHE_TYPE', 'memcache');
function cache_data_store()
{
	$memcache = new Memcache();
	$memcache->connect('db.flourishlib.com', 11211);
	return $memcache;	
}