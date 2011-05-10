<?php
require_once('./support/init.php');
 
class User extends fActiveRecord { }
class Group extends fActiveRecord { }
class Artist extends fActiveRecord { }
class Album extends fActiveRecord { }
class Song extends fActiveRecord { }
class UserDetail extends fActiveRecord { }
class OtherUserDetail extends fActiveRecord { }
class RecordDeal extends fActiveRecord { }
class RecordLabel extends fActiveRecord { } 
class FavoriteAlbum extends fActiveRecord { }
class InvalidTable extends fActiveRecord { }

class Flourish2User extends fActiveRecord { }
class Flourish2Group extends fActiveRecord { }
class Flourish2Artist extends fActiveRecord { }
class Flourish2Album extends fActiveRecord { }

function fix_schema($input)
{
	if (DB_TYPE != 'oracle' && DB_TYPE != 'db2') {
		return $input;	
	}
	$input = str_replace('flourish2.', DB_SECOND_SCHEMA . '.', $input);
	return str_replace('flourish_role', DB_NAME . '_role', $input);
}

class fActiveRecordWithMultipleSchemasTest extends PHPUnit_Framework_TestCase
{
	protected static $db;
	protected static $schema;

	public static function setUpBeforeClass()
	{
		if (defined('SKIPPING')) {
			return;
		}
		$db = new fDatabase(DB_TYPE, DB, DB_USERNAME, DB_PASSWORD, DB_HOST, DB_PORT); 
		$db->execute(file_get_contents(DB_SETUP_FILE));
		$db->execute(file_get_contents(DB_EXTENDED_SETUP_FILE));
		$db->execute(fix_schema(file_get_contents(DB_ALTERNATE_SCHEMA_SETUP_FILE)));
		
		self::$db = $db;
		self::$schema = new fSchema($db);
	}

	public static function tearDownAfterClass()
	{
		if (defined('SKIPPING')) {
			return;
		}
		self::$db->execute(fix_schema(file_get_contents(DB_ALTERNATE_SCHEMA_TEARDOWN_FILE)));
		teardown(self::$db, DB_EXTENDED_TEARDOWN_FILE);
		teardown(self::$db, DB_TEARDOWN_FILE);
	}

	protected function createUser()
	{
		$user = new Flourish2User();
		$user->setFirstName('John');
		$user->setLastName('Smith Jr.');
		return $user;	
	}
	
	public function setUp()
	{	
		if (defined('SKIPPING')) {
			$this->markTestSkipped();
		}
		fORMDatabase::attach(self::$db);
		fORMSchema::attach(self::$schema);
		fORM::mapClassToTable('Flourish2User', fix_schema('flourish2.users'));
		fORM::mapClassToTable('Flourish2Group', fix_schema('flourish2.groups'));
		fORM::mapClassToTable('Flourish2Artist', fix_schema('flourish2.artists'));
		fORM::mapClassToTable('Flourish2Album', fix_schema('flourish2.albums'));
	}
	
	public function tearDown()
	{
		if (defined('SKIPPING')) {
			return;
		}
		self::$db->query('DELETE FROM users WHERE user_id > 4');
		__reset();	
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
			self::$db->query(fix_schema('SELECT user_id FROM flourish2.users WHERE user_id = %i'), $id)->countReturnedRows()
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
			self::$db->query(fix_schema('SELECT * FROM flourish2.users WHERE first_name = %s'), 'testInsert')->countReturnedRows()
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
			self::$db->query(fix_schema('SELECT * FROM flourish2.users WHERE first_name = %s'), 'Jim')->countReturnedRows()
		);
		
		self::$db->query(fix_schema('UPDATE flourish2.users SET first_name = %s WHERE user_id = %i'), 'James', 1);		
	}
}