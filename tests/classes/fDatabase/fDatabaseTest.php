<?php
require_once('./support/init.php');

class fDatabaseTest extends PHPUnit_Framework_TestSuite
{
	public static function suite()
	{
		$suite = new fDatabaseTest();
		$suite->addTestSuite('fDatabaseTestNoModifications');
//		$suite->addTestSuite('fDatabaseTestModifications');
		return $suite;
	}
}

/*class fDatabaseTestModifications extends PHPUnit_Framework_TestSuite
{
	public static function suite()
	{
		return new fDatabaseTestModifications('fDatabaseTestModificationsChild');
	}		
}

class fDatabaseTestModificationsChild extends PHPUnit_Framework_TestCase
{
	public $db;
	
	public function setUp()
	{
		$this->db = new fDatabase(DB_TYPE, DB, DB_USERNAME, DB_PASSWORD, DB_HOST, DB_PORT); 
		$this->db->execute(file_get_contents(DB_SETUP_FILE));
	}
	
	public function tearDown()
	{
		$this->db->execute(file_get_contents(DB_TEARDOWN_FILE));
	}
}*/
 
class fDatabaseTestNoModifications extends PHPUnit_Framework_TestSuite
{
	public static function suite()
	{
		return new fDatabaseTestNoModifications('fDatabaseTestNoModificationsChild');
	}
 
	protected function setUp()
	{
		if (defined('SKIPPING')) {
			return;
		}
		$db = new fDatabase(DB_TYPE, DB, DB_USERNAME, DB_PASSWORD, DB_HOST, DB_PORT); 
		$db->execute(file_get_contents(DB_SETUP_FILE));
		$this->sharedFixture = $db;
	}
 
	protected function tearDown()
	{
		if (defined('SKIPPING')) {
			return;
		}
		$db = $this->sharedFixture;
		$db->execute(file_get_contents(DB_TEARDOWN_FILE));
	}
}
 
class fDatabaseTestNoModificationsChild extends PHPUnit_Framework_TestCase
{
	public $db;
	
	public function setUp()
	{
		if (defined('SKIPPING')) {
			$this->markTestSkipped();
		}
		$this->db = $this->sharedFixture;
	}
	
	public function tearDown()
	{
		
	}
	
	public function testGetDatabase()
	{
		$this->assertEquals(DB, $this->db->getDatabase());
	}
	
	public function testGetExtension()
	{
		$this->assertContains($this->db->getExtension(), array('ibm_db2', 'pdo', 'oci8', 'sqlsvr', 'sqlite', 'sqlsrv', 'mysql', 'mysqli', 'mssql', 'pgsql'));
	}
	
	public function testGetType()
	{
		$this->assertEquals(DB_TYPE, $this->db->getType());
	}
	
	public function testQuery()
	{
		$res = $this->db->query('SELECT user_id FROM users');
		$this->assertEquals('fResult', get_class($res));	
	}
	
	public function testSqlError()
	{
		$this->setExpectedException('fSQLException');    
		$this->db->query('SLECT * FROM users');	
	}
	
	public function testTranslatedQuery()
	{
		$res = $this->db->translatedQuery('SELECT user_id FROM users');
		$this->assertEquals('fResult', get_class($res));	
	}
	
	public function testTranslatedSqlError()
	{
		$this->setExpectedException('fSQLException');    
		$this->db->translatedQuery('SLECT * FROM users');	
	}
	
	public function testUnbufferedQuery()
	{
		$res = $this->db->unbufferedQuery('SELECT user_id FROM users');
		$this->assertEquals('fUnbufferedResult', get_class($res));
	}
	
	public function testUnbufferedSqlError()
	{
		$this->setExpectedException('fSQLException');    
		$this->db->unbufferedQuery('SLECT * FROM users');
	}
	
	public function testUnbufferedTranslatedQuery()
	{
		$res = $this->db->unbufferedTranslatedQuery('SELECT user_id FROM users');
		$this->assertEquals('fUnbufferedResult', get_class($res));
	}
	
	public function testUnbufferedTranslatedSqlError()
	{
		$this->setExpectedException('fSQLException');    
		$this->db->unbufferedTranslatedQuery('SLECT * FROM users');	
	}
	
