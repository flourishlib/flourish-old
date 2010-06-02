<?php
require_once('./support/init.php');
 
class fTemplatingTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{	
		
	}
	
	public function testSetArray()
	{
		$tmpl = new fTemplating();
		$tmpl->set('foo', FALSE);
		$tmpl->set(array(
			'foo' => TRUE,
			'bar' => '2'
		));
		$this->assertEquals(TRUE, $tmpl->get('foo'));
		$this->assertEquals('2', $tmpl->get('bar'));
	}
	
	public function testGet()
	{
		$tmpl = new fTemplating();
		$tmpl->set('foo', TRUE);
		$tmpl->set('bar', '2');
		$this->assertEquals(
			TRUE,
			$tmpl->get('foo')
		);
	}
	
	public function testGetDefault()
	{
		$tmpl = new fTemplating();
		$tmpl->set('foo', TRUE);
		$tmpl->set('bar', '2');
		$this->assertEquals(
			'3',
			$tmpl->get('baz', '3')
		);
	}
	
	public function testGetArray()
	{
		$tmpl = new fTemplating();
		$tmpl->set('foo', TRUE);
		$tmpl->set('bar', '2');
		$this->assertEquals(
			array(
				'foo' => TRUE,
				'bar' => '2'
			),
			$tmpl->get(array('foo', 'bar'))
		);
	}
	
	public function testGetArrayDefaults()
	{
		$tmpl = new fTemplating();
		$tmpl->set('foo', TRUE);
		$tmpl->set('bar', '2');
		$this->assertEquals(
			array(
				'foo' => TRUE,
				'bar' => '2',
				'baz' => '3'
			),
			$tmpl->get(array(
				'foo' => NULL,
				'bar' => '1',
				'baz' => '3'
			))
		);
	}
	
	public function tearDown()
	{
		
	}
}