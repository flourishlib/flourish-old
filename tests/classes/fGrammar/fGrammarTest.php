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
		$output[] = array('user_FirstName', FALSE, 'userFirstName');
		
		
		return $output;
	}
	
	/**
	 * @dataProvider camelizeProvider
	 */
	public function testCamelize($input, $upper, $output)
	{
		$this->assertEquals($output, fGrammar::camelize($input, $upper));
	}
	
	public function testCamelizeCustom()
	{
		fGrammar::addCamelUnderscoreRule('3rdParty', '3rd_party');
		$this->assertEquals('3rdParty', fGrammar::camelize('3rd_party', TRUE));
	}
	
	public static function singularizePluralizeProvider()
	{
		$output = array();
		
		$output[] = array('boats', 'boat');
		$output[] = array('young children', 'young child');
		$output[] = array('WebPages', 'WebPage');
		$output[] = array('domain_names', 'domain_name');
		$output[] = array('Model_Users', 'Model_User');
		$output[] = array('Model_People', 'Model_Person');
		$output[] = array('videos', 'video');
		$output[] = array('licenses', 'license');
		$output[] = array('lice', 'louse');
		$output[] = array('mice', 'mouse');
		$output[] = array('amices', 'amice');
		
		return $output;
	}
	
	/**
	 * @dataProvider singularizePluralizeProvider
	 */
	public function testPluralizer($output, $input)
	{
		$this->assertEquals($output, fGrammar::pluralize($input));
	}
	
	/**
	 * @dataProvider singularizePluralizeProvider
	 */
	public function testSingularize($input, $output)
	{
		$this->assertEquals($output, fGrammar::singularize($input));
	}
	
	public static function underscorizeProvider()
	{
		$output = array();
		
		$output[] = array('first_name', 'first_name');
		$output[] = array('first name', 'first_name');
		$output[] = array('FIRST name', 'first_name');
		$output[] = array('first NAME', 'first_name');
		$output[] = array('name', 'name');
		$output[] = array('user_FirstName', 'user_first_name');
		
		
		return $output;
	}
	
	/**
	 * @dataProvider underscorizeProvider
	 */
	public function testUnderscorize($input, $output)
	{
		$this->assertEquals($output, fGrammar::underscorize($input));
	}
	
	public function tearDown()
	{
		fGrammar::reset();
	}
}