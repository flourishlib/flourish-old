<?php
include_once './support/constants.php';

define('CACHE_TYPE', 'directory');
function cache_data_store()
{
	if (!file_exists('output/fCache.cache')) {
		mkdir('output/fCache.cache', 0777, TRUE);
	}
	return 'output/fCache.cache';
}