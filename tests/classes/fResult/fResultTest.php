<?php
require_once('./support/init.php');
 
class fResultTest extends PHPUnit_Framework_TestSuite
{
	public static function suite()
	{
		$suite = new fResultTest();
		$suite->addTestSuite('fResultTestNoModifications');
		$suite->addTestSuite('fResultTestModifications');
		return $suite;
	}
}

class fResultTestModifications extends PHPUnit_Framework_TestCase
{
	public $db;
	
	public function setUp()
	{
		if (defined('SKIPPING')) {
			$this->markTestSkipped();
		}
		$this->db = new fDatabase(DB_TYPE, DB, DB_USERNAME, DB_PASSWORD, DB_HOST, DB_PORT); 
		$this->db->execute(file_get_contents(DB_SETUP_FILE));
	}
	
	public function tearDown()
	{
		if (defined('SKIPPING')) {
			return;
		}
		teardown($this->db, DB_TEARDOWN_FILE);
	}
	
	public function testTransactionRollback()
	{
		$this->db->query("BEGIN");
		$this->db->query("DELETE FROM users WHERE user_id = 4");
		$res = $this->db->query("SELECT user_id FROM users");
		$this->assertEquals(3, $res->countReturnedRows());
		$this->db->query("ROLLBACK");
		$res = $this->db->query("SELECT user_id FROM users");
		$this->assertEquals(4, $res->countReturnedRows());
	}
	
	public function testTransactionCommit()
	{
		$this->db->query("BEGIN");
		$this->db->query("DELETE FROM users WHERE user_id = 4");
		$res = $this->db->query("SELECT user_id FROM users");
		$this->assertEquals(3, $res->countReturnedRows());
		$this->db->query("COMMIT");
		$res = $this->db->query("SELECT user_id FROM users");
		$this->assertEquals(3, $res->countReturnedRows());
	}
}

