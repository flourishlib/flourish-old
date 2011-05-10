<?php
require_once('./support/init.php');
 
class fStatementTest extends PHPUnit_Framework_TestSuite
{
	public static function suite()
	{
		$suite = new fStatementTest();
		$suite->addTestSuite('fStatementTestNoModifications');
		$suite->addTestSuite('fStatementTestModifications');
		return $suite;
	}
}

class fStatementTestModifications extends PHPUnit_Framework_TestCase
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
		$statement = $this->db->prepare("SELECT user_id FROM users");
		
		$this->db->query("BEGIN");
		$this->db->query("DELETE FROM users WHERE user_id = %i", 4);
		$res = $this->db->query($statement);
		$this->assertEquals(3, $res->countReturnedRows());
		$this->db->query("ROLLBACK");
		$res = $this->db->query($statement);
		$this->assertEquals(4, $res->countReturnedRows());
	}
	
	public function testTransactionCommit()
	{
		$statement = $this->db->prepare("SELECT user_id FROM users");
		
		$this->db->query("BEGIN");
		$this->db->query("DELETE FROM users WHERE user_id = %i", 4);
		$res = $this->db->query($statement);
		$this->assertEquals(3, $res->countReturnedRows());
		$this->db->query("COMMIT");
		$res = $this->db->query($statement);
		$this->assertEquals(3, $res->countReturnedRows());
	}
}

