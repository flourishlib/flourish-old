<?php
require_once('./support/init.php');
 
class fFilesystemTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{	
		
	}
	
	public static function convertToBytesProvider()
	{
		$output = array();
		
		$output[] = array('1 MB', 1048576);
		$output[] = array('55.27m', 57954796);
		$output[] = array(46, 46);
		$output[] = array('1.1k', 1126);
		$output[] = array('1.5 tera bytes', 1649267441664);
		
		return $output;
	}
	
	/**
	 * @dataProvider convertToBytesProvider
	 */
	public function testConvertToBytes($input, $output)
	{
		$this->assertEquals($output, fFilesystem::convertToBytes($input));	
	}
	
	public function tearDown()
	{
		
	}
}