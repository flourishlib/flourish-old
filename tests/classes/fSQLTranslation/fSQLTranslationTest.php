<?php
require_once('./support/init.php');

class fSQLTranslationTest extends PHPUnit_Framework_TestCase
{
	protected static $db;

	public static function setUpBeforeClass()
	{
		if (defined('SKIPPING')) {
			return;
		}
		$db = new fDatabase(DB_TYPE, DB, DB_USERNAME, DB_PASSWORD, DB_HOST, DB_PORT); 
		$db->execute(file_get_contents(DB_SETUP_FILE));

		self::$db = $db;
	}

	public static function tearDownAfterClass()
	{
		if (defined('SKIPPING')) {
			return;
		}
		teardown(self::$db, DB_TEARDOWN_FILE);
	}
	
	public function setUp()
	{
		if (defined('SKIPPING')) {
			$this->markTestSkipped();
		}
	}
	
	public function tearDown()
	{
		
	}

	public function testModOperator()
	{
		$res = self::$db->translatedQuery("SELECT 5 % 2 as mod_col FROM users");
		$this->assertEquals(1, $res->fetchScalar());
	}
	
	public function testLike()
	{
		$res = self::$db->translatedQuery("SELECT user_id, email_address FROM users WHERE first_name LIKE 'wil%'");
		$this->assertEquals(
			array(
				array(
					'user_id'       => 1,
					'email_address' => 'will@flourishlib.com'
				)
			),
			$res->fetchAllRows()
		);
	}
	
	public function testAcos()
	{
		$res = self::$db->translatedQuery("SELECT acos(0.5) FROM users");
		$this->assertEquals(
			(string)(float) 1.0471975511966,
			(string)(float) $res->fetchScalar()
		);
	}
	
	public function testAsin()
	{
		$res = self::$db->translatedQuery("SELECT asin(0.5) FROM users");
		$this->assertEquals(
			(string)(float) 0.5235987755983,
			(string)(float) $res->fetchScalar()
		);
	}
	
	public function testAtan()
	{
		$res = self::$db->translatedQuery("SELECT atan(0.5) FROM users");
		$this->assertEquals(
			(string)(float) 0.46364760900081,
			(string)(float) $res->fetchScalar()
		);
	}
	
	public function testAtan2()
	{
		$res = self::$db->translatedQuery("SELECT atan2(0.5, 0.5) FROM users");
		$this->assertEquals(
			(string)(float) 0.78539816339745,
			(string)(float) $res->fetchScalar()
		);
	}
	
	public function testCeil()
	{
		$res = self::$db->translatedQuery("SELECT ceil(1.2) FROM users");
		$this->assertEquals(
			(string) 2,
			(string) $res->fetchScalar()
		);
	}
	
	public function testCeiling()
	{
		$res = self::$db->translatedQuery("SELECT ceiling(0.1) FROM users");
		$this->assertEquals(
			(string) 1,
			(string) $res->fetchScalar()
		);
	}
	
	public function testCos()
	{
		$res = self::$db->translatedQuery("SELECT cos(0.5) FROM users");
		$this->assertEquals(
			(string)(float) 0.87758256189037,
			(string)(float) $res->fetchScalar()
		);
	}
	
	public function testCot()
	{
		$res = self::$db->translatedQuery("SELECT cot(0.3) FROM users");
		$this->assertEquals(
			(string)(float) 3.2327281437658,
			(string)(float) $res->fetchScalar()
		);
	}
	
	public function testDegrees()
	{
		$res = self::$db->translatedQuery("SELECT degrees(0.5) FROM users");
		$this->assertEquals(
			(string)(float) 28.647889756541,
			(string)(float) $res->fetchScalar()
		);
	}
	
	public function testExp()
	{
		$res = self::$db->translatedQuery("SELECT exp(2.5) FROM users");
		$this->assertEquals(
			(string)(float) 12.182493960703,
			(string)(float) $res->fetchScalar()
		);
	}
	
	public function testFloor()
	{
		$res = self::$db->translatedQuery("SELECT floor(2.5) FROM users");
		$this->assertEquals(
			2,
			$res->fetchScalar()
		);
	}
	