	public function testEscapeBlob()
	{
		if ($this->db->getExtension() == 'sqlite') {
			$this->assertEquals("'" . bin2hex('☺☻♥♦♣♠•◘○◙') . "'", $this->db->escape('%l', '☺☻♥♦♣♠•◘○◙'));	
			return;	
		}
		
		switch ($this->db->getType()) {
			case 'db2':        $expected = "BLOB(X'" . bin2hex('☺☻♥♦♣♠•◘○◙') . "')"; break;
			case 'sqlite':     $expected = "X'" . bin2hex('☺☻♥♦♣♠•◘○◙') . "'"; break;
			case 'mysql':      $expected = "x'" . bin2hex('☺☻♥♦♣♠•◘○◙') . "'"; break;
			case 'postgresql': $expected = "E'\\\\342\\\\230\\\\272\\\\342\\\\230\\\\273\\\\342\\\\231\\\\245\\\\342\\\\231\\\\246\\\\342\\\\231\\\\243\\\\342\\\\231\\\\240\\\\342\\\\200\\\\242\\\\342\\\\227\\\\230\\\\342\\\\227\\\\213\\\\342\\\\227\\\\231'"; break;
			case 'mssql':      $expected = "0x" . bin2hex('☺☻♥♦♣♠•◘○◙'); break;
			case 'oracle':     $expected = "'" . bin2hex('☺☻♥♦♣♠•◘○◙') . "'"; break;	
		}
		$this->assertEquals($expected, $this->db->escape('%l', '☺☻♥♦♣♠•◘○◙'));
	}
	
	public static function escapeBooleanProvider()
	{
		$output = array();
		
		$output[] = array(TRUE);
		$output[] = array(FALSE);
		
		return $output;
	}
	
	/**
	 * @dataProvider escapeBooleanProvider
	 */
	public function testEscapeBoolean($input)
	{
		$this->db->query('SELECT user_id FROM users WHERE is_validated = ' . $this->db->escape('%b', $input));
	}
	
	public static function escapeDateProvider()
	{
		$output = array();
		
		$output[] = array('tomorrow');
		$output[] = array('2007-02-01');
		$output[] = array('last week');
		$output[] = array('May 5th, 1950');
		
		return $output;
	}
	
	/**
	 * @dataProvider escapeDateProvider
	 */
	public function testEscapeDate($input)
	{
		$this->db->query('SELECT user_id FROM users WHERE date_created < ' . $this->db->escape('%d', $input));
	}
	
	/**
	 * @dataProvider escapeDateTimeFailProvider
	 */
	public function testEscapeDateFail($input, $output)
	{
		$this->assertSame($output, $this->db->escape('%d', $input));
	}
	
	public static function escapeIdentifierProvider()
	{
		$output = array();
		
		$output[] = array('users');
		$output[] = array('"users"');
		
		switch (DB_TYPE) {
			case 'postgresql':
				$output[] = array('public.users');
				$output[] = array('"public".users');
				$output[] = array('public."users"');
				$output[] = array('"public"."users"');
				break;
				
			case 'mssql':
				$output[] = array('dbo.users');
				$output[] = array('"dbo".users');
				$output[] = array('dbo."users"');
				$output[] = array('"dbo"."users"');
				break;
				
			case 'oracle':
				$output[] = array(DB_USERNAME . '.users');
				$output[] = array('"' . DB_USERNAME . '".users');
				$output[] = array(DB_USERNAME . '."users"');
				$output[] = array('"' . DB_USERNAME . '"."users"');
				break;	
		}
		
		return $output;
	}
	
	/**
	 * @dataProvider escapeIdentifierProvider
	 */
	public function testEscapeIdentifier($input)
	{
		$this->db->query('SELECT user_id FROM ' . $this->db->escape('%r', $input));
	}
	
	public static function escapeIntegerProvider()
	{
		$output = array();
		
		$output[] = array(1, '1');
		$output[] = array(4, '4');              
		$output[] = array("2", '2');
		$output[] = array("Abc", 'NULL');
		$output[] = array('+25.289', '25');
		$output[] = array("-5055", '-5055');
		
		return $output;
	}
	
