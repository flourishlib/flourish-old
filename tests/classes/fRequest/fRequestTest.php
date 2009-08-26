<?php
require_once('./support/init.php');
 
class fRequestTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{	
		$_GET     = array();
		$_POST    = array();
		$_REQUEST = array();	
	}
	
	public function testGetMissingField()
	{
		$this->assertEquals(NULL, fRequest::get('test'));
	}
	
	public function testGetBlankField()
	{
		$_GET['test'] = '';
		$this->assertEquals(NULL, fRequest::get('test'));
	}
	
	public function testGetBlankField2()
	{
		$_POST['test'] = '';
		$this->assertEquals(NULL, fRequest::get('test'));
	}
	
	public function testGetBlankFieldCastString()
	{
		$_GET['test'] = '';
		$this->assertEquals('', fRequest::get('test', 'string'));
	}
	
	public function testGetBlankFieldCastBoolean()
	{
		$_GET['test'] = '';
		$this->assertEquals(FALSE, fRequest::get('test', 'boolean'));
	}
	
	public function testGetBlankFieldCastInteger()
	{
		$_GET['test'] = '';
		$this->assertEquals(0, fRequest::get('test', 'integer'));
	}
	
	public function testGetBlankFieldCastArray()
	{
		$_GET['test'] = '';
		$this->assertEquals(array(), fRequest::get('test', 'array'));
	}
	
	public function testGetBlankFieldCastDate()
	{
		$_GET['test'] = '';
		$this->assertEquals(TRUE, fRequest::get('test', 'date')->eq());
	}
	
	public function testGetBlankFieldCastTime()
	{
		$_GET['test'] = '';
		$this->assertEquals(TRUE, fRequest::get('test', 'time')->eq());
	}
	
	public function testGetBlankFieldCastTimestamp()
	{
		$_GET['test'] = '';
		$this->assertEquals(TRUE, fRequest::get('test', 'timestamp')->eq());
	}
	
	public function testGetMissingFieldCastString()
	{
		$this->assertEquals('', fRequest::get('test', 'string'));
	}
	
	public function testGetMissingFieldCastBoolean()
	{
		$this->assertEquals(FALSE, fRequest::get('test', 'boolean'));
	}
	
	public function testGetMissingFieldCastInteger()
	{
		$this->assertEquals(0, fRequest::get('test', 'integer'));
	}
	
	public function testGetMissingFieldCastArray()
	{
		$this->assertEquals(array(), fRequest::get('test', 'array'));
	}
	
	public function testGetMissingFieldCastDate()
	{
		$this->assertEquals(TRUE, fRequest::get('test', 'date')->eq());
	}
	
	public function testGetMissingFieldCastTime()
	{
		$this->assertEquals(TRUE, fRequest::get('test', 'time')->eq());
	}
	
	public function testGetMissingFieldCastTimestamp()
	{
		$this->assertEquals(TRUE, fRequest::get('test', 'timestamp')->eq());
	}
	
	public function testGetMissingFieldCastStringQuestion()
	{
		$this->assertEquals(NULL, fRequest::get('test', 'string?'));
	}
	
	public function testGetMissingFieldCastBooleanQuestion()
	{
		$this->assertEquals(NULL, fRequest::get('test', 'boolean?'));
	}
	
	public function testGetMissingFieldCastIntegerQuestion()
	{
		$this->assertEquals(NULL, fRequest::get('test', 'integer?'));
	}
	
	public function testGetMissingFieldCastArrayQuestion()
	{
		$this->assertEquals(NULL, fRequest::get('test', 'array?'));
	}
	
	public function testGetMissingFieldCastDateQuestion()
	{
		$this->assertEquals(NULL, fRequest::get('test', 'date?'));
	}
	
	public function testGetMissingFieldCastTimeQuestion()
	{
		$this->assertEquals(NULL, fRequest::get('test', 'time?'));
	}
	
	public function testGetMissingFieldCastTimestampQuestion()
	{
		$this->assertEquals(NULL, fRequest::get('test', 'timestamp?'));
	}
	
	public function testGetBlankFieldCastStringQuestion()
	{
		$_GET['test'] = '';
		$this->assertEquals(NULL, fRequest::get('test', 'string?'));
	}
	
	public function testGetBlankFieldCastBooleanQuestion()
	{
		$_GET['test'] = '';
		$this->assertEquals(NULL, fRequest::get('test', 'boolean?'));
	}
	
	public function testGetBlankFieldCastIntegerQuestion()
	{
		$_GET['test'] = '';
		$this->assertEquals(NULL, fRequest::get('test', 'integer?'));
	}
	
	public function testGetBlankFieldCastArrayQuestion()
	{
		$_GET['test'] = '';
		$this->assertEquals(NULL, fRequest::get('test', 'array?'));
	}
	
	public function testGetBlankFieldCastDateQuestion()
	{
		$_GET['test'] = '';
		$this->assertEquals(NULL, fRequest::get('test', 'date?'));
	}
	
	public function testGetBlankFieldCastTimeQuestion()
	{
		$_GET['test'] = '';
		$this->assertEquals(NULL, fRequest::get('test', 'time?'));
	}
	
	public function testGetBlankFieldCastTimestampQuestion()
	{
		$_GET['test'] = '';
		$this->assertEquals(NULL, fRequest::get('test', 'timestamp?'));
	}
	
	public function testGetFieldCastStringQuestion()
	{
		$_GET['test'] = 'test';
		$this->assertEquals('test', fRequest::get('test', 'string?'));
	}
	
	public function testGetFieldCastBooleanQuestion()
	{
		$_GET['test'] = 'true';
		$this->assertEquals(TRUE, fRequest::get('test', 'boolean?'));
	}
	
	public function testGetFieldCastIntegerQuestion()
	{
		$_GET['test'] = '10a';
		$this->assertEquals(10, fRequest::get('test', 'integer?'));
	}
	
	public function testGetFieldCastArrayQuestion()
	{
		$_GET['test'] = array(1,2);
		$this->assertEquals(array(1,2), fRequest::get('test', 'array?'));
	}
	
	public function testGetFieldCastDateQuestion()
	{
		$_GET['test'] = '5/7/2009';
		$this->assertEquals(TRUE, fRequest::get('test', 'date?')->eq('5/7/2009'));
	}
	
	public function testGetFieldCastTimeQuestion()
	{
		$_GET['test'] = '5 am';
		$this->assertEquals(TRUE, fRequest::get('test', 'time?')->eq('5 am'));
	}
	
	public function testGetFieldCastTimestampQuestion()
	{
		$_GET['test'] = '5/7/2009 5 am';
		$this->assertEquals(TRUE, fRequest::get('test', 'timestamp?')->eq('5/7/2009 5 am'));
	}
	
	public function testGetCastArrayFromCommas()
	{
		$_GET['test'] = 'one,two,three';
		$this->assertEquals(array('one', 'two', 'three'), fRequest::get('test', 'array'));
	}
	
	public function testGetCastBooleanFromWord()
	{
		$_GET['test'] = 'f';
		$this->assertEquals(FALSE, fRequest::get('test', 'boolean'));
	}
	
	public function testGetCastBooleanFromWord2()
	{
		$_GET['test'] = 'false';
		$this->assertEquals(FALSE, fRequest::get('test', 'boolean'));
	}
	
	public function testGetCastBooleanFromWord3()
	{
		$_GET['test'] = 'no';
		$this->assertEquals(FALSE, fRequest::get('test', 'boolean'));
	}
	
	public function testGetPostOverGet()
	{
		$_GET['test'] = 'get';
		$_POST['test'] = 'post';
		$this->assertEquals('post', fRequest::get('test'));
	}
	
	public function tearDown()
	{
			
	}
}