class fStatementTestNoModifications extends PHPUnit_Framework_TestCase
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
		self::$db->execute('BEGIN');
	}
	
	public function tearDown()
	{
		if (defined('SKIPPING')) {
			return;
		}
		self::$db->execute('ROLLBACK');
	}

	public function testInsertAutoIncrementedValue()
	{
		$statement = self::$db->prepare("INSERT INTO users (first_name, middle_initial, last_name, email_address, status, times_logged_in, date_created, birthday, time_of_last_login, is_validated, hashed_password) VALUES (%s, %s, %s, %s, %s, %i, %p, %d, %t, %b, %s)");
		$res = self::$db->query(
			$statement,
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
		$statement = self::$db->prepare("INSERT INTO users (first_name, middle_initial, last_name, email_address, status, times_logged_in, date_created, birthday, time_of_last_login, is_validated, hashed_password) VALUES (%s, %s, %s, %s, %s, %i, %p, %d, %t, %b, %s)");
		$res = self::$db->query(
			$statement,
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
		$statement = self::$db->prepare("INSERT INTO users (first_name, middle_initial, last_name, email_address, status, times_logged_in, date_created, birthday, time_of_last_login, is_validated, hashed_password) VALUES (%s, %s, %s, %s, %s, %i, %p, %d, %t, %b, %s)");
		$res = self::$db->query(
			$statement,
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
		
		$statement = self::$db->prepare("INSERT INTO users (first_name, middle_initial, last_name, email_address, status, times_logged_in, date_created, birthday, time_of_last_login, is_validated, hashed_password) VALUES (%s, %s, %s, %s, %s, %i, %p, %d, %t, %b, %s)");
		$res = self::$db->query(
			$statement,
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
		
		$statement = self::$db->prepare("INSERT INTO users (first_name, middle_initial, last_name, email_address, status, times_logged_in, date_created, birthday, time_of_last_login, is_validated, hashed_password) VALUES (%s, %s, %s, %s, %s, %i, %p, %d, %t, %b, %s)");
		$res = self::$db->query(
			$statement,
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
		$statement = self::$db->prepare("INSERT INTO users (first_name, middle_initial, last_name, email_address, status, times_logged_in, date_created, birthday, time_of_last_login, is_validated, hashed_password) VALUES (%s, %s, %s, %s, %s, %i, %p, %d, %t, %b, %s)");
		$res = self::$db->query(
			$statement,
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
		$res = self::$db->query(self::$db->prepare("DELETE FROM users WHERE user_id > %i AND user_id < %i"), 2, 5);
		$this->assertEquals(2, $res->countAffectedRows());	
	}
	
	public function testDeleteReturnedRows()
	{
		$res = self::$db->query(self::$db->prepare("DELETE FROM users WHERE user_id > %i AND user_id < %i"), 2, 5);
		$this->assertEquals(0, $res->countReturnedRows());	
	}
	
	public function testUpdateAffectedRows()
	{
		$res = self::$db->query(self::$db->prepare("UPDATE users SET first_name = %s"), 'First');
		$this->assertEquals(4, $res->countAffectedRows());	
	}
	
	public function testUpdateReturnedRows()
	{
		$res = self::$db->query(self::$db->prepare("UPDATE users SET first_name = %s"), 'First');
		$this->assertEquals(0, $res->countReturnedRows());	
	}

	public function testMultipleExecuteInsert()
	{
		$insert_statement = self::$db->prepare("INSERT INTO users (first_name, middle_initial, last_name, email_address, status, times_logged_in, date_created, birthday, time_of_last_login, is_validated, hashed_password) VALUES (%s, %s, %s, %s, %s, %i, %p, %d, %t, %b, %s)");
		$select_statement = self::$db->prepare("SELECT email_address FROM users ORDER BY user_id");
		
		self::$db->execute(
			$insert_statement,
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
		
		$this->assertEquals(
			array(
				array('email_address' => 'will@flourishlib.com'),
				array('email_address' => 'john@smith.com'),
				array('email_address' => 'bar@example.com'),
				array('email_address' => 'foo@example.com'),
				array('email_address' => 'john@doe.com')
			),
			self::$db->query($select_statement)->fetchAllRows()
		);
		
		self::$db->execute(
			$insert_statement,
			'John',
			'',
			'Doe',
			'john2@doe.com',
			'Active',
			5,
			new fTimestamp(),
			new fDate(),
			new fTime(),
			TRUE,
			'password'
		);
		
		$this->assertEquals(
			array(
				array('email_address' => 'will@flourishlib.com'),
				array('email_address' => 'john@smith.com'),
				array('email_address' => 'bar@example.com'),
				array('email_address' => 'foo@example.com'),
				array('email_address' => 'john@doe.com'),
				array('email_address' => 'john2@doe.com')
			),
			self::$db->query($select_statement)->fetchAllRows()
		);
	}
	
	public function testMultipleExecuteDelete()
	{
		$delete_statement = self::$db->prepare("DELETE FROM users WHERE user_id = %i");
		$select_statement = self::$db->prepare("SELECT email_address FROM users ORDER BY user_id");
		
		$insert_statement = self::$db->prepare("INSERT INTO users (first_name, middle_initial, last_name, email_address, status, times_logged_in, date_created, birthday, time_of_last_login, is_validated, hashed_password) VALUES (%s, %s, %s, %s, %s, %i, %p, %d, %t, %b, %s)");
		
		$res = self::$db->query(
			$insert_statement,
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
		
		$res2 = self::$db->query(
			$insert_statement,
			'John',
			'',
			'Doe',
			'john2@doe.com',
			'Active',
			5,
			new fTimestamp(),
			new fDate(),
			new fTime(),
			TRUE,
			'password'
		);
		
		self::$db->execute($delete_statement, $res2->getAutoIncrementedValue());
		
		$this->assertEquals(
			array(
				array('email_address' => 'will@flourishlib.com'),
				array('email_address' => 'john@smith.com'),
				array('email_address' => 'bar@example.com'),
				array('email_address' => 'foo@example.com'),
				array('email_address' => 'john@doe.com')
			),
			self::$db->query($select_statement)->fetchAllRows()
		);
		
		self::$db->execute($delete_statement, $res->getAutoIncrementedValue());
		
		$this->assertEquals(
			array(
				array('email_address' => 'will@flourishlib.com'),
				array('email_address' => 'john@smith.com'),
				array('email_address' => 'bar@example.com'),
				array('email_address' => 'foo@example.com')
			),
			self::$db->query($select_statement)->fetchAllRows()
		);
		
		self::$db->execute($delete_statement, 4);
		
		$this->assertEquals(
			array(
				array('email_address' => 'will@flourishlib.com'),
				array('email_address' => 'john@smith.com'),
				array('email_address' => 'bar@example.com')
			),
			self::$db->query($select_statement)->fetchAllRows()
		);
		
		self::$db->execute($delete_statement, 3);
		
		$this->assertEquals(
			array(
				array('email_address' => 'will@flourishlib.com'),
				array('email_address' => 'john@smith.com')
			),
			self::$db->query($select_statement)->fetchAllRows()
		);
	}
	
	public function testSQLFail()
	{
		$this->setExpectedException('fSQLException');
		$statement = self::$db->prepare("DELETE FROM usrs");
		$res = self::$db->query($statement);
	}
	
	public function testGetSql()
	{
		$res = self::$db->query(self::$db->prepare("SELECT user_id FROM users"));
		$this->assertEquals('SELECT user_id FROM users', $res->getSQL());
	}
	
	public function testGetUntranslatedSql()
	{
		$res = self::$db->query(self::$db->prepare("SELECT user_id FROM users"));
		$this->assertEquals(NULL, $res->getUntranslatedSQL());
	}
	
	public function testCountAffectedRows()
	{
		$res = self::$db->query(self::$db->prepare("SELECT user_id FROM users"));
		$this->assertEquals(0, $res->countAffectedRows());
	}
	
	public function testCountReturnedRows()
	{
		$res = self::$db->query(self::$db->prepare("SELECT user_id FROM users"));
		$this->assertEquals(4, $res->countReturnedRows());
	}
	
	public function testNoAutoIncrementedValue()
	{
		$res = self::$db->query(self::$db->prepare("SELECT user_id FROM users"));
		$this->assertEquals(NULL, $res->getAutoIncrementedValue());
	}
	
	public function testCountReturnedRows2()
	{
		$res = self::$db->query(self::$db->prepare("SELECT user_id FROM users WHERE user_id = %i"), 99);
		$this->assertEquals(0, $res->countReturnedRows());
	}
	
	public function testFetchScalar()
	{
		$res = self::$db->query(self::$db->prepare("SELECT first_name FROM users WHERE user_id = %i"), 1);
		$this->assertEquals('Will', $res->fetchScalar());
	}

	public function testProvideArray()
	{
		$res = self::$db->query(self::$db->prepare("SELECT first_name FROM users WHERE user_id = %i OR user_id = %i"), array(1, 2));
		$this->assertEquals('Will', $res->fetchScalar());
	}

	public function testInvalidValueToNull()
	{
		$res = self::$db->query(self::$db->prepare("SELECT first_name FROM users WHERE user_id = %i"), 'test');
		$this->assertEquals(0, $res->countReturnedRows());
	}
	
	public function testFetchRow()
	{
		$res = self::$db->query(self::$db->prepare("SELECT first_name, last_name, email_address FROM users WHERE user_id = %i"), 1);
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
		
		$res = self::$db->query(self::$db->prepare("SELECT first_name, last_name, email_address FROM users WHERE user_id = %i"), 25);
		$res->fetchRow();
	}
	
	public function testFetchAllRows()
	{
		$res = self::$db->query(self::$db->prepare("SELECT first_name, last_name, email_address FROM users WHERE user_id < %i ORDER BY user_id"), 3);
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
		$res = self::$db->query(self::$db->prepare("SELECT first_name, last_name, email_address FROM users WHERE user_id IN (%i) ORDER BY user_id"), 25);
		$this->assertEquals(array(), $res->fetchAllRows());
	}
	
	public function testIteration()
	{
		$res = self::$db->query(self::$db->prepare("SELECT first_name, last_name, email_address FROM users WHERE user_id < %i ORDER BY user_id"), 3);
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
		$res = self::$db->query(self::$db->prepare("SELECT first_name, last_name, email_address FROM users WHERE user_id < %i ORDER BY user_id"), 3);
		
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
		$res = self::$db->query(self::$db->prepare("SELECT first_name, last_name, email_address FROM users WHERE user_id IN (%i) ORDER BY user_id"), 25);
	
		$i = 0;
		foreach ($res as $row) {
			$i++;	
		}
		
		$this->assertEquals(0, $i);
	}
	
	public function testTossIfEmpty()
	{
		$this->setExpectedException('fNoRowsException');
		$res = self::$db->query(self::$db->prepare("SELECT first_name, last_name, email_address FROM users WHERE user_id IN (%i) ORDER BY user_id"), 25);
		$res->tossIfNoRows();
	}
	
	public function testTossIfEmpty2()
	{
		$res = self::$db->query(self::$db->prepare("SELECT first_name, last_name, email_address FROM users WHERE user_id IN (%i) ORDER BY user_id"), 1);
		$res->tossIfNoRows();
	}
	
	public function testSeek()
	{
		$res = self::$db->query(self::$db->prepare("SELECT first_name, last_name, email_address FROM users ORDER BY user_id"));
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
		$res = self::$db->query(self::$db->prepare("SELECT first_name, last_name, email_address FROM users ORDER BY user_id"));
		$res->seek(4);
	}
	
	public function testConcurrentResults()
	{
		$res = self::$db->query(self::$db->prepare("SELECT first_name, last_name, email_address FROM users WHERE user_id < %i ORDER BY user_id"), 3);
		
		$res2 = self::$db->query(self::$db->prepare("SELECT first_name, last_name, email_address FROM users WHERE user_id > %i AND user_id < %i ORDER BY user_id"), 2, 5);
		
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
	
	public function testReuse()
	{
		$statement = self::$db->prepare("SELECT user_id, email_address FROM users WHERE user_id = %i");
		
		$this->assertEquals(
			array('user_id' => 1, 'email_address' => 'will@flourishlib.com'),
			self::$db->query($statement, 1)->fetchRow()
		);
		$this->assertEquals(
			array('user_id' => 2, 'email_address' => 'john@smith.com'),
			self::$db->query($statement, 2)->fetchRow()
		);
		$this->assertEquals(
			array('user_id' => 3, 'email_address' => 'bar@example.com'),
			self::$db->query($statement, 3)->fetchRow()
		);
		$this->assertEquals(
			array('user_id' => 4, 'email_address' => 'foo@example.com'),
			self::$db->query($statement, 4)->fetchRow()
		);
		
		$this->assertEquals(
			array(
				array('email_address' => 'will@flourishlib.com'),
				array('email_address' => 'john@smith.com'),
				array('email_address' => 'bar@example.com'),
				array('email_address' => 'foo@example.com')
			),
			self::$db->query("SELECT email_address FROM users ORDER BY user_id ASC")->fetchAllRows()
		);
	}
}