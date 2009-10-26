<?php
require_once('./support/init.php');
 
class fSessionTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{	
			
	}
	
	public function testOpen()
	{
		fSession::open();
		$this->assertEquals(TRUE, strlen(session_id()) > 0);
	}
	
	public function testOpenAlreadyOpen()
	{
		session_start();
		fSession::open();
		$this->assertEquals(TRUE, strlen(session_id()) > 0);
	}
	
	public function testClose()
	{
		fSession::open();
		$this->assertEquals(TRUE, strlen(session_id()) > 0);
		fSession::close();
		$this->assertEquals(FALSE, isset($_SESSION));
	}
	
	public function testCloseAndOpen()
	{
		fSession::open();
		$_SESSION['close_open'] = 'close_open';
		fSession::close();
		fSession::open();
		$this->assertEquals('close_open', $_SESSION['close_open']);
	}
	
	public function testSet()
	{
		fSession::open();
		fSession::set('test', 'value');
		$this->assertEquals('value', $_SESSION['test']);
	}
	
	public function testAdd()
	{
		fSession::open();
		fSession::add('array', 'value');
		$this->assertEquals(array('value'), $_SESSION['array']);
	}
	
	public function testAddMultiple()
	{
		fSession::open();
		fSession::add('array', 'value');
		fSession::add('array', 'value2');
		$this->assertEquals(array('value', 'value2'), $_SESSION['array']);
	}
	
	public function testAddToNonArray()
	{
		$this->setExpectedException('fProgrammerException');
		
		fSession::open();
		fSession::set('non_array', 'value');
		fSession::add('non_array', 'value2');
	}
	
	public function testGet()
	{
		fSession::open();
		$_SESSION['test2'] = 'value2';
		$this->assertEquals('value2', fSession::get('test2'));
	}
	
	public function testGetNoValue()
	{
		fSession::open();
		$this->assertEquals(NULL, fSession::get('key'));
	}
	
	public function testGetNoValueDefault()
	{
		fSession::open();
		$this->assertEquals('default', fSession::get('key', 'default'));
	}
	
	public function testDelete()
	{
		fSession::open();
		$_SESSION['delete'] = TRUE;
		fSession::delete('delete');
		$this->assertEquals(FALSE, isset($_SESSION['delete']));
	}
	
	public function testClear()
	{
		fSession::open();
		$_SESSION['delete']      = 1;
		$_SESSION['delete_3']    = 2;
		$_SESSION['delete_test'] = 3;
		$_SESSION['non_delete']  = 4;
		fSession::clear('delete');
		$this->assertEquals(array('fSession::type', 'fSession::expires', 'non_delete'), array_keys($_SESSION));
	}
	
	public function tearDown()
	{
		fSession::reset();
	}
}