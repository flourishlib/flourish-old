<?php
require_once('./support/init.php');
 
class fSQLTranslationTest extends PHPUnit_Framework_TestSuite
{
	public static function suite()
	{
		return new fSQLTranslationTest('fSQLTranslationTestChild');
	}
 
	protected function setUp()
	{
		$db = new fDatabase(DB_TYPE, DB, DB_USERNAME, DB_PASSWORD, DB_HOST, DB_PORT); 
		$sql = file_get_contents(DB_SETUP_FILE);
		$result = $db->query($sql);
		
		$this->sharedFixture = $db;
	}
 
	protected function tearDown()
	{
		$db = $this->sharedFixture;
		
		try {
			// Clean up the testCreateTable() tables
			$db->query('DROP TABLE unicode_test');
			$db->query('DROP TABLE translation_test_2');
			$db->query('DROP TABLE translation_test');
			if ($db->getType() == 'oracle') {
				$db->query('DROP SEQUENCE unicode_test_unicode_test__seq');
				$db->query('DROP SEQUENCE translation_test_translati_seq');
			}
		} catch (Exception $e) {
			echo $e->getMessage();	
		}
		
		$sql = file_get_contents(DB_TEARDOWN_FILE);        
		$result = $db->query($sql);
	}
}
 
class fSQLTranslationTestChild extends PHPUnit_Framework_TestCase
{
	public $db;
	
	public function setUp()
	{
		$this->db = $this->sharedFixture;	
	}
	
	public function tearDown()
	{
				
	}
	
	public function testModOperator()
	{
		$res = $this->db->translatedQuery("SELECT 5 % 2 as mod_col FROM users");
		$this->assertEquals(1, $res->fetchScalar());
	}
	
	public function testLike()
	{
		$res = $this->db->translatedQuery("SELECT user_id, email_address FROM users WHERE first_name LIKE 'wil%'");
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
		$res = $this->db->translatedQuery("SELECT acos(0.5) FROM users");
		$this->assertEquals(
			(string)(float) 1.0471975511966,
			(string)(float) $res->fetchScalar()
		);
	}
	
	public function testAsin()
	{
		$res = $this->db->translatedQuery("SELECT asin(0.5) FROM users");
		$this->assertEquals(
			(string)(float) 0.5235987755983,
			(string)(float) $res->fetchScalar()
		);
	}
	
	public function testAtan()
	{
		$res = $this->db->translatedQuery("SELECT atan(0.5) FROM users");
		$this->assertEquals(
			(string)(float) 0.46364760900081,
			(string)(float) $res->fetchScalar()
		);
	}
	
	public function testAtan2()
	{
		$res = $this->db->translatedQuery("SELECT atan2(0.5, 0.5) FROM users");
		$this->assertEquals(
			(string)(float) 0.78539816339745,
			(string)(float) $res->fetchScalar()
		);
	}
	
	public function testCeil()
	{
		$res = $this->db->translatedQuery("SELECT ceil(1.2) FROM users");
		$this->assertEquals(
			(string) 2,
			(string) $res->fetchScalar()
		);
	}
	
	public function testCeiling()
	{
		$res = $this->db->translatedQuery("SELECT ceiling(0.1) FROM users");
		$this->assertEquals(
			(string) 1,
			(string) $res->fetchScalar()
		);
	}
	
	public function testCos()
	{
		$res = $this->db->translatedQuery("SELECT cos(0.5) FROM users");
		$this->assertEquals(
			(string)(float) 0.87758256189037,
			(string)(float) $res->fetchScalar()
		);
	}
	
	public function testCot()
	{
		$res = $this->db->translatedQuery("SELECT cot(0.3) FROM users");
		$this->assertEquals(
			(string)(float) 3.2327281437658,
			(string)(float) $res->fetchScalar()
		);
	}
	
	public function testDegrees()
	{
		$res = $this->db->translatedQuery("SELECT degrees(0.5) FROM users");
		$this->assertEquals(
			(string)(float) 28.647889756541,
			(string)(float) $res->fetchScalar()
		);
	}
	
	public function testExp()
	{
		$res = $this->db->translatedQuery("SELECT exp(2.5) FROM users");
		$this->assertEquals(
			(string)(float) 12.182493960703,
			(string)(float) $res->fetchScalar()
		);
	}
	
	public function testFloor()
	{
		$res = $this->db->translatedQuery("SELECT floor(2.5) FROM users");
		$this->assertEquals(
			2,
			$res->fetchScalar()
		);
	}
	
