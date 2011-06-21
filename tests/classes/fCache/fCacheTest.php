<?php
require_once('./support/init.php');
 
class fCacheTest extends PHPUnit_Framework_TestCase
{
	public static $cache;
	
	public static function setUpBeforeClass()
	{	
		if (defined('SKIPPING')) {
			return;
		}
		$_SERVER['PHP_AUTH_USER'] = 'flourish';
		$_SERVER['PHP_AUTH_PW']   = '5f4dcc3b5aa765d61d8327deb882cf99';
		self::$cache = new fCache(
			CACHE_TYPE,
			cache_data_store(),
			function_exists('cache_config') ? cache_config() : array()
		);
	}

	public static function tearDownAfterClass()
	{
		if (defined('SKIPPING')) {
			return;
		}
		self::$cache->clear();
		self::$cache->__destruct();
	}

	public function setUp()
	{
		if (defined('SKIPPING')) {
			$this->markTestSkipped();
		}
	}
	
	public function testSet()
	{
		self::$cache->set('testkey', 1);
		
		$this->assertEquals(
			1,
			self::$cache->get('testkey')	
		);	
	}

	public function testSerializerString()
	{
		$config = function_exists('cache_config') ? cache_config() : array();
		$cache = new fCache(
			CACHE_TYPE,
			cache_data_store(),
			array_merge(
				array('serializer' => 'string', 'unserializer' => 'string'),
				$config
			)
		);
		$cache->set('testkey', TRUE);
		
		$this->assertEquals(
			'1',
			$cache->get('testkey')	
		);
		
		$cache->set('testkey', FALSE);
		
		$this->assertEquals(
			'',
			$cache->get('testkey')	
		);
	}

	public function testSerializerJSON()
	{
		$config = function_exists('cache_config') ? cache_config() : array();
		$cache = new fCache(
			CACHE_TYPE,
			cache_data_store(),
			array_merge(
				array('serializer' => array('fJSON', 'encode'), 'unserializer' => array('fJSON', 'decode')),
				$config
			)
		);
		$cache->set('testkey', TRUE);
		
		$this->assertEquals(
			TRUE,
			$cache->get('testkey')	
		);
		
		$cache->set('testkey', FALSE);
		
		$this->assertEquals(
			FALSE,
			$cache->get('testkey')	
		);
	}
	
	public function testSet2()
	{
		self::$cache->set('testkey2', TRUE);
		
		$this->assertEquals(
			TRUE,
			self::$cache->get('testkey2')	
		);	
	}
	
	public function testOverwriteSet()
	{
		self::$cache->set('overwrite', TRUE);
		self::$cache->set('overwrite', 'test');
		$this->assertEquals(
			'test',
			self::$cache->get('overwrite')	
		);	
	}
	
	public function testDelete()
	{
		self::$cache->set('delete', TRUE);
		self::$cache->delete('delete');
		$this->assertEquals(
			NULL,
			self::$cache->get('delete')	
		);	
	}
	
	public function testAdd()
	{
		self::$cache->add('add', TRUE);
		$this->assertEquals(
			TRUE,
			self::$cache->get('add')	
		);	
	}
	
	public function testAdd2()
	{
		self::$cache->set('add2', TRUE);
		self::$cache->add('add2', 'test');
		$this->assertEquals(
			TRUE,
			self::$cache->get('add2')	
		);	
	}
}