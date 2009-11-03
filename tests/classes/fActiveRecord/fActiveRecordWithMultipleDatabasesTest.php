<?php
require_once('./support/init.php');
 
class User extends fActiveRecord { }
class Group extends fActiveRecord { }
class Artist extends fActiveRecord { }
class Album extends fActiveRecord { }
class Song extends fActiveRecord { }

class Db2User extends fActiveRecord { }
class Db2Group extends fActiveRecord { }
 
class fActiveRecordWithMultipleDatabasesTest extends PHPUnit_Framework_TestSuite
{
	public static function suite()
	{
		return new fActiveRecordWithMultipleDatabasesTest('fActiveRecordWithMultipleDatabasesTestChild');
	}
 
	protected function setUp()
	{
		$db = new fDatabase(DB_TYPE, DB, DB_USERNAME, DB_PASSWORD, DB_HOST, DB_PORT); 
		$db->query(file_get_contents(DB_SETUP_FILE));
		$db->clearCache();
		
		$db2 = new fDatabase(DB_TYPE, DB_2, DB_2_USERNAME, DB_2_PASSWORD, DB_2_HOST, DB_2_PORT); 
		$db2->query(file_get_contents(DB_2_SETUP_FILE));
		$db2->clearCache();
		
		$this->sharedFixture = array($db, $db2);
	}
 
	protected function tearDown()
	{
		$db = $this->sharedFixture[0];
		$db->query(file_get_contents(DB_TEARDOWN_FILE));
		$db2 = $this->sharedFixture[1];
		$db2->query(file_get_contents(DB_2_TEARDOWN_FILE));
	}
}
 
class fActiveRecordWithMultipleDatabasesTestChild extends PHPUnit_Framework_TestCase
{
	protected function createUser()
	{
		$user = new Db2User();
		$user->setFirstName('John');
		$user->setLastName('Smith Jr.');
		$user->setEmailAddress('john@smith.com');
		return $user;	
	}
	
	public function setUp()
	{	
		fORMDatabase::attach($this->sharedFixture[0]);
		fORMDatabase::attach($this->sharedFixture[1], 'db2');
		fORM::mapClassToTable('Db2User', 'users');
		fORM::mapClassToDatabase('Db2User', 'db2');
		fORM::mapClassToTable('Db2Group', 'groups');
		fORM::mapClassToDatabase('Db2Group', 'db2');
	}
	
	public function testSimpleConstruct()
	{
		$user = new Db2User(1);
		$this->assertEquals(
			1,
			$user->getUserId()	
		);			
	}
	
	public function testDelete()
	{
		$user = $this->createUser();
		$user->store();
		$id = $user->getUserId();
		
		$user->delete();
		
		$this->assertEquals(
			0,
			$this->sharedFixture[1]->query('SELECT user_id FROM users WHERE user_id = %i', $id)->countReturnedRows()
		);		
	}
	
	public function testLoad()
	{
		$user = new Db2User(1);
		$user->setFirstName('');
		$user->load();
		
		$this->assertEquals(
			'Frank',
			$user->getFirstName()	
		);		
	}
	
	public function testInsert()
	{
		$user = new Db2User();
		$user->setFirstName('testInsert');
		$user->setLastName('Db2User');
		$user->setEmailAddress('testInsert@example.com');
		$user->store();
		
		$this->assertEquals(
			1,
			$this->sharedFixture[1]->query('SELECT * FROM users WHERE first_name = %s', 'testInsert')->countReturnedRows()
		);
		
		$user->delete();		
	}
	
	public function testUpdate()
	{
		$user = new Db2User(1);
		$user->setFirstName('Jim');
		$user->store();
		
		$this->assertEquals(
			1,
			$this->sharedFixture[1]->query('SELECT * FROM users WHERE first_name = %s', 'Jim')->countReturnedRows()
		);
		
		$this->sharedFixture[1]->query('UPDATE users SET first_name = %s WHERE user_id = %i', 'James', 1);		
	}
	
	public function tearDown()
	{
		$this->sharedFixture[1]->query('DELETE FROM users WHERE user_id > 2');
		__reset();	
	}
}