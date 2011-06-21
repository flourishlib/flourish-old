<?php
include_once './support/constants.php';

define('CACHE_TYPE', 'database');
function cache_data_store()
{
	if (!file_exists('fCache')) {
		mkdir('fCache');
	}
	return new fDatabase('sqlite', 'classes/fCache/database_cache.sqlite');
}
function cache_config()
{
	return array(
		'table'        => 'cache_entries',
		'key_column'   => 'key',
		'value_column' => 'value',
		'ttl_column'   => 'expiration_timestamp'
	);
}