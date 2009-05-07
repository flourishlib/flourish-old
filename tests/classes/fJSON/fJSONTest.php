<?php
require_once('./support/init.php');
 
class fJSONTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{	
		
	}
	
	public static function decode2Provider()
	{
		$class1 = new stdClass();
		$var = '1';
		$class1->$var = 1;

		$class2 = new stdClass();
		$var = '_empty_';
		$class2->$var = 1;

		$class3 = new stdClass();
		$class3->a = 1;

		$class4 = new stdClass();
		$class4->true = 1;

		return array(
			array("", NULL),
			array("1", 1),
			array("true", TRUE),
			array("null", NULL),
			array("\"hello\"", "hello"),
			array("not a value", NULL),
			array(" [", NULL),
			array("[]", array()),
			array("[1]", array(1)),
			array("[1.1]", array(1.1)),
			array("[-1E+4]", array((float)-10000)),
			array("[100.0e-2]", array((float)1)),
			array("[.5]", NULL),
			array("[5.]", array((float)5)),
			array("[.]", NULL),
			array("[5..5]", NULL),
			array("[10e]", NULL),
			array("[e10]", NULL),
			array("[010e2]", NULL),
			array("[010.2]", NULL),
			array("[010]", NULL),
			array("[0xFF]", NULL),
			array("[0xff]", NULL),
			array("[true]", array(true)),
			array("[TRUE]", NULL),
			array("[null]", array(NULL)),
			array("[NULL]", NULL),
			array("[\"\"]", array("")),
			array("[\"a\"]", array("a")),
			array(" [ \"a\" ] ", array("a")),
			array("[1,]", NULL),
			array("[,]", NULL),
			array("[,1]", NULL),
			array("[1,,1]", NULL),
			array("// comment here [1]", NULL),
			array("[// comment here 1]", NULL),
			array("[1// comment here ]", NULL),
			array("[1]// comment here ", NULL),
			array("/*comment here*/[1]", NULL),
			array("[/*comment here*/1]", NULL),
			array("[1/*comment here*/]", NULL),
			array("[1]/*comment here*/", NULL),
			array("/**/[1]", NULL),
			array("// [1]", NULL),
			array("[1 // comment here ]", NULL),
			array("[ // comment here 1]", NULL),
			array("[1]// comment here", NULL),
			array("[1] // comment here", NULL),
			array("[\"a // this is a comment\nb\"]", NULL),
			array("[\"a // this is not a comment b\"]", array("a // this is not a comment b")),
			array("['a']", NULL),
			array("[\"a]", NULL),
			array("[a\"]", NULL),
			array("['a\"]", NULL),
			array("[\"a']", NULL),
			array("[']", NULL),
			array("['']", NULL),
			array("[''']", NULL),
			array("[\"]", NULL),
			array("[\"\"\"]", NULL),
			array("[\"'\"]", array("'")),
			array("['\"']", NULL),
			array("[\"\\'\"]", NULL),
			array("[\"\\\\'\"]", array("\\'")),
			array("[\"\\\"\"]", array("\"")),
			array("[\"\\\"]", NULL),
			array("[\"\\\\\"]", array("\\")),
			array("[\"\\\\\\\"]", NULL),
			array(" [\"a\"]", array("a")),
			array("[ \"a\"]", array("a")),
			array("[\"a\n\"]", NULL),
			array("[\"a\" ]", array("a")),
			array("[\"a\"] ", array("a")),
			array("[\"\\u0041\\u00DC\"]", array("AÃœ")),
			array("[\"\\b\\t\\f\\v\\r\\n\"]", NULL),
			array("[\"\\b\\t\\f\\r\\n\"]", array("\x8\t\xC\r\n")),
			array("[\"\\x41\\xDC\"]", NULL),
			array("[ ] [ ]", NULL),
			array("[\"a\" \"b\"]", NULL),
			array("[1 2]", NULL),
			array("[{}]", array(new stdClass())),
			array("[ { } ]", array(new stdClass())),
			array("[{1}]", NULL),
			array("[{1:1}]", NULL),
			array("[{:1}]", NULL),
			array("[{\"1\":}]", NULL),
			array("[{\"1\":1}]", array($class1)),
			array("[{\"\":1}]", array($class2)),
			array("[{\"a\":}]", NULL),
			array("[{\"a\":1}]", array($class3)),
			array("[{\"true\":1}]", array($class4)),
			array("[{true:1}]", NULL),
			array("[{null:1}]", NULL),
			array("[{a \"a\":1}]", NULL),
			array("[{\"a\":1\"a\"}]", NULL),
			array("[{a b:\"a\"}]", NULL),
			array("[{a b:a b}]", NULL),
			array("[{a 1:a 1}]", NULL),
			array("[{a:b:c}]", NULL),
			array("[/*comment here*/{\"1\":1}]", NULL),
			array("[{/*comment here*/\"1\":1}]", NULL),
			array("[{\"1\"/*comment here*/:1}]", NULL),
			array("[{\"1\":/*comment here*/1}]", NULL),
			array("[{\"1\":1/*comment here*/}]", NULL),
			array("[{\"1\":1}/*comment here*/]", NULL),
			array("[[[]]", NULL)
		);	
	}
	
	public static function encode2Provider()
	{
		$class1 = new stdClass();
		$class1->id = NULL;
		$class1->content_type = "application/json";
		$class1->payload = NULL;
		$class1->methodname = 'dummy';
		$class1->params = array();
		$class1->debug = 0;

		$class2 = new stdClass();
		$class2->me = array();
		$class2->mytype = 0;
		$class2->_php_class = null;

		$failure_array = version_compare(PHP_VERSION, '5.2.9', '>=') || !function_exists('json_encode') ? array("Günter, Elène", 'null') : array("Günter, Elène", '"G"');
		$failure_array_2 = version_compare(PHP_VERSION, '5.2.9', '>=') || !function_exists('json_encode') ? array(array("Günter, Elène"), '[null]') : array(array("Günter, Elène"), '["G"]');
		
		return array(
			array(true, "true"),
			array(false, "false"),
			array(0, "0"),
			array(1, "1"),
			array((float)1, "1"),
			array(1.1, "1.1"),
			array("", '""'),
			array(NULL, "null"),
			array("1", '"1"'),
			array("20060101T12:00:00", '"20060101T12:00:00"'),
			array("GÃ¼nter, ElÃ¨ne", '"G\u00fcnter, El\u00e8ne"'),
			$failure_array,
			array(fopen(__FILE__, 'r'), "null"),
			array("aGVsbG8=", '"aGVsbG8="'),
			array($class1, '{"id":null,"content_type":"application\/json","payload":null,"methodname":"dummy","params":[],"debug":0}'),
			array($class2, '{"me":[],"mytype":0,"_php_class":null}'),
			array(array(), "[]"),
			array(array("GÃ¼nter, ElÃ¨ne"), '["G\u00fcnter, El\u00e8ne"]'),
			$failure_array_2,
			array(array("a"), '["a"]'),
			array(array(array(1)), '[[1]]'),
			array(array('2'=>true,'3'=>false), '{"2":true,"3":false}'),
			array(array('hello'=>'world'), '{"hello":"world"}'),
			array(array('hello' => true, 0 => 'world'), '{"hello":true,"0":"world"}'),
			array(array('hello' => true, 0 => 'hello', 1 => 'world'), '{"hello":true,"0":"hello","1":"world"}'),
			array(array('methodname' => 'hello', 'params' => array()), '{"methodname":"hello","params":[]}'),
			array(array("faultCode" => 666, "faultString" => "hello world"), '{"faultCode":666,"faultString":"hello world"}'),
			array(array("faultCode" => 666, "faultString" => "hello world", "faultWhat?" => "dunno"), '{"faultCode":666,"faultString":"hello world","faultWhat?":"dunno"}'),
			array(array("faultCode" => 666, "faultString" => array("hello world")), '{"faultCode":666,"faultString":["hello world"]}'),
			array(array("faultCode" => 666, "faultString" => array("hello" => "world")), '{"faultCode":666,"faultString":{"hello":"world"}}'),
		);	
	}
	
	public static function provider()
	{
		$output = array();
		
		$output[] = array(array(), '[]');
		$output[] = array(array('a'), '["a"]');
		$output[] = array(new stdClass(), '{}');
		
		$class = new stdClass();
		$class->foo = 'bar';
		$output[] = array($class, '{"foo":"bar"}');
		
		$output[] = array(TRUE,'true');
		$output[] = array(FALSE,'false');
		$output[] = array(NULL,'null');
		$output[] = array(0,'0');
		$output[] = array(21.25,'21.25');
		$output[] = array(7e-15,'7e-15');
		$output[] = array(-0.2,'-0.2');
		$output[] = array(-1,'-1');
		$output[] = array('','""');
		
		$class = new stdClass();
		$class->foo = 'bar';
		$output[] = array(
			array('a',1,array(TRUE,NULL),$class),
			'["a",1,[true,null],{"foo":"bar"}]'
		);
		
		$output[] = array(
			array(
				array(
					array(
						1,
						array(
							'a',
							'b'
						)
					)
				),
				array()
			),
			'[[[1,["a","b"]]],[]]'
		);
		
		return $output;
	}
	
	/**
	 * @dataProvider provider
	 */
	public function testEncode($input, $output)
	{
		$this->assertEquals($output, fJSON::encode($input));	
	}
	
	/**
	 * @dataProvider encode2Provider
	 */
	public function testEncode2($input, $output)
	{
		$this->assertEquals($output, fJSON::encode($input));	
	}
	
	/**
	 * @dataProvider provider
	 */
	public function testDecode($output, $input)
	{
		$this->assertEquals($output, fJSON::decode($input));	
	}
	
	/**
	 * @dataProvider decode2Provider
	 */
	public function testDecode2($input, $output)
	{
		$this->assertEquals(print_r($output, true), print_r(fJSON::decode($input), true));	
	}
	
	public function testDecodeObjectToAssoc()
	{
		$output = fJSON::decode(
			'{
				 "level1_key1": {
					 "level2_key1": {
						 "level3_key1": true,
						 "level3_key2": false
					 },
					 "level2_key2": {
						 "level3_key3": true,
						 "level3_key4": false,
						 "level3_key5": null
					 }
				 }
			 }', TRUE);
		$expected = array(
			'level1_key1' => array(
				'level2_key1' => array(
					'level3_key1' => TRUE,
					'level3_key2' => FALSE
				),
				'level2_key2' => array(
					'level3_key3' => TRUE,
					'level3_key4' => FALSE,
					'level3_key5' => NULL
				)
			)
		);
		$this->assertEquals($expected, $output);	
	}
	
	public function tearDown()
	{
		
	}
}