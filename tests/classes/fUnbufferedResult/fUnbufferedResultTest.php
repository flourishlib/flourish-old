<?php
require_once('./support/init.php');
 
class fUnbufferedResultTest extends PHPUnit_Framework_TestSuite
{
	public static function suite()
	{
		$suite = new fUnbufferedResultTest();
		$suite->addTestSuite('fUnbufferedResultTestNoModifications');
		$suite->addTestSuite('fUnbufferedResultTestModifications');
		return $suite;
	}
}

class fUnbufferedResultTestModifications extends PHPUnit_Framework_TestSuite
{
	public static function suite()
	{
		return new fUnbufferedResultTestModifications('fUnbufferedResultTestModificationsChild');
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
	
	public function testTransactionRollback()
	{
		$this->db->unbufferedQuery("BEGIN");
		
		$this->db->unbufferedQuery("DELETE FROM users WHERE user_id = 4");
		
		$res = $this->db->unbufferedQuery("SELECT * FROM users");
		$i = 0;
		while ($res->valid()) {
			$res->fetchRow();
			$i++;	
		}
		$this->assertEquals(3, $i);
		
		$this->db->unbufferedQuery("ROLLBACK");
		
		$res = $this->db->unbufferedQuery("SELECT * FROM users");
		$i = 0;
		while ($res->valid()) {
			$res->fetchRow();
			$i++;	
		}
		$this->assertEquals(4, $i);
	}
	
	public function testTransactionCommit()
	{
		$this->db->unbufferedQuery("BEGIN");
		
		$this->db->unbufferedQuery("DELETE FROM users WHERE user_id = 4");
		
		$res = $this->db->unbufferedQuery("SELECT * FROM users");
		$i = 0;
		while ($res->valid()) {
			$res->fetchRow();
			$i++;	
		}
		$this->assertEquals(3, $i);
		
		$this->db->unbufferedQuery("COMMIT");
		
		$res = $this->db->unbufferedQuery("SELECT * FROM users");
		$i = 0;
		while ($res->valid()) {
			$res->fetchRow();
			$i++;	
		}
		$this->assertEquals(3, $i);
	}
	
	public function tearDown()
	{
		$this->db->query(file_get_contents(DB_TEARDOWN_FILE));
	}	
}

class fUnbufferedResultTestNoModifications extends PHPUnit_Framework_TestSuite
{
	public static function suite()
	{
		return new fUnbufferedResultTestNoModifications('fUnbufferedResultTestNoModificationsChild');
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
 
class fUnbufferedResultTestNoModificationsChild extends PHPUnit_Framework_TestCase
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
		$res = $this->db->unbufferedQuery("SELECT user_id FROM users");
		$this->assertEquals('SELECT user_id FROM users', $res->getSQL());
	}
	
	public function testGetUntranslatedSql()
	{
		$res = $this->db->unbufferedQuery("SELECT user_id FROM users");
		$this->assertEquals(NULL, $res->getUntranslatedSQL());
	}
	
	public function testFetchRow()
	{
		$res = $this->db->unbufferedQuery("SELECT first_name, last_name, email_address FROM users WHERE user_id = 1");
		$this->assertEquals(
			array(
				'first_name'    => 'Will',
				'last_name'     => 'Bond',
				'email_address' => 'will@flourishlib.com'
			),
			$res->fetchRow()
		);
		$res->__destruct();
	}
	
	public function testFetchRowException()
	{
		$this->setExpectedException('fNoRowsException');
		
		$res = $this->db->unbufferedQuery("SELECT first_name, last_name, email_address FROM users WHERE user_id = 25");
		$row = $res->fetchRow();
	}
	
	public function testIteration()
	{
		$res = $this->db->unbufferedQuery("SELECT first_name, last_name, email_address FROM users WHERE user_id IN (1, 2) ORDER BY user_id");
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
		$this->setExpectedException('fProgrammerException');
		
		$res = $this->db->unbufferedQuery("SELECT first_name, last_name, email_address FROM users WHERE user_id IN (1, 2) ORDER BY user_id");
		
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
			$i++;
		}
	}
	
	public function testEmptyIteration()
	{
		$res = $this->db->unbufferedQuery("SELECT first_name, last_name, email_address FROM users WHERE user_id IN (25) ORDER BY user_id");
	
		$i = 0;
		foreach ($res as $row) {
			$i++;	
		}
		
		$this->assertEquals(0, $i);
	}
	
	public function testTossIfEmpty()
	{
		$this->setExpectedException('fNoRowsException');
		$res = $this->db->unbufferedQuery("SELECT first_name, last_name, email_address FROM users WHERE user_id IN (25) ORDER BY user_id");
		$res->tossIfNoRows();
	}
	
	public function testTossIfEmpty2()
	{
		$res = $this->db->unbufferedQuery("SELECT first_name, last_name, email_address FROM users WHERE user_id IN (1) ORDER BY user_id");
		$res->tossIfNoRows();
	}
	
	public function testConcurrentResults()
	{
		$this->setExpectedException('fProgrammerException');
		
		$res = $this->db->unbufferedQuery("SELECT first_name, last_name, email_address FROM users WHERE user_id IN (1, 2) ORDER BY user_id");
		
		$res2 = $this->db->unbufferedQuery("SELECT first_name, last_name, email_address FROM users WHERE user_id IN (3, 4) ORDER BY user_id");
		
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