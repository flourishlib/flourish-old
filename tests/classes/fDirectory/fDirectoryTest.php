<?php
require_once('./support/init.php');
 
class fDirectoryTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		mkdir('output/fDirectory/');
		file_put_contents('output/fDirectory/test.txt', 'test');
				
	}
	
	private function createScannableFiles()
	{
		mkdir('output/fDirectory_scan');
		mkdir('output/fDirectory_scan/subdir/');
		mkdir('output/fDirectory_scan/subdir/subsubdir/');
		mkdir('output/fDirectory_scan/subdir/subsubdir3/');
		mkdir('output/fDirectory_scan/subdir/subsubdir2/');
		mkdir('output/fDirectory_scan/subdir/subsubdir/subsubsubdir/');
		touch('output/fDirectory_scan/file1.txt');
		touch('output/fDirectory_scan/file.txt');
		touch('output/fDirectory_scan/fIle2.txt');
		touch('output/fDirectory_scan/file');
		touch('output/fDirectory_scan/file.csv');
		touch('output/fDirectory_scan/foo');
		touch('output/fDirectory_scan/boo');
		touch('output/fDirectory_scan/subdir/file1.txt');
		touch('output/fDirectory_scan/subdir/file2.txt');
		touch('output/fDirectory_scan/subdir/subsubdir/file3.txt');;
		touch('output/fDirectory_scan/subdir/subsubdir/subsubsubdir/file4.txt');
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
	
	public function testScan()
	{
		$this->createScannableFiles();
		$dir   = new fDirectory('output/fDirectory_scan/');
		$files = $dir->scan();
		$filenames = array();
		foreach ($files as $file) {
			$filenames[] = str_replace($dir->getPath(), '', $file->getPath());	
		}
		
		$this->assertEquals(
			array(
				'boo',
				'file',
				'file.csv',
				'file.txt',
				'file1.txt',
				'fIle2.txt',
				'foo',
				'subdir' . DIRECTORY_SEPARATOR
			),
			$filenames
		);
	}
	
	public function testScanRecursive()
	{
		$this->createScannableFiles();
		$dir   = new fDirectory('output/fDirectory_scan/');
		$files = $dir->scanRecursive();
		$filenames = array();
		foreach ($files as $file) {
			$filenames[] = str_replace($dir->getPath(), '', $file->getPath());	
		}
		
		$this->assertEquals(
			array(
				'boo',
				'file',
				'file.csv',
				'file.txt',
				'file1.txt',
				'fIle2.txt',
				'foo',
				'subdir' . DIRECTORY_SEPARATOR,
				'subdir' . DIRECTORY_SEPARATOR . 'file1.txt',
				'subdir' . DIRECTORY_SEPARATOR . 'file2.txt',
				'subdir' . DIRECTORY_SEPARATOR . 'subsubdir' . DIRECTORY_SEPARATOR,
				'subdir' . DIRECTORY_SEPARATOR . 'subsubdir' . DIRECTORY_SEPARATOR . 'file3.txt',
				'subdir' . DIRECTORY_SEPARATOR . 'subsubdir' . DIRECTORY_SEPARATOR . 'subsubsubdir' . DIRECTORY_SEPARATOR,
				'subdir' . DIRECTORY_SEPARATOR . 'subsubdir' . DIRECTORY_SEPARATOR . 'subsubsubdir' . DIRECTORY_SEPARATOR . 'file4.txt',
				'subdir' . DIRECTORY_SEPARATOR . 'subsubdir2' . DIRECTORY_SEPARATOR,
				'subdir' . DIRECTORY_SEPARATOR . 'subsubdir3' . DIRECTORY_SEPARATOR
			),
			$filenames
		);
	}
	
	public function testScanGlob()
	{
		$this->createScannableFiles();
		$dir   = new fDirectory('output/fDirectory_scan/');
		$files = $dir->scan('*file*');
		$filenames = array();
		foreach ($files as $file) {
			$filenames[] = str_replace($dir->getPath(), '', $file->getPath());	
		}
		
		$this->assertEquals(
			array(
				'file',
				'file.csv',
				'file.txt',
				'file1.txt'
			),
			$filenames
		);
	}
	
	public function testScanRecursiveGlob()
	{
		$this->createScannableFiles();
		$dir   = new fDirectory('output/fDirectory_scan/');
		$files = $dir->scanRecursive('*file*');
		$filenames = array();
		foreach ($files as $file) {
			$filenames[] = str_replace($dir->getPath(), '', $file->getPath());	
		}
		
		$this->assertEquals(
			array(
				'file',
				'file.csv',
				'file.txt',
				'file1.txt',
				'subdir' . DIRECTORY_SEPARATOR . 'file1.txt',
				'subdir' . DIRECTORY_SEPARATOR . 'file2.txt',
				'subdir' . DIRECTORY_SEPARATOR . 'subsubdir' . DIRECTORY_SEPARATOR . 'file3.txt',
				'subdir' . DIRECTORY_SEPARATOR . 'subsubdir' . DIRECTORY_SEPARATOR . 'subsubsubdir' . DIRECTORY_SEPARATOR . 'file4.txt'
			),
			$filenames
		);
	}
	
	public function testScanRegex()
	{
		$this->createScannableFiles();
		$dir   = new fDirectory('output/fDirectory_scan/');
		$files = $dir->scan('#file#i');
		$filenames = array();
		foreach ($files as $file) {
			$filenames[] = str_replace($dir->getPath(), '', $file->getPath());	
		}
		
		$this->assertEquals(
			array(
				'file',
				'file.csv',
				'file.txt',
				'file1.txt',
				'fIle2.txt'
			),
			$filenames
		);
	}
	
	public function testScanRecursiveRegex()
	{
		$this->createScannableFiles();
		$dir   = new fDirectory('output/fDirectory_scan/');
		$files = $dir->scanRecursive('#/#');
		$filenames = array();
		foreach ($files as $file) {
			$filenames[] = str_replace($dir->getPath(), '', $file->getPath());	
		}
		
		$this->assertEquals(
			array(
				'subdir' . DIRECTORY_SEPARATOR,
				'subdir' . DIRECTORY_SEPARATOR . 'file1.txt',
				'subdir' . DIRECTORY_SEPARATOR . 'file2.txt',
				'subdir' . DIRECTORY_SEPARATOR . 'subsubdir' . DIRECTORY_SEPARATOR,
				'subdir' . DIRECTORY_SEPARATOR . 'subsubdir' . DIRECTORY_SEPARATOR . 'file3.txt',
				'subdir' . DIRECTORY_SEPARATOR . 'subsubdir' . DIRECTORY_SEPARATOR . 'subsubsubdir' . DIRECTORY_SEPARATOR,
				'subdir' . DIRECTORY_SEPARATOR . 'subsubdir' . DIRECTORY_SEPARATOR . 'subsubsubdir' . DIRECTORY_SEPARATOR . 'file4.txt',
				'subdir' . DIRECTORY_SEPARATOR . 'subsubdir2' . DIRECTORY_SEPARATOR,
				'subdir' . DIRECTORY_SEPARATOR . 'subsubdir3' . DIRECTORY_SEPARATOR,
			),
			$filenames
		);
	}
	
	public function testScanDirs()
	{
		$this->createScannableFiles();
		$dir   = new fDirectory('output/fDirectory_scan/');
		$files = $dir->scanRecursive('#/$#');
		$filenames = array();
		foreach ($files as $file) {
			$filenames[] = str_replace($dir->getPath(), '', $file->getPath());    
		}
		
		$this->assertEquals(
			array(
				'subdir' . DIRECTORY_SEPARATOR,
				'subdir' . DIRECTORY_SEPARATOR . 'subsubdir' . DIRECTORY_SEPARATOR,
				'subdir' . DIRECTORY_SEPARATOR . 'subsubdir' . DIRECTORY_SEPARATOR . 'subsubsubdir' . DIRECTORY_SEPARATOR,
				'subdir' . DIRECTORY_SEPARATOR . 'subsubdir2' . DIRECTORY_SEPARATOR,
				'subdir' . DIRECTORY_SEPARATOR . 'subsubdir3' . DIRECTORY_SEPARATOR,
			),
			$filenames
		);
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
		$dirs = array('output/fDirectory', 'output/fDirectory2', 'output/fDirectory3', 'output/fDirectory_scan');
		foreach ($dirs as $dir) {
			$this->recursiveDelete($dir);
		}
	}
	
	public function recursiveDelete($dir)
	{
		if (!file_exists($dir)) { return; }
		$dir = $dir . '/';
		$files = array_diff(scandir($dir), array('.', '..'));
		foreach ($files as $file) {
			if (is_dir($dir . $file)) {
				$this->recursiveDelete($dir . $file);
			} else {
				unlink($dir . $file);
			}
		}
		rmdir($dir);
	}
}