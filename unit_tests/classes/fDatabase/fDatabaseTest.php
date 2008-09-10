<?php
require_once('./support/init.php');
 
class fDatabaseTest extends PHPUnit_Framework_TestSuite
{
	public static function suite()
	{
		return new fDatabaseTest('fDatabaseTestChild');
	}
 
	protected function setUp()
	{
		include('./database/config.php');	
		$cd = $config[$config['active_database']];
		$cd['type'] = $config['active_database'];    
		
		$db = new fDatabase($cd['type'], $cd['database'], $cd['username'], $cd['password'], $cd['host'], $cd['port']); 
		$db->query(file_get_contents('./database/setup.' . $cd['type'] . '.sql'));
	}
 
	protected function tearDown()
	{
		include('./database/config.php');	
		$cd = $config[$config['active_database']];
		$cd['type'] = $config['active_database'];
		
		$db = new fDatabase($cd['type'], $cd['database'], $cd['username'], $cd['password'], $cd['host'], $cd['port']); 
		$db->query(file_get_contents('./database/teardown.' . $cd['type'] . '.sql'));   
	}
}
 
class fDatabaseTestChild extends PHPUnit_Framework_TestCase
{
	public $db;
	public $cd;
	
	public function setUp()
	{
		include('./database/config.php');	
		$cd = $config[$config['active_database']];
		$cd['type'] = $config['active_database'];	
		
		$this->cd = $cd;
		$this->db = new fDatabase($cd['type'], $cd['database'], $cd['username'], $cd['password'], $cd['host'], $cd['port']);
	}
	
	public function testGetDatabase()
	{
		$this->assertEquals($this->cd['database'], $this->db->getDatabase());
	}
	
	public function testGetExtension()
	{
		$this->assertContains($this->db->getExtension(), array('pdo', 'sqlite', 'mysql', 'mysqli', 'mssql', 'pgsql'));
	}
	
	public function testGetType()
	{
		$this->assertEquals($this->cd['type'], $this->db->getType());
	}
	
	public function testQuery()
	{
		$res = $this->db->query('SELECT * FROM users');
		$this->assertEquals('fResult', get_class($res));	
	}
	
	public function testSqlError()
	{
		$this->setExpectedException('fSQLException');    
		$res = $this->db->query('SLECT * FROM users');	
	}
	
	public function testTranslatedQuery()
	{
		$res = $this->db->translatedQuery('SELECT * FROM users');
		$this->assertEquals('fResult', get_class($res));	
	}
	
	public function testTranslatedSqlError()
	{
		$this->setExpectedException('fSQLException');    
		$res = $this->db->translatedQuery('SLECT * FROM users');	
	}
	
	public function testUnbufferedQuery()
	{
		$res = $this->db->unbufferedQuery('SELECT * FROM users');
		$this->assertEquals('fUnbufferedResult', get_class($res));
		$res->__destruct();	
	}
	
	public function testUnbufferedSqlError()
	{
		$this->setExpectedException('fSQLException');    
		$res = $this->db->unbufferedQuery('SLECT * FROM users');	
	}
	
	public function testUnbufferedTranslatedQuery()
	{
		$res = $this->db->unbufferedTranslatedQuery('SELECT * FROM users');
		$this->assertEquals('fUnbufferedResult', get_class($res));
		$res->__destruct();	
	}
	
	public function testUnbufferedTranslatedSqlError()
	{
		$this->setExpectedException('fSQLException');    
		$res = $this->db->unbufferedTranslatedQuery('SLECT * FROM users');	
	}
	
	public function testEscapeBlob()
	{
		switch ($this->db->getType()) {
			case 'pdo':    $expected = "'☺☻♥♦♣♠•◘○◙'"; break;
			case 'sqlite': $expected = "X'" . bin2hex('☺☻♥♦♣♠•◘○◙') . "'"; break;
			case 'mysql':  $expected = "'☺☻♥♦♣♠•◘○◙'"; break;
			case 'pgsql':  $expected = "'☺☻♥♦♣♠•◘○◙'"; break;
			case 'mssql':  $expected = "0x" . bin2hex('☺☻♥♦♣♠•◘○◙'); break;	
		}
		$this->assertEquals($expected, $this->db->escape('%l', '☺☻♥♦♣♠•◘○◙'));
	}
	
	public function testEscapeBoolean()
	{
		$res = $this->db->query('SELECT * FROM users WHERE is_validated = ' . $this->db->escape('%b', TRUE));
		$res = $this->db->query('SELECT * FROM users WHERE is_validated = ' . $this->db->escape('%b', FALSE));
	}
	