	/**
	 * @dataProvider escapeIntegerProvider
	 */
	public function testEscapeInteger($input, $output)
	{
		$this->assertEquals($output, $this->db->escape('%i', $input));
	}
	
	public static function escapeStringProvider()
	{
		$output = array();
		
		$output[] = array("O'keefe");
		$output[] = array("Johnathan");
		$output[] = array("\\slashed apos'");
		$output[] = array('FooBar');
		$output[] = array("Double apo''"); 
		
		return $output;
	}
	
	/**
	 * @dataProvider escapeStringProvider
	 */
	public function testEscapeString($input)
	{
		$this->db->query('SELECT user_id FROM users WHERE first_name = ' . $this->db->escape('%s', $input));
	}
	
	public static function escapeTimeProvider()
	{
		$output = array();
		
		$output[] = array("now");
		$output[] = array("9:35");
		$output[] = array("2pm");
		$output[] = array('midnight'); 
		
		return $output;
	}
	
	/**
	 * @dataProvider escapeTimeProvider
	 */
	public function testEscapeTime($input)
	{
		$this->db->query('SELECT user_id FROM users WHERE time_of_last_login < ' . $this->db->escape('%t', $input));
	}
	
	public static function escapeDateTimeFailProvider()
	{
		$output = array();
		
		$output[] = array(TRUE, 'NULL');
		$output[] = array('foo', 'NULL');
		$output[] = array('the 6th of may in 2008', 'NULL');
		
		return $output;
	}
	
	/**
	 * @dataProvider escapeDateTimeFailProvider
	 */
	public function testEscapeTimeFail($input, $output)
	{
		$this->assertSame($output, $this->db->escape('%t', $input));
	}
	
	public static function escapeTimestampProvider()
	{
		$output = array();
		
		$output[] = array('now');
		$output[] = array('yesterday 5 pm');
		$output[] = array('June 5th, 2004 1:15 am');
		$output[] = array('2008-02-02 20:15:15');
		
		return $output;
	}
	
	/**
	 * @dataProvider escapeTimestampProvider
	 */
	public function testEscapeTimestamp($input)
	{
		$this->db->query('SELECT user_id FROM users WHERE time_of_last_login < ' . $this->db->escape('%p', $input));
	}
	
	/**
	 * @dataProvider escapeDateTimeFailProvider
	 */
	public function testEscapeTimestampFail($input, $output)
	{
		$this->assertSame($output, $this->db->escape('%p', $input));
	}
	
	public function testUnescapeBlob()
	{
		$res = $this->db->query('SELECT data FROM blobs WHERE blob_id = 1');
		$row = $res->fetchRow();
		$this->assertEquals(pack("H*", "5527939aca3e9e80d5ab3bee47391f0f"), $this->db->unescape('%l', $row['data']));
	}
	
	public function testUnescapeBoolean()
	{
		$res = $this->db->query('SELECT is_validated FROM users WHERE user_id = 1');
		$row = $res->fetchRow();
		$this->assertEquals(TRUE, $this->db->unescape('%b', $row['is_validated']));
	}
	
	public function testUnescapeDate()
	{
		$res = $this->db->query('SELECT birthday FROM users WHERE user_id = 1');
		$row = $res->fetchRow();
		$this->assertEquals('1980-09-01', $this->db->unescape('%d', $row['birthday']));
	}
	
	public function testUnescapeString()
	{
		$res = $this->db->query('SELECT first_name FROM users WHERE user_id = 1');
		$row = $res->fetchRow();
		$this->assertEquals('Will', $this->db->unescape('%s', $row['first_name']));
	}
	
	public function testUnescapeTime()
	{
		$res = $this->db->query('SELECT time_of_last_login FROM users WHERE user_id = 1');
		$row = $res->fetchRow();
		$this->assertEquals('17:00:00', $this->db->unescape('%t', $row['time_of_last_login']));
	}
	
	public function testUnescapeTimestamp()
	{
		$res = $this->db->query('SELECT date_created FROM users WHERE user_id = 1');
		$row = $res->fetchRow();
		$this->assertEquals('2008-05-01 13:00:00', $this->db->unescape('%p', $row['date_created']));
	}
}
