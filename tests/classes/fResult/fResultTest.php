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

class fResultTestModifications extends PHPUnit_Framework_TestSuite
{
	public static function suite()
	{
		return new fResultTestModifications('fResultTestModificationsChild');
	}		
}

class fResultTestModificationsChild extends PHPUnit_Framework_TestCase
{
	public $db;
	
	public function setUp()
	{
		$this->db = new fDatabase(DB_TYPE, DB, DB_USERNAME, DB_PASSWORD, DB_HOST, DB_PORT); 
		$this->db->query(file_get_contents(DB_SETUP_FILE));
	}
	
	
	public function testInsertAutoIncrementedValue()
	{
		$res = $this->db->query(
			"INSERT INTO users (first_name, middle_initial, last_name, email_address, status, times_logged_in, date_created, birthday, time_of_last_login, is_validated, hashed_password) VALUES (%s, %s, %s, %s, %s, %i, %p, %d, %t, %b, %l)",
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
		$res = $this->db->query(
			"INSERT INTO users (first_name, middle_initial, last_name, email_address, status, times_logged_in, date_created, birthday, time_of_last_login, is_validated, hashed_password) VALUES (%s, %s, %s, %s, %s, %i, %p, %d, %t, %b, %l)",
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
		$res = $this->db->query(
			"INSERT INTO users (first_name, middle_initial, last_name, email_address, status, times_logged_in, date_created, birthday, time_of_last_login, is_validated, hashed_password) VALUES (%s, %s, %s, %s, %s, %i, %p, %d, %t, %b, %l)",
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
		
		$res = $this->db->query(
			"INSERT INTO users (first_name, middle_initial, last_name, email_address, status, times_logged_in, date_created, birthday, time_of_last_login, is_validated, hashed_password) VALUES (%s, %s, %s, %s, %s, %i, %p, %d, %t, %b, %l)",
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
		
		$res = $this->db->query(
			"INSERT INTO users (first_name, middle_initial, last_name, email_address, status, times_logged_in, date_created, birthday, time_of_last_login, is_validated, hashed_password) VALUES (%s, %s, %s, %s, %s, %i, %p, %d, %t, %b, %l)",
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
		$res = $this->db->query(
			"INSERT INTO users (first_name, middle_initial, last_name, email_address, status, times_logged_in, date_created, birthday, time_of_last_login, is_validated, hashed_password) VALUES (%s, %s, %s, %s, %s, %i, %p, %d, %t, %b, %l)",
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
		$res = $this->db->query("DELETE FROM users WHERE user_id IN (3, 4)");
		$this->assertEquals(2, $res->countAffectedRows());	
	}
	
	public function testDeleteReturnedRows()
	{
		$res = $this->db->query("DELETE FROM users WHERE user_id IN (3, 4)");
		$this->assertEquals(0, $res->countReturnedRows());	
	}
	
	public function testUpdateAffectedRows()
	{
		$res = $this->db->query("UPDATE users SET first_name = %s", 'First');
		$this->assertEquals(4, $res->countAffectedRows());	
	}
	
	public function testUpdateReturnedRows()
	{
		$res = $this->db->query("UPDATE users SET first_name = %s", 'First');
		$this->assertEquals(0, $res->countReturnedRows());	
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
	
	public function tearDown()
	{
		$this->db->query(file_get_contents(DB_TEARDOWN_FILE));
	}	
}

class fResultTestNoModifications extends PHPUnit_Framework_TestSuite
{
	public static function suite()
	{
		return new fResultTestNoModifications('fResultTestNoModificationsChild');
	}
	
	protected function setUp()
	{
		$db = new fDatabase(DB_TYPE, DB, DB_USERNAME, DB_PASSWORD, DB_HOST, DB_PORT); 
		$db->query(file_get_contents(DB_SETUP_FILE));
		$this->sharedFixture = $db;
	}
 
	protected function tearDown()
	{
		$db = $this->sharedFixture;
		$db->query(file_get_contents(DB_TEARDOWN_FILE));
	} 		
}
 
class fResultTestNoModificationsChild extends PHPUnit_Framework_TestCase
{
	public $db;
	
	public function setUp()
	{
		$this->db = $this->sharedFixture;
	}
	
	public function tearDown()
	{
		
	}
	
	public function testGetSql()
	{
		$res = $this->db->query("SELECT user_id FROM users");
		$this->assertEquals('SELECT user_id FROM users', $res->getSQL());
	}
	
	public function testGetUntranslatedSql()
	{
		$res = $this->db->query("SELECT user_id FROM users");
		$this->assertEquals(NULL, $res->getUntranslatedSQL());
	}
	
	public function testCountAffectedRows()
	{
		$res = $this->db->query("SELECT user_id FROM users");
		$this->assertEquals(0, $res->countAffectedRows());
	}
	
	public function testCountReturnedRows()
	{
		$res = $this->db->query("SELECT user_id FROM users");
		$this->assertEquals(4, $res->countReturnedRows());
	}
	
	public function testNoAutoIncrementedValue()
	{
		$res = $this->db->query("SELECT user_id FROM users");
		$this->assertEquals(NULL, $res->getAutoIncrementedValue());
	}
	
	public function testCountReturnedRows2()
	{
		$res = $this->db->query("SELECT user_id FROM users WHERE user_id = 99");
		$this->assertEquals(0, $res->countReturnedRows());
	}
	
	public function testFetchScalar()
	{
		$res = $this->db->query("SELECT first_name FROM users WHERE user_id = 1");
		$this->assertEquals('Will', $res->fetchScalar());
	}
	
	public function testFetchRow()
	{
		$res = $this->db->query("SELECT first_name, last_name, email_address FROM users WHERE user_id = 1");
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
		
		$res = $this->db->query("SELECT first_name, last_name, email_address FROM users WHERE user_id = 25");
		$res->fetchRow();
	}
	
	public function testFetchAllRows()
	{
		$res = $this->db->query("SELECT first_name, last_name, email_address FROM users WHERE user_id IN (1, 2) ORDER BY user_id");
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
		$res = $this->db->query("SELECT first_name, last_name, email_address FROM users WHERE user_id IN (25) ORDER BY user_id");
		$this->assertEquals(array(), $res->fetchAllRows());
	}
	
	public function testIteration()
	{
		$res = $this->db->query("SELECT first_name, last_name, email_address FROM users WHERE user_id IN (1, 2) ORDER BY user_id");
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
		$res = $this->db->query("SELECT first_name, last_name, email_address FROM users WHERE user_id IN (1, 2) ORDER BY user_id");
		
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
		$res = $this->db->query("SELECT first_name, last_name, email_address FROM users WHERE user_id IN (25) ORDER BY user_id");
	
		$i = 0;
		foreach ($res as $row) {
			$i++;	
		}
		
		$this->assertEquals(0, $i);
	}
	
	public function testTossIfEmpty()
	{
		$this->setExpectedException('fNoRowsException');
		$res = $this->db->query("SELECT first_name, last_name, email_address FROM users WHERE user_id IN (25) ORDER BY user_id");
		$res->tossIfNoRows();
	}
	
	public function testTossIfEmpty2()
	{
		$res = $this->db->query("SELECT first_name, last_name, email_address FROM users WHERE user_id IN (1) ORDER BY user_id");
		$res->tossIfNoRows();
	}
	
	public function testSeek()
	{
		$res = $this->db->query("SELECT first_name, last_name, email_address FROM users ORDER BY user_id");
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
		$res = $this->db->query("SELECT first_name, last_name, email_address FROM users ORDER BY user_id");
		$res->seek(4);
	}
	
	public function testConcurrentResults()
	{
		$res = $this->db->query("SELECT first_name, last_name, email_address FROM users WHERE user_id IN (1, 2) ORDER BY user_id");
		
		$res2 = $this->db->query("SELECT first_name, last_name, email_address FROM users WHERE user_id IN (3, 4) ORDER BY user_id");
		
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