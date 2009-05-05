<?php
ob_start();
define('TEST_EXIT_SCRIPT', './support/test_exit.php');
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

date_default_timezone_set('America/New_York');
error_reporting(E_ALL | E_STRICT);

$_SERVER['SERVER_NAME'] = 'example.com';
$_SERVER['REQUEST_URI'] = '/index.php';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SERVER_PORT'] = 80;

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