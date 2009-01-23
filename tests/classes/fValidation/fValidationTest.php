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
	
	public function testRequiredFields()
	{
		$this->setExpectedException('fValidationException');
		
		try {
			$v = new fValidation();
			$v->addRequiredFields('foo');
			$v->validate();
			
		} catch (fValidationException $e) {
			$this->assertContains('Foo: Please enter a value', $e->getMessage());
			throw $e;	
		}
	}
	
	public function testRequiredFields2()
	{
		$this->setExpectedException('fValidationException');
		
		$_GET['foo'] = 'This is a test';
		
		try {
			$v = new fValidation();
			$v->addRequiredFields('foo', 'bar');
			$v->validate();
			
		} catch (fValidationException $e) {
			$this->assertNotContains('Foo: Please enter a value', $e->getMessage());
			$this->assertContains('Bar: Please enter a value', $e->getMessage());
			throw $e;	
		}
	}
	
	public function testRequiredFields3()
	{
		$this->setExpectedException('fValidationException');
		
		$_POST['foo'] = 'This is a test';
		
		try {
			$v = new fValidation();
			$v->addRequiredFields('foo', 'bar');
			$v->validate();
			
		} catch (fValidationException $e) {
			$this->assertNotContains('Foo: Please enter a value', $e->getMessage());
			$this->assertContains('Bar: Please enter a value', $e->getMessage());
			throw $e;	
		}
	}
	
	public function testRequiredFields4()
	{
		$_GET['foo'] = 'This is a test';
		$_GET['bar'] = 'This is a test';
		
		$v = new fValidation();
		$v->addRequiredFields('foo', 'bar');
		$v->validate();
	}
	
	public function testRequiredFields5()
	{
		$_POST['foo'] = 'This is a test';
		$_POST['bar'] = 'This is a test';
		
		$v = new fValidation();
		$v->addRequiredFields('foo', 'bar');
		$v->validate();
	}
	
	public function testRequiredFields6()
	{
		$_GET['foo'] = 'This is a test';
		$_POST['bar'] = 'This is a test';
		
		$v = new fValidation();
		$v->addRequiredFields('foo', 'bar');
		$v->validate();
	}
	
	public function testRequiredFields7()
	{
		$this->setExpectedException('fValidationException');
		
		$_GET['foo'] = '';
		
		try {
			$v = new fValidation();
			$v->addRequiredFields('foo', 'bar');
			$v->validate();
			
		} catch (fValidationException $e) {
			$this->assertContains('Foo: Please enter a value', $e->getMessage());
			$this->assertContains('Bar: Please enter a value', $e->getMessage());
			throw $e;	
		}
	}
	
	public function testRequiredFields8()
	{
		$this->setExpectedException('fValidationException');
		
		$_POST['bar'] = '';
		
		try {
			$v = new fValidation();
			$v->addRequiredFields('foo', 'bar');
			$v->validate();
			
		} catch (fValidationException $e) {
			$this->assertContains('Foo: Please enter a value', $e->getMessage());
			$this->assertContains('Bar: Please enter a value', $e->getMessage());
			throw $e;	
		}
	}
	
	public function testRequiredFields9()
	{
		$_GET['foo'] = '0';
		$_POST['bar'] = '0';
		
		$v = new fValidation();
		$v->addRequiredFields('foo', 'bar');
		$v->validate();
	}
	
	public function testRequiredFields10()
	{
		$_GET['foo'] = '0';
		$_POST['bar'] = '0';
		
		$v = new fValidation();
		$v->addRequiredFields('foo');
		$v->addRequiredFields('bar');
		$v->validate();
	}
	
	public function testRequiredFields11()
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
	
	public function testRequiredFieldsOr()
	{
		$this->setExpectedException('fValidationException');
		
		try {
			$v = new fValidation();
			$v->addRequiredFields(array('foo', 'bar'));
			$v->validate();
			
		} catch (fValidationException $e) {
			$this->assertContains('Foo, Bar: Please enter at least one', $e->getMessage());
			throw $e;	
		}
	}
	
	public function testRequiredFieldsOr2()
	{
		$this->setExpectedException('fValidationException');
		
		$_GET['far'] = 'This is a test';
		
		try {
			$v = new fValidation();
			$v->addRequiredFields(array('foo', 'bar'));
			$v->validate();
			
		} catch (fValidationException $e) {
			$this->assertContains('Foo, Bar: Please enter at least one', $e->getMessage());
			throw $e;	
		}
	}
	
	public function testRequiredFieldsOr3()
	{
		$_GET['foo'] = 'This is a test';
		
		$v = new fValidation();
		$v->addRequiredFields(array('foo', 'bar'));
		$v->validate();
	}
	
	public function testRequiredFieldsOr4()
	{
		$_GET['bar'] = 'This is a test';
		
		$v = new fValidation();
		$v->addRequiredFields(array('foo', 'bar'));
		$v->validate();
	}
	
	public function testRequiredFieldsOr5()
	{
		$_GET['foo'] = 'This is a test';
		$_GET['bar'] = 'This is a test';
		
		$v = new fValidation();
		$v->addRequiredFields(array('foo', 'bar'));
		$v->validate();
	}
	
	public function testRequiredFieldsOr6()
	{
		$this->setExpectedException('fValidationException');
		
		$_GET['foo'] = '';
		$_POST['bar'] = '';
		
		try {
			$v = new fValidation();
			$v->addRequiredFields(array('foo', 'bar'));
			$v->validate();
			
		} catch (fValidationException $e) {
			$this->assertContains('Foo, Bar: Please enter at least one', $e->getMessage());
			throw $e;	
		}
	}
	
	public function testRequiredFieldsOr7()
	{
		$_GET['foo'] = '0';
		$_POST['bar'] = '';
		
		$v = new fValidation();
		$v->addRequiredFields(array('foo', 'bar'));
		$v->validate();
	}
	
	public function testRequiredFieldsOr8()
	{
		$_POST['foo'] = '0';
		$_POST['bar'] = '';
		
		$v = new fValidation();
		$v->addRequiredFields(array('foo', 'bar'));
		$v->validate();
	}
	
	public function testRequiredFieldsConditional()
	{
		$v = new fValidation();
		$v->addRequiredFields(array('foo' => array('bar')));
		$v->validate();
	}
	
	public function testRequiredFieldsConditional2()
	{
		$this->setExpectedException('fValidationException');
		
		$_GET['foo'] = 'This is a test';
		
		try {
			$v = new fValidation();
			$v->addRequiredFields(array('foo' => array('bar')));
			$v->validate();
			
		} catch (fValidationException $e) {
			$this->assertContains('Bar: Please enter a value', $e->getMessage());
			throw $e;	
		}
	}
	
	public function testRequiredFieldsConditional3()
	{
		$this->setExpectedException('fValidationException');
		
		$_POST['foo'] = 'This is a test';
		
		try {
			$v = new fValidation();
			$v->addRequiredFields(array('foo' => array('bar')));
			$v->validate();
			
		} catch (fValidationException $e) {
			$this->assertContains('Bar: Please enter a value', $e->getMessage());
			throw $e;	
		}
	}
	
	public function testRequiredFieldsConditional4()
	{
		$_GET['far'] = 'This is a test';
		
		$v = new fValidation();
		$v->addRequiredFields(array('foo' => array('bar')));
		$v->validate();
	}
	
	public function testRequiredFieldsConditional5()
	{
		$_GET['foo'] = 'This is a test';
		$_GET['bar'] = 'This is a test';
		
		$v = new fValidation();
		$v->addRequiredFields(array('foo' => array('bar')));
		$v->validate();
	}
	
	public function testRequiredFieldsConditional6()
	{
		$_POST['foo'] = 'This is a test';
		$_POST['bar'] = 'This is a test';
		
		$v = new fValidation();
		$v->addRequiredFields(array('foo' => array('bar')));
		$v->validate();
	}
	
	public function testRequiredFieldsConditional7()
	{
		$_POST['foo'] = 'This is a test';
		$_GET['bar'] = 'This is a test';
		
		$v = new fValidation();
		$v->addRequiredFields(array('foo' => array('bar')));
		$v->validate();
	}
	
	public function testRequiredCombo()
	{
		$this->setExpectedException('fValidationException');
		
		try {
			$v = new fValidation();
			$v->addRequiredFields('foo');
			$v->addRequiredFields(array('bar', 'baz'));
			$v->validate();
			
		} catch (fValidationException $e) {
			$this->assertContains('Foo: Please enter a value', $e->getMessage());
			$this->assertContains('Bar, Baz: Please enter at least one', $e->getMessage());
			throw $e;	
		}
	}
	
	public function testRequiredCombo2()
	{
		$this->setExpectedException('fValidationException');
		
		try {
			$v = new fValidation();
			$v->addRequiredFields(array('foo' => array('bar')));
			$v->addRequiredFields(array('bar', 'baz'));
			$v->validate();
			
		} catch (fValidationException $e) {
			$this->assertContains('Bar, Baz: Please enter at least one', $e->getMessage());
			throw $e;	
		}
	}
	
	public function testRequiredCombo3()
	{
		$this->setExpectedException('fValidationException');
		
		$_GET['foo'] = 'This is a test';
		
		try {
			$v = new fValidation();
			$v->addRequiredFields(array('foo' => array('bar')));
			$v->addRequiredFields(array('bar', 'baz'));
			$v->validate();
			
		} catch (fValidationException $e) {
			$this->assertContains('Bar: Please enter a value', $e->getMessage());
			$this->assertContains('Bar, Baz: Please enter at least one', $e->getMessage());
			throw $e;	
		}
	}
	
	public function testEmail()
	{
		$this->setExpectedException('fValidationException');
		
		$_GET['foo'] = 'This is a test';
		
		try {
			$v = new fValidation();
			$v->addEmailFields('foo');
			$v->validate();
			
		} catch (fValidationException $e) {
			$this->assertContains('Foo: Please enter an email address in the form name@example.com', $e->getMessage());
			throw $e;	
		}
	}
	
	public function testEmail2()
	{
		$v = new fValidation();
		$v->addEmailFields('foo');
		$v->validate();
	}
	
	public function testEmail3()
	{
		$this->setExpectedException('fValidationException');
		
		$_GET['foo'] = 'This is a test';
		$_GET['bar'] = '0';
		
		try {
			$v = new fValidation();
			$v->addEmailFields('foo', 'bar');
			$v->validate();
			
		} catch (fValidationException $e) {
			$this->assertContains('Foo: Please enter an email address in the form name@example.com', $e->getMessage());
			$this->assertContains('Bar: Please enter an email address in the form name@example.com', $e->getMessage());
			throw $e;	
		}
	}
	
	public static function email4Provider()
	{
		$output = array();
		
		$output[] = array('will@flourishlib.com');
		$output[] = array('name@example.com');
		$output[] = array('john.smith@sub.domain.co.uk');
		$output[] = array("O'donnel@www.example.com");
		$output[] = array('foo+bar@example.com');
		$output[] = array('foo-b_a_r@example.com');
		$output[] = array('"crazy"."example with quotes strings"@example.com');
		$output[] = array('foo~bar@example.com');
		$output[] = array('name@192.168.0.1');
		
		return $output;
	}
	
	/**
	 * @dataProvider email4Provider
	 */
	public function testEmail4($email)
	{
		$_GET['foo'] = $email;
		
		$v = new fValidation();
		$v->addEmailFields('foo');
		$v->validate();
	}
	
	public static function email5Provider()
	{
		$output = array();
		
		$output[] = array('will.@flourishlib.com');
		$output[] = array('name @example.com');
		$output[] = array('will..bond@flourishlib.com');
		$output[] = array('.will@flourishlib.com');
		
		return $output;
	}
	
	/**
	 * @dataProvider email5Provider
	 */
	public function testEmail5($email)
	{
		$this->setExpectedException('fValidationException');
		
		$_GET['foo'] = $email;
		
		$v = new fValidation();
		$v->addEmailFields('foo');
		$v->validate();
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
	
	public function testEmailHeader()
	{
		$this->setExpectedException('fValidationException');
		
		$_GET['foo'] = "This is a test\n";
		
		try {
			$v = new fValidation();
			$v->addEmailHeaderFields('foo');
			$v->validate();
			
		} catch (fValidationException $e) {
			$this->assertContains('Foo: Line breaks are not allowed', $e->getMessage());
			throw $e;	
		}
	}
	
	public function testEmailHeader2()
	{
		$this->setExpectedException('fValidationException');
		
		$_GET['foo'] = "This is a test\n";
		$_GET['bar'] = "This is a test";
		
		try {
			$v = new fValidation();
			$v->addEmailHeaderFields('foo', 'bar');
			$v->validate();
			
		} catch (fValidationException $e) {
			$this->assertContains('Foo: Line breaks are not allowed', $e->getMessage());
			$this->assertNotContains('Bar: Line breaks are not allowed', $e->getMessage());
			throw $e;	
		}
	}
	
	public function testEmailHeader3()
	{
		$_GET['foo'] = "This is a test";
		$_GET['bar'] = "This is a test";
		
		$v = new fValidation();
		$v->addEmailHeaderFields('foo', 'bar');
		$v->validate();
	}
	
	public function testAddDate()
	{
		$this->setExpectedException('fValidationException');
		
		$_GET['foo'] = "foobar";
		
		try {
			$v = new fValidation();
			$v->addDateFields('foo');
			$v->validate();
			
		} catch (fValidationException $e) {
			$this->assertContains('Foo: Please enter a date', $e->getMessage());
			throw $e;	
		}
	}
	
	public function testAddDate2()
	{
		$this->setExpectedException('fValidationException');
		
		$_POST['foo'] = "foobar";
		
		try {
			$v = new fValidation();
			$v->addDateFields('foo');
			$v->validate();
			
		} catch (fValidationException $e) {
			$this->assertContains('Foo: Please enter a date', $e->getMessage());
			throw $e;	
		}
	}
	
	public function testAddDate3()
	{
		$v = new fValidation();
		$v->addDateFields('foo');
		$v->validate();
	}
	
	public static function addDate4Provider()
	{
		$output = array();
		
		$output[] = array('today');
		$output[] = array('1/10/08');
		$output[] = array('tomorrow');
		$output[] = array('2009-01-01');
		$output[] = array('jan 1st, 2009');
		
		return $output;
	}
	
	/**
	 * @dataProvider addDate4Provider
	 */
	public function testAddDate4($date)
	{
		$_GET['foo'] = $date;
		
		$v = new fValidation();
		$v->addDateFields('foo');
		$v->validate();
	}
	
	public function testAddDate5()
	{
		$_POST['foo'] = '10/1/2008';
		
		$v = new fValidation();
		$v->addDateFields('foo');
		$v->validate();
	}
	
	public function testAddDate6()
	{
		$this->setExpectedException('fValidationException');
		
		$_POST['foo'] = "foobar";
		$_GET['bar'] = "foobar";
		
		try {
			$v = new fValidation();
			$v->addDateFields('foo', 'bar');
			$v->validate();
			
		} catch (fValidationException $e) {
			$this->assertContains('Foo: Please enter a date', $e->getMessage());
			$this->assertContains('Bar: Please enter a date', $e->getMessage());
			throw $e;	
		}
	}
	
	public function testAddURL()
	{
		$this->setExpectedException('fValidationException');
		
		$_GET['foo'] = "foobar";
		
		try {
			$v = new fValidation();
			$v->addURLFields('foo');
			$v->validate();
			
		} catch (fValidationException $e) {
			$this->assertContains('Foo: Please enter a URL in the form http://www.example.com/page', $e->getMessage());
			throw $e;	
		}
	}
	
	public function testAddURL2()
	{
		$this->setExpectedException('fValidationException');
		
		$_POST['foo'] = "foobar";
		
		try {
			$v = new fValidation();
			$v->addURLFields('foo');
			$v->validate();
			
		} catch (fValidationException $e) {
			$this->assertContains('Foo: Please enter a URL in the form http://www.example.com/page', $e->getMessage());
			throw $e;	
		}
	}
	
	public function testAddURL3()
	{
		$v = new fValidation();
		$v->addURLFields('foo');
		$v->validate();
	}
	
	public static function addURL4Provider()
	{
		$output = array();
		
		$output[] = array('http://flourishlib.com');
		$output[] = array('http://www.flourishlib.com');
		$output[] = array('https://flourishlib.com');
		$output[] = array('http://flourishlib.co.uk');
		$output[] = array('http://flourishlib.com/this/is/a/page.php?query=string&foo=base%20');
		
		return $output;
	}
	
	/**
	 * @dataProvider addURL4Provider
	 */
	public function testAddURL4($url)
	{
		$_GET['foo'] = $url;
		
		$v = new fValidation();
		$v->addURLFields('foo');
		$v->validate();
	}
	
	public function testAddURL5()
	{
		$_POST['foo'] = 'http://flourishlib.com';
		
		$v = new fValidation();
		$v->addURLFields('foo');
		$v->validate();
	}
	
	public function testAddURL6()
	{
		$this->setExpectedException('fValidationException');
		
		$_POST['foo'] = "http://flourishlib.com/foo bar/";
		$_GET['bar'] = "http://flourishlib.com/foo bar/";
		
		try {
			$v = new fValidation();
			$v->addURLFields('foo', 'bar');
			$v->validate();
			
		} catch (fValidationException $e) {
			$this->assertContains('Foo: Please enter a URL in the form http://www.example.com/page', $e->getMessage());
			$this->assertContains('Bar: Please enter a URL in the form http://www.example.com/page', $e->getMessage());
			throw $e;	
		}
	}
	
	public function tearDown()
	{
			
	}
}