	public function testLn()
	{
		$res = $this->db->translatedQuery("SELECT ln(2.1) FROM users");
		$this->assertEquals(
			(string)(float) 0.74193734472938,
			(string)(float) $res->fetchScalar()
		);
	}
	
	public function testLog()
	{
		$res = $this->db->translatedQuery("SELECT log(10, 5.1) FROM users");
		$this->assertEquals(
			(string)(float) 0.70757017609794,
			(string)(float) $res->fetchScalar()
		);
	}
	
	public function testPi()
	{
		$res = $this->db->translatedQuery("SELECT pi() FROM users");
		$this->assertEquals(
			(string)(float) 3.1415926535898,
			(string)(float) $res->fetchScalar()
		);
	}
	
	public function testPower()
	{
		$res = $this->db->translatedQuery("SELECT power(1.2000000000000, 3.5) FROM users");
		$this->assertEquals(
			(string)(float) 1.8929291587379,
			(string)(float) $res->fetchScalar()
		);
	}
	
	public function testRadians()
	{
		$res = $this->db->translatedQuery("SELECT radians(118.1) FROM users");
		$this->assertEquals(
			(string)(float) 2.0612338466053,
			(string)(float) $res->fetchScalar()
		);
	}
	
	public function testRandom()
	{
		$res = $this->db->translatedQuery("SELECT random() FROM users");
		$rand = (float) $res->fetchScalar();
		$this->assertGreaterThanOrEqual(0.0, $rand);
		$this->assertLessThanOrEqual(1.0, $rand);
	}
	
	public function testRound()
	{
		$res = $this->db->translatedQuery("SELECT round(118.1) FROM users");
		$this->assertEquals(
			118,
			$res->fetchScalar()
		);
	}
	
	public function testRound2()
	{
		$res = $this->db->translatedQuery("SELECT round(2.9) FROM users");
		$this->assertEquals(
			3,
			$res->fetchScalar()
		);
	}
	
	public function testRound3()
	{
		$res = $this->db->translatedQuery("SELECT round(1.9876, 2) FROM users");
		$this->assertEquals(
			1.99,
			$res->fetchScalar()
		);
	}
	
	public function testSign()
	{
		$res = $this->db->translatedQuery("SELECT sign(0) AS sign_of_zero FROM users");
		$this->assertEquals(
			0,
			$res->fetchScalar()
		);
	}
	
	public function testSign2()
	{
		$res = $this->db->translatedQuery("SELECT sign(-25) AS sign_of_neg_25 FROM users");
		$this->assertEquals(
			-1,
			$res->fetchScalar()
		);
	}
	
	public function testSqrt()
	{
		$res = $this->db->translatedQuery("SELECT sqrt(9) FROM users");
		$this->assertEquals(
			3,
			$res->fetchScalar()
		);
	}
	
	public function testSin()
	{
		$res = $this->db->translatedQuery("SELECT sin(0.5) FROM users");
		$this->assertEquals(
			(string)(float) 0.4794255386042,
			(string)(float) $res->fetchScalar()
		);
	}
	
	public function testTan()
	{
		$res = $this->db->translatedQuery("SELECT tan(0.5) FROM users");
		$this->assertEquals(
			(string)(float) 0.54630248984379,
			(string)(float) $res->fetchScalar()
		);
	}
	
	public function testTrim()
	{
		$res = $this->db->translatedQuery("SELECT trim('  testing trim ') FROM users");
		$this->assertEquals(
			'testing trim',
			$res->fetchScalar()
		);
	}
	
	public function testRTrim()
	{
		$res = $this->db->translatedQuery("SELECT rtrim('  testing trim ') FROM users");
		$this->assertEquals(
			'  testing trim',
			$res->fetchScalar()
		);
	}
	
	public function testLTrim()
	{
		$res = $this->db->translatedQuery("SELECT ltrim('  testing trim ') FROM users");
		$this->assertEquals(
			'testing trim ',
			$res->fetchScalar()
		);
	}
	
	public function testSubstr()
	{
		$res = $this->db->translatedQuery("SELECT substr('testing', 2, 3) FROM users");
		$this->assertEquals(
			'est',
			$res->fetchScalar()
		);
	}
	
	public function testLength()
	{
		$res = $this->db->translatedQuery("SELECT length('testing') FROM users");
		$this->assertEquals(
			7,
			$res->fetchScalar()
		);
	}
	
	public function testCurrentTimestamp()
	{
		$res = $this->db->translatedQuery("SELECT CURRENT_TIMESTAMP FROM users");
		$current_timestamp = strtotime($this->db->unescape('timestamp', $res->fetchScalar()));
		$this->assertGreaterThanOrEqual(time()-60, $current_timestamp);
		$this->assertLessThanOrEqual(time()+60, $current_timestamp);
	}
	
