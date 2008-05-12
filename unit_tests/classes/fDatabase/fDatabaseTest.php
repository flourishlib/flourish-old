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
		switch ($this->db->getExtension()) {
			case 'pdo':    $expected = "'☺☻♥♦♣♠•◘○◙'"; break;
			case 'sqlite': $expected = "X'" . bin2hex('☺☻♥♦♣♠•◘○◙') . "'"; break;
			case 'mysql':  $expected = "'☺☻♥♦♣♠•◘○◙'"; break;
			case 'pgsql':  $expected = "'☺☻♥♦♣♠•◘○◙'"; break;
			case 'mssql':  $expected = "0x" . bin2hex('☺☻♥♦♣♠•◘○◙'); break;	
		}
		$this->assertEquals($expected, $this->db->escapeBlob('☺☻♥♦♣♠•◘○◙'));
	}
	
	public function testEscapeBoolean()
	{
		$res = $this->db->query('SELECT * FROM users WHERE is_validated = ' . $this->db->escapeBoolean(TRUE));
		$res = $this->db->query('SELECT * FROM users WHERE is_validated = ' . $this->db->escapeBoolean(FALSE));
	}
	
	public function testEscapeDate()
	{
		$res = $this->db->query('SELECT * FROM users WHERE date_created < ' . $this->db->escapeDate('tomorrow'));
		$res = $this->db->query('SELECT * FROM users WHERE date_created < ' . $this->db->escapeDate('2007-02-01'));
		$res = $this->db->query('SELECT * FROM users WHERE date_created < ' . $this->db->escapeDate('last week'));
		$res = $this->db->query('SELECT * FROM users WHERE date_created < ' . $this->db->escapeDate('May 5th, 1950'));
	}
	
	public function testEscapeDateFail()
	{
		$this->setExpectedException('fValidationException'); 
		$res = $this->db->query('SELECT * FROM users WHERE date_created < ' . $this->db->escapeDate(TRUE));
	}
	
	public function testEscapeDateFail2()
	{
		$this->setExpectedException('fValidationException'); 
		$res = $this->db->query('SELECT * FROM users WHERE date_created < ' . $this->db->escapeDate('foo'));
	}
	
	public function testEscapeString()
	{
		$res = $this->db->query('SELECT * FROM users WHERE first_name = ' . $this->db->escapeString("O'keefe"));
		$res = $this->db->query('SELECT * FROM users WHERE first_name = ' . $this->db->escapeString("Johnathan"));
		$res = $this->db->query('SELECT * FROM users WHERE first_name = ' . $this->db->escapeString("\\slashed apos'"));
		$res = $this->db->query('SELECT * FROM users WHERE first_name = ' . $this->db->escapeString('FooBar'));
	}
	
	public function testEscapeTime()
	{
		$res = $this->db->query('SELECT * FROM users WHERE time_of_last_login < ' . $this->db->escapeTime('now'));
		$res = $this->db->query('SELECT * FROM users WHERE time_of_last_login < ' . $this->db->escapeTime('9:35'));
		$res = $this->db->query('SELECT * FROM users WHERE time_of_last_login < ' . $this->db->escapeTime('2pm'));
		$res = $this->db->query('SELECT * FROM users WHERE time_of_last_login < ' . $this->db->escapeTime('midnight'));
	}
	
	public function testEscapeTimeFail()
	{
		$this->setExpectedException('fValidationException'); 
		$res = $this->db->query('SELECT * FROM users WHERE time_of_last_login < ' . $this->db->escapeTime(TRUE));
	}
	
	public function testEscapeTimeFail2()
	{
		$this->setExpectedException('fValidationException'); 
		$res = $this->db->query('SELECT * FROM users WHERE time_of_last_login < ' . $this->db->escapeTime('foo'));
	}
	
	public function testEscapeTimestamp()
	{
		$res = $this->db->query('SELECT * FROM users WHERE time_of_last_login < ' . $this->db->escapeTimestamp('now'));
		$res = $this->db->query('SELECT * FROM users WHERE time_of_last_login < ' . $this->db->escapeTimestamp('yesterday 5 pm'));
		$res = $this->db->query('SELECT * FROM users WHERE time_of_last_login < ' . $this->db->escapeTimestamp('June 5th, 2004 1:15 am'));
		$res = $this->db->query('SELECT * FROM users WHERE time_of_last_login < ' . $this->db->escapeTimestamp('2008-02-02 20:15:15'));
	}
	
	public function testEscapeTimestampFail()
	{
		$this->setExpectedException('fValidationException'); 
		$res = $this->db->query('SELECT * FROM users WHERE date_created < ' . $this->db->escapeTimestamp(TRUE));
	}
	
	public function testEscapeTimestampFail2()
	{
		$this->setExpectedException('fValidationException'); 
		$res = $this->db->query('SELECT * FROM users WHERE date_created < ' . $this->db->escapeTimestamp('foo'));
	}
	
	public function testUnescapeBlob()
	{
		$res = $this->db->query('SELECT * FROM users WHERE user_id = 1');
		$row = $res->fetchRow();
		$this->assertEquals(pack("H*", "5527939aca3e9e80d5ab3bee47391f0f"), $this->db->unescapeBlob($row['hashed_password']));
	}
	
	public function testUnescapeBoolean()
	{
		$res = $this->db->query('SELECT * FROM users WHERE user_id = 1');
		$row = $res->fetchRow();
		$this->assertEquals(TRUE, $this->db->unescapeBoolean($row['is_validated']));
	}
	
	public function testUnescapeDate()
	{
		$res = $this->db->query('SELECT * FROM users WHERE user_id = 1');
		$row = $res->fetchRow();
		$this->assertEquals('1980-09-01', $this->db->unescapeDate($row['birthday']));
	}
	
	public function testUnescapeString()
	{
		$res = $this->db->query('SELECT * FROM users WHERE user_id = 1');
		$row = $res->fetchRow();
		$this->assertEquals('Will', $this->db->unescapeString($row['first_name']));
	}
	
	public function testUnescapeTime()
	{
		$res = $this->db->query('SELECT * FROM users WHERE user_id = 1');
		$row = $res->fetchRow();
		$this->assertEquals('17:00', $this->db->unescapeTime($row['time_of_last_login']));
	}
	
	public function testUnescapeTimestamp()
	{
		$res = $this->db->query('SELECT * FROM users WHERE user_id = 1');
		$row = $res->fetchRow();
		$this->assertEquals('2008-05-01 13:00:00', $this->db->unescapeTime($row['date_created']));
	}
	
	public function tearDown()
	{
		
	}
}
?>