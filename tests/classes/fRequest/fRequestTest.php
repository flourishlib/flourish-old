<?php
require_once('./support/init.php');
 
class fRequestTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{	
		$_GET     = array();
		$_POST    = array();
		$_REQUEST = array();	
	}
	
	public function testGetBestAcceptLanguage()
	{
		$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-us,en;q=0.5';
		$this->assertEquals('en-us', fRequest::getBestAcceptLanguage());
	}
	
	public function testGetBestAcceptLanguageEmpty()
	{
		$_SERVER['HTTP_ACCEPT_LANGUAGE'] = '';
		$this->assertEquals(NULL, fRequest::getBestAcceptLanguage());
	}
	
	public function testGetBestAcceptLanguageNotSet()
	{
		$this->assertEquals(NULL, fRequest::getBestAcceptLanguage());
	}
	
	public function testGetBestAcceptLanguageWildcardNoFilter()
	{
		$_SERVER['HTTP_ACCEPT_LANGUAGE'] = '*;q=0.5';
		$this->assertEquals(NULL, fRequest::getBestAcceptLanguage());
	}
	
	public function testGetBestAcceptLanguageFilterNoMatch()
	{
		$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-us,en;q=0.5';
		$this->assertEquals(FALSE, fRequest::getBestAcceptLanguage('es-es', 'es-ec'));
	}
	
	public function testGetBestAcceptLanguageFilterPrefixMatch()
	{
		$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'es-es,en;q=0.5';
		$this->assertEquals('en-us', fRequest::getBestAcceptLanguage('en-us', 'es-ec'));
	}
	
	public function testGetBestAcceptLanguageFilterEmpty()
	{
		$_SERVER['HTTP_ACCEPT_LANGUAGE'] = '';
		$this->assertEquals('es-es', fRequest::getBestAcceptLanguage(array('es-es', 'es-ec')));
	}
	
	public function testGetBestAcceptLanguageFilterNotSet()
	{
		$this->assertEquals('es-es', fRequest::getBestAcceptLanguage('es-es', 'es-ec'));
	}
	
	public function testGetBestAcceptType()
	{
		$_SERVER['HTTP_ACCEPT'] = 'image/png,image/*;q=0.8,*/*;q=0.5';
		$this->assertEquals('image/png', fRequest::getBestAcceptType());
	}
	
	public function testGetBestAcceptTypeEmpty()
	{
		$_SERVER['HTTP_ACCEPT'] = '';
		$this->assertEquals(NULL, fRequest::getBestAcceptType());
	}
	
	public function testGetBestAcceptTypeNotSet()
	{
		$this->assertEquals(NULL, fRequest::getBestAcceptType());
	}
	
	public function testGetBestAcceptTypeWildcardNoFilter()
	{
		$_SERVER['HTTP_ACCEPT'] = '*/*;q=0.1';
		$this->assertEquals(NULL, fRequest::getBestAcceptType());
	}
	
	public function testGetBestAcceptTypeFilterNoMatch()
	{
		$_SERVER['HTTP_ACCEPT'] = 'image/png,image/*;q=0.8';
		$this->assertEquals(FALSE, fRequest::getBestAcceptType('text/html', 'application/json'));
	}
	
	public function testGetBestAcceptTypeFilterLowWildcardMatch()
	{
		$_SERVER['HTTP_ACCEPT'] = 'image/png,image/*;q=0.8,*/*;q=0.5';
		$this->assertEquals('text/html', fRequest::getBestAcceptType('text/html', 'application/json'));
	}
	
	public function testGetBestAcceptTypeFilterWildcardMatch()
	{
		$_SERVER['HTTP_ACCEPT'] = 'image/png,image/*;q=0.8,*/*;q=0.5';
		$this->assertEquals('image/jpeg', fRequest::getBestAcceptType(array('text/html', 'image/jpeg')));
	}
	
	public function testGetBestAcceptTypeFilterEmpty()
	{
		$_SERVER['HTTP_ACCEPT'] = '';
		$this->assertEquals('text/html', fRequest::getBestAcceptType('text/html', 'application/json'));
	}
	
	public function testGetBestAcceptTypeFilterNotSet()
	{
		$this->assertEquals('text/html', fRequest::getBestAcceptType('text/html', 'application/json'));
	}
	
	public function testCheckBlankField()
	{
		$_GET['test'] = '';
		$this->assertEquals(TRUE, fRequest::check('test'));
	}
	
	public function testCheckNullField()
	{
		$_POST['test'] = NULL;
		$this->assertEquals(FALSE, fRequest::check('test'));
	}
	
	public function testCheckArraySyntax()
	{
		$_POST['foo'] = array('bar' => '1');
		$this->assertEquals(TRUE, fRequest::check('foo[bar]'));
	}
	
	public function testCheckNestedArraySyntax()
	{
		$_GET['foo'] = array('bar' => array('baz' => '1'));
		$this->assertEquals(TRUE, fRequest::check('foo[bar][baz]'));
	}
	
	public function testGetBug53632()
	{
		$_GET['test'] = '2.2250738585072011e-308';
		$this->assertEquals(2.2250738585072012e-308, fRequest::get('test', 'float'));
	}
	
	public function testGetMissingField()
	{
		$this->assertEquals(NULL, fRequest::get('test'));
	}
	
	public function testGetBlankField()
	{
		$_GET['test'] = '';
		$this->assertEquals(NULL, fRequest::get('test'));
	}
	
	public function testGetBlankField2()
	{
		$_POST['test'] = '';
		$this->assertEquals(NULL, fRequest::get('test'));
	}
	
	public function testGetBlankFieldCastString()
	{
		$_GET['test'] = '';
		$this->assertEquals('', fRequest::get('test', 'string'));
	}
	
	public function testGetBlankFieldCastBoolean()
	{
		$_GET['test'] = '';
		$this->assertEquals(FALSE, fRequest::get('test', 'boolean'));
	}
	
	public function testGetBlankFieldCastInteger()
	{
		$_GET['test'] = '';
		$this->assertEquals(0, fRequest::get('test', 'integer'));
	}
	
	public function testGetCastInteger()
	{
		$_GET['test'] = '1827';
		$this->assertSame(1827, fRequest::get('test', 'integer'));
	}
	
	public function testGetCastLargeInteger()
	{
		$_GET['test'] = '182702337829321093210';
		$this->assertSame('182702337829321093210', fRequest::get('test', 'integer'));
	}
	
	public function testGetCastLargeIntegerForce()
	{
		$_GET['test'] = '182702337829321093210';
		$int = fRequest::get('test', 'integer!');
		// 64 bit machines will return this
		if ($int == 9223372036854775807) {
			$this->assertSame(9223372036854775807, $int);
		} else {
			$this->assertSame(2147483647, $int);
		}
	}
	
	public function testGetRemoveLowByte()
	{
		$_GET['test'] = "Thi\x00s is \x06a \x1Etest of removing low-byte\x09\x0A\x0D characters";
		$this->assertEquals("This is a test of removing low-byte\t\n\r characters", fRequest::get('test'));
	}
	
	public function testGetBlankFieldCastArray()
	{
		$_GET['test'] = '';
		$this->assertEquals(array(), fRequest::get('test', 'array'));
	}
	
	public function testGetBlankFieldCastDate()
	{
		$_GET['test'] = '';
		$this->assertEquals(TRUE, fRequest::get('test', 'date')->eq());
	}
	
	public function testGetBlankFieldCastTime()
	{
		$_GET['test'] = '';
		$this->assertEquals(TRUE, fRequest::get('test', 'time')->eq());
	}
	
	public function testGetBlankFieldCastTimestamp()
	{
		$_GET['test'] = '';
		$this->assertEquals(TRUE, fRequest::get('test', 'timestamp')->eq());
	}
	
	public function testGetMissingFieldCastString()
	{
		$this->assertEquals('', fRequest::get('test', 'string'));
	}

	public function testGetBlankFieldUseDefault()
	{
		$_GET['test'] = '';
		$this->assertEquals('foo', fRequest::get('test', 'string', 'foo', TRUE));
	}

	public function testGetBlankFieldUseDefault2()
	{
		$this->assertEquals('foo', fRequest::get('test', 'string', 'foo', TRUE));
	}
	
	public function testGetMissingFieldCastBoolean()
	{
		$this->assertEquals(FALSE, fRequest::get('test', 'boolean'));
	}
	
	public function testGetMissingFieldCastInteger()
	{
		$this->assertEquals(0, fRequest::get('test', 'integer'));
	}
	
	public function testGetMissingFieldCastArray()
	{
		$this->assertEquals(array(), fRequest::get('test', 'array'));
	}
	
	public function testGetMissingFieldCastDate()
	{
		$this->assertEquals(TRUE, fRequest::get('test', 'date')->eq());
	}
	
	public function testGetMissingFieldCastTime()
	{
		$this->assertEquals(TRUE, fRequest::get('test', 'time')->eq());
	}
	
	public function testGetMissingFieldCastTimestamp()
	{
		$this->assertEquals(TRUE, fRequest::get('test', 'timestamp')->eq());
	}
	
	public function testGetMissingFieldCastStringQuestion()
	{
		$this->assertEquals(NULL, fRequest::get('test', 'string?'));
	}
	
	public function testGetMissingFieldCastBooleanQuestion()
	{
		$this->assertEquals(NULL, fRequest::get('test', 'boolean?'));
	}
	
	public function testGetMissingFieldCastIntegerQuestion()
	{
		$this->assertEquals(NULL, fRequest::get('test', 'integer?'));
	}
	
	public function testGetMissingFieldCastArrayQuestion()
	{
		$this->assertEquals(NULL, fRequest::get('test', 'array?'));
	}
	
	public function testGetMissingFieldCastDateQuestion()
	{
		$this->assertEquals(NULL, fRequest::get('test', 'date?'));
	}
	
	public function testGetMissingFieldCastTimeQuestion()
	{
		$this->assertEquals(NULL, fRequest::get('test', 'time?'));
	}
	
	public function testGetMissingFieldCastTimestampQuestion()
	{
		$this->assertEquals(NULL, fRequest::get('test', 'timestamp?'));
	}
	
	public function testGetBlankFieldCastStringQuestion()
	{
		$_GET['test'] = '';
		$this->assertSame(NULL, fRequest::get('test', 'string?'));
	}
	
	public function testGetBlankFieldCastBooleanQuestion()
	{
		$_GET['test'] = '';
		$this->assertSame(NULL, fRequest::get('test', 'boolean?'));
	}
	
	public function testGetBlankFieldCastIntegerQuestion()
	{
		$_GET['test'] = '';
		$this->assertSame(NULL, fRequest::get('test', 'integer?'));
	}
	
	public function testGetBlankFieldCastArrayQuestion()
	{
		$_GET['test'] = '';
		$this->assertSame(NULL, fRequest::get('test', 'array?'));
	}
	
	public function testGetBlankFieldCastDateQuestion()
	{
		$_GET['test'] = '';
		$this->assertSame(NULL, fRequest::get('test', 'date?'));
	}
	
	public function testGetBlankFieldCastTimeQuestion()
	{
		$_GET['test'] = '';
		$this->assertSame(NULL, fRequest::get('test', 'time?'));
	}
	
	public function testGetBlankFieldCastTimestampQuestion()
	{
		$_GET['test'] = '';
		$this->assertEquals(NULL, fRequest::get('test', 'timestamp?'));
	}
	
	public function testGetFieldCastStringQuestion()
	{
		$_GET['test'] = 'test';
		$this->assertEquals('test', fRequest::get('test', 'string?'));
	}
	
	public function testGetFieldCastBooleanQuestion()
	{
		$_GET['test'] = 'true';
		$this->assertEquals(TRUE, fRequest::get('test', 'boolean?'));
	}
	
	public function testGetFieldCastIntegerQuestion()
	{
		$_GET['test'] = '10a';
		$this->assertEquals(10, fRequest::get('test', 'integer?'));
	}
	
	public function testGetFieldCastArrayQuestion()
	{
		$_GET['test'] = array(1,2);
		$this->assertEquals(array(1,2), fRequest::get('test', 'array?'));
	}
	
	public function testGetFieldCastDateQuestion()
	{
		$_GET['test'] = '5/7/2009';
		$this->assertEquals(TRUE, fRequest::get('test', 'date?')->eq('5/7/2009'));
	}
	
	public function testGetFieldCastTimeQuestion()
	{
		$_GET['test'] = '5 am';
		$this->assertEquals(TRUE, fRequest::get('test', 'time?')->eq('5 am'));
	}
	
	public function testGetFieldCastTimestampQuestion()
	{
		$_GET['test'] = '5/7/2009 5 am';
		$this->assertEquals(TRUE, fRequest::get('test', 'timestamp?')->eq('5/7/2009 5 am'));
	}
	
	public function testGetCastArrayFromCommas()
	{
		$_GET['test'] = 'one,two,three';
		$this->assertEquals(array('one', 'two', 'three'), fRequest::get('test', 'array'));
	}
	
	public function testGetCastBooleanFromWord()
	{
		$_GET['test'] = 'f';
		$this->assertEquals(FALSE, fRequest::get('test', 'boolean'));
	}
	
	public function testGetCastBooleanFromWord2()
	{
		$_GET['test'] = 'false';
		$this->assertEquals(FALSE, fRequest::get('test', 'boolean'));
	}
	
	public function testGetCastBooleanFromWord3()
	{
		$_GET['test'] = 'no';
		$this->assertEquals(FALSE, fRequest::get('test', 'boolean'));
	}
	
	public function testGetArray()
	{
		$_GET['user'] = array(
			'name'  => 'John',
			'email' => 'john@smith.com'
		);
		$this->assertEquals('John', fRequest::get('user[name]'));
	}
	
	public function testGetMultiDimensionalArray()
	{
		$_GET['user'] = array(
			'name'  => array(
				'first' => 'John',
				'last'  => 'Smith'
			),
			'email' => 'john@smith.com'
		);
		$this->assertEquals('John', fRequest::get('user[name][first]'));
	}
	
	public function testGetThreeDimensionalArray()
	{
		$_GET['user'] = array(
			'name'  => array(
				'first' => array('prefix' => 'Mr', 'name' => 'John'),
				'last'  => array('suffix' => 'Jr', 'name' => 'Smith')
			),
			'email' => 'john@smith.com'
		);
		$this->assertEquals(array(
			'name'  => array(
				'first' => array('prefix' => 'Mr', 'name' => 'John'),
				'last'  => array('suffix' => 'Jr', 'name' => 'Smith')
			),
			'email' => 'john@smith.com'
		), fRequest::get('user'));
	}
	
	public function testGetThreeDimensionalArrayDereference()
	{
		$_GET['user'] = array(
			'name'  => array(
				'first' => array('prefix' => 'Mr', 'name' => 'John'),
				'last'  => array('suffix' => 'Jr', 'name' => 'Smith')
			),
			'email' => 'john@smith.com'
		);
		$this->assertEquals(array('prefix' => 'Mr', 'name' => 'John'), fRequest::get('user[name][first]'));
	}
	
	public function testGetIntegerOnArray()
	{
		$_GET['numbers'] = array('1', '592', 'abc', array());
		$this->assertSame(1, fRequest::get('numbers', 'integer'));
	}
	
	public function testGetIntegerArrayOnArray()
	{
		$_GET['numbers'] = array('1', '592', 'abc', array());
		$this->assertSame(array(1, 592, 0, 0), fRequest::get('numbers', 'integer[]'));
	}
	
	public function testGetIntegerArrayOnInteger()
	{
		$_GET['numbers'] = '1';
		$this->assertSame(array(1), fRequest::get('numbers', 'integer[]'));
	}
	
	public function testGetIntegerArrayOnString()
	{
		$_GET['numbers'] = '1,000,000';
		$this->assertSame(array(1, 0, 0), fRequest::get('numbers', 'integer[]'));
	}
	
	public function testGetIntegerArrayOnBlank()
	{
		$_GET['numbers'] = '';
		$this->assertSame(array(), fRequest::get('numbers', 'integer[]'));
	}
	
	public function testGetIntegerArrayOnNull()
	{
		$_GET['numbers'] = NULL;
		$this->assertSame(array(), fRequest::get('numbers', 'integer[]'));
	}
	
	public function testGetArrayDefault()
	{
		$_GET['user'] = array(
			'name'  => array(
				'first' => 'John',
				'last'  => 'Smith'
			),
			'email' => 'john@smith.com'
		);
		$this->assertEquals('N/A', fRequest::get('user[full_name][first]', 'string', 'N/A'));
	}
	
	public function testGetArrayNoValue()
	{
		$_GET['user'] = array(
			'name'  => array(
				'first' => 'John',
				'last'  => 'Smith'
			),
			'email' => 'john@smith.com'
		);
		$this->assertEquals(NULL, fRequest::get('user[full_name][first][0]'));
	}
	
	public function testGetArrayCast()
	{
		$_GET['user'] = array(
			'name'  => array(
				'first' => 'John',
				'last'  => 'Smith'
			),
			'email' => 'john@smith.com',
			'times_logged_in' => '3'
		);
		$this->assertEquals(3, fRequest::get('user[times_logged_in]', 'integer'));
	}
	
	public function testSetArray()
	{
		fRequest::set('user[name]', 'John');
		$this->assertEquals(
			array(
				'user' => array('name'  => 'John')
			),
			$_GET
		);
	}
	
	public function testSetMultiDimensionalArray()
	{
		fRequest::set('user[name][first]', 'John');
		$this->assertEquals(
			array(
				'user' => array(
					'name'  => array(
						'first' => 'John'
					)
				)
			),
			$_GET
		);
	}
	
	public function testSetArrayOverrideScalar()
	{
		$_GET = array(
			'user' => array(
				'name'  => 'John Smith',
				'email' => 'john@smith.com'
			)
		);
		fRequest::set('user[name][first]', 'John');
		$this->assertEquals(
			array(
				'user' => array(
					'name'  => array(
						'first' => 'John'
					),
					'email' => 'john@smith.com'
				)
			),
			$_GET
		);
	}
	
	public function testGetPostOverGet()
	{
		$_GET['test'] = 'get';
		$_POST['test'] = 'post';
		$this->assertEquals('post', fRequest::get('test'));
	}
	
	public function testGetFieldCastIntegerLarge()
	{
		$_GET['test'] = '173923263927309232632545218129';
		$this->assertEquals('173923263927309232632545218129', fRequest::get('test', 'integer'));
	}
	
	public function testGetFieldCastIntegerNegativeLarge()
	{
		$_GET['test'] = '-173923263927309232632545218129';
		$this->assertEquals('-173923263927309232632545218129', fRequest::get('test', 'integer'));
	}
	
	public function tearDown()
	{
			
	}
}