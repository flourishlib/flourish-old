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
	
	
	public static function eqProvider()
	{
		$output = array();
		
		$output[] = array('12:00am', '11:59pm', FALSE);
		$output[] = array('5:00am', '5:01am', FALSE);
		$output[] = array('5:00am', '5:00am', TRUE);
		$output[] = array('5:00am', '4:59am', FALSE);
		$output[] = array('5:00pm', '5:00am', FALSE);
		$output[] = array(1234567891, 1234567891, TRUE);
		$output[] = array(new fTime('2:46:00 am'), new fTime('2:45:01 am'), FALSE);
		$output[] = array(new fTime('2:45:00 am'), new fTime('2:45:00 am'), TRUE);
		
		return $output;
	}
	
	/**
	 * @dataProvider eqProvider
	 */
	public function testEq($primary, $secondary, $result)
	{
		$time = new fTime($primary);
		$this->assertEquals($result, $time->eq($secondary));	
	}
	
	public static function gtProvider()
	{
		$output = array();
		
		$output[] = array('12:00am', '11:59pm', FALSE);
		$output[] = array('5:00am', '5:01am', FALSE);
		$output[] = array('5:00am', '5:00am', FALSE);
		$output[] = array('5:00am', '4:59am', TRUE);
		$output[] = array('5:00pm', '5:00am', TRUE);
		$output[] = array(1234567892, 1234567891, TRUE);
		$output[] = array(new fTime('2:46:00 am'), new fTime('2:45:01 am'), TRUE);
		$output[] = array(new fTime('2:45:00 am'), new fTime('2:45:00 am'), FALSE);
		
		return $output;
	}
	
	/**
	 * @dataProvider gtProvider
	 */
	public function testGt($primary, $secondary, $result)
	{
		$time = new fTime($primary);
		$this->assertEquals($result, $time->gt($secondary));	
	}
	
	public static function gteProvider()
	{
		$output = array();
		
		$output[] = array('12:00am', '11:59pm', FALSE);
		$output[] = array('5:00am', '5:01am', FALSE);
		$output[] = array('5:00am', '5:00am', TRUE);
		$output[] = array('5:00am', '4:59am', TRUE);
		$output[] = array('5:00pm', '5:00am', TRUE);
		$output[] = array(1234567891, 1234567891, TRUE);
		$output[] = array(new fTime('2:45:00 am'), new fTime('2:45:01 am'), FALSE);
		$output[] = array(new fTime('2:45:00 am'), new fTime('2:45:00 am'), TRUE);
		
		return $output;
	}
	
	/**
	 * @dataProvider gteProvider
	 */
	public function testGte($primary, $secondary, $result)
	{
		$time = new fTime($primary);
		$this->assertEquals($result, $time->gte($secondary));	
	}
	
	public static function ltProvider()
	{
		$output = array();
		
		$output[] = array('12:00am', '11:59pm', TRUE);
		$output[] = array('5:00am', '5:01am', TRUE);
		$output[] = array('5:00am', '5:00am', FALSE);
		$output[] = array('5:00am', '4:59am', FALSE);
		$output[] = array('5:00am', '5:00pm', TRUE);
		$output[] = array(1234567890, 1234567891, TRUE);
		$output[] = array(new fTime('2:45:00 am'), new fTime('2:45:01 am'), TRUE);
		$output[] = array(new fTime('2:45:00 am'), new fTime('2:45:00 am'), FALSE);
		
		return $output;
	}
	
	/**
	 * @dataProvider ltProvider
	 */
	public function testLt($primary, $secondary, $result)
	{
		$time = new fTime($primary);
		$this->assertEquals($result, $time->lt($secondary));	
	}
	
	public static function lteProvider()
	{
		$output = array();
		
		$output[] = array('12:00am', '11:59pm', TRUE);
		$output[] = array('5:00am', '5:01am', TRUE);
		$output[] = array('5:00am', '5:00am', TRUE);
		$output[] = array('5:00am', '4:59am', FALSE);
		$output[] = array('5:00am', '5:00pm', TRUE);
		$output[] = array(1234567890, 1234567891, TRUE);
		$output[] = array(new fTime('2:45:00 am'), new fTime('2:45:01 am'), TRUE);
		$output[] = array(new fTime('2:45:00 am'), new fTime('2:45:00 am'), TRUE);
		
		return $output;
	}
	
	/**
	 * @dataProvider lteProvider
	 */
	public function testLte($primary, $secondary, $result)
	{
		$time = new fTime($primary);
		$this->assertEquals($result, $time->lte($secondary));	
	}
	
	public function tearDown()
	{
		
	}
}