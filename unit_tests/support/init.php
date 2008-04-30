<?php
ob_start();
define('TEST_EXIT_SCRIPT', './support/test_exit.php');
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

$_SERVER['SERVER_NAME'] = 'example.com';
$_SERVER['REQUEST_URI'] = '/index.php';

function __autoload($class_name)
{
	// Customize this to your root Flourish directory
	$flourish_root = '../classes/';
	
	$sub_dirs = array(
		'database/',
		'database/object_relational_mapping/',
		'datetime/',
		'ecommerce/',
		'exceptions/',
		'filesystem/',
		'request/',
		'response/',
		'security/',
		'session/',
		'utility/'
	);
	
	$file = $class_name . '.php';
	
	foreach ($sub_dirs as $sub_dir) {
		if (file_exists($flourish_root . $sub_dir . $file)) {
			require_once($flourish_root . $sub_dir . $file);
			return;
		}        
	}
	
	die('The class ' . $class_name . ' could not be loaded');
}
?>