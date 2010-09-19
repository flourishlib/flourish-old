<?php
require_once('./support/init.php');
 
class fURLTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{	
		
	}
	
	public static function makeFriendlyProvider()
	{
		$output = array();
		
		$output[] = array('This is a test', NULL, 'this_is_a_test');
		$output[] = array('Here is some punctuation-and the output!', NULL, 'here_is_some_punctuation-and_the_output');
		$output[] = array("tests of dashes - and under_scores", NULL, 'tests_of_dashes-and_under_scores');
		$output[] = array("Iñtërnâtiônàlizætiøn!", NULL, 'internationalizaetion');
		$output[] = array("test", 2, 'te');
		$output[] = array("this is a test of a really long string to be converted to a url", 36, 'this_is_a_test_of_a_really_long');
		$output[] = array("this is a test of a really long string to be converted to a url", 19, 'this_is_a_test_of_a');
		$output[] = array("Here is a really long string hjdhjkhdksahdiusahdiushdiusahdiusahdiusahdiudhsahusiudsdjsldssa", 80, 'here_is_a_really_long_string_hjdhjkhdksahdiusahdiushdiusahdiusahdiusahdiudhsahus');
		
		return $output;
	}
	
	/**
	 * @dataProvider makeFriendlyProvider
	 */
	public function testMakeFriendly($input, $max_length, $output)
	{
		$this->assertEquals($output, fURL::makeFriendly($input, $max_length));
	}
	
	public function tearDown()
	{
		
	}
}