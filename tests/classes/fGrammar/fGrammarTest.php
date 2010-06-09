<?php
require_once('./support/init.php');

class fGrammarTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{	
		
	}
	
	public function testStem()
	{
		$input_handle  = fopen('./resources/words/input.txt', 'r');
		$output_handle = fopen('./resources/words/output.txt', 'r');
		while ($in_word = trim(fgets($input_handle))) {
			$out_word = trim(fgets($output_handle));
			$this->assertEquals($out_word, fGrammar::stem($in_word));
		}
	}
	
	public static function camelizeProvider()
	{
		$output = array();
		
		$output[] = array('first_name', TRUE, 'FirstName');
		$output[] = array('first_name', FALSE, 'firstName');
		$output[] = array('first name', TRUE, 'FirstName');
		$output[] = array('first name', FALSE, 'firstName');
		$output[] = array('FIRST name', TRUE, 'FirstName');
		$output[] = array('first NAME', FALSE, 'firstName');
		$output[] = array('name', FALSE, 'name');
		
		return $output;
	}
	
	/**
	 * @dataProvider camelizeProvider
	 */
	public function testCamelize($input, $upper, $output)
	{
		$this->assertEquals($output, fGrammar::camelize($input, $upper));
	}
	
	public function tearDown()
	{
		
	}
}