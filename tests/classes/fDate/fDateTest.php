<?php
require_once('./support/init.php');
 
class fDateTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{	
			
	}
	
	public static function constructorProvider()
	{
		$output = array();
		
		$output[] = array('now');
		$output[] = array('CURRENT_TIMESTAMP');
		$output[] = array('CURRENT_DATE');
		$output[] = array('today');
		$output[] = array('3 pm');
		$output[] = array('+2 hours');
		$output[] = array('05:00:12');
		$output[] = array('05:00');
		$output[] = array('Jan 1st, 2009');
		$output[] = array('2/5/2008');
		$output[] = array('2008-05-06');
		$output[] = array('2009-01-01 5:00 am');
		$output[] = array('5:00 am 2008-11-25');
		
		return $output;
	}
	
	/**
	 * @dataProvider constructorProvider
	 */
	public function testConstructor($input)
	{
		new fDate($input);	
	}
	
	public static function constructorFailProvider()
	{
		$output = array();
		
		$output[] = array('+now');
		$output[] = array('CURRENT_TIME'); 
		$output[] = array('2008-01-01 11:00:00.907 AM');
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
		new fDate($input);	
	}
	
	
	public static function eqProvider()
	{
		$output = array();
		
		$output[] = array('1/1/2009', '1/1/2009', TRUE);
		$output[] = array('1/2/2009', '1/1/2009', FALSE);
		$output[] = array('5/1/2008', '5/1/2009', FALSE);
		$output[] = array('June 1st, 2009', '6/1/2009', TRUE);
		$output[] = array(1234567890, 1234567891, TRUE);
		$output[] = array('today', new fDate('today'), TRUE);
		
		return $output;
	}
	
	/**
	 * @dataProvider eqProvider
	 */
	public function testEq($primary, $secondary, $result)
	{
		$date = new fDate($primary);
		$this->assertEquals($result, $date->eq($secondary));	
	}
	
	public static function gtProvider()
	{
		$output = array();
		
		$output[] = array('1/1/2009', '1/1/2009', FALSE);
		$output[] = array('1/2/2009', '1/1/2009', TRUE);
		$output[] = array('5/1/2008', '5/1/2009', FALSE);
		$output[] = array('June 2nd, 2009', '6/1/2009', TRUE);
		$output[] = array('tomorrow', new fDate('today'), TRUE);
		
		return $output;
	}
	
	/**
	 * @dataProvider gtProvider
	 */
	public function testGt($primary, $secondary, $result)
	{
		$date = new fDate($primary);
		$this->assertEquals($result, $date->gt($secondary));	
	}
	
	public static function gteProvider()
	{
		$output = array();
		
		$output[] = array('1/1/2009', '1/1/2009', TRUE);
		$output[] = array('1/2/2009', '1/1/2009', TRUE);
		$output[] = array('5/1/2008', '5/1/2009', FALSE);
		$output[] = array('June 2nd, 2009', '6/1/2009', TRUE);
		$output[] = array('tomorrow', new fDate('today'), TRUE);
		
		return $output;
	}
	
	/**
	 * @dataProvider gteProvider
	 */
	public function testGte($primary, $secondary, $result)
	{
		$date = new fDate($primary);
		$this->assertEquals($result, $date->gte($secondary));	
	}
	
	public static function ltProvider()
	{
		$output = array();
		
		$output[] = array('1/1/2009', '1/1/2009', FALSE);
		$output[] = array('1/2/2009', '1/1/2009', FALSE);
		$output[] = array('5/1/2008', '5/1/2009', TRUE);
		$output[] = array('May 20th, 2009', '6/1/2009', TRUE);
		$output[] = array('yesterday', new fDate('today'), TRUE);
		
		return $output;
	}
	
	/**
	 * @dataProvider ltProvider
	 */
	public function testLt($primary, $secondary, $result)
	{
		$date = new fDate($primary);
		$this->assertEquals($result, $date->lt($secondary));	
	}
	
	public static function lteProvider()
	{
		$output = array();
		
		$output[] = array('1/1/2009', '1/1/2009', TRUE);
		$output[] = array('1/2/2009', '1/1/2009', FALSE);
		$output[] = array('5/1/2008', '5/1/2009', TRUE);
		$output[] = array('May 20th, 2009', '6/1/2009', TRUE);
		$output[] = array('yesterday', new fDate('today'), TRUE);
		
		return $output;
	}
	
	/**
	 * @dataProvider lteProvider
	 */
	public function testLte($primary, $secondary, $result)
	{
		$date = new fDate($primary);
		$this->assertEquals($result, $date->lte($secondary));	
	}
	
	public function tearDown()
	{
		
	}
}