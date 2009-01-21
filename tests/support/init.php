<?php
ob_start();
define('TEST_EXIT_SCRIPT', './support/test_exit.php');
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

$_SERVER['SERVER_NAME'] = 'example.com';
$_SERVER['REQUEST_URI'] = '/index.php';

if (empty($_SERVER['DOCUMENT_ROOT'])) {
	$_SERVER['DOCUMENT_ROOT'] = realpath(dirname(__FILE__) . '/../');	
}

function __autoload($class_name)
{
	$file = '../classes/' . $class_name . '.php';
	if (file_exists($file)) {
		require_once($file);
		return;
	}        
	
	die('The class ' . $class_name . ' could not be loaded');
}
?>