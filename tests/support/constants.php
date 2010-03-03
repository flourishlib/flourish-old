<?php
if (strpos(ini_get('user_dir'), ':') === FALSE) {
	return;	
}

$pairs = explode(',', ini_get('user_dir'));
foreach ($pairs as $pair) {
	list ($name, $value) = explode(':', $pair);
	define($name, $value);	
}