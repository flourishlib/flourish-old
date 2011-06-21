<?php
include_once './support/constants.php';

define('CACHE_TYPE', 'file');
function cache_data_store()
{
	return 'classes/fCache/file.cache';
}