	public function testEscapeDate()
	{
		$res = $this->db->query('SELECT * FROM users WHERE date_created < ' . $this->db->escape('%d', 'tomorrow'));
		$res = $this->db->query('SELECT * FROM users WHERE date_created < ' . $this->db->escape('%d', '2007-02-01'));
		$res = $this->db->query('SELECT * FROM users WHERE date_created < ' . $this->db->escape('%d', 'last week'));
		$res = $this->db->query('SELECT * FROM users WHERE date_created < ' . $this->db->escape('%d', 'May 5th, 1950'));
	}
	
	/**
	 * @dataProvider escapeDateTimeFailProvider
	 */
	public function testEscapeDateFail($input, $output)
	{
		$this->assertSame($output, $this->db->escape('%d', $input));
	}
	
	public function testEscapeString()
	{
		$res = $this->db->query('SELECT * FROM users WHERE first_name = ' . $this->db->escape('%s', "O'keefe"));
		$res = $this->db->query('SELECT * FROM users WHERE first_name = ' . $this->db->escape('%s', "Johnathan"));
		$res = $this->db->query('SELECT * FROM users WHERE first_name = ' . $this->db->escape('%s', "\\slashed apos'"));
		$res = $this->db->query('SELECT * FROM users WHERE first_name = ' . $this->db->escape('%s', 'FooBar'));
	}
	
	public function testEscapeTime()
	{
		$res = $this->db->query('SELECT * FROM users WHERE time_of_last_login < ' . $this->db->escape('%t', 'now'));
		$res = $this->db->query('SELECT * FROM users WHERE time_of_last_login < ' . $this->db->escape('%t', '9:35'));
		$res = $this->db->query('SELECT * FROM users WHERE time_of_last_login < ' . $this->db->escape('%t', '2pm'));
		$res = $this->db->query('SELECT * FROM users WHERE time_of_last_login < ' . $this->db->escape('%t', 'midnight'));
	}
	
	public static function escapeDateTimeFailProvider()
	{
		$output = array();
		
		$output[] = array(TRUE, 'NULL');
		$output[] = array('foo', 'NULL');
		
		return $output;
	}
	
	/**
	 * @dataProvider escapeDateTimeFailProvider
	 */
	public function testEscapeTimeFail($input, $output)
	{
		$this->assertSame($output, $this->db->escape('%t', $input));
	}
	
	public function testEscapeTimestamp()
	{
		$res = $this->db->query('SELECT * FROM users WHERE time_of_last_login < ' . $this->db->escape('%p', 'now'));
		$res = $this->db->query('SELECT * FROM users WHERE time_of_last_login < ' . $this->db->escape('%p', 'yesterday 5 pm'));
		$res = $this->db->query('SELECT * FROM users WHERE time_of_last_login < ' . $this->db->escape('%p', 'June 5th, 2004 1:15 am'));
		$res = $this->db->query('SELECT * FROM users WHERE time_of_last_login < ' . $this->db->escape('%p', '2008-02-02 20:15:15'));
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
		$res = $this->db->query('SELECT * FROM users WHERE user_id = 1');
		$row = $res->fetchRow();
		$this->assertEquals(pack("H*", "5527939aca3e9e80d5ab3bee47391f0f"), $this->db->unescape('%l', $row['hashed_password']));
	}
	
	public function testUnescapeBoolean()
	{
		$res = $this->db->query('SELECT * FROM users WHERE user_id = 1');
		$row = $res->fetchRow();
		$this->assertEquals(TRUE, $this->db->unescape('%b', $row['is_validated']));
	}
	
	public function testUnescapeDate()
	{
		$res = $this->db->query('SELECT * FROM users WHERE user_id = 1');
		$row = $res->fetchRow();
		$this->assertEquals('1980-09-01', $this->db->unescape('%d', $row['birthday']));
	}
	
	public function testUnescapeString()
	{
		$res = $this->db->query('SELECT * FROM users WHERE user_id = 1');
		$row = $res->fetchRow();
		$this->assertEquals('Will', $this->db->unescape('%s', $row['first_name']));
	}
	
	public function testUnescapeTime()
	{
		$res = $this->db->query('SELECT * FROM users WHERE user_id = 1');
		$row = $res->fetchRow();
		$this->assertEquals('17:00:00', $this->db->unescape('%t', $row['time_of_last_login']));
	}
	
	public function testUnescapeTimestamp()
	{
		$res = $this->db->query('SELECT * FROM users WHERE user_id = 1');
		$row = $res->fetchRow();
		$this->assertEquals('2008-05-01 13:00:00', $this->db->unescape('%p', $row['date_created']));
	}
	
	public function tearDown()
	{
		
	}
}
?>