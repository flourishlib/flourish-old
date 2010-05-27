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
	
	public function tearDown()
	{
		
	}
}