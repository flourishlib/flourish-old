<?php
require_once('./support/init.php');
 
class fTextTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{	
		
	}
	
	public function testCompose()
	{
		$this->assertEquals(
			'This is a string and a 2',
			fText::compose('This is %s and a %d', 'a string', 2)
		);
		
		$this->assertEquals(
			'This is a string',
			fText::compose('This is %s', 'a string')
		);
		
		$this->assertEquals(
			'This is a 2',
			fText::compose('This is a %d', 2)
		);	
	}
	
	public function testComposeArray()
	{
		$this->assertEquals(
			'This is a string and a 2',
			fText::compose('This is %s and a %d', array('a string', 2))
		);
		
		$this->assertEquals(
			'This is a string',
			fText::compose('This is %s', array('a string'))
		);
		
		$this->assertEquals(
			'This is a 2',
			fText::compose('This is a %d', array(2))
		);	
	}
	
	public function tearDown()
	{
		
	}
}