	public function testLn()
	{
		$res = self::$db->translatedQuery("SELECT ln(2.1) FROM users");
		$this->assertEquals(
			(string)(float) 0.74193734472938,
			(string)(float) $res->fetchScalar()
		);
	}
	
	public function testLog()
	{
		$res = self::$db->translatedQuery("SELECT log(10, 5.1) FROM users");
		$this->assertEquals(
			(string)(float) 0.70757017609794,
			(string)(float) $res->fetchScalar()
		);
	}
	
	public function testPi()
	{
		$res = self::$db->translatedQuery("SELECT pi() FROM users");
		$this->assertEquals(
			(string)(float) 3.1415926535898,
			(string)(float) $res->fetchScalar()
		);
	}
	
	public function testPower()
	{
		$res = self::$db->translatedQuery("SELECT power(1.2000000000000, 3.5) FROM users");
		$this->assertEquals(
			(string)(float) 1.8929291587379,
			(string)(float) $res->fetchScalar()
		);
	}
	
	public function testRadians()
	{
		$res = self::$db->translatedQuery("SELECT radians(118.1) FROM users");
		$this->assertEquals(
			(string)(float) 2.0612338466053,
			(string)(float) $res->fetchScalar()
		);
	}
	
	public function testRandom()
	{
		$res = self::$db->translatedQuery("SELECT random() FROM users");
		$rand = (float) $res->fetchScalar();
		$this->assertGreaterThanOrEqual(0.0, $rand);
		$this->assertLessThanOrEqual(1.0, $rand);
	}
	
	public function testRound()
	{
		$res = self::$db->translatedQuery("SELECT round(118.1) FROM users");
		$this->assertEquals(
			118,
			$res->fetchScalar()
		);
	}
	
	public function testRound2()
	{
		$res = self::$db->translatedQuery("SELECT round(2.9) FROM users");
		$this->assertEquals(
			3,
			$res->fetchScalar()
		);
	}
	
	public function testRound3()
	{
		$res = self::$db->translatedQuery("SELECT round(1.9876, 2) FROM users");
		$this->assertEquals(
			1.99,
			$res->fetchScalar()
		);
	}
	
	public function testSign()
	{
		$res = self::$db->translatedQuery("SELECT sign(0) AS sign_of_zero FROM users");
		$this->assertEquals(
			0,
			$res->fetchScalar()
		);
	}
	
	public function testSign2()
	{
		$res = self::$db->translatedQuery("SELECT sign(-25) AS sign_of_neg_25 FROM users");
		$this->assertEquals(
			-1,
			$res->fetchScalar()
		);
	}
	
	public function testSqrt()
	{
		$res = self::$db->translatedQuery("SELECT sqrt(9) FROM users");
		$this->assertEquals(
			3,
			$res->fetchScalar()
		);
	}
	
	public function testSin()
	{
		$res = self::$db->translatedQuery("SELECT sin(0.5) FROM users");
		$this->assertEquals(
			(string)(float) 0.4794255386042,
			(string)(float) $res->fetchScalar()
		);
	}
	
	public function testTan()
	{
		$res = self::$db->translatedQuery("SELECT tan(0.5) FROM users");
		$this->assertEquals(
			(string)(float) 0.54630248984379,
			(string)(float) $res->fetchScalar()
		);
	}
	
	public function testTrim()
	{
		$res = self::$db->translatedQuery("SELECT trim('  testing trim ') FROM users");
		$this->assertEquals(
			'testing trim',
			$res->fetchScalar()
		);
	}
	
	public function testRTrim()
	{
		$res = self::$db->translatedQuery("SELECT rtrim('  testing trim ') FROM users");
		$this->assertEquals(
			'  testing trim',
			$res->fetchScalar()
		);
	}
	
	public function testLTrim()
	{
		$res = self::$db->translatedQuery("SELECT ltrim('  testing trim ') FROM users");
		$this->assertEquals(
			'testing trim ',
			$res->fetchScalar()
		);
	}
	
	public function testSubstr()
	{
		$res = self::$db->translatedQuery("SELECT substr('testing', 2, 3) FROM users");
		$this->assertEquals(
			'est',
			$res->fetchScalar()
		);
	}
	
