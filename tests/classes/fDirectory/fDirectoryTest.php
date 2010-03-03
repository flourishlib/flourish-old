<?php
require_once('./support/init.php');
 
class fDirectoryTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		mkdir('output/fDirectory/');
		file_put_contents('output/fDirectory/test.txt', 'test');
				
	}
	
	public function testCreate()
	{
		$dir = fDirectory::create('output/fDirectory2/');
		$this->assertEquals(TRUE, $dir instanceof fDirectory);
	}
	
	public function testConstruct()
	{
		$dir = new fDirectory('output/fDirectory/');
		$this->assertEquals(TRUE, $dir instanceof fDirectory);
	}
	
	public function testConstructNoDir()
	{
		$this->setExpectedException('fValidationException');
		$file = new fFile('output/fDirectory2/');
	}
	
	public function testConstructRegFile()
	{
		$this->setExpectedException('fValidationException');
		$dir = new fDirectory('output/fDirectory/test.txt');
	}
	
	public function testToString()
	{
		$dir = new fDirectory('output/fDirectory/');
		$this->assertEquals(str_replace('/', DIRECTORY_SEPARATOR, $_SERVER['DOCUMENT_ROOT'] . '/output/fDirectory/'), $dir->__toString());
	}
	
	public function testGetName()
	{
		$dir = new fDirectory('output/fDirectory/');
		$this->assertEquals('fDirectory', $dir->getName());
	}
	
	public function testGetParent()
	{
		$dir = new fDirectory('output/fDirectory/');
		$this->assertEquals(TRUE, $dir->getParent() instanceof fDirectory);
		$this->assertEquals(dirname($dir->getPath()) . DIRECTORY_SEPARATOR, $dir->getParent()->getPath());
	}
	
	public function testGetPath()
	{
		$dir = new fDirectory('output/fDirectory/');
		$this->assertEquals(str_replace('/', DIRECTORY_SEPARATOR, '/output/fDirectory/'), str_replace($_SERVER['DOCUMENT_ROOT'], '', $dir->getPath()));
	}
	
	public function testGetSize()
	{
		$dir = new fDirectory('output/fDirectory/');
		$this->assertEquals(4, $dir->getSize());
	}
	
	public function testRename()
	{
		$dir = new fDirectory('output/fDirectory/');
		$dir->rename('fDirectory3', TRUE);
		$this->assertEquals('fDirectory3', $dir->getName());
		$this->assertEquals(str_replace('/', DIRECTORY_SEPARATOR, $_SERVER['DOCUMENT_ROOT'] . '/output/'), $dir->getParent()->getPath());
	}
	
	public function testRenameAnotherDirectory()
	{
		$dir = fDirectory::create('output/fDirectory2/');
		$dir->rename('output/fDirectory/fDirectory3', TRUE);
		$this->assertEquals('fDirectory3', $dir->getName());
		$this->assertEquals(str_replace('/', DIRECTORY_SEPARATOR, $_SERVER['DOCUMENT_ROOT'] . '/output/fDirectory/'), $dir->getParent()->getPath());
	}
	
	public function testMove()
	{
		$dir = fDirectory::create('output/fDirectory2/');
		$dir->move('output/fDirectory/', TRUE);
		$this->assertEquals('fDirectory2', $dir->getName());
		$this->assertEquals(str_replace('/', DIRECTORY_SEPARATOR, $_SERVER['DOCUMENT_ROOT'] . '/output/fDirectory/'), $dir->getParent()->getPath());
	}
	
	public function tearDown()
	{
		$dirs = array('output/fDirectory/', 'output/fDirectory2/', 'output/fDirectory3/');
		foreach ($dirs as $dir) {
			if (file_exists($dir)) {
				$files = array_diff(scandir($dir), array('.', '..'));
				foreach ($files as $file) {
					if (is_dir($dir . $file)) {
						rmdir($dir . $file);	
					} else {
						unlink($dir . $file);
					}
				}
				rmdir($dir);
			}
		}
	}
}