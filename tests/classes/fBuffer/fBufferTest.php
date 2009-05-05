<?php
require_once('./support/init.php');
 
class fBufferTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{	
		$this->started_buffer = FALSE;
		$this->started_capture = FALSE;
	}
	
	public function testStartBuffering()
	{
		$level = ob_get_level();
		fBuffer::start();
		$this->started_buffer = TRUE;
		$this->assertEquals($level+1, ob_get_level());
	}
	
	public function testStartBufferingTwice()
	{
		$this->setExpectedException('fProgrammerException');
		
		fBuffer::start();
		$this->started_buffer = TRUE;
		fBuffer::start();
	}
	
	public function testCapturing()
	{
		ob_start();
		fBuffer::startCapture();
		echo 'testing capture';
		$this->assertEquals('testing capture', fBuffer::stopCapture());
		$this->assertEquals('', ob_get_clean());
	}
	
	public function testStartCapturingAfterBuffering()
	{
		fBuffer::start();
		$this->started_buffer = TRUE;
		fBuffer::startCapture();
		$this->started_capture = TRUE;
	}
	
	public function testIsStarted()
	{
		$this->assertEquals(FALSE, fBuffer::isStarted());
		fBuffer::start();
		$this->started_buffer = TRUE;
		$this->assertEquals(TRUE, fBuffer::isStarted());
	}
	
	public function testGet()
	{
		fBuffer::start();
		$this->started_buffer = TRUE;
		echo 'testing get';
		$this->assertEquals('testing get', fBuffer::get());
	}
	
	public function testGetBeforeStart()
	{
		$this->setExpectedException('fProgrammerException');

		fBuffer::get();
	}
	
	public function testGetDuringCapture()
	{
		$this->setExpectedException('fProgrammerException');

		fBuffer::start();
		$this->started_buffer = TRUE;
		fBuffer::startCapture();
		$this->started_capture = TRUE;
		fBuffer::get();
	}
	
	public function testErase()
	{
		fBuffer::start();
		$this->started_buffer = TRUE;
		echo 'testing erase';
		fBuffer::erase();
		$this->assertEquals('', fBuffer::get());
	}
	
	public function testEraseBeforeStart()
	{
		$this->setExpectedException('fProgrammerException');

		fBuffer::erase();
	}
	
	public function testEraseDuringCapture()
	{
		$this->setExpectedException('fProgrammerException');

		fBuffer::start();
		$this->started_buffer = TRUE;
		fBuffer::startCapture();
		$this->started_capture = TRUE;
		fBuffer::erase();
	}
	
	public function testStopBuffering()
	{
		$level = ob_get_level();
		fBuffer::start();
		fBuffer::stop();
		$this->assertEquals($level, ob_get_level());
	}
	
	public function testStopBeforeStart()
	{
		$this->setExpectedException('fProgrammerException');

		fBuffer::stop();
	}
	
	public function testStopDuringCapture()
	{
		$this->setExpectedException('fProgrammerException');

		fBuffer::start();
		$this->started_buffer = TRUE;
		fBuffer::startCapture();
		$this->started_capture = TRUE;
		fBuffer::stop();
	}

	public function tearDown()
	{
		if ($this->started_capture) {
			fBuffer::stopCapture();	
			$this->started_capture = FALSE;
		}
		if ($this->started_buffer) {
			ob_clean();
			fBuffer::stop();	
			$this->started_buffer = FALSE;
		}
	}
}