	public function testLength()
	{
		$res = self::$db->translatedQuery("SELECT length('testing') FROM users");
		$this->assertEquals(
			7,
			$res->fetchScalar()
		);
	}

	public function testLengthMultibyte()
	{
		$res = self::$db->translatedQuery("SELECT length(%s) FROM users", 'résumé');
		$this->assertEquals(
			6,
			$res->fetchScalar()
		);
	}
	
	public function testCurrentTimestamp()
	{
		$res = self::$db->translatedQuery("SELECT CURRENT_TIMESTAMP FROM users");
		// Only SQLite does any translation
		if (DB_TYPE == 'sqlite') {
			$current_timestamp = strtotime(self::$db->unescape('timestamp', $res->fetchScalar()));
			$this->assertGreaterThanOrEqual(time()-120, $current_timestamp);
			$this->assertLessThanOrEqual(time()+120, $current_timestamp);
		}
	}
	
	public function testBoolean()
	{
		$res = self::$db->translatedQuery("SELECT user_id, email_address FROM users WHERE is_validated = TRUE ORDER BY user_id ASC");
		$this->assertEquals(
			array(
				array(
					'user_id'       => 1,
					'email_address' => 'will@flourishlib.com'
				),
				array(
					'user_id'       => 2,
					'email_address' => 'john@smith.com'
				),
				array(
					'user_id'       => 3,
					'email_address' => 'bar@example.com'
				)
			),
			$res->fetchAllRows()
		);
	}
	
	public function testLimit()
	{
		$res = self::$db->translatedQuery("SELECT * FROM users LIMIT 3");
		$this->assertEquals(3, $res->countReturnedRows());
	}
	
	public function testLimit2()
	{
		$res = self::$db->translatedQuery("SELECT user_id, email_address FROM users ORDER BY user_id ASC LIMIT 2");
		$this->assertEquals(
			array(
				array(
					'user_id'       => 1,
					'email_address' => 'will@flourishlib.com'
				),
				array(
					'user_id'       => 2,
					'email_address' => 'john@smith.com'
				)
			),
			$res->fetchAllRows()
		);
	}
	
	public function testLimitOffset()
	{
		$res = self::$db->translatedQuery("SELECT user_id, email_address FROM users ORDER BY user_id ASC LIMIT 2 OFFSET 1");
		$this->assertEquals(
			array(
				array(
					'user_id'       => 2,
					'email_address' => 'john@smith.com'
				),
				array(
					'user_id'       => 3,
					'email_address' => 'bar@example.com'
				)
			),
			$res->fetchAllRows()
		);
	}
	
	public function testPreparedStatement()
	{
		$statement = self::$db->translatedPrepare("SELECT user_id, email_address FROM users ORDER BY user_id ASC LIMIT 2 OFFSET 1");
		$res = self::$db->query($statement);
		$this->assertEquals(
			array(
				array(
					'user_id'       => 2,
					'email_address' => 'john@smith.com'
				),
				array(
					'user_id'       => 3,
					'email_address' => 'bar@example.com'
				)
			),
			$res->fetchAllRows()
		);
	}
	
	public function testEmptyStrings()
	{
		$res = self::$db->translatedQuery("SELECT user_id, email_address FROM users WHERE middle_initial = '' AND first_name <> '' ORDER BY user_id ASC");
		
		$this->assertEquals(
			array(
				array(
					'user_id'       => 1,
					'email_address' => 'will@flourishlib.com'
				),
				array(
					'user_id'       => 2,
					'email_address' => 'john@smith.com'
				),
				array(
					'user_id'       => 3,
					'email_address' => 'bar@example.com'
				),
				array(
					'user_id'       => 4,
					'email_address' => 'foo@example.com'
				)
			),    
			$res->fetchAllRows()
		);
	}
	
	public function testEmptyStrings2()
	{
		$res = self::$db->translatedQuery("UPDATE users SET middle_initial = '' WHERE middle_initial = ''");
		
		$this->assertEquals(
			// MySQL doesn't report an affected row if the old and new values are the same
			(DB_TYPE == 'mysql') ? 0 : 4,    
			$res->countAffectedRows()
		);
	}
}
