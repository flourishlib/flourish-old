<?php
require_once('./support/init.php');
 
class fCacheTest extends PHPUnit_Framework_TestCase
{
	public $cache;
	
	public function setUp()
	{	
		$_SERVER['PHP_AUTH_USER'] = 'flourish';
		$_SERVER['PHP_AUTH_PW']   = '5f4dcc3b5aa765d61d8327deb882cf99';
		$this->cache = new fCache(CACHE_TYPE, cache_data_store());	
	}
	
	public function testSet()
	{
		$this->cache->set('testkey', 1);
		
		$this->assertEquals(
			1,
			$this->cache->get('testkey')	
		);	
	}
	
	public function testSet2()
	{
		$this->cache->set('testkey2', TRUE);
		
		$this->assertEquals(
			TRUE,
			$this->cache->get('testkey2')	
		);	
	}
	
	public function testOverwriteSet()
	{
		$this->cache->set('overwrite', TRUE);
		$this->cache->set('overwrite', 'test');
		$this->assertEquals(
			'test',
			$this->cache->get('overwrite')	
		);	
	}
	
	public function testDelete()
	{
		$this->cache->set('delete', TRUE);
		$this->cache->delete('delete');
		$this->assertEquals(
			NULL,
			$this->cache->get('delete')	
		);	
	}
	
	public function testAdd()
	{
		$this->cache->add('add', TRUE);
		$this->assertEquals(
			TRUE,
			$this->cache->get('add')	
		);	
	}
	
	public function testAdd2()
	{
		$this->cache->set('add2', TRUE);
		$this->cache->add('add2', 'test');
		$this->assertEquals(
			TRUE,
			$this->cache->get('add2')	
		);	
	}

	public function tearDown()
	{
		$this->cache->clear();
		$this->cache->__destruct();
	}
}