<?php
require_once('./support/init.php');
 
class User extends fActiveRecord { }
class Group extends fActiveRecord { }
class Artist extends fActiveRecord { }
class Album extends fActiveRecord { }
class Song extends fActiveRecord { }
class UserDetail extends fActiveRecord { }
class RecordDeal extends fActiveRecord { }
class RecordLabel extends fActiveRecord { } 
class FavoriteAlbum extends fActiveRecord { }
class InvalidTable extends fActiveRecord { }

class Flourish2User extends fActiveRecord { }
class Flourish2Group extends fActiveRecord { }
class Flourish2Artist extends fActiveRecord { }
class Flourish2Album extends fActiveRecord { }
 
class fActiveRecordWithMultipleSchemasTest extends PHPUnit_Framework_TestSuite
{
	public static function suite()
	{
		return new fActiveRecordWithMultipleSchemasTest('fActiveRecordWithMultipleSchemasTestChild');
	}
 
	protected function setUp()
	{
		$db = new fDatabase(DB_TYPE, DB, DB_USERNAME, DB_PASSWORD, DB_HOST, DB_PORT); 
		$db->query(file_get_contents(DB_SETUP_FILE));
		$db->query(file_get_contents(DB_EXTENDED_SETUP_FILE));
		$db->query(file_get_contents(DB_ALTERNATE_SCHEMA_SETUP_FILE));
		$db->clearCache();
		$this->sharedFixture = $db;
	}
 
	protected function tearDown()
	{
		$db = $this->sharedFixture;
		$db->query(file_get_contents(DB_ALTERNATE_SCHEMA_TEARDOWN_FILE));		
		$db->query(file_get_contents(DB_EXTENDED_TEARDOWN_FILE));		
		$db->query(file_get_contents(DB_TEARDOWN_FILE));
	}
}
 
class fActiveRecordWithMultipleSchemasTestChild extends PHPUnit_Framework_TestCase
{
	protected function createUser()
	{
		$user = new Flourish2User();
		$user->setFirstName('John');
		$user->setLastName('Smith Jr.');
		return $user;	
	}
	
	public function setUp()
	{	
		fORMDatabase::attach($this->sharedFixture);
		fORM::mapClassToTable('Flourish2User', 'flourish2.users');
		fORM::mapClassToTable('Flourish2Group', 'flourish2.groups');
		fORM::mapClassToTable('Flourish2Artist', 'flourish2.artists');
		fORM::mapClassToTable('Flourish2Album', 'flourish2.albums');
	}
	
	public function testSimpleConstruct()
	{
		$user = new Flourish2User(1);
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
			$this->sharedFixture->query('SELECT user_id FROM flourish2.users WHERE user_id = %i', $id)->countReturnedRows()
		);		
	}
	
	public function testLoad()
	{
		$user = new Flourish2User(1);
		$user->setFirstName('');
		$user->load();
		
		$this->assertEquals(
			'James',
			$user->getFirstName()	
		);		
	}
	
	public function testInsert()
	{
		$user = new Flourish2User();
		$user->setFirstName('testInsert');
		$user->setLastName('Flourish2User');
		$user->store();
		
		$this->assertEquals(
			1,
			$this->sharedFixture->query('SELECT * FROM flourish2.users WHERE first_name = %s', 'testInsert')->countReturnedRows()
		);
		
		$user->delete();		
	}
	
	public function testUpdate()
	{
		$user = new Flourish2User(1);
		$user->setFirstName('Jim');
		$user->store();
		
		$this->assertEquals(
			1,
			$this->sharedFixture->query('SELECT * FROM flourish2.users WHERE first_name = %s', 'Jim')->countReturnedRows()
		);
		
		$this->sharedFixture->query('UPDATE flourish2.users SET first_name = %s WHERE user_id = %i', 'James', 1);		
	}
	
	public function tearDown()
	{
		$this->sharedFixture->query('DELETE FROM users WHERE user_id > 4');
		__reset();	
	}
}