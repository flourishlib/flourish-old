<?php
require_once('./support/init.php');
 
class fURLTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{	
		
	}
	
	public static function makeFriendlyProvider()
	{
		$output = array();
		
		$output[] = array('This is a test', NULL, 'this_is_a_test');
		$output[] = array('Here is some punctuation-and the output!', NULL, 'here_is_some_punctuation-and_the_output');
		$output[] = array("tests of dashes - and under_scores", NULL, 'tests_of_dashes-and_under_scores');
		$output[] = array("Iñtërnâtiônàlizætiøn!", NULL, 'internationalizaetion');
		$output[] = array("test", 2, 'te');
		$output[] = array("this is a test of a really long string to be converted to a url", 36, 'this_is_a_test_of_a_really_long');
		$output[] = array("this is a test of a really long string to be converted to a url", 19, 'this_is_a_test_of_a');
		$output[] = array("Here is a really long string hjdhjkhdksahdiusahdiushdiusahdiusahdiusahdiudhsahusiudsdjsldssa", 80, 'here_is_a_really_long_string_hjdhjkhdksahdiusahdiushdiusahdiusahdiusahdiudhsahus');
		
		return $output;
	}
	
	/**
	 * @dataProvider makeFriendlyProvider
	 */
	public function testMakeFriendly($input, $max_length, $output)
	{
		$this->assertEquals($output, fURL::makeFriendly($input, $max_length));
	}

	public static function redirectProvider()
	{
		$output = array();
		
		$output[] = array('/foo/bar/baz', '/foo/bar/baz', '/index.php');
		$output[] = array('foobar', '/foobar', '/index.php');
		$output[] = array('foobar', '/baz/foobar', '/baz/');
		$output[] = array('?foo=baz', '/index.php?foo=baz', '/index.php');
		$output[] = array('./foobar', '/foo/baz/foobar', '/foo/baz/index.php');
		$output[] = array('../../foobar', '/../foobar', '/dir/index.php');
		$output[] = array('../../foobar', '/foobar', '/dir/dir2/index.php');

		return $output;
	}

	/**
	 * @dataProvider redirectProvider
	 */
	public function testRedirect($path, $result, $request_uri)
	{
		// This is a gross cli wrapper script since we have to test for exit
		$code  = "\$_SERVER['REQUEST_URI'] = '" . $request_uri . "';";
		$code .= "fURL::redirect('" . $path . "');";
		$this->assertEquals('http://example.com' . $result, shell_exec('php ' . TEST_EXIT_SCRIPT . ' ' . escapeshellarg($code)));
	}
	
	public function tearDown()
	{
		
	}
}