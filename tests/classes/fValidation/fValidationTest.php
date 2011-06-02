<?php
require_once('./support/init.php');
 
class fValidationTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{	
		$_GET     = array();
		$_POST    = array();
		$_REQUEST = array();	
	}
	
	
	public static function requiredFieldsProvider()
	{
		$output = array();
		
		$output[] = array(
			array(),
			array(),
			TRUE,
			array('Foo: Please enter a value'),
			array(),
			array('foo')
		);
		
		$output[] = array(
			array('foo' => ''),
			array(),
			TRUE,
			array('Foo: Please enter a value', 'Bar: Please enter a value'),
			array(),
			array('foo', 'bar')
		);
		
		$output[] = array(
			array(),
			array('bar' => ''),
			TRUE,
			array('Foo: Please enter a value', 'Bar: Please enter a value'),
			array(),
			array('foo', 'bar')
		);
		
		$output[] = array(
			array('foo' => 'This is a test'),
			array(),
			TRUE,
			array('Bar: Please enter a value'),
			array('Foo: Please enter a value'),
			array('foo', 'bar')
		);
		
		$output[] = array(
			array('user' => array('name' => 'John')),
			array(),
			FALSE,
			array(),
			array(),
			array('user[name]')
		);
		
		$output[] = array(
			array('user' => array('name' => 'John')),
			array(),
			TRUE,
			array('User Name First: Please enter a value'),
			array(),
			array('user[name][first]')
		);
		
		$output[] = array(
			array('user' => array('name' => 'John')),
			array(),
			TRUE,
			array('User Email: Please enter a value'),
			array(),
			array('user[email]')
		);
		
		$output[] = array(
			array('user' => array('John')),
			array(),
			TRUE,
			array('User #2: Please enter a value'),
			array(),
			array('user[1]')
		);
		
		$output[] = array(
			array(),
			array('foo' => 'This is a test'),
			TRUE,
			array('Bar: Please enter a value'),
			array('Foo: Please enter a value'),
			array('foo', 'bar')
		);
		
		$output[] = array(
			array(),
			array('foo' => 'This is a test', 'bar' => 'This is a test'),
			FALSE,
			array(),
			array(),
			array('foo', 'bar')
		);
		
		$output[] = array(
			array('foo' => 'This is a test', 'bar' => 'This is a test'),
			array(),
			FALSE,
			array(),
			array(),
			array('foo', 'bar')
		);
		
		$output[] = array(
			array('foo' => 'This is a test'),
			array('bar' => 'This is a test'),
			FALSE,
			array(),
			array(),
			array('foo', 'bar')
		);
		
		return $output;
	}
	
	/**
	 * @dataProvider requiredFieldsProvider
	 */
	public function testRequiredFields($get_params, $post_params, $should_throw_exception, $contains, $not_contains, $fields)
	{
		if ($should_throw_exception) {
			$this->setExpectedException('fValidationException');
		}
		
		$_GET  = array_merge($_GET, $get_params);
		$_POST = array_merge($_POST, $post_params);
		
		try {
			$v = new fValidation();
			call_user_func_array($v->addRequiredFields, $fields);
			$v->validate();
		} catch (fValidationException $e) {
			foreach ($contains as $string) {
				$this->assertContains($string, $e->getMessage());
			}
			foreach ($not_contains as $string) {
				$this->assertNotContains($string, $e->getMessage());
			}
			throw $e;
		}
	}
	
	public function testRequiredFieldsMultipleCalls()
	{
		$_GET['foo'] = '0';
		$_POST['bar'] = '0';
		
		$v = new fValidation();
		$v->addRequiredFields('foo');
		$v->addRequiredFields('bar');
		$v->validate();
	}
	
	public function testRequiredFieldsMultipleCallsFail()
	{
		$this->setExpectedException('fValidationException');
		
		$_POST['bar'] = '';
		
		try {
			$v = new fValidation();
			$v->addRequiredFields('foo');
			$v->addRequiredFields('bar');
			$v->validate();
			
		} catch (fValidationException $e) {
			$this->assertContains('Foo: Please enter a value', $e->getMessage());
			$this->assertContains('Bar: Please enter a value', $e->getMessage());
			throw $e;	
		}
	}
	
	public static function oneOrMoreRuleProvider()
	{
		$output = array();
		
		$output[] = array(
			array(),
			array(),
			TRUE,
			array('Foo, Bar: Please enter a value for at least one'),
			array(),
			array('foo', 'bar')
		);
		
		$output[] = array(
			array('foo' => '', 'bar' => ''),
			array(),
			TRUE,
			array('Foo, Bar: Please enter a value for at least one'),
			array(),
			array('foo', 'bar')
		);
		
		$output[] = array(
			array('foo' => NULL, 'bar' => NULL),
			array(),
			TRUE,
			array('Foo, Bar: Please enter a value for at least one'),
			array(),
			array('foo', 'bar')
		);
		
		$output[] = array(
			array('foo' => 0),
			array('bar' => 0),
			FALSE,
			array(),
			array(),
			array('foo', 'bar')
		);
		
		$output[] = array(
			array('far' => 'test'),
			array(),
			TRUE,
			array('Foo, Bar: Please enter a value for at least one'),
			array(),
			array('foo', 'bar')
		);
		
		$output[] = array(
			array('foo' => 'Test'),
			array(),
			FALSE,
			array(),
			array(),
			array('foo', 'bar')
		);
		
		$output[] = array(
			array('bar' => '0'),
			array(),
			FALSE,
			array(),
			array(),
			array('foo', 'bar')
		);
		
		$output[] = array(
			array(),
			array('bar' => '', 'foo' => '0'),
			FALSE,
			array(),
			array(),
			array('foo', 'bar')
		);
		
		$output[] = array(
			array('bar' => '0'),
			array('foo' => 'Test'),
			FALSE,
			array(),
			array(),
			array('foo', 'bar')
		);
		
		
		return $output;
	}
	
	/**
	 * @dataProvider oneOrMoreRuleProvider
	 */
	public function testOneOrMoreRule($get_params, $post_params, $should_throw_exception, $contains, $not_contains, $params)
	{
		if ($should_throw_exception) {
			$this->setExpectedException('fValidationException');
		}
		
		$_GET  = array_merge($_GET, $get_params);
		$_POST = array_merge($_POST, $post_params);
		
		try {
			$v = new fValidation();
			call_user_func_array($v->addOneOrMoreRule, $params);
			$v->validate();
		} catch (fValidationException $e) {
			foreach ($contains as $string) {
				$this->assertContains($string, $e->getMessage());
			}
			foreach ($not_contains as $string) {
				$this->assertNotContains($string, $e->getMessage());
			}
			throw $e;
		}
	}
	
	
	public static function onlyOneRuleProvider()
	{
		$output = array();
		
		$output[] = array(
			array(),
			array(),
			TRUE,
			array('Foo, Bar: Please enter a value for one'),
			array(),
			array('foo', 'bar')
		);
		
		$output[] = array(
			array('foo' => '', 'bar' => ''),
			array(),
			TRUE,
			array('Foo, Bar: Please enter a value for one'),
			array(),
			array('foo', 'bar')
		);
		
		$output[] = array(
			array('foo' => NULL, 'bar' => NULL),
			array(),
			TRUE,
			array('Foo, Bar: Please enter a value for one'),
			array(),
			array('foo', 'bar')
		);
		
		$output[] = array(
			array('foo' => 0),
			array('bar' => 0),
			TRUE,
			array('Foo, Bar: Please enter a value for only one'),
			array(),
			array('foo', 'bar')
		);
		
		$output[] = array(
			array('far' => 'test'),
			array(),
			TRUE,
			array('Foo, Bar: Please enter a value for one'),
			array(),
			array('foo', 'bar')
		);
		
		$output[] = array(
			array('foo' => 'Test'),
			array(),
			FALSE,
			array(),
			array(),
			array('foo', 'bar')
		);
		
		$output[] = array(
			array('bar' => '0'),
			array(),
			FALSE,
			array(),
			array(),
			array('foo', 'bar')
		);
		
		$output[] = array(
			array(),
			array('bar' => '', 'foo' => '0'),
			FALSE,
			array(),
			array(),
			array('foo', 'bar')
		);
		
		$output[] = array(
			array('bar' => '0'),
			array('foo' => 'Test'),
			TRUE,
			array('Foo, Bar: Please enter a value for only one'),
			array(),
			array('foo', 'bar')
		);
		
		
		return $output;
	}
	
	/**
	 * @dataProvider onlyOneRuleProvider
	 */
	public function testOnlyOneRule($get_params, $post_params, $should_throw_exception, $contains, $not_contains, $params)
	{
		if ($should_throw_exception) {
			$this->setExpectedException('fValidationException');
		}
		
		$_GET  = array_merge($_GET, $get_params);
		$_POST = array_merge($_POST, $post_params);
		
		try {
			$v = new fValidation();
			call_user_func_array($v->addOnlyOneRule, $params);
			$v->validate();
		} catch (fValidationException $e) {
			foreach ($contains as $string) {
				$this->assertContains($string, $e->getMessage());
			}
			foreach ($not_contains as $string) {
				$this->assertNotContains($string, $e->getMessage());
			}
			throw $e;
		}
	}
	
	
	public static function conditionalRuleProvider()
	{
		$output = array();
		
		$output[] = array(
			array(),
			array(),
			FALSE,
			array(),
			array(),
			array('foo', NULL, 'bar')
		);
		
		$output[] = array(
			array('foo' => 'This is a test'),
			array(),
			TRUE,
			array('Bar: Please enter a value'),
			array(),
			array('foo', NULL, 'bar')
		);
		
		$output[] = array(
			array(),
			array('foo' => 'This is a test'),
			TRUE,
			array('Bar: Please enter a value'),
			array(),
			array('foo', NULL, 'bar')
		);
		
		$output[] = array(
			array('far' => 'This is a test'),
			array(),
			FALSE,
			array(),
			array(),
			array('foo', NULL, 'bar')
		);
		
		$output[] = array(
			array('foo' => 'This is a test', 'bar' => 'This is a test'),
			array(),
			FALSE,
			array(),
			array(),
			array('foo', NULL, 'bar')
		);
		
		$output[] = array(
			array(),
			array('foo' => 'This is a test', 'bar' => 'This is a test'),
			FALSE,
			array(),
			array(),
			array('foo', NULL, 'bar')
		);
		
		$output[] = array(
			array('bar' => 'This is a test'),
			array('foo' => 'This is a test'),
			FALSE,
			array(),
			array(),
			array('foo', NULL, 'bar')
		);
		
		return $output;
	}
	
	/**
	 * @dataProvider conditionalRuleProvider
	 */
	public function testConditionalRule($get_params, $post_params, $should_throw_exception, $contains, $not_contains, $params)
	{
		if ($should_throw_exception) {
			$this->setExpectedException('fValidationException');
		}
		
		$_GET  = array_merge($_GET, $get_params);
		$_POST = array_merge($_POST, $post_params);
		
		try {
			$v = new fValidation();
			call_user_func_array($v->addConditionalRule, $params);
			$v->validate();
		} catch (fValidationException $e) {
			foreach ($contains as $string) {
				$this->assertContains($string, $e->getMessage());
			}
			foreach ($not_contains as $string) {
				$this->assertNotContains($string, $e->getMessage());
			}
			throw $e;
		}
	}
	
	public function measure($value)
	{
		return strlen($value) > 4;
	}

	public function measure2($value)
	{
		return strlen($value) > 2;
	}
	
	public function testCallbackRule()
	{
		$this->setExpectedException('fValidationException');
		
		$_GET['foo'] = '1';
		
		try {
			$v = new fValidation();
			$v->addCallbackRule('foo', array($this, 'measure'), 'Please enter something');
			$v->validate();
			
		} catch (fValidationException $e) {
			$this->assertContains('Foo: Please enter something', $e->getMessage());
			throw $e;
		}
	}
	
	public function testCallbackRule2()
	{
		$_GET['foo'] = 'This is a test';
		
		$v = new fValidation();
		$v->addCallbackRule('foo', array($this, 'measure'), 'Please enter something');
		$v->validate();
	}

	public function testMultipleCallbackRules()
	{
		$this->setExpectedException('fValidationException');
		
		$_GET['foo'] = 'Thi';
		
		try {
			$v = new fValidation();
			$v->addCallbackRule('foo', array($this, 'measure'), 'Please enter something four characters long');
			$v->addCallbackRule('foo', array($this, 'measure2'), 'Please enter something two characters long');
			$v->validate();
		} catch (fValidationException $e) {
			$this->assertContains('Foo: Please enter something four characters long', $e->getMessage());
			throw $e;
		}
	}
	
	public function testRegexRule()
	{
		$this->setExpectedException('fValidationException');
		
		$_GET['foo'] = '1';
		
		try {
			$v = new fValidation();
			$v->addRegexRule('foo', '#^.{4,}$#D', 'Please enter something');
			$v->validate();
			
		} catch (fValidationException $e) {
			$this->assertContains('Foo: Please enter something', $e->getMessage());
			throw $e;	
		}
	}
	
	public function testRegexRule2()
	{
		$_GET['foo'] = 'This is a test';
		
		$v = new fValidation();
		$v->addRegexRule('foo', '#^.{4,}$#D', 'Please enter something');
		$v->validate();
	}
	
	public static function validValuesProvider()
	{
		$output = array();
		
		$output[] = array(array(), array(), FALSE, array(), array('foo', array('1', '2', 3)));
		$output[] = array(array('foo' => 'bar'), array(), TRUE, array('Foo: Please choose from one of the following: 1, 2, 3'), array('foo', array('1', '2', 3)));
		$output[] = array(array('foo' => '1 '), array(), TRUE, array('Foo: Please choose from one of the following: 1, 2, 3'), array('foo', array('1', '2', 3)));
		$output[] = array(array('foo' => 1), array(), FALSE, array(), array('foo', array('1', '2', 3)));
		$output[] = array(array('foo' => '3'), array(), FALSE, array(), array('foo', array('1', '2', 3)));
		$output[] = array(array('foo' => array('1', '2', '3')), array(), FALSE, array(), array('foo', array(array('1', '2', 3), 1)));
		$output[] = array(array('foo' => 1), array(), FALSE, array(), array('foo', array(array('1', '2', 3), 1)));
		$output[] = array(array('foo' => array('1', '2', '3')), array(), TRUE, array('Foo: Please choose from one of the following: (1, 3, 2), 1'), array('foo', array(array('1', 3, 2), 1)));
		
		return $output;
	}
	
	/**
	 * @dataProvider validValuesProvider
	 */
	public function testValidValuesRule($get_params, $post_params, $should_throw_exception, $contains, $params)
	{
		if ($should_throw_exception) {
			$this->setExpectedException('fValidationException');
		}
		
		$_GET  = array_merge($_GET, $get_params);
		$_POST = array_merge($_POST, $post_params);
		
		try {
			$v = new fValidation();
			call_user_func_array($v->addValidValuesRule, $params);
			$v->validate();
		} catch (fValidationException $e) {
			foreach ($contains as $string) {
				$this->assertContains($string, $e->getMessage());
			}
			throw $e;
		}
	}
	
	public function testRequiredCombo()
	{
		$this->setExpectedException('fValidationException');
		
		try {
			$v = new fValidation();
			$v->addRequiredFields('foo');
			$v->addOneOrMoreRule('bar', 'baz');
			$v->validate();
			
		} catch (fValidationException $e) {
			$this->assertContains('Foo: Please enter a value', $e->getMessage());
			$this->assertContains('Bar, Baz: Please enter a value for at least one', $e->getMessage());
			throw $e;	
		}
	}
	
	public function testRequiredCombo2()
	{
		$this->setExpectedException('fValidationException');
		
		try {
			$v = new fValidation();
			$v->addConditionalRule('foo', NULL, 'bar');
			$v->addOneOrMoreRule('bar', 'baz');
			$v->validate();
			
		} catch (fValidationException $e) {
			$this->assertContains('Bar, Baz: Please enter a value for at least one', $e->getMessage());
			throw $e;	
		}
	}
	
	public function testRequiredCombo3()
	{
		$this->setExpectedException('fValidationException');
		
		$_GET['foo'] = 'This is a test';
		
		try {
			$v = new fValidation();
			$v->addConditionalRule('foo', NULL, 'bar');
			$v->addOneOrMoreRule('bar', 'baz');
			$v->validate();
			
		} catch (fValidationException $e) {
			$this->assertContains('Bar: Please enter a value', $e->getMessage());
			$this->assertContains('Bar, Baz: Please enter a value for at least one', $e->getMessage());
			throw $e;	
		}
	}
	
	
	public static function addEmailProvider()
	{
		$output = array();
		
		$output[] = array(array('foo' => 'This is a test'), array('bar' => '0'), TRUE, array('Foo: Please enter an email address in the form name@example.com', 'Bar: Please enter an email address in the form name@example.com'), array('foo', 'bar'));
		$output[] = array(array('foo' => 'This is a test'), array(), TRUE, array('Foo: Please enter an email address in the form name@example.com'), array('foo'));
		$output[] = array(array('foo' => 'will.@flourishlib.com'), array(), TRUE, array('Foo: Please enter an email address in the form name@example.com'), array('foo'));
		$output[] = array(array('foo' => 'name @example.com'), array(), TRUE, array('Foo: Please enter an email address in the form name@example.com'), array('foo'));
		$output[] = array(array('foo' => 'will..bond@flourishlib.com'), array(), TRUE, array('Foo: Please enter an email address in the form name@example.com'), array('foo'));
		$output[] = array(array('foo' => '.will@flourishlib.com'), array(), TRUE, array('Foo: Please enter an email address in the form name@example.com'), array('foo'));
		
		$output[] = array(array(), array(), FALSE, array(), array('foo'));
		$output[] = array(array('foo' => 'will@flourishlib.com'), array(), FALSE, array(), array('foo'));
		$output[] = array(array('foo' => 'name@example.com'), array(), FALSE, array(), array('foo'));
		$output[] = array(array('foo' => 'john.smith@sub.domain.co.uk'), array(), FALSE, array(), array('foo'));
		$output[] = array(array('foo' => "O'donnel@www.example.com"), array(), FALSE, array(), array('foo'));
		$output[] = array(array('foo' => 'foo+bar@example.com'), array(), FALSE, array(), array('foo'));
		$output[] = array(array('foo' => 'foo-b_a_r@example.com'), array(), FALSE, array(), array('foo'));
		$output[] = array(array('foo' => '"crazy"."example with quotes strings"@example.com'), array(), FALSE, array(), array('foo'));
		$output[] = array(array('foo' => 'foo~bar@example.com'), array(), FALSE, array(), array('foo'));
		$output[] = array(array('foo' => 'name@[192.168.0.1]'), array(), FALSE, array(), array('foo'));
		
		return $output;
	}
	
	/**
	 * @dataProvider addEmailProvider
	 */
	public function testAddEmail($get_params, $post_params, $should_throw_exception, $contains, $fields)
	{
		if ($should_throw_exception) {
			$this->setExpectedException('fValidationException');
		}
		
		$_GET  = array_merge($_GET, $get_params);
		$_POST = array_merge($_POST, $post_params);
		
		try {
			$v = new fValidation();
			call_user_func_array($v->addEmailFields, $fields);
			$v->validate();
		} catch (fValidationException $e) {
			foreach ($contains as $string) {
				$this->assertContains($string, $e->getMessage());
			}
			throw $e;
		}
	}
	
	public function testCombo()
	{
		$this->setExpectedException('fValidationException');
		
		$_GET['email'] = 'This is a test';
		
		try {
			$v = new fValidation();
			$v->addRequiredFields('foo', 'bar');
			$v->addEmailFields('email');
			$v->validate();
			
		} catch (fValidationException $e) {
			$this->assertContains('Foo: Please enter a value', $e->getMessage());
			$this->assertContains('Bar: Please enter a value', $e->getMessage());
			$this->assertContains('Email: Please enter an email address in the form name@example.com', $e->getMessage());
			throw $e;	
		}
	}
	
	public static function addEmailHeaderProvider()
	{
		$output = array();
		
		$output[] = array(array('foo' => "This is a test\n"), array(), TRUE, array('Foo: Line breaks are not allowed'), array('foo'));
		$output[] = array(array('foo' => "This is a test\n"), array('bar' => 'This is a test'), TRUE, array('Foo: Line breaks are not allowed'), array('foo'));
		$output[] = array(array('foo' => "This is a test"), array('bar' => 'This is a test'), FALSE, array(), array('foo', 'bar'));
		
		return $output;
	}
	
	/**
	 * @dataProvider addEmailHeaderProvider
	 */
	public function testAddEmailHeader($get_params, $post_params, $should_throw_exception, $contains, $fields)
	{
		if ($should_throw_exception) {
			$this->setExpectedException('fValidationException');
		}
		
		$_GET  = array_merge($_GET, $get_params);
		$_POST = array_merge($_POST, $post_params);
		
		try {
			$v = new fValidation();
			call_user_func_array($v->addEmailHeaderFields, $fields);
			$v->validate();
		} catch (fValidationException $e) {
			foreach ($contains as $string) {
				$this->assertContains($string, $e->getMessage());
			}
			throw $e;
		}
	}
	
	public static function addDateProvider()
	{
		$output = array();
		
		$output[] = array(array('foo' => 'foobar'), array(), TRUE, array('Foo: Please enter a date'), array('foo'));
		$output[] = array(array(), array('foo' => 'foobar'), TRUE, array('Foo: Please enter a date'), array('foo'));
		$output[] = array(array(), array(), FALSE, array(), array('foo'));
		$output[] = array(array('foo' => 'today'), array(), FALSE, array(), array('foo'));
		$output[] = array(array('foo' => '1/10/08'), array(), FALSE, array(), array('foo'));
		$output[] = array(array('foo' => 'tomorrow'), array(), FALSE, array(), array('foo'));
		$output[] = array(array('foo' => '2009-01-01'), array(), FALSE, array(), array('foo'));
		$output[] = array(array('foo' => 'jan 1st, 2009'), array(), FALSE, array(), array('foo'));
		$output[] = array(array(), array('foo' => 'today'), FALSE, array(), array('foo'));
		$output[] = array(array('foo' => '11/1/2009'), array(), FALSE, array(), array('foo'));
		$output[] = array(array(), array('foo' => '10/1/2008'), FALSE, array(), array('foo'));
		$output[] = array(array('foo' => 'foobar'), array('bar' => 'foobar'), TRUE, array('Foo: Please enter a date', 'Bar: Please enter a date'), array('foo', 'bar'));
		
		return $output;
	}
	
	/**
	 * @dataProvider addDateProvider
	 */
	public function testAddDate($get_params, $post_params, $should_throw_exception, $contains, $fields)
	{
		if ($should_throw_exception) {
			$this->setExpectedException('fValidationException');
		}
		
		$_GET  = array_merge($_GET, $get_params);
		$_POST = array_merge($_POST, $post_params);
		
		try {
			$v = new fValidation();
			call_user_func_array($v->addDateFields, $fields);
			$v->validate();
		} catch (fValidationException $e) {
			foreach ($contains as $string) {
				$this->assertContains($string, $e->getMessage());
			}
			throw $e;
		}
	}
	
	public static function addURLProvider()
	{
		$output = array();
		
		$output[] = array(array('foo' => 'foobar'), array(), TRUE, array('Foo: Please enter a URL in the form http://www.example.com/page'));
		$output[] = array(array(), array('foo' => 'foobar'), TRUE, array('Foo: Please enter a URL in the form http://www.example.com/page'));
		$output[] = array(array(), array(), FALSE, array());
		$output[] = array(array('foo' => 'http://flourishlib.com'), array(), FALSE, array());
		$output[] = array(array('foo' => 'http://www.flourishlib.com'), array(), FALSE, array());
		$output[] = array(array('foo' => 'https://flourishlib.com'), array(), FALSE, array());
		$output[] = array(array('foo' => 'http://flourishlib.co.uk'), array(), FALSE, array());
		$output[] = array(array('foo' => 'http://192.168.10.1'), array(), FALSE, array());
		$output[] = array(array('foo' => 'flourishlib.co.uk'), array(), FALSE, array());
		$output[] = array(array('foo' => 'http://flourishlib.com/this/is/a/page.php?query=string&foo=base%20'), array(), FALSE, array());
		$output[] = array(array(), array('foo' => 'http://flourishlib.com'), FALSE, array());
		$output[] = array(array(), array('foo' => 'http://flourishlib.com/foo bar/'), FALSE, array());
		
		return $output;
	}
	
	/**
	 * @dataProvider addURLProvider
	 */
	public function testAddURL($get_params, $post_params, $should_throw_exception, $contains)
	{
		if ($should_throw_exception) {
			$this->setExpectedException('fValidationException');
		}
		
		$_GET = array_merge($_GET, $get_params);
		$_POST = array_merge($_POST, $post_params);
		
		try {
			$v = new fValidation();
			$v->addURLFields('foo');
			$v->validate();
		} catch (fValidationException $e) {
			foreach ($contains as $string) {
				$this->assertContains($string, $e->getMessage());
			}
			throw $e;
		}
	}
	
	public static function addBooleanProvider()
	{
		$output = array();
		
		$output[] = array(
			array(),
			array(),
			FALSE,
			array(),
			array(),
			array('foo')
		);
		
		$output[] = array(
			array('foo' => ''),
			array(),
			FALSE,
			array(),
			array(),
			array('foo')
		);
		
		$output[] = array(
			array('foo' => 'yes'),
			array(),
			FALSE,
			array(),
			array(),
			array('foo')
		);
		
		$output[] = array(
			array('foo' => 'YES'),
			array(),
			FALSE,
			array(),
			array(),
			array('foo')
		);
		
		$output[] = array(
			array('foo' => 'no'),
			array(),
			FALSE,
			array(),
			array(),
			array('foo')
		);
		
		$output[] = array(
			array('foo' => 'No'),
			array(),
			FALSE,
			array(),
			array(),
			array('foo')
		);
		
		$output[] = array(
			array('foo' => 0),
			array(),
			FALSE,
			array(),
			array(),
			array('foo')
		);
		
		$output[] = array(
			array('foo' => '1'),
			array(),
			FALSE,
			array(),
			array(),
			array('foo')
		);
		
		$output[] = array(
			array('foo' => 't'),
			array(),
			FALSE,
			array(),
			array(),
			array('foo')
		);
		
		$output[] = array(
			array('foo' => 'f'),
			array(),
			FALSE,
			array(),
			array(),
			array('foo')
		);
		
		$output[] = array(
			array('foo' => 'true'),
			array(),
			FALSE,
			array(),
			array(),
			array('foo')
		);
		
		$output[] = array(
			array('foo' => 'false'),
			array(),
			FALSE,
			array(),
			array(),
			array('foo')
		);
		
		$output[] = array(
			array('foo' => 'maybe'),
			array(),
			TRUE,
			array('Foo: Please enter Yes or No'),
			array(),
			array('foo')
		);
		
		$output[] = array(
			array('bar' => 'maybe'),
			array('foo' => 'Yes'),
			TRUE,
			array('Bar: Please enter Yes or No'),
			array(),
			array('foo', 'bar')
		);
		
		return $output;
	}
	
	/**
	 * @dataProvider addBooleanProvider
	 */
	public function testAddBoolean($get_params, $post_params, $should_throw_exception, $contains, $not_contains, $fields)
	{
		if ($should_throw_exception) {
			$this->setExpectedException('fValidationException');
		}
		
		$_GET  = array_merge($_GET, $get_params);
		$_POST = array_merge($_POST, $post_params);
		
		try {
			$v = new fValidation();
			call_user_func_array($v->addBooleanFields, $fields);
			$v->validate();
		} catch (fValidationException $e) {
			foreach ($contains as $string) {
				$this->assertContains($string, $e->getMessage());
			}
			foreach ($not_contains as $string) {
				$this->assertNotContains($string, $e->getMessage());
			}
			throw $e;
		}
	}
	
	public static function addFloatProvider()
	{
		$output = array();
		
		$output[] = array(
			array(),
			array(),
			FALSE,
			array(),
			array(),
			array('foo')
		);
		
		$output[] = array(
			array('foo' => ''),
			array(),
			FALSE,
			array(),
			array(),
			array('foo')
		);
		
		$output[] = array(
			array('foo' => 1.1),
			array(),
			FALSE,
			array(),
			array(),
			array('foo')
		);
		
		$output[] = array(
			array('foo' => '-2.8390'),
			array(),
			FALSE,
			array(),
			array(),
			array('foo')
		);
		
		$output[] = array(
			array('foo' => '+7843984738974389472392389723894723'),
			array(),
			FALSE,
			array(),
			array(),
			array('foo')
		);
		
		$output[] = array(
			array('foo' => '-1843e-5'),
			array(),
			FALSE,
			array(),
			array(),
			array('foo')
		);
		
		$output[] = array(
			array('foo' => 0.0),
			array(),
			FALSE,
			array(),
			array(),
			array('foo')
		);
		
		$output[] = array(
			array('foo' => '+1.839E3'),
			array(),
			FALSE,
			array(),
			array(),
			array('foo')
		);
		
		$output[] = array(
			array('foo' => '1.'),
			array(),
			FALSE,
			array(),
			array(),
			array('foo')
		);
		
		$output[] = array(
			array('foo' => '.1323'),
			array(),
			FALSE,
			array(),
			array(),
			array('foo')
		);
		
		$output[] = array(
			array('foo' => '1.0'),
			array(),
			FALSE,
			array(),
			array(),
			array('foo')
		);
		
		$output[] = array(
			array('foo' => 'Test'),
			array(),
			TRUE,
			array('Foo: Please enter a number'),
			array(),
			array('foo')
		);
		
		$output[] = array(
			array('foo' => '1abc'),
			array(),
			TRUE,
			array('Foo: Please enter a number'),
			array(),
			array('foo')
		);
		
		$output[] = array(
			array('foo' => '1', 'bar' => -20),
			array(),
			FALSE,
			array(),
			array(),
			array('foo', 'bar')
		);
		
		$output[] = array(
			array('foo' => 'Test', 'bar' => -20),
			array(),
			TRUE,
			array('Foo: Please enter a number'),
			array(),
			array('foo', 'bar')
		);
		
		return $output;
	}
	
	/**
	 * @dataProvider addFloatProvider
	 */
	public function testAddFloat($get_params, $post_params, $should_throw_exception, $contains, $not_contains, $fields)
	{
		if ($should_throw_exception) {
			$this->setExpectedException('fValidationException');
		}
		
		$_GET  = array_merge($_GET, $get_params);
		$_POST = array_merge($_POST, $post_params);
		
		try {
			$v = new fValidation();
			call_user_func_array($v->addFloatFields, $fields);
			$v->validate();
		} catch (fValidationException $e) {
			foreach ($contains as $string) {
				$this->assertContains($string, $e->getMessage());
			}
			foreach ($not_contains as $string) {
				$this->assertNotContains($string, $e->getMessage());
			}
			throw $e;
		}
	}
	
	public static function addIntegerProvider()
	{
		$output = array();
		
		$output[] = array(
			array(),
			array(),
			FALSE,
			array(),
			array(),
			array('foo')
		);
		
		$output[] = array(
			array('foo' => ''),
			array(),
			FALSE,
			array(),
			array(),
			array('foo')
		);
		
		$output[] = array(
			array('foo' => 1),
			array(),
			FALSE,
			array(),
			array(),
			array('foo')
		);
		
		$output[] = array(
			array('foo' => '-2'),
			array(),
			FALSE,
			array(),
			array(),
			array('foo')
		);
		
		$output[] = array(
			array('foo' => '+7843984738974389472392389723894723'),
			array(),
			FALSE,
			array(),
			array(),
			array('foo')
		);
		
		$output[] = array(
			array('foo' => '-1843e5'),
			array(),
			FALSE,
			array(),
			array(),
			array('foo')
		);
		
		$output[] = array(
			array('foo' => 0),
			array(),
			FALSE,
			array(),
			array(),
			array('foo')
		);
		
		$output[] = array(
			array('foo' => '+1E3'),
			array(),
			FALSE,
			array(),
			array(),
			array('foo')
		);
		
		$output[] = array(
			array('foo' => '1.'),
			array(),
			TRUE,
			array('Foo: Please enter a whole number'),
			array(),
			array('foo')
		);
		
		$output[] = array(
			array('foo' => '1.0'),
			array(),
			TRUE,
			array('Foo: Please enter a whole number'),
			array(),
			array('foo')
		);
		
		$output[] = array(
			array('foo' => 'Test'),
			array(),
			TRUE,
			array('Foo: Please enter a whole number'),
			array(),
			array('foo')
		);
		
		$output[] = array(
			array('foo' => '1abc'),
			array(),
			TRUE,
			array('Foo: Please enter a whole number'),
			array(),
			array('foo')
		);
		
		$output[] = array(
			array('foo' => '1', 'bar' => -20),
			array(),
			FALSE,
			array(),
			array(),
			array('foo', 'bar')
		);
		
		return $output;
	}
	
	/**
	 * @dataProvider addIntegerProvider
	 */
	public function testAddInteger($get_params, $post_params, $should_throw_exception, $contains, $not_contains, $fields)
	{
		if ($should_throw_exception) {
			$this->setExpectedException('fValidationException');
		}
		
		$_GET  = array_merge($_GET, $get_params);
		$_POST = array_merge($_POST, $post_params);
		
		try {
			$v = new fValidation();
			call_user_func_array($v->addIntegerFields, $fields);
			$v->validate();
		} catch (fValidationException $e) {
			foreach ($contains as $string) {
				$this->assertContains($string, $e->getMessage());
			}
			foreach ($not_contains as $string) {
				$this->assertNotContains($string, $e->getMessage());
			}
			throw $e;
		}
	}
	
	public function testStringReplacement()
	{
		$this->setExpectedException('fValidationException');
		
		$_GET['bar'] = 'true';
		
		try {
			$v = new fValidation();
			$v->addStringReplacement('Please enter a value', 'Please fill in');
			$v->addStringReplacement('Bar', 'BAR');
			$v->addRequiredFields('foo');
			$v->addIntegerFields('bar');
			$v->validate();
		} catch (fValidationException $e) {
			$this->assertContains('Foo: Please fill in', $e->getMessage());
			$this->assertContains('BAR: Please enter a whole number', $e->getMessage());
			
			throw $e;
		}
	}
	
	public function testRegexReplacement()
	{
		$this->setExpectedException('fValidationException');
		
		$_GET['bar'] = 'true';
		
		try {
			$v = new fValidation();
			$v->addRegexReplacement('#Please enter a value$#', 'Please fill in');
			$v->addRegexReplacement('#bar#i', 'BAR');
			$v->addRequiredFields('foo');
			$v->addIntegerFields('bar');
			$v->validate();
		} catch (fValidationException $e) {
			$this->assertContains('Foo: Please fill in', $e->getMessage());
			$this->assertContains('BAR: Please enter a whole number', $e->getMessage());
			
			throw $e;
		}
	}
	
	public function testCustomFieldNames()
	{
		$this->setExpectedException('fValidationException');
		
		$_GET['bar'] = 'true';
		
		try {
			$v = new fValidation();
			$v->overrideFieldName(array(
				'foo' => 'Far',
				'bar' => 'BAR'
			));
			$v->addRequiredFields('foo');
			$v->addIntegerFields('bar');
			$v->validate();
		} catch (fValidationException $e) {
			$this->assertContains('Far: Please enter a value', $e->getMessage());
			$this->assertContains('BAR: Please enter a whole number', $e->getMessage());
			
			throw $e;
		}
	}
	
	public function testSetMessageOrder()
	{
		$this->setExpectedException('fValidationException');
		
		try {
			$v = new fValidation();
			$v->addRequiredFields('foobar', 'foo', 'baz', 'name');
			$v->setMessageOrder('name', 'foo', 'foobar');
			$v->validate();
		} catch (fValidationException $e) {
			$this->assertEquals(1, preg_match('#Name:.*</li>\n<li>Foo:.*</li>\n<li>Foobar:.*</li>\n<li>Baz:#', $e->getMessage()));
			
			throw $e;
		}
	}
	
	public function testReturnMessage()
	{
		$v = new fValidation();
		$v->addRequiredFields('foobar', 'foo', 'baz', 'name');
		$this->assertEquals(
			array(
				'foobar' => 'Foobar: Please enter a value',
				'foo'    => 'Foo: Please enter a value',
				'baz'    => 'Baz: Please enter a value',
				'name'   => 'Name: Please enter a value'
			),
			$v->validate(TRUE)
		);
	}
	
	public function testRemoveFieldNames()
	{
		$v = new fValidation();
		$v->addRequiredFields('foobar', 'foo', 'baz', 'name');
		$v->addOneOrMoreRule('email', 'phone');
		$this->assertEquals(
			array(
				'foobar' => 'Please enter a value',
				'foo'    => 'Please enter a value',
				'baz'    => 'Please enter a value',
				'name'   => 'Please enter a value',
				'email,phone' => 'Please enter a value for at least one'
			),
			$v->validate(TRUE, TRUE)
		);
	}
	
	public function testRemoveFieldNamesReorder()
	{
		$v = new fValidation();
		$v->addRequiredFields('foobar', 'foo', 'baz', 'name');
		$v->addOneOrMoreRule('email', 'phone');
		$v->setMessageOrder('email', 'foo', 'baz');
		$this->assertSame(
			array(
				'email,phone' => 'Please enter a value for at least one',
				'foobar' => 'Please enter a value',
				'foo'    => 'Please enter a value',
				'baz'    => 'Please enter a value',
				'name'   => 'Please enter a value'
			),
			$v->validate(TRUE, TRUE)
		);
		$this->assertNotSame(
			array(
				'email,phone' => 'Please enter a value for at least one',
				'foo'    => 'Please enter a value',
				'foobar' => 'Please enter a value',
				'baz'    => 'Please enter a value',
				'name'   => 'Please enter a value'
			),
			$v->validate(TRUE, TRUE)
		);
	}
	
	public function testFileUploadField()
	{
		$this->setExpectedException('fValidationException');
		
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_SERVER['CONTENT_TYPE'] = 'multipart/form-data';
		$_FILES = array();
		$_FILES['file'] = array(
			'name' => '',
			'type' => '',
			'tmp_name' => '',
			'error' => '',
			'size' => 0
		);
		
		$uploader = new fUpload();
		
		try {
			$v = new fValidation();
			$v->addFileUploadRule('file', $uploader);
			$v->validate();
		} catch (fValidationException $e) {
			$this->assertContains('File: Please upload a file', $e->getMessage());
			
			throw $e;
		}
	}
	
	public function testFileUploadField2()
	{
		$this->setExpectedException('fValidationException');
		
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_SERVER['CONTENT_TYPE'] = 'multipart/form-data';
		$_FILES = array();
		$_FILES['file'] = array(
			'name' => 'test.txt',
			'type' => '',
			'tmp_name' => './resources/text/example',
			'error' => '',
			'size' => 17
		);
		
		$uploader = new fUpload();
		$uploader->setMIMETypes(
			array('text/csv'),
			'Please upload a CSV file'
		);
		
		try {
			$v = new fValidation();
			$v->addFileUploadRule('file', $uploader);
			$v->validate();
		} catch (fValidationException $e) {
			$this->assertContains('File: Please upload a CSV file', $e->getMessage());
			
			throw $e;
		}
	}
	
	public function tearDown()
	{
			
	}
}