class fResultTestNoModifications extends PHPUnit_Framework_TestCase
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
		self::$db->query('BEGIN');
	}
	
	public function tearDown()
	{
		if (defined('SKIPPING')) {
			return;
		}
		self::$db->query('ROLLBACK');
	}

	public function testInsertAutoIncrementedValue()
	{
		$res = self::$db->query(
			"INSERT INTO users (first_name, middle_initial, last_name, email_address, status, times_logged_in, date_created, birthday, time_of_last_login, is_validated, hashed_password) VALUES (%s, %s, %s, %s, %s, %i, %p, %d, %t, %b, %s)",
			'John',
			'',
			'Doe',
			'john@doe.com',
			'Active',
			5,
			new fTimestamp(),
			new fDate(),
			new fTime(),
			TRUE,
			'password'
		);	
		$this->assertEquals(5, $res->getAutoIncrementedValue());	
	}
	
	public function testInsertAffectedRows()
	{
		$res = self::$db->query(
			"INSERT INTO users (first_name, middle_initial, last_name, email_address, status, times_logged_in, date_created, birthday, time_of_last_login, is_validated, hashed_password) VALUES (%s, %s, %s, %s, %s, %i, %p, %d, %t, %b, %s)",
			'John',
			'',
			'Doe',
			'john@doe.com',
			'Active',
			5,
			new fTimestamp(),
			new fDate(),
			new fTime(),
			TRUE,
			'password'
		);	
		$this->assertEquals(1, $res->countAffectedRows());	
	}
	
	public function testInsertReturnedRows()
	{
		$res = self::$db->query(
			"INSERT INTO users (first_name, middle_initial, last_name, email_address, status, times_logged_in, date_created, birthday, time_of_last_login, is_validated, hashed_password) VALUES (%s, %s, %s, %s, %s, %i, %p, %d, %t, %b, %s)",
			'John',
			'',
			'Doe',
			'john@doe.com',
			'Active',
			5,
			new fTimestamp(),
			new fDate(),
			new fTime(),
			TRUE,
			'password'
		);
		$this->assertEquals(0, $res->countReturnedRows());
	}
	
	public function testInsertFetchRow()
	{
		$this->setExpectedException('fNoRowsException');
		
		$res = self::$db->query(
			"INSERT INTO users (first_name, middle_initial, last_name, email_address, status, times_logged_in, date_created, birthday, time_of_last_login, is_validated, hashed_password) VALUES (%s, %s, %s, %s, %s, %i, %p, %d, %t, %b, %s)",
			'John',
			'',
			'Doe',
			'john@doe.com',
			'Active',
			5,
			new fTimestamp(),
			new fDate(),
			new fTime(),
			TRUE,
			'password'
		);
		$res->fetchRow();
	}
	
	public function testInsertFetchScalar()
	{
		$this->setExpectedException('fNoRowsException');
		
		$res = self::$db->query(
			"INSERT INTO users (first_name, middle_initial, last_name, email_address, status, times_logged_in, date_created, birthday, time_of_last_login, is_validated, hashed_password) VALUES (%s, %s, %s, %s, %s, %i, %p, %d, %t, %b, %s)",
			'John',
			'',
			'Doe',
			'john@doe.com',
			'Active',
			5,
			new fTimestamp(),
			new fDate(),
			new fTime(),
			TRUE,
			'password'
		);
		$res->fetchScalar();
	}
	
	public function testInsertFetchAllRows()
	{
		$res = self::$db->query(
			"INSERT INTO users (first_name, middle_initial, last_name, email_address, status, times_logged_in, date_created, birthday, time_of_last_login, is_validated, hashed_password) VALUES (%s, %s, %s, %s, %s, %i, %p, %d, %t, %b, %s)",
			'John',
			'',
			'Doe',
			'john@doe.com',
			'Active',
			5,
			new fTimestamp(),
			new fDate(),
			new fTime(),
			TRUE,
			'password'
		);
		$this->assertEquals(array(), $res->fetchAllRows());
	}
	
	public function testDeleteAffectedRows()
	{
		$res = self::$db->query("DELETE FROM users WHERE user_id IN (3, 4)");
		$this->assertEquals(2, $res->countAffectedRows());	
	}
	
	public function testDeleteReturnedRows()
	{
		$res = self::$db->query("DELETE FROM users WHERE user_id IN (3, 4)");
		$this->assertEquals(0, $res->countReturnedRows());	
	}
	
	public function testUpdateAffectedRows()
	{
		$res = self::$db->query("UPDATE users SET first_name = %s", 'First');
		$this->assertEquals(4, $res->countAffectedRows());	
	}
	
	public function testUpdateReturnedRows()
	{
		$res = self::$db->query("UPDATE users SET first_name = %s", 'First');
		$this->assertEquals(0, $res->countReturnedRows());	
	}
	
	public function testGetSql()
	{
		$res = self::$db->query("SELECT user_id FROM users");
		$this->assertEquals('SELECT user_id FROM users', $res->getSQL());
	}
	
	public function testGetUntranslatedSql()
	{
		$res = self::$db->query("SELECT user_id FROM users");
		$this->assertEquals(NULL, $res->getUntranslatedSQL());
	}
	
	public function testCountAffectedRows()
	{
		$res = self::$db->query("SELECT user_id FROM users");
		$this->assertEquals(0, $res->countAffectedRows());
	}
	
	public function testCountReturnedRows()
	{
		$res = self::$db->query("SELECT user_id FROM users");
		$this->assertEquals(4, $res->countReturnedRows());
	}
	
	public function testNoAutoIncrementedValue()
	{
		$res = self::$db->query("SELECT user_id FROM users");
		$this->assertEquals(NULL, $res->getAutoIncrementedValue());
	}
	
	public function testCountReturnedRows2()
	{
		$res = self::$db->query("SELECT user_id FROM users WHERE user_id = 99");
		$this->assertEquals(0, $res->countReturnedRows());
	}
	
	public function testFetchScalar()
	{
		$res = self::$db->query("SELECT first_name FROM users WHERE user_id = 1");
		$this->assertEquals('Will', $res->fetchScalar());
	}
	
	public function testFetchRow()
	{
		$res = self::$db->query("SELECT first_name, last_name, email_address FROM users WHERE user_id = 1");
		$this->assertEquals(
			array(
				'first_name'    => 'Will',
				'last_name'     => 'Bond',
				'email_address' => 'will@flourishlib.com'
			),
			$res->fetchRow()
		);
	}
	
	public function testFetchRowException()
	{
		$this->setExpectedException('fNoRowsException');
		
		$res = self::$db->query("SELECT first_name, last_name, email_address FROM users WHERE user_id = 25");
		$res->fetchRow();
	}
	
	public function testFetchAllRows()
	{
		$res = self::$db->query("SELECT first_name, last_name, email_address FROM users WHERE user_id IN (1, 2) ORDER BY user_id");
		$this->assertEquals(
			array(
				array(
					'first_name'    => 'Will',
					'last_name'     => 'Bond',
					'email_address' => 'will@flourishlib.com'
				),
				array(
					'first_name'    => 'John',
					'last_name'     => 'Smith',
					'email_address' => 'john@smith.com'
				)
			),
			$res->fetchAllRows()
		);
	}
	
	public function testFetchAllRows2()
	{
		$res = self::$db->query("SELECT first_name, last_name, email_address FROM users WHERE user_id IN (25) ORDER BY user_id");
		$this->assertEquals(array(), $res->fetchAllRows());
	}
	
	public function testIteration()
	{
		$res = self::$db->query("SELECT first_name, last_name, email_address FROM users WHERE user_id IN (1, 2) ORDER BY user_id");
		$i = 0;
		foreach ($res as $row) {
			$this->assertEquals(
				array(
					'first_name',
					'last_name',
					'email_address'
				),
				array_keys($row)
			);	
			$i++;
		}
		$this->assertEquals(2, $i);
	}
	
	public function testRepeatIteration()
	{
		$res = self::$db->query("SELECT first_name, last_name, email_address FROM users WHERE user_id IN (1, 2) ORDER BY user_id");
		
		$i = 0;
		foreach ($res as $row) {
			$this->assertEquals(
				array(
					'first_name',
					'last_name',
					'email_address'
				),
				array_keys($row)
			);	
			$i++;
		}
		$this->assertEquals(2, $i);
		
		$i = 0;
		foreach ($res as $row) {
			$this->assertEquals(
				array(
					'first_name',
					'last_name',
					'email_address'
				),
				array_keys($row)
			);	
			$i++;
		}
		$this->assertEquals(2, $i);
	}
	
	public function testEmptyIteration()
	{
		$res = self::$db->query("SELECT first_name, last_name, email_address FROM users WHERE user_id IN (25) ORDER BY user_id");
	
		$i = 0;
		foreach ($res as $row) {
			$i++;	
		}
		
		$this->assertEquals(0, $i);
	}
	
	public function testTossIfEmpty()
	{
		$this->setExpectedException('fNoRowsException');
		$res = self::$db->query("SELECT first_name, last_name, email_address FROM users WHERE user_id IN (25) ORDER BY user_id");
		$res->tossIfNoRows();
	}
	
	public function testTossIfEmpty2()
	{
		$res = self::$db->query("SELECT first_name, last_name, email_address FROM users WHERE user_id IN (1) ORDER BY user_id");
		$res->tossIfNoRows();
	}
	
	public function testSeek()
	{
		$res = self::$db->query("SELECT first_name, last_name, email_address FROM users ORDER BY user_id");
		$res->seek(3);
		$this->assertEquals(
			array(
				'first_name'    => 'Foo',
				'last_name'     => 'Barish',
				'email_address' => 'foo@example.com'
			),
			$res->fetchRow()
		);
		$res->seek(0);
		$this->assertEquals(
			array(
				'first_name'    => 'Will',
				'last_name'     => 'Bond',
				'email_address' => 'will@flourishlib.com'
			),
			$res->fetchRow()
		);
	}
	
	public function testSeekFailure()
	{
		$this->setExpectedException('fProgrammerException');
		$res = self::$db->query("SELECT first_name, last_name, email_address FROM users ORDER BY user_id");
		$res->seek(4);
	}
	
	public function testConcurrentResults()
	{
		$res = self::$db->query("SELECT first_name, last_name, email_address FROM users WHERE user_id IN (1, 2) ORDER BY user_id");
		
		$res2 = self::$db->query("SELECT first_name, last_name, email_address FROM users WHERE user_id IN (3, 4) ORDER BY user_id");
		
		$this->assertEquals(
			array(
				'first_name'    => 'Will',
				'last_name'     => 'Bond',
				'email_address' => 'will@flourishlib.com'
			),
			$res->fetchRow()
		);
		
		$this->assertEquals(
			array(
				'first_name'    => 'Bar',
				'last_name'     => 'Sheba',
				'email_address' => 'bar@example.com'
			),
			$res2->fetchRow()
		);
		
		$this->assertEquals(
			array(
				'first_name'    => 'John',
				'last_name'     => 'Smith',
				'email_address' => 'john@smith.com'
			),
			$res->fetchRow()
		);
		
		$this->assertEquals(
			array(
				'first_name'    => 'Foo',
				'last_name'     => 'Barish',
				'email_address' => 'foo@example.com'
			),
			$res2->fetchRow()
		);
	}
}