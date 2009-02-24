<?php
require_once('./support/init.php');
 
class fTimeTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{	
			
	}
	
	public static function constructorProvider()
	{
		$output = array();
		
		$output[] = array('now');
		$output[] = array('CURRENT_TIMESTAMP');
		$output[] = array('CURRENT_TIME');
		$output[] = array('today');
		$output[] = array('3 pm');
		$output[] = array('+2 hours');
		$output[] = array('05:00:12');
		$output[] = array('05:00');
		
		return $output;
	}
	
	/**
	 * @dataProvider constructorProvider
	 */
	public function testConstructor($input)
	{
		new fTime($input);	
	}
	
	public static function constructorFailProvider()
	{
		$output = array();
		
		$output[] = array('+now');
		$output[] = array('CURRENT_DATE');
		$output[] = array('44:00:00');
		$output[] = array('red');
		$output[] = array('six past noon time');
		
		return $output;
	}
	
	/**
	 * @dataProvider constructorFailProvider
	 */
	public function testConstructorFail($input)
	{
		$this->setExpectedException('fValidationException');
		new fTime($input);	
	}
	
	public function tearDown()
	{
		
	}
}
?>