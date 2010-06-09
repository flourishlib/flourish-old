<?php
require_once('./support/init.php');

class fMoneyTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{	
		
	}
	
	public static function formatProvider()
	{
		$output = array();
		
		$output[] = array('1', 'USD', FALSE, '$1.00');
		$output[] = array('1', 'USD', TRUE, '$1');
		$output[] = array('1.87', 'USD', TRUE, '$1.87');
		$output[] = array('500000', 'USD', FALSE, '$500,000.00');
		$output[] = array('500000', 'USD', TRUE, '$500,000');
		
		return $output;
	}
	
	/**
	 * @dataProvider formatProvider
	 */
	public function testFormat($amount, $currency, $remove_zero_decimal, $output)
	{
		$money = new fMoney($amount, $currency);
		$this->assertEquals($output, $money->format($remove_zero_decimal));
	}
	
	public function tearDown()
	{
		
	}
}