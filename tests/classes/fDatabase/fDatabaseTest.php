<?php
require_once('./support/init.php');

class fDatabaseTest extends PHPUnit_Framework_TestCase
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
		self::$db->__destruct();
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
	
	public function testConnectFailPort()
	{
		if (DB_TYPE == 'sqlite') {
			$this->markTestSkipped();
		}
		try {
			$db = new fDatabase(DB_TYPE, DB, DB_USERNAME, DB_PASSWORD, DB_HOST, 8473, 2);
			$db->connect();
		} catch (fConnectivityException $e) {
			$this->assertEquals('Unable to connect to database - connection refused or timed out', $e->getMessage());
		}
		$db->__destruct();
	}

	public function testConnectFailHostname()
	{
		if (DB_TYPE == 'sqlite') {
			$this->markTestSkipped();
		}
		try {
			$db = new fDatabase(DB_TYPE, DB, DB_USERNAME, DB_PASSWORD, 'badhost.flourishlib.com', DB_PORT, 2);
			$db->connect();
		} catch (fConnectivityException $e) {
			$this->assertEquals('Unable to connect to database - hostname not found', $e->getMessage());
		}
		$db->__destruct();
	}

	public function testConnectFailIp()
	{
		if (DB_TYPE == 'sqlite') {
			$this->markTestSkipped();
		}
		try {
			$db = new fDatabase(DB_TYPE, DB, DB_USERNAME, DB_PASSWORD, '127.0.0.200', DB_PORT, 2);
			$db->connect();
		} catch (fConnectivityException $e) {
			$this->assertEquals('Unable to connect to database - connection refused or timed out', $e->getMessage());
		}
		$db->__destruct();
	}

	public function testConnectFailUsername()
	{
		if (DB_TYPE == 'sqlite') {
			$this->markTestSkipped();
		}
		try {
			$db = new fDatabase(DB_TYPE, DB, 'unknown', DB_PASSWORD, DB_HOST, DB_PORT, 2);
			$db->connect();
		} catch (fAuthorizationException $e) {
			$this->assertEquals('Unable to connect to database - login credentials refused', $e->getMessage());
		}
		$db->__destruct();
	}

	public function testConnectFailPassword()
	{
		if (DB_TYPE == 'sqlite') {
			$this->markTestSkipped();
		}
		try {
			$db = new fDatabase(DB_TYPE, DB, DB_USERNAME, 'badpassword', DB_HOST, DB_PORT, 2);
			$db->connect();
		} catch (fAuthorizationException $e) {
			$this->assertEquals('Unable to connect to database - login credentials refused', $e->getMessage());
		}
		$db->__destruct();
	}

	public function testConnectFailDatabase()
	{
		if (DB_TYPE == 'sqlite') {
			$this->markTestSkipped();
		}
		try {
			$db = new fDatabase(DB_TYPE, 'baddb', DB_USERNAME, DB_PASSWORD, DB_HOST, DB_PORT, 2);
			$db->connect();
		} catch (fNotFoundException $e) {
			$this->assertEquals('Unable to connect to database - database specified not found', $e->getMessage());
		}
		$db->__destruct();
	}

	public function testGetDatabase()
	{
		$this->assertEquals(DB, self::$db->getDatabase());
	}
	
	public function testGetExtension()
	{
		$this->assertContains(self::$db->getExtension(), array('ibm_db2', 'pdo', 'oci8', 'sqlsvr', 'sqlite', 'sqlsrv', 'mysql', 'mysqli', 'mssql', 'pgsql'));
	}
	
	public function testGetType()
	{
		$this->assertEquals(DB_TYPE, self::$db->getType());
	}

	public function testGetVersion()
	{
		$this->assertEquals(preg_match('#^\d+(\.\d+)*$#D', self::$db->getVersion()), 1);
	}
	
	public function testQuery()
	{
		$res = self::$db->query('SELECT user_id FROM users');
		$this->assertEquals('fResult', get_class($res));	
	}
	
	public function testSqlError()
	{
		$this->setExpectedException('fSQLException');    
		self::$db->query('SLECT * FROM users');	
	}
	
	public function testTranslatedQuery()
	{
		$res = self::$db->translatedQuery('SELECT user_id FROM users');
		$this->assertEquals('fResult', get_class($res));	
	}
	
	public function testTranslatedSqlError()
	{
		$this->setExpectedException('fSQLException');    
		self::$db->translatedQuery('SLECT * FROM users');	
	}
	
	public function testUnbufferedQuery()
	{
		$res = self::$db->unbufferedQuery('SELECT user_id FROM users');
		$this->assertEquals('fUnbufferedResult', get_class($res));
	}
	
	public function testUnbufferedSqlError()
	{
		$this->setExpectedException('fSQLException');    
		self::$db->unbufferedQuery('SLECT * FROM users');
	}
	
	public function testUnbufferedTranslatedQuery()
	{
		$res = self::$db->unbufferedTranslatedQuery('SELECT user_id FROM users');
		$this->assertEquals('fUnbufferedResult', get_class($res));
	}
	
	public function testUnbufferedTranslatedSqlError()
	{
		$this->setExpectedException('fSQLException');    
		self::$db->unbufferedTranslatedQuery('SLECT * FROM users');	
	}

	public function testEscapePercent()
	{
		$this->assertEquals('SELECT * FROM users WHERE first_name = %%s AND user_id = 1', self::$db->escape("SELECT * FROM users WHERE first_name = %%s AND user_id = %i", 1));
	}
	
	public function testEscapeBlob()
	{
		if (self::$db->getExtension() == 'sqlite') {
			$this->assertEquals("'" . bin2hex('☺☻♥♦♣♠•◘○◙') . "'", self::$db->escape('%l', '☺☻♥♦♣♠•◘○◙'));	
			return;	
		}
		
		switch (self::$db->getType()) {
			case 'db2':        $expected = "BLOB(X'" . bin2hex('☺☻♥♦♣♠•◘○◙') . "')"; break;
			case 'sqlite':     $expected = "X'" . bin2hex('☺☻♥♦♣♠•◘○◙') . "'"; break;
			case 'mysql':      $expected = "x'" . bin2hex('☺☻♥♦♣♠•◘○◙') . "'"; break;
			case 'postgresql': $expected = "E'\\\\342\\\\230\\\\272\\\\342\\\\230\\\\273\\\\342\\\\231\\\\245\\\\342\\\\231\\\\246\\\\342\\\\231\\\\243\\\\342\\\\231\\\\240\\\\342\\\\200\\\\242\\\\342\\\\227\\\\230\\\\342\\\\227\\\\213\\\\342\\\\227\\\\231'"; break;
			case 'mssql':      $expected = "0x" . bin2hex('☺☻♥♦♣♠•◘○◙'); break;
			case 'oracle':     $expected = "'" . bin2hex('☺☻♥♦♣♠•◘○◙') . "'"; break;	
		}
		$this->assertEquals($expected, self::$db->escape('%l', '☺☻♥♦♣♠•◘○◙'));
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
		self::$db->query('SELECT user_id FROM users WHERE is_validated = ' . self::$db->escape('%b', $input));
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
		self::$db->query('SELECT user_id FROM users WHERE date_created < ' . self::$db->escape('%d', $input));
	}
	
	/**
	 * @dataProvider escapeDateTimeFailProvider
	 */
	public function testEscapeDateFail($input, $output)
	{
		$this->assertSame($output, self::$db->escape('%d', $input));
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
		self::$db->query('SELECT user_id FROM ' . self::$db->escape('%r', $input));
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
		$this->assertEquals($output, self::$db->escape('%i', $input));
	}
	
	public static function escapeFloatProvider()
	{
		$output = array();
		
		$output[] = array(1.0, '1');
		$output[] = array('4.', '4');              
		$output[] = array('.7', '0.7');
		$output[] = array("-.6", '-0.6');
		$output[] = array('+25.289', '+25.289');
		$output[] = array(0.10, '0.1');
		
		return $output;
	}
	
	/**
	 * @dataProvider escapeFloatProvider
	 */
	public function testEscapeFloat($input, $output)
	{
		$this->assertSame($output, self::$db->escape('%f', $input));
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
		self::$db->query('SELECT user_id FROM users WHERE first_name = ' . self::$db->escape('%s', $input));
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
		self::$db->query('SELECT user_id FROM users WHERE time_of_last_login < ' . self::$db->escape('%t', $input));
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
		$this->assertSame($output, self::$db->escape('%t', $input));
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
		self::$db->query('SELECT user_id FROM users WHERE time_of_last_login < ' . self::$db->escape('%p', $input));
	}
	
	/**
	 * @dataProvider escapeDateTimeFailProvider
	 */
	public function testEscapeTimestampFail($input, $output)
	{
		$this->assertSame($output, self::$db->escape('%p', $input));
	}
	
	public function testUnescapeBlob()
	{
		$res = self::$db->query('SELECT data FROM blobs WHERE blob_id = 1');
		$row = $res->fetchRow();
		$this->assertEquals(pack("H*", "5527939aca3e9e80d5ab3bee47391f0f"), self::$db->unescape('%l', $row['data']));
	}
	
	public function testUnescapeBoolean()
	{
		$res = self::$db->query('SELECT is_validated FROM users WHERE user_id = 1');
		$row = $res->fetchRow();
		$this->assertEquals(TRUE, self::$db->unescape('%b', $row['is_validated']));
	}
	
	public function testUnescapeDate()
	{
		$res = self::$db->query('SELECT birthday FROM users WHERE user_id = 1');
		$row = $res->fetchRow();
		$this->assertEquals('1980-09-01', self::$db->unescape('%d', $row['birthday']));
	}
	
	public function testUnescapeString()
	{
		$res = self::$db->query('SELECT first_name FROM users WHERE user_id = 1');
		$row = $res->fetchRow();
		$this->assertEquals('Will', self::$db->unescape('%s', $row['first_name']));
	}
	
	public function testUnescapeTime()
	{
		$res = self::$db->query('SELECT time_of_last_login FROM users WHERE user_id = 1');
		$row = $res->fetchRow();
		$this->assertEquals('17:00:00', self::$db->unescape('%t', $row['time_of_last_login']));
	}
	
	public function testUnescapeTimestamp()
	{
		$res = self::$db->query('SELECT date_created FROM users WHERE user_id = 1');
		$row = $res->fetchRow();
		$this->assertEquals('2008-05-01 13:00:00', self::$db->unescape('%p', $row['date_created']));
	}
}
