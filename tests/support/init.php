<?php
ob_start();
define('TEST_EXIT_SCRIPT', './support/test_exit.php');
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';
require_once 'PHPUnit/Extensions/OutputTestCase.php';

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


/**
 * This cleans up all class configurations by calling static reset methods
 * 
 * @param  array $ignore_classes  These classes will not be reset
 * @return void
 */
function __reset($ignore_classes=array())
{
	$classes = scandir('../classes/');
	$classes = array_diff($classes, array('.', '..'));
	
	foreach ($classes as $class) {
		$class = str_replace('.php', '', $class);
		if (!class_exists($class, FALSE)) {
			continue;
		}
		if (in_array($class, $ignore_classes)) {
			continue;	
		}
		if (method_exists($class, 'reset')) {
			call_user_func(array($class, 'reset'));	
		}
	}	
}