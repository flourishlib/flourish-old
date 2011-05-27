<?php
require_once('./support/init.php');
 
class fCoreTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{	
		
	}
	
	public function testBacktrace()
	{
		$this->assertRegExp('#\{doc_root\}(/|\\\\)classes(/|\\\\)fCore(/|\\\\)fCoreTest.php\(\d+\): fCore::backtrace\(\)#', fCore::backtrace());
	}
	
	public function testCall()
	{
		$this->assertEquals('testing', fCore::call('substr', array('testing', 0)));
		$this->assertEquals('testing', fCore::call('substr', 'testing', 0));
		$this->assertEquals('test', fCore::call('substr', array('testing', 0, 4)));
		$this->assertEquals('test', fCore::call('substr', 'testing', 0, 4));
	}
	
	public function testCallback()
	{
		$this->assertEquals('substr',  fCore::callback('substr'));
		$this->assertEquals('testing', fCore::callback('testing'));
		$this->assertEquals(array('Class', 'method'), fCore::callback(array('Class', 'method')));
		$this->assertEquals(array('Class', 'method'), fCore::callback('Class::method'));
		$this->assertEquals(array('OtherClass', 'method2'), fCore::callback('OtherClass::method2'));
	}
	
	public function testCapture()
	{
		fCore::startErrorCapture();
		echo $invalid_var;
		$errors = fCore::stopErrorCapture();
		$this->assertEquals(1, count($errors));
		$this->assertEquals('Undefined variable: invalid_var', $errors[0]['string']);
	}

	public function testNestedCapture()
	{
		fCore::startErrorCapture();
		echo $invalid_var;

		fCore::startErrorCapture();
		echo $invalid_var_2;
		
		$errors = fCore::stopErrorCapture();
		$this->assertEquals(1, count($errors));
		$this->assertEquals('Undefined variable: invalid_var_2', $errors[0]['string']);

		$errors = fCore::stopErrorCapture();
		$this->assertEquals(1, count($errors));
		$this->assertEquals('Undefined variable: invalid_var', $errors[0]['string']);
	}
	
	public function testDebugCallback()
	{
		fCore::registerDebugCallback(create_function('$message', 'echo strtoupper($message);'));
		fCore::enableDebugging(TRUE);
		
		ob_start();
		fCore::debug('This is a test', FALSE);
		$output = ob_get_clean();
		
		$this->assertEquals('THIS IS A TEST', $output);
	}
	
	public static function debugProvider()
	{
		$output = array();
		
		$output[] = array(NULL, FALSE, FALSE, '');
		$output[] = array('this is a test', FALSE, FALSE, '');
		$output[] = array('this is a test', TRUE, FALSE, 'this is a test');
		$output[] = array('this is a test', FALSE, TRUE, 'this is a test');
		$output[] = array('this is a test', TRUE, TRUE, 'this is a test');
		$output[] = array(NULL, TRUE, FALSE, '{null}');
		$output[] = array(TRUE, TRUE, FALSE, '{true}');
		$output[] = array(FALSE, TRUE, FALSE, '{false}');
		$output[] = array('', TRUE, FALSE, '{empty_string}');
		$output[] = array(1, TRUE, FALSE, '1');
		$output[] = array(array(), TRUE, FALSE, "Array\n(\n)");
		$output[] = array(new stdClass, TRUE, FALSE, "stdClass Object\n(\n)");
		
		return $output;
	}
	
	/**
	 * @dataProvider debugProvider
	 */
	public function testDebug($value, $force, $global, $expected_output)
	{
		ob_start();
		if ($global) {
			fCore::enableDebugging(TRUE);	
		}
		fCore::debug($value, $force);
		$output = ob_get_clean();
		
		$this->assertEquals($expected_output, rtrim($output, "\n"));
	}
	
	public static function dumpProvider()
	{
		$output = array();
		
		$output[] = array(NULL, '{null}');
		$output[] = array('this is a test', 'this is a test');
		$output[] = array(TRUE, '{true}');
		$output[] = array(FALSE, '{false}');
		$output[] = array('', '{empty_string}');
		$output[] = array(1, '1');
		$output[] = array(array(), "Array\n(\n)");
		$output[] = array(array(1,2,3), "Array\n(\n    [0] => 1\n    [1] => 2\n    [2] => 3\n)");
		$output[] = array(new stdClass, "stdClass Object\n(\n)");
		
		return $output;
	}
	
	/**
	 * @dataProvider dumpProvider
	 */
	public function testDump($value, $output)
	{
		$this->assertEquals($output, fCore::dump($value));
	}
	
	public static function exposeProvider()
	{
		$output = array();
		
		$output[] = array('this is a test', 'this is a test');
		$output[] = array(NULL, '{null}');
		$output[] = array(TRUE, '{true}');
		$output[] = array(FALSE, '{false}');
		$output[] = array('', '{empty_string}');
		$output[] = array(1, '1');
		$output[] = array(array(), "Array\n(\n)");
		$output[] = array(new stdClass, "stdClass Object\n(\n)");
		
		return $output;
	}
	
	/**
	 * @dataProvider exposeProvider
	 */
	public function testExpose($value, $expected_output)
	{
		ob_start();
		fCore::expose($value);
		$output = ob_get_clean();
		
		$this->assertEquals($expected_output, rtrim($output, "\n"));
	}
	
	public function testExposeMultiple()
	{
		ob_start();
		fCore::expose('string', TRUE);
		$output = ob_get_clean();
		
		$this->assertEquals("Array\n(\n    [0] => string\n    [1] => {true}\n)\n", $output);
	}
	
	public function testHandleError()
	{
		error_reporting(E_ALL | E_STRICT);
		ob_start();
		fCore::enableErrorHandling('html');
		echo $undefined_var;
		$output = ob_get_clean();
		$this->assertEquals(TRUE, strlen($output) > 0);
	}
	
	public function testHandleErrorCapture()
	{
		error_reporting(E_ALL | E_STRICT);
		fCore::enableErrorHandling('html');
		fCore::startErrorCapture();
		echo $undefined_var;
		$errors = fCore::stopErrorCapture();
		$this->assertEquals(1, count($errors));
	}
	
	public function testHandleErrorCaptureType()
	{
		error_reporting(E_ALL | E_STRICT);
		ob_start();
		fCore::enableErrorHandling('html');
		fCore::startErrorCapture(E_NOTICE);
		echo $undefined_var;
		$errors = fCore::stopErrorCapture();
		$output = ob_get_clean();
		$this->assertEquals(1, count($errors));
		$this->assertEquals(TRUE, strlen($output) == 0);
	}
	
	public function testHandleErrorCaptureTypeIncorrect()
	{
		error_reporting(E_ALL | E_STRICT);
		ob_start();
		fCore::enableErrorHandling('html');
		fCore::startErrorCapture(E_ERROR);
		echo $undefined_var;
		$errors = fCore::stopErrorCapture();
		$output = ob_get_clean();
		$this->assertEquals(0, count($errors));
		$this->assertEquals(TRUE, strlen($output) > 0);
	}
	
	public function testHandleErrorCaptureTypeIncorrectPreviousHandler()
	{
		$this->setExpectedException('PHPUnit_Framework_Error_Notice');
		error_reporting(E_ALL | E_STRICT);
		fCore::startErrorCapture(E_ERROR);
		echo $undefined_var;
		$errors = fCore::stopErrorCapture();
	}
	
	public function testHandleErrorCapturePattern()
	{
		error_reporting(E_ALL | E_STRICT);
		ob_start();
		fCore::enableErrorHandling('html');
		fCore::startErrorCapture(E_NOTICE, '#print_r#');
		echo $print_r;
		echo $undefined_var;
		print_r();
		$errors = fCore::stopErrorCapture();
		$output = ob_get_clean();
		$this->assertEquals(1, count($errors));
		$this->assertEquals(TRUE, strlen($output) > 0);
	}
	
	public function tearDown()
	{
		fCore::reset();
	}
}