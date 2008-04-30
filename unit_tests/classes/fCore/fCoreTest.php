<?php
require_once('./support/init.php');
 
class fCoreTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{	
		
	}
	
	public function testToss()
	{
		$this->setExpectedException('fProgrammerException');
		
		fCore::toss('fProgrammerException', 'This is a test');
	}
	
	public function tearDown()
	{
		
	}
}
?>