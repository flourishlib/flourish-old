<?php
require_once('./support/init.php');
 
class fHTMLTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{	
		
	}
	
	public static function makeLinksProvider()
	{
		$output = array();
		
		$output[] = array('http://example.com',     0, '<a href="http://example.com">http://example.com</a>');
		$output[] = array('http://www.example.com', 0, '<a href="http://www.example.com">http://www.example.com</a>');
		$output[] = array('www.example.com',        0, '<a href="http://www.example.com">www.example.com</a>');
		$output[] = array('will@example.com',       0, '<a href="mailto:will@example.com">will@example.com</a>');
		
		$output[] = array('<a href="http://example.com">http://example.com</a>', 0, '<a href="http://example.com">http://example.com</a>');
		$output[] = array('<img src="http://example.com/foo.gif" alt="" />', 0, '<img src="http://example.com/foo.gif" alt="" />');
		$output[] = array('<img src="http://example.com/foo.gif" alt="" /> www.foobar.com', 0, '<img src="http://example.com/foo.gif" alt="" /> <a href="http://www.foobar.com">www.foobar.com</a>');
		$output[] = array('<script type="text/javascript" src="http://example.com/foo.jg"></script>', 0, '<script type="text/javascript" src="http://example.com/foo.jg"></script>');
		
		$output[] = array('www.example.com',        5, '<a href="http://www.example.com">www.e…</a>');
		$output[] = array('www.example.com',        8, '<a href="http://www.example.com">www.exam…</a>');
		$output[] = array('www.example.com',        20, '<a href="http://www.example.com">www.example.com</a>');
		
		$output[] = array('will@example.com <script type="text/javascript" src="http://example.com/foo.jg"></script>www.foobar.com http://flourishlib.com <img src="http://example.com/foo.gif" alt="" />', 0, '<a href="mailto:will@example.com">will@example.com</a> <script type="text/javascript" src="http://example.com/foo.jg"></script><a href="http://www.foobar.com">www.foobar.com</a> <a href="http://flourishlib.com">http://flourishlib.com</a> <img src="http://example.com/foo.gif" alt="" />');
		
		$output[] = array('will@example.com <script type="text/javascript" src="http://example.com/foo.jg"></script>www.foobar.com http://flourishlib.com <img src="http://example.com/foo.gif" alt="" />', 8, '<a href="mailto:will@example.com">will@exa…</a> <script type="text/javascript" src="http://example.com/foo.jg"></script><a href="http://www.foobar.com">www.foob…</a> <a href="http://flourishlib.com">http://f…</a> <img src="http://example.com/foo.gif" alt="" />');
		
		return $output;
	}
	
	/**
	 * @dataProvider makeLinksProvider
	 */
	public function testMakeLinks($input, $length, $output)
	{
		$this->assertEquals($output, fHTML::makeLinks($input, $length));	
	}
	
	public function tearDown()
	{
		
	}
}