	public function testBoolean()
	{
		$res = $this->db->translatedQuery("SELECT user_id, email_address FROM users WHERE is_validated = TRUE ORDER BY user_id ASC");
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
		$res = $this->db->translatedQuery("SELECT * FROM users LIMIT 3");
		$this->assertEquals(3, $res->countReturnedRows());
	}
	
	public function testLimit2()
	{
		$res = $this->db->translatedQuery("SELECT user_id, email_address FROM users ORDER BY user_id ASC LIMIT 2");
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
		$res = $this->db->translatedQuery("SELECT user_id, email_address FROM users ORDER BY user_id ASC LIMIT 2 OFFSET 1");
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
		$res = $this->db->translatedQuery("SELECT user_id, email_address FROM users WHERE middle_initial = '' AND first_name <> '' ORDER BY user_id ASC");
		
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
		$res = $this->db->translatedQuery("UPDATE users SET middle_initial = '' WHERE middle_initial = ''");
		
		$this->assertEquals(
			// MySQL doesn't report an affected row if the old and new values are the same
			($this->db->getType() == 'mysql') ? 0 : 4,    
			$res->countAffectedRows()
		);
	}
	
	public function testCreateTable()
	{
		$this->db->translatedQuery(
			"CREATE TABLE translation_test (
				translation_test_id INTEGER AUTOINCREMENT PRIMARY KEY,
				bigint_col BIGINT NULL,
				char_col CHAR(40) NULL,
				varchar_col VARCHAR(100) NULL,
				text_col TEXT NULL,
				blob_col BLOB NULL,
				timestamp_col TIMESTAMP NULL,
				time_col TIME NULL,
				date_col DATE NULL,
				boolean_col BOOLEAN NULL
			)"
		);
		
