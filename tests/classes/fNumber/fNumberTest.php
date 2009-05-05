<?php
require_once('./support/init.php');
 
class fNumberTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{	
		
	}
	
	public static function constructProvider()
	{
		$output = array();
		
		$output[] = array('1', NULL, '1');
		$output[] = array('-0', NULL, '0');
		$output[] = array('-0', 2, '0.00');
		$output[] = array('-7839', 2, '-7839.00');
		$output[] = array('0000', NULL, '0');
		$output[] = array('001', 1, '1.0');
		$output[] = array('5.9473922', 0, '5');
		$output[] = array('+25', 0, '25');
		$output[] = array('2e2', NULL, '200');
		$output[] = array('2e+2', NULL, '200');
		$output[] = array('2e-2', NULL, '0.02');
		$output[] = array('202e-2', NULL, '2.02');
		$output[] = array('-894389378923687534937964382789432.89734638', 3, '-894389378923687534937964382789432.897');
		
		return $output;
	}
	
	/**
	 * @dataProvider constructProvider
	 */
	public function testConstruct($number, $scale, $output)
	{
		$num = new fNumber($number, $scale);
		$this->assertSame($output, $num->__toString());	
	}
	
	public static function invalidNumProvider()
	{
		$output = array();
		
		$output[] = array('788EF97');
		$output[] = array('NaN');
		$output[] = array('Inf');
		$output[] = array('++0');
		$output[] = array('99.');
		$output[] = array('foobar');
		
		return $output;
	}
	
	/**
	 * @dataProvider invalidNumProvider
	 * @expectedException fValidationException
	 */
	public function testConstructFail($number)
	{
		new fNumber($number);
	}
	
	public static function baseConvertProvider()
	{
		$output = array();
		
		$output[] = array('0', 2, 2, '0');
		$output[] = array('0', 8, 2, '0');
		$output[] = array('0', 10, 2, '0');
		$output[] = array('0', 16, 2, '0');
		$output[] = array('0', 2, 16, '0');
		$output[] = array('0', 2, 10, '0');
		$output[] = array('0', 2, 8, '0');
		$output[] = array('1', 10, 2, '1');
		$output[] = array('11', 10, 2, '1011');
		$output[] = array('486465', 10, 2, '1110110110001000001');
		$output[] = array('1110110110001000001', 2, 10, '486465');
		$output[] = array('1110110110001000001', 2, 8, '1666101');
		$output[] = array('1666101', 8, 2, '1110110110001000001');
		$output[] = array('1110110110001000001', 2, 16, '76C41');
		$output[] = array('76C41', 16, 2, '1110110110001000001');
		$output[] = array('486465', 10, 16, '76C41');
		$output[] = array('76C41', 16, 10, '486465');
		$output[] = array('4861465864534618135486', 10, 2, '1000001111000101001100100010001101010010011101001001100011101101110111110');
		$output[] = array('1000001111000101001100100010001101010010011101001001100011101101110111110', 2, 10, '4861465864534618135486');
		$output[] = array('9972173149006060478', 10, 16, '8A6446A4E931DBBE');
		$output[] = array('8A6446A4E931DBBE', 16, 10, '9972173149006060478');
		$output[] = array('1051442152235114355676', 8, 16, '8A6446A4E931DBBE');
		$output[] = array('8A6446A4E931DBBE', 16, 8, '1051442152235114355676');
		$output[] = array('11010110100101101010001010010000100000000011010011', 2, 10, '943769843269843');
		$output[] = array('943769843269843', 10, 2, '11010110100101101010001010010000100000000011010011');
		
		return $output;
	}
	
	/**
	 * @dataProvider baseConvertProvider
	 */
	public function testBaseConvert($number, $from_base, $to_base, $output)
	{
		$this->assertSame($output, fNumber::baseConvert($number, $from_base, $to_base));	
	}
	
	public static function baseConvertFailProvider()
	{
		$output = array();
		
		$output[] = array('-1', 2, 2, '0');
		$output[] = array('5.2', 8, 2, '0');
		$output[] = array('0', 1, 2, '0');
		$output[] = array('0', 26, 2, '0');
		$output[] = array('0', 2, 1, '0');
		$output[] = array('0', 2, 59, '0');
		$output[] = array(new fNumber('0'), 2, 8, '0');
		
		return $output;
	}
	
	/**
	 * @dataProvider baseConvertFailProvider
	 * @expectedException fProgrammerException
	 */
	public function testBaseConvertFail($number, $from_base, $to_base, $output)
	{
		fNumber::baseConvert($number, $from_base, $to_base);
	}
	
	public static function absProvider()
	{
		$output = array();
		
		$output[] = array('1', NULL, '1');
		$output[] = array('-1', NULL, '1');
		$output[] = array('-7839', 2, '7839.00');
		$output[] = array('-1345.123', 1, '1345.1');
		$output[] = array('-1', 0, '1');
		$output[] = array('500', 5, '500.00000');
		$output[] = array('-894389378923687534937964382789432.89734638', 1.01, '894389378923687534937964382789432.8');
		
		return $output;
	}
	
	/**
	 * @dataProvider absProvider
	 */
	public function testAbs($number, $scale, $output)
	{
		$num = new fNumber($number);
		$this->assertSame($output, $num->abs($scale)->__toString());	
	}
	
	public static function addProvider()
	{
		$output = array();
		
		$output[] = array('1', '1', '2', NULL);
		$output[] = array('0', '0', '0', NULL);
		$output[] = array('00000', '000', '0', NULL);
		$output[] = array('01', '1.0', '2', NULL);
		$output[] = array('10', '37', '47', NULL);
		$output[] = array('1111111111111111', '11111111111111111111', '11112222222222222222', NULL);
		$output[] = array('25', '-10', '15', NULL);
		$output[] = array('-10', '5', '-5', NULL);
		$output[] = array('-5', '-5', '-10', NULL);
		$output[] = array('4786465484146546132484351564', '8484864684641258979494613132', '13271330168787805111978964696', NULL);
		$output[] = array('2.1849383', '1.39293092111', '3.5778692', NULL);
		$output[] = array('1.0e5', '1.01', '2.010000', NULL);
		$output[] = array('1001e-3', '1', '2.001', NULL);
		$output[] = array('1', '1', '2', 0);
		$output[] = array('0', '-0.0', '0.00', 2.99);
		$output[] = array('2.1849383', '1.39293092111', '3.57', 2.1);
		$output[] = array('-8473947322.84773758326', '-8473937222.8362627482', '-16947884545.6840003314', 10);
		
		return $output;
	}
	
	/**
	 * @dataProvider addProvider
	 */
	public function testAdd($input1, $input2, $output, $scale)
	{
		$num = new fNumber($input1);
		$this->assertSame($output, $num->add($input2, $scale)->__toString());	
	}
	
	/**
	 * @dataProvider invalidNumProvider
	 * @expectedException fValidationException
	 */
	public function testAddFail($number)
	{
		$num = new fNumber('1');
		$num->add($number);
	}
	
	public static function ceilProvider()
	{
		$output = array();
		
		$output[] = array('1.00', '1');
		$output[] = array('1.000001', '2');
		$output[] = array('2.9999', '3');
		$output[] = array('89999999999999999999999999999.89734638', '90000000000000000000000000000');
		$output[] = array('-3.000', '-3');
		$output[] = array('-4.235', '-4');
		$output[] = array('-0', '0');
		$output[] = array('0', '0');
		$output[] = array('-0.0001', '0');
		
		return $output;
	}
	
	/**
	 * @dataProvider ceilProvider
	 */
	public function testCeil($number, $output)
	{
		$num = new fNumber($number);
		$this->assertSame($output, $num->ceil()->__toString());	
	}
	
	public static function divProvider()
	{
		$output = array();
		
		$output[] = array('1', '1', '1', NULL);
		$output[] = array('0', '3', '0', NULL);
		$output[] = array('00000', '01', '0', NULL);
		$output[] = array('15', '3', '5', NULL);
		$output[] = array('12', '3.8', '3', NULL);
		$output[] = array('1', '5837893647832', '0', NULL);
		$output[] = array('25', '-5', '-5', NULL);
		$output[] = array('-10', '5', '-2', NULL);
		$output[] = array('-5', '-5', '1', NULL);
		$output[] = array('4786465484146546132484351564', '48645314653165123656', '98395200', NULL);
		$output[] = array('2.3549383', '1.1', '2.1408530', NULL);
		$output[] = array('2.35', '1.1', '2.13', NULL);
		$output[] = array('2.35', '1.1', '2.136363636363', 12);
		$output[] = array('10', '3', '3.33333', 5);
		$output[] = array('10', '3', '3', 0);
		$output[] = array('-10', '5.00000', '-2.00', 2.281);
		
		return $output;
	}
	
	/**
	 * @dataProvider divProvider
	 */
	public function testDiv($input1, $input2, $output, $scale)
	{
		$num = new fNumber($input1);
		$this->assertSame($output, $num->div($input2, $scale)->__toString());	
	}
	
	/**
	 * @dataProvider invalidNumProvider
	 * @expectedException fValidationException
	 */
	public function testDivFail($number)
	{
		$num = new fNumber('1');
		$num->add($number);
	}
	
	/**
	 * @expectedException fValidationException
	 */
	public function testDivFail2()
	{
		$num = new fNumber('1');
		$num->div('0');
	}
	
	public static function floorProvider()
	{
		$output = array();
		
		$output[] = array('1.00', '1');
		$output[] = array('1.000001', '1');
		$output[] = array('2.9999', '2');
		$output[] = array('89999999999999999999999999999.89734638', '89999999999999999999999999999');
		$output[] = array('-3.000', '-3');
		$output[] = array('-4.235', '-5');
		$output[] = array('-0', '0');
		$output[] = array('0', '0');
		$output[] = array('-0.0001', '-1');
		
		return $output;
	}
	
	/**
	 * @dataProvider floorProvider
	 */
	public function testFloor($number, $output)
	{
		$num = new fNumber($number);
		$this->assertSame($output, $num->floor()->__toString());	
	}
	
	public static function fmodProvider()
	{
		$output = array();
		
		$output[] = array('1.00', '1.00', '0.00', NULL);
		$output[] = array('10', '2.3', '0', NULL);
		$output[] = array('89999999954894039.89734638', '8973833462232', '1424162169311.89734638', NULL);
		$output[] = array('23.2', '4.1', '2.7', NULL);
		$output[] = array('23.2', '-4.1', '2.7', NULL);
		$output[] = array('-23.2', '-4.1', '2.7', NULL);
		$output[] = array('-23.2', '4.1', '2.7', NULL);
		$output[] = array('89999999954894039.89734638', '8973833462232', '1424162169311', 0);
		$output[] = array('89999999954894039.89734638', '8973833462232', '1424162169311.8', 1);
		$output[] = array('89999999954894039.89734638', '-8973833462232', '1424162169311.89734', 5);
		
		return $output;
	}
	
	/**
	 * @dataProvider fmodProvider
	 */
	public function testFmod($number, $modulus, $output, $scale)
	{
		$num = new fNumber($number);
		$this->assertSame($output, $num->fmod($modulus, $scale)->__toString());	
	}
	
	/**
	 * @dataProvider invalidNumProvider
	 * @expectedException fValidationException
	 */
	public function testFmodFail($number)
	{
		$num = new fNumber('1');
		$num->fmod($number);
	}
	
	public static function formatProvider()
	{
		$output = array();
		
		$output[] = array('1.0', '1.0');
		$output[] = array('1000', '1,000');
		$output[] = array('12600.0', '12,600.0');
		$output[] = array('89999999999999999999999999999.89734638', '89,999,999,999,999,999,999,999,999,999.89734638');
		$output[] = array('-12600.0', '-12,600.0');
		$output[] = array('-1112600.0', '-1,112,600.0');
		
		return $output;
	}
	
	/**
	 * @dataProvider formatProvider
	 */
	public function testFormat($input, $output)
	{
		$num = new fNumber($input);
		$this->assertSame($output, $num->format());
	}
	
	public static function gtProvider()
	{
		$output = array();
		
		$output[] = array('1.00', '1', FALSE, NULL);
		$output[] = array('1.000001', '1', TRUE, NULL);
		$output[] = array('2.9999', '2', TRUE, NULL);
		$output[] = array('4847861534861314543', '65468435', TRUE, NULL);
		$output[] = array('0', '-5812', TRUE, NULL);
		$output[] = array('1', '478643', FALSE, NULL);
		$output[] = array('0', '-0', FALSE, NULL);
		$output[] = array('-1', '1', FALSE, NULL);
		$output[] = array('2.9999', '2.999', TRUE, NULL);
		$output[] = array('02.0', '-2.0', TRUE, NULL);
		$output[] = array('2.9999', '2.999', FALSE, 2);
		$output[] = array('1.000001', '1', FALSE, 4);
		$output[] = array('2.9999', '2', TRUE, 1);
		$output[] = array('3.9999', '2', TRUE, 0);
		$output[] = array('-1', '-2', TRUE, NULL);
		$output[] = array('-1.99990', '-1.99999', TRUE, NULL);
		
		return $output;
	}
	
	/**
	 * @dataProvider gtProvider
	 */
	public function testGt($number, $number2, $output, $scale)
	{
		$num = new fNumber($number);
		$this->assertSame($output, $num->gt($number2, $scale));	
	}
	
	/**
	 * @dataProvider invalidNumProvider
	 * @expectedException fValidationException
	 */
	public function testGtFail($number)
	{
		$num = new fNumber('1');
		$num->gt($number);
	}
	
	public static function gteProvider()
	{
		$output = array();
		
		$output[] = array('1.00', '1', TRUE, NULL);
		$output[] = array('1.000001', '1', TRUE, NULL);
		$output[] = array('2.9999', '2', TRUE, NULL);
		$output[] = array('4847861534861314543', '65468435', TRUE, NULL);
		$output[] = array('0', '-5812', TRUE, NULL);
		$output[] = array('1', '478643', FALSE, NULL);
		$output[] = array('0', '-0', TRUE, NULL);
		$output[] = array('-1', '1', FALSE, NULL);
		$output[] = array('-1', '-2', TRUE, NULL);
		$output[] = array('2.9999', '2.9990', TRUE, NULL);
		$output[] = array('02.0', '-2.0', TRUE, NULL);
		$output[] = array('2.9999', '2.999', TRUE, 2);
		$output[] = array('1.000001', '1', TRUE, 4);
		$output[] = array('1.99990', '1.99999', TRUE, 1);
		$output[] = array('3', '3.9999', TRUE, 0);
		$output[] = array('-1.99990', '-1.99999', TRUE, NULL);
		
		return $output;
	}
	
	/**
	 * @dataProvider gteProvider
	 */
	public function testGte($number, $number2, $output, $scale)
	{
		$num = new fNumber($number);
		$this->assertSame($output, $num->gte($number2, $scale));	
	}
	
	/**
	 * @dataProvider invalidNumProvider
	 * @expectedException fValidationException
	 */
	public function testGteFail($number)
	{
		$num = new fNumber('1');
		$num->gte($number);
	}
	
	public static function ltProvider()
	{
		$output = array();
		
		$output[] = array('1.00', '1', FALSE, NULL);
		$output[] = array('1.000001', '1', FALSE, NULL);
		$output[] = array('1.9999', '2', TRUE, NULL);
		$output[] = array('4847861534861314543', '65468435', FALSE, NULL);
		$output[] = array('-4847861534861314543', '65468435', TRUE, NULL);
		$output[] = array('0', '-5812', FALSE, NULL);
		$output[] = array('1', '478643', TRUE, NULL);
		$output[] = array('0', '-0', FALSE, NULL);
		$output[] = array('-1', '1', TRUE, NULL);
		$output[] = array('2.9999', '2.999', FALSE, NULL);
		$output[] = array('2.999', '2.9991', TRUE, NULL);
		$output[] = array('2.9999', '2.999', FALSE, 2);
		$output[] = array('1.99000', '1.999', TRUE, 3);
		$output[] = array('2.9999', '2', FALSE, 0);
		$output[] = array('-2', '2', TRUE, 8);
		
		return $output;
	}
	
	/**
	 * @dataProvider ltProvider
	 */
	public function testLt($number, $number2, $output, $scale)
	{
		$num = new fNumber($number);
		$this->assertSame($output, $num->lt($number2, $scale));	
	}
	
	/**
	 * @dataProvider invalidNumProvider
	 * @expectedException fValidationException
	 */
	public function testLtFail($number)
	{
		$num = new fNumber('1');
		$num->lt($number);
	}
	
	public static function lteProvider()
	{
		$output = array();
		
		$output[] = array('1.00', '1', TRUE, NULL);
		$output[] = array('1.000001', '1', FALSE, NULL);
		$output[] = array('1.9999', '2', TRUE, NULL);
		$output[] = array('4847861534861314543', '65468435', FALSE, NULL);
		$output[] = array('-4847861534861314543', '65468435', TRUE, NULL);
		$output[] = array('0', '-5812', FALSE, NULL);
		$output[] = array('1', '478643', TRUE, NULL);
		$output[] = array('0', '-0', TRUE, NULL);
		$output[] = array('-1', '1', TRUE, NULL);
		$output[] = array('2.9999', '2.999', FALSE, NULL);
		$output[] = array('2.999', '2.999999', TRUE, NULL);
		$output[] = array('2.999999', '2.9900', TRUE, 2);
		$output[] = array('0', '0', TRUE, 4);
		$output[] = array('0.000000001', '0', TRUE, 7);
		
		return $output;
	}
	
	/**
	 * @dataProvider lteProvider
	 */
	public function testLte($number, $number2, $output, $scale)
	{
		$num = new fNumber($number);
		$this->assertSame($output, $num->lte($number2, $scale));	
	}
	
	/**
	 * @dataProvider invalidNumProvider
	 * @expectedException fValidationException
	 */
	public function testLteFail($number)
	{
		$num = new fNumber('1');
		$num->lte($number);
	}
	
	public static function modProvider()
	{
		$output = array();
		
		$output[] = array('15', '4', '3');
		$output[] = array('7', '2', '1');
		$output[] = array('-0', '2', '0');
		$output[] = array('2', '2', '0');
		$output[] = array('89999999954894039', '8973833462232', '1424162169311');
		$output[] = array('23', '4', '3');
		$output[] = array('23', '-4', '3');
		$output[] = array('-23', '-4', '-3');
		$output[] = array('-23', '4', '-3');
		$output[] = array('23.65', '4', '3');
		$output[] = array('23', '4.9', '3');
		$output[] = array('23.65', '4.9', '3');
		
		return $output;
	}
	
	/**
	 * @dataProvider modProvider
	 */
	public function testMod($number, $modulus, $output)
	{
		$num = new fNumber($number);
		$this->assertSame($output, $num->mod($modulus)->__toString());	
	}
	
	/**
	 * @dataProvider invalidNumProvider
	 * @expectedException fValidationException
	 */
	public function testModFail($number)
	{
		$num = new fNumber('1');
		$num->mod($number);
	}
	
	public static function mulProvider()
	{
		$output = array();
		
		$output[] = array('1', '1', '1', NULL);
		$output[] = array('0', '3', '0', NULL);
		$output[] = array('00000', '01', '0', NULL);
		$output[] = array('15', '3', '45', NULL);
		$output[] = array('12', '3.8', '45', NULL);
		$output[] = array('25', '-5', '-125', NULL);
		$output[] = array('-10', '5', '-50', NULL);
		$output[] = array('-5', '-5', '25', NULL);
		$output[] = array('47864654842', '48645314653165123656', '2328391195554233586626689142352', NULL);
		$output[] = array('2.3549383', '1.1', '2.5904321', NULL);
		$output[] = array('2.35', '1.1', '2.58', NULL);
		$output[] = array('2.35', '1.1', '2.585000000000', 12);
		$output[] = array('10', '3', '30.00000', 5);
		$output[] = array('10', '3', '30', 0);
		$output[] = array('-10', '5.00000', '-50.00', 2.281);
		$output[] = array('2.3549383', '1.1', '2.590', 3);
		$output[] = array('0', '3', '0.000', 3);
		
		return $output;
	}
	
	/**
	 * @dataProvider mulProvider
	 */
	public function testMul($input1, $input2, $output, $scale)
	{
		$num = new fNumber($input1);
		$this->assertSame($output, $num->mul($input2, $scale)->__toString());	
	}
	
	/**
	 * @dataProvider invalidNumProvider
	 * @expectedException fValidationException
	 */
	public function testMulFail($number)
	{
		$num = new fNumber('1');
		$num->mul($number);
	}
	
	public static function negProvider()
	{
		$output = array();
		
		$output[] = array('1', '-1', NULL);
		$output[] = array('0', '0', NULL);
		$output[] = array('00000', '0', NULL);
		$output[] = array('-15', '15', NULL);
		$output[] = array('48645314653165123656', '-48645314653165123656', NULL);
		$output[] = array('-2.3549383', '2.3549383', NULL);
		$output[] = array('-2.3549383', '2.35493', 5);
		$output[] = array('-2.3549383', '2', 0);
		$output[] = array('-2.3549383', '2.35', 2);
		
		return $output;
	}
	
	/**
	 * @dataProvider negProvider
	 */
	public function testNeg($input, $output, $scale)
	{
		$num = new fNumber($input);
		$this->assertSame($output, $num->neg($scale)->__toString());	
	}
	
	public static function piProvider()
	{
		$output = array();
		
		$output[] = array('2', '3.14');
		$output[] = array('0', '3');
		$output[] = array('4', '3.1415');
		$output[] = array('10', '3.1415926535');
		$output[] = array('500', '3.14159265358979323846264338327950288419716939937510582097494459230781640628620899862803482534211706798214808651328230664709384460955058223172535940812848111745028410270193852110555964462294895493038196442881097566593344612847564823378678316527120190914564856692346034861045432664821339360726024914127372458700660631558817488152092096282925409171536436789259036001133053054882046652138414695194151160943305727036575959195309218611738193261179310511854807446237996274956735188575272489122793818301194912');
		
		return $output;
	}
	
	/**
	 * @dataProvider piProvider
	 */
	public function testPi($scale, $output)
	{
		$this->assertSame($output, fNumber::pi($scale)->__toString());	
	}
	
	public static function piFailProvider()
	{
		$output = array();
		
		$output[] = array('-100');
		$output[] = array('-1');
		$output[] = array('501');
		$output[] = array('550');
		
		return $output;
	}
	
	/**
	 * @dataProvider piFailProvider
	 * @expectedException fProgrammerException
	 */
	public function testPiFail($scale)
	{
		fNumber::pi($scale);
	}
	
	public static function powProvider()
	{
		$output = array();
		
		$output[] = array('2', '1', '2', NULL);
		$output[] = array('0', '0', '1', NULL);
		$output[] = array('0', '5', '0', NULL);
		$output[] = array('50', '2', '2500', NULL);
		$output[] = array('-5', '3', '-125', NULL);
		$output[] = array('50', '-4', '0.0000001600', 10);
		$output[] = array('-89743984393893', '2', '8053982734891310334575695449', NULL);
		
		return $output;
	}
	
	/**
	 * @dataProvider powProvider
	 */
	public function testPow($input, $power, $output, $scale)
	{
		$num = new fNumber($input);
		$this->assertSame($output, $num->pow($power, $scale)->__toString());	
	}
	
	public static function powmodProvider()
	{
		$output = array();
		
		$output[] = array('2', '10', '45', '34');
		$output[] = array('0', '0', '13', '1');
		$output[] = array('0', '5', '3', '0');
		$output[] = array('83764836793467896438274872687342643728674093827094376984326984327463284320948', '99', '497', '435');
		$output[] = array('94376984326984327463284320948', '99', '497', '113');
		$output[] = array('943769843269843', '99', '497', '50');
		$output[] = array('214748364799999', '99', '497', '1');
		$output[] = array('2147483647', '99', '497', '323');
		
		return $output;
	}
	
	/**
	 * @dataProvider powmodProvider
	 */
	public function testPowmod($input, $power, $modulus, $output)
	{
		$num = new fNumber($input);
		$this->assertSame($output, $num->powmod($power, $modulus)->__toString());	
	}
	
	public static function powmodFailProvider()
	{
		$output = array();
		
		$output[] = array('-100', '50', '50');
		$output[] = array('-1', '50', '50');
		$output[] = array('100', '-50', '50');
		$output[] = array('100', '50', '-50');
		
		return $output;
	}
	
	/**
	 * @dataProvider powmodFailProvider
	 * @expectedException fProgrammerException
	 */
	public function testPowmodFail($number, $power, $modulus)
	{
		$num = new fNumber($number);
		$num->powmod($power, $modulus);
	}
	
	public static function roundProvider()
	{
		$output = array();
		
		$output[] = array('2.7465', 3,  '2.747');
		$output[] = array('2.7465', 0,  '3');
		$output[] = array('2.7465', -1, '0');
		$output[] = array('2.7465', 1,  '2.7');
		$output[] = array('47864654841465461.32484351', 10, '47864654841465461.3248435100');
		$output[] = array('47864654841465461.32484351', -5, '47864654841500000');
		
		return $output;
	}
	
	/**
	 * @dataProvider roundProvider
	 */
	public function testRound($input, $scale, $output)
	{
		$num = new fNumber($input);
		$this->assertSame($output, $num->round($scale)->__toString());	
	}
	
	public static function signProvider()
	{
		$output = array();
		
		$output[] = array('000000', 0);
		$output[] = array('0', 0);
		$output[] = array('2.7465', 1);
		$output[] = array('-2.7465', -1);
		$output[] = array('47864654841465461', 1);
		$output[] = array('-47864654841465461', -1);
		
		return $output;
	}
	
	/**
	 * @dataProvider signProvider
	 */
	public function testSign($input, $output)
	{
		$num = new fNumber($input);
		$this->assertSame($output, $num->sign());	
	}
	
	public static function sqrtProvider()
	{
		$output = array();
		
		$output[] = array('9.00', NULL, '3.00');
		$output[] = array('25', NULL, '5');
		$output[] = array('25.0', 5, '5.00000');
		$output[] = array('36', NULL, '6');
		$output[] = array('0', NULL, '0');
		$output[] = array('0', 2, '0.00');
		$output[] = array('45614752456145655746548615641456', NULL, '6753869443226279');
		$output[] = array('45614752456145655746548615641456', 0, '6753869443226279');
		$output[] = array('45614752456145655746548615641456', 1, '6753869443226279.5');
		$output[] = array('45614752456145655746548615641456', 5, '6753869443226279.58115');
		$output[] = array('45614752456145655746548615641456', 16, '6753869443226279.5811566454937029');
		$output[] = array('25.00000', 0, '5');
		
		return $output;
	}
	
	/**
	 * @dataProvider sqrtProvider
	 */
	public function testSqrt($input, $scale, $output)
	{
		$num = new fNumber($input);
		$this->assertSame($output, $num->sqrt($scale)->__toString());	
	}
	
	public static function sqrtFailProvider()
	{
		$output = array();
		
		$output[] = array('-100');
		$output[] = array('-1');
		
		return $output;
	}
	
	/**
	 * @dataProvider sqrtFailProvider
	 * @expectedException fProgrammerException
	 */
	public function testSqrtFail($number)
	{
		$num = new fNumber($number);
		$num->sqrt();
	}
	
	public static function subProvider()
	{
		$output = array();
		
		$output[] = array('1', '1', '0', NULL);
		$output[] = array('55', '3', '52', NULL);
		$output[] = array('40', '52', '-12', NULL);
		$output[] = array('111', '99', '12', NULL);
		$output[] = array('-10', '-10', '0', NULL);
		$output[] = array('1111111111111111', '11111111111111111111', '-11110000000000000000', NULL);
		$output[] = array('25', '-10', '35', NULL);
		$output[] = array('-10', '5', '-15', NULL);
		$output[] = array('-5', '-5', '0', NULL);
		$output[] = array('4786465484146546132484351564', '8484864684641258979494613132', '-3698399200494712847010261568', NULL);
		$output[] = array('3025e-2', '12.25', '18.00', NULL);
		$output[] = array('40', '52', '-12.00', 2);
		$output[] = array('1.0123', '1.0001', '0.012', 3);
		$output[] = array('10.1111', '12.1111', '-2', 0);
		
		return $output;
	}
	
	/**
	 * @dataProvider subProvider
	 */
	public function testSub($input1, $input2, $output, $scale)
	{
		$num = new fNumber($input1);
		$this->assertSame($output, $num->sub($input2, $scale)->__toString());	
	}
	
	/**
	 * @dataProvider invalidNumProvider
	 * @expectedException fValidationException
	 */
	public function testSubFail($number)
	{
		$num = new fNumber('1');
		$num->sub($number);
	}
	
	public static function truncProvider()
	{
		$output = array();
		
		$output[] = array('2.7465', 3,  '2.746');
		$output[] = array('2.7465', 0,  '2');
		$output[] = array('2.7465', -1, '0');
		$output[] = array('2.7465', 1,  '2.7');
		$output[] = array('47864654841465461.32484351', 10, '47864654841465461.3248435100');
		$output[] = array('47864654841465461.32484351', -5, '47864654841400000');
		
		return $output;
	}
	
	/**
	 * @dataProvider truncProvider
	 */
	public function testTrunc($input, $scale, $output)
	{
		$num = new fNumber($input);
		$this->assertSame($output, $num->trunc($scale)->__toString());	
	}
	
	public function tearDown()
	{
		
	}
}