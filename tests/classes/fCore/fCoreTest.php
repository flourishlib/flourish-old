<?php
require_once('./support/init.php');
 
class fCoreTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{	
		fCore::reset();	
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
		$output[] = array('this is a test', TRUE, FALSE, '<pre class="exposed">this is a test</pre>');
		$output[] = array('this is a test', FALSE, TRUE, '<pre class="exposed">this is a test</pre>');
		$output[] = array('this is a test', TRUE, TRUE, '<pre class="exposed">this is a test</pre>');
		$output[] = array(NULL, TRUE, FALSE, '<pre class="exposed">{null}</pre>');
		$output[] = array(TRUE, TRUE, FALSE, '<pre class="exposed">{true}</pre>');
		$output[] = array(FALSE, TRUE, FALSE, '<pre class="exposed">{false}</pre>');
		$output[] = array('', TRUE, FALSE, '<pre class="exposed">{empty_string}</pre>');
		$output[] = array(1, TRUE, FALSE, '<pre class="exposed">1</pre>');
		$output[] = array(array(), TRUE, FALSE, "<pre class=\"exposed\">Array\n(\n)</pre>");
		$output[] = array(new stdClass, TRUE, FALSE, "<pre class=\"exposed\">stdClass Object\n(\n)</pre>");
		
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
		
		$this->assertEquals($expected_output, $output);
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
		
		$output[] = array('this is a test', '<pre class="exposed">this is a test</pre>');
		$output[] = array(NULL, '<pre class="exposed">{null}</pre>');
		$output[] = array(TRUE, '<pre class="exposed">{true}</pre>');
		$output[] = array(FALSE, '<pre class="exposed">{false}</pre>');
		$output[] = array('', '<pre class="exposed">{empty_string}</pre>');
		$output[] = array(1, '<pre class="exposed">1</pre>');
		$output[] = array(array(), "<pre class=\"exposed\">Array\n(\n)</pre>");
		$output[] = array(new stdClass, "<pre class=\"exposed\">stdClass Object\n(\n)</pre>");
		
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
		
		$this->assertEquals($expected_output, $output);
	}
	
	public function tearDown()
	{
			
	}
}