		$this->db->translatedQuery(
			"CREATE TABLE translation_test_2 (
				translation_test_2_id INTEGER PRIMARY KEY,
				translation_test_id INTEGER NOT NULL REFERENCES translation_test(translation_test_id) ON DELETE CASCADE,
				name VARCHAR(100) NULL
			)"
		);
		
		$schema = new fSchema($this->db);
		
		$translation_test_schema   = $schema->getColumnInfo('translation_test');
		
		foreach ($translation_test_schema as $type => &$list) {
			ksort($list);	
		}
		ksort($translation_test_schema);
		
		$this->assertEquals(
			array(
				'bigint_col' => array(
					"auto_increment" => FALSE,
					"decimal_places" => NULL,
					"default"        => NULL,
					"max_length"     => NULL,
					"not_null"       => FALSE,
					"placeholder"    => "%i",
					"type"           => "integer",
					"valid_values"   => NULL
				),
				'blob_col' => array(
					"auto_increment" => FALSE,
					"decimal_places" => NULL,
					"default"        => NULL,
					"max_length"     => NULL,
					"not_null"       => FALSE,
					"placeholder"    => "%l",
					"type"           => "blob",
					"valid_values"   => NULL
				),
				'boolean_col' => array(
					"auto_increment" => FALSE,
					"decimal_places" => NULL,
					"default"        => NULL,
					"max_length"     => NULL,
					"not_null"       => FALSE,
					"placeholder"    => "%b",
					"type"           => "boolean",
					"valid_values"   => NULL
				),
				'char_col' => array(
					"auto_increment" => FALSE,
					"decimal_places" => NULL,
					"default"        => NULL,
					"max_length"     => 40,
					"not_null"       => FALSE,
					"placeholder"    => "%s",
					"type"           => "char",
					"valid_values"   => NULL
				),
				'date_col' => array(
					"auto_increment" => FALSE,
					"decimal_places" => NULL,
					"default"        => NULL,
					"max_length"     => NULL,
					"not_null"       => FALSE,
					"placeholder"    => ($this->db->getType() == 'mssql') ? "%p" : "%d",
					"type"           => ($this->db->getType() == 'mssql') ? "timestamp" : "date",
					"valid_values"   => NULL
				),
				'text_col' => array(
					"auto_increment" => FALSE,
					"decimal_places" => NULL,
					"default"        => NULL,
					"max_length"     => NULL,
					"not_null"       => FALSE,
					"placeholder"    => "%s",
					"type"           => "text",
					"valid_values"   => NULL
				),
				'time_col' => array(
					"auto_increment" => FALSE,
					"decimal_places" => NULL,
					"default"        => NULL,
					"max_length"     => NULL,
					"not_null"       => FALSE,
					"placeholder"    => (in_array($this->db->getType(), array('mssql', 'oracle'))) ? "%p" : "%t",
					"type"           => (in_array($this->db->getType(), array('mssql', 'oracle'))) ? "timestamp" : "time",
					"valid_values"   => NULL
				),
				'timestamp_col' => array(
					"auto_increment" => FALSE,
					"decimal_places" => NULL,
					"default"        => NULL,
					"max_length"     => NULL,
					"not_null"       => FALSE,
					"placeholder"    => "%p",
					"type"           => "timestamp",
					"valid_values"   => NULL
				),
				'translation_test_id' => array(
					"auto_increment" => TRUE,
					"decimal_places" => NULL,
					"default"        => NULL,
					"max_length"     => NULL,
					"not_null"       => TRUE,
					"placeholder"    => "%i",
					"type"           => "integer",
					"valid_values"   => NULL
				),
				'varchar_col' => array(
					"auto_increment" => FALSE,
					"decimal_places" => NULL,
					"default"        => NULL,
					"max_length"     => 100,
					"not_null"       => FALSE,
					"placeholder"    => "%s",
					"type"           => "varchar",
					"valid_values"   => NULL
				)
			),
			$translation_test_schema
		);
		
		$translation_test_2_schema = $schema->getColumnInfo('translation_test_2');
		
		foreach ($translation_test_2_schema as $type => &$list) {
			ksort($list);	
		}
		ksort($translation_test_2_schema);
		
		$this->assertEquals(
			array(
				'name' => array(
					"auto_increment" => FALSE,
					"decimal_places" => NULL,
					"default"        => NULL,
					"max_length"     => 100,
					"not_null"       => FALSE,
					"placeholder"    => "%s",
					"type"           => "varchar",
					"valid_values"   => NULL
				),
				'translation_test_2_id' => array(
					"auto_increment" => ($this->db->getType() == 'sqlite') ? TRUE : FALSE,
					"decimal_places" => NULL,
					"default"        => NULL,
					"max_length"     => NULL,
					"not_null"       => TRUE,
					"placeholder"    => "%i",
					"type"           => "integer",
					"valid_values"   => NULL
				),
				'translation_test_id' => array(
					"auto_increment" => FALSE,
					"decimal_places" => NULL,
					"default"        => NULL,
					"max_length"     => NULL,
					"not_null"       => TRUE,
					"placeholder"    => "%i",
					"type"           => "integer",
					"valid_values"   => NULL
				)
			),
			$translation_test_2_schema
		);
		
		$translation_test_keys = $schema->getKeys('translation_test');
		ksort($translation_test_keys);
		$this->assertEquals(
			array(
				'foreign' => array(),
				'primary' => array('translation_test_id'),
				'unique' => array()
			),
			$translation_test_keys
		);
		
		$translation_test_2_keys = $schema->getKeys('translation_test_2');
		ksort($translation_test_2_keys);
		$this->assertEquals(
			array(
				'foreign' => array(
					array(
						"column"         => "translation_test_id",
						"foreign_table"  => "translation_test",
						"foreign_column" => "translation_test_id",
						"on_delete"      => "cascade",
						"on_update"      => "no_action"
					)
				),
				'primary' => array('translation_test_2_id'),
				'unique' => array()
			),
			$translation_test_2_keys
		);
	}
	
	
	public function testUnicode()
	{
		$this->db->translatedQuery(
			"CREATE TABLE unicode_test (
				unicode_test_id INTEGER AUTOINCREMENT PRIMARY KEY,
				varchar_col VARCHAR(100) NULL,
				varchar_col_2 VARCHAR(100) NULL,
				char_col VARCHAR(10) NULL,
				text_col VARCHAR(100) NULL
			)"
		);
		
		$this->db->getSQLTranslation()->clearCache();
		$this->db->clearCache();
		
		$this->db->translatedQuery(
			"INSERT INTO unicode_test (varchar_col, varchar_col_2, char_col, text_col) VALUES (%s, %s, %s, %s)",
			"Արամ Խաչատրյան",
			"সুকুমার রায়",
			"Ελλάς",
			"Ђорђе Балашевић"
		);
		
		$res = $this->db->translatedQuery("SELECT * FROM unicode_test");
		$this->assertEquals(
			array(
				'unicode_test_id' => 1,
				'varchar_col'     => "Արամ Խաչատրյան",
				'varchar_col_2'   => "সুকুমার রায়",
				'char_col'        => "Ελλάς",
				'text_col'        => "Ђорђе Балашевић"
			),
			$res->fetchRow()	
		);
	}
}