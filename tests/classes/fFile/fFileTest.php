<?php
require_once('./support/init.php');
 
class fFileTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{	
		mkdir('output/fFile/');
		mkdir('output/fFile2/');
		file_put_contents('output/fFile/one.txt', 'one');
		file_put_contents('output/fFile/two.txt', 'two');	
	}
	
	public function tearDown()
	{
		$dirs = array('output/fFile/', 'output/fFile2/');
		foreach ($dirs as $dir) {
			$files = array_diff(scandir($dir), array('.', '..'));
			foreach ($files as $file) {
				unlink($dir . $file);	
			}
			rmdir($dir);
		}
		fFilesystem::reset();
	}
	
	public function testAppend()
	{
		$file = new fFile('output/fFile/one.txt');
		$file->append('+one=two');
		$this->assertEquals('one+one=two', file_get_contents('output/fFile/one.txt'));	
	}
	
	public function testAppendRollback()
	{
		fFilesystem::begin();
		$file = new fFile('output/fFile/one.txt');
		$file->append('+one=two');
		$this->assertEquals('one+one=two', file_get_contents('output/fFile/one.txt'));
		fFilesystem::rollback();
		$this->assertEquals('one', file_get_contents('output/fFile/one.txt'));
	}
	
	public function testCreate()
	{
		$file = fFile::create('output/fFile/three.txt', 'thr33');
		$this->assertEquals(TRUE, $file instanceof fFile);
		$this->assertEquals('thr33', file_get_contents('output/fFile/three.txt'));	
	}
	
	public function testClone()
	{
		$file     = fFile::create('output/fFile/three.txt', 'thr33');
		$new_file = clone $file;
		$this->assertEquals(TRUE, $new_file instanceof fFile);
		$this->assertEquals(TRUE, $new_file->getPath() != $file->getPath());
		$this->assertEquals('thr33', file_get_contents($new_file->getPath()));	
	}
	
	public function testConstruct()
	{
		$file     = new fFile('output/fFile/one.txt');
		$this->assertEquals(TRUE, $file instanceof fFile);
		$this->assertEquals('one', file_get_contents($file->getPath()));	
	}
	
	public function testConstructNoFile()
	{
		$this->setExpectedException('fValidationException');
		$file = new fFile('output/fFile/five.txt');
	}
	
	public function testConstructDirectory()
	{
		$this->setExpectedException('fValidationException');
		$file = new fFile('output/fFile/');
	}
	
	public function testToString()
	{
		$file = new fFile('output/fFile/one.txt');
		$this->assertEquals('one.txt', $file->__toString());
	}
	
	public function testGetName()
	{
		$file = new fFile('output/fFile/one.txt');
		$this->assertEquals('one.txt', $file->getName());
	}
	
	public function testGetNameNoExt()
	{
		$file = new fFile('output/fFile/one.txt');
		$this->assertEquals('one', $file->getName(TRUE));
	}
	
	public function testGetParent()
	{
		$file = new fFile('output/fFile/one.txt');
		$this->assertEquals(TRUE, $file->getParent() instanceof fDirectory);
		$this->assertEquals(dirname($file->getPath()) . DIRECTORY_SEPARATOR, $file->getParent()->getPath());
	}
	
	public function testGetPath()
	{
		$file = new fFile('output/fFile/one.txt');
		$this->assertEquals(str_replace('/', DIRECTORY_SEPARATOR, '/output/fFile/one.txt'), str_replace($_SERVER['DOCUMENT_ROOT'], '', $file->getPath()));
	}
	
	public function testGetSize()
	{
		$file = new fFile('output/fFile/one.txt');
		$this->assertEquals(3, $file->getSize());
	}

	public function testCount()
	{
		$file = new fFile('resources/words/input.txt');
		$this->assertEquals(23532, count($file));	
	}

	public function testCount2()
	{
		$file = new fFile('resources/text/example.txt');
		$this->assertEquals(1, $file->count());	
	}

	public function testCount3()
	{
		$file = new fFile('resources/text/empty.txt');
		$this->assertEquals(0, $file->count());	
	}
	
	public function testRename()
	{
		$file = new fFile('output/fFile/one.txt');
		$file->rename('three.txt', TRUE);
		$this->assertEquals('three.txt', $file->getName());
		$this->assertEquals(str_replace('/', DIRECTORY_SEPARATOR, $_SERVER['DOCUMENT_ROOT'] . '/output/fFile/'), $file->getParent()->getPath());
	}
    
    public function testRenameToSameName()
    {
        $file = new fFile('output/fFile/one.txt');
        $file->rename('one.txt', TRUE);
        $this->assertEquals('one.txt', $file->getName());
    }
	
	public function testRenameAnotherDirectory()
	{
		$file = new fFile('output/fFile/one.txt');
		$file->rename('output/fFile2/two.txt', TRUE);
		$this->assertEquals('two.txt', $file->getName());
		$this->assertEquals(str_replace('/', DIRECTORY_SEPARATOR, $_SERVER['DOCUMENT_ROOT'] . '/output/fFile2/'), $file->getParent()->getPath());
	}
	
	public function testMove()
	{
		$file = new fFile('output/fFile/one.txt');
		$file->move('output/fFile2/', TRUE);
		$this->assertEquals('one.txt', $file->getName());
		$this->assertEquals(str_replace('/', DIRECTORY_SEPARATOR, $_SERVER['DOCUMENT_ROOT'] . '/output/fFile2/'), $file->getParent()->getPath());
	}
}