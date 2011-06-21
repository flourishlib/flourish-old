<?php
require_once('./support/init.php');
 
class fSessionTest extends PHPUnit_Framework_TestCase
{
	static public $cache;

	public static function setUpBeforeClass()
	{
		if (defined('SKIPPING')) {
			return;	
		}
		if (defined('CACHE_TYPE') && function_exists('cache_data_store')) {
			self::$cache = new fCache(CACHE_TYPE, cache_data_store(), array('serializer' => 'string', 'unserializer' => 'string'));
		}
	}

	public static function tearDownAfterClass()
	{
		if (defined('SKIPPING')) {
			return;	
		}
		if (isset(self::$cache)) {
			self::$cache->clear();
		}
	}

	public function setUp()
	{	
		if (defined('SKIPPING')) {
			$this->markTestSkipped();
		} elseif (isset(self::$cache)) {
			fSession::setBackend(
				self::$cache,
				'fSession::'
			);
		}
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
	
	public function testSetArrayKey()
	{
		fSession::open();
		fSession::set('test[foo]', 'value');
		$this->assertEquals(array('foo' => 'value'), $_SESSION['test']);
	}
	
	public function testSetNestedArrayKey()
	{
		fSession::open();
		fSession::set('test[foo][bar]', 'value');
		$this->assertEquals(array('foo' => array('bar' => 'value')), $_SESSION['test']);
	}
	
	public function testAdd()
	{
		fSession::open();
		fSession::add('array', 'value');
		$this->assertEquals(array('value'), $_SESSION['array']);
	}
	
	public function testAddBeginning()
	{
		fSession::open();
		fSession::add('array', 'value');
		fSession::add('array', 'value2', TRUE);
		$this->assertEquals(array('value2', 'value'), $_SESSION['array']);
	}
	
	public function testAddArraySyntax()
	{
		fSession::open();
		fSession::add('array[foo]', 'value');
		$this->assertEquals(array('foo' => array('value')), $_SESSION['array']);
	}
	
	public function testAddNestedArraySyntax()
	{
		fSession::open();
		fSession::add('array[foo][bar]', 'value');
		$this->assertEquals(array('foo' => array('bar' => array('value'))), $_SESSION['array']);
		fSession::add('array[foo][bar]', 'value2');
		$this->assertEquals(array('foo' => array('bar' => array('value', 'value2'))), $_SESSION['array']);
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
	
	public function testGetArraySyntax()
	{
		fSession::open();
		$_SESSION['test2'] = array('foo' => 'value2');
		$this->assertEquals('value2', fSession::get('test2[foo]'));
	}
	
	public function testGetNestedArraySyntax()
	{
		fSession::open();
		$_SESSION['test2'] = array('foo' => array('bar' => 'value2', 'baz' => 'value3'));
		$this->assertEquals('value3', fSession::get('test2[foo][baz]'));
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
		$this->assertEquals(TRUE, fSession::delete('delete'));
		$this->assertEquals(FALSE, isset($_SESSION['delete']));
	}
	
	public function testDeleteDefault()
	{
		fSession::open();
		$_SESSION['delete2'] = TRUE;
		$this->assertEquals(FALSE, fSession::delete('delete', FALSE));
	}
	
	public function testDeleteArraySyntax()
	{
		fSession::open();
		$_SESSION['delete'] = array('foo' => 'bar');
		$this->assertEquals('bar', fSession::delete('delete[foo]'));
		$this->assertEquals(array(), $_SESSION['delete']);
	}
	
	public function testDeleteNestedArraySyntax()
	{
		fSession::open();
		$_SESSION['delete'] = array('foo' => array('bar' => 'baz'));
		$this->assertEquals('baz', fSession::delete('delete[foo][bar]'));
		$this->assertEquals(array('foo' => array()), $_SESSION['delete']);
	}
	
	public function testRemove()
	{
		fSession::open();
		$_SESSION['foo'] = array(1, 2);
		$this->assertEquals(2, fSession::remove('foo'));
	}
	
	public function testRemoveBeginning()
	{
		fSession::open();
		$_SESSION['foo'] = array(1, 2);
		$this->assertEquals(1, fSession::remove('foo', TRUE));
		$this->assertEquals(array(2), $_SESSION['foo']);
	}
	
	public function testRemoveArraySyntax()
	{
		fSession::open();
		$_SESSION['foo'] = array('bar' => array(1, 2), 2);
		$this->assertEquals(2, fSession::remove('foo[bar]'));
		$this->assertEquals(array(1), $_SESSION['foo']['bar']);
	}
	
	public function testRemoveNestedArraySyntax()
	{
		fSession::open();
		$_SESSION['foo'] = array('bar' => array('baz' => array(1, 2)), 2);
		$this->assertEquals(2, fSession::remove('foo[bar][baz]'));
		$this->assertEquals(array(1), $_SESSION['foo']['bar']['baz']);
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
		if (defined('SKIPPING')) {
			return;	
		}
		fSession::reset();
	}
}