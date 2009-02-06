<?php
require_once('./support/init.php');
 
class fExpectedExceptionTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{	
		
	}
	
	public static function throwPercentsProvider()
	{
		$output = array();
		
		$output[] = array('This is a test of using a % in the middle of a message.');
		$output[] = array('This tests putting % 2 in the middle of a message.');
		$output[] = array('%% is ok also');
		$output[] = array('Messages that don\'t contain a percent sign are fine too!');  
		
		return $output;
	}
	
	/**
	 * @dataProvider throwPercentsProvider
	 */
	public function testThrowPercents($message)
	{
		$this->setExpectedException('fExpectedException');
		
		try {
			throw new fExpectedException($message);
			
		} catch (fExpectedException $e) {
			$this->assertContains(
				str_replace('%%', '%', $message),
				$e->getMessage()
			);
			throw $e;	
		}
	}
	
	public function testThrow()
	{
		$this->setExpectedException('fExpectedException');
		
		try {
			throw new fExpectedException('This is a test of adding %s', 'a string');
			
		} catch (fExpectedException $e) {
			$this->assertContains('This is a test of adding a string', $e->getMessage());
			throw $e;	
		}
	}
	
	public function testThrow2()
	{
		$this->setExpectedException('fExpectedException');
		
		try {
			throw new fExpectedException('This is a test of adding %s %d %s', 'a string', 2, 'another string');
			
		} catch (fExpectedException $e) {
			$this->assertContains('a string 2 another string', $e->getMessage());
			throw $e;	
		}
	}
	
	public function testThrowNoComponent()
	{
		$this->setExpectedException('Exception');
		
		throw new fExpectedException('This is a test of adding %s');
	}
	
	public function testThrowNoComponent2()
	{
		$this->setExpectedException('Exception');
		
		throw new fExpectedException('This is a test of adding %\'.d');
	}
	
	public function tearDown()
	{
			
	}
}