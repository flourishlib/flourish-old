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
 
class fActiveRecordTest extends PHPUnit_Framework_TestSuite
{
	public static function suite()
	{
		return new fActiveRecordTest('fActiveRecordTestChild');
	}
 
	protected function setUp()
	{
		$db = new fDatabase(DB_TYPE, DB, DB_USERNAME, DB_PASSWORD, DB_HOST, DB_PORT); 
		$db->query(file_get_contents(DB_SETUP_FILE));
		$db->query(file_get_contents(DB_EXTENDED_SETUP_FILE));
		$this->sharedFixture = $db;
	}
 
	protected function tearDown()
	{
		$db = $this->sharedFixture;
		$db->query(file_get_contents(DB_EXTENDED_TEARDOWN_FILE));		
		$db->query(file_get_contents(DB_TEARDOWN_FILE));
	}
}
 
class fActiveRecordTestChild extends PHPUnit_Framework_TestCase
{
	protected function createUser()
	{
		$user = new User();
		$user->setFirstName('John');
		$user->setLastName('Smith Jr.');
		$user->setEmailAddress('johnjr@smith.com');
		$user->setDateCreated(new fTimestamp());
		$user->setHashedPassword('8njsbck');
		return $user;	
	}
	
	public function setUp()
	{	
		fORMDatabase::attach($this->sharedFixture);	
	}
	
	public function testMissingPrimaryKey()
	{
		$this->setExpectedException('fProgrammerException');
		
		$invalid_table = new InvalidTable();
	}
	
	public function testSimpleConstruct()
	{
		$user = new User(1);
		$this->assertEquals(
			1,
			$user->getUserId()	
		);			
	}
	
	public function testSimpleTextConstruct()
	{
		$label = new RecordLabel('EMI');
		$this->assertEquals(
			'EMI',
			$label->getName()	
		);			
	}
	
	public function testArrayConstruct()
	{
		$user = new User(array('user_id' => 1));
		$this->assertEquals(
			1,
			$user->getUserId()	
		);			
	}
	
	public function testMultiColumnConstruct()
	{
		$album = new FavoriteAlbum(array('email' => 'will@flourishlib.com', 'album_id' => 1));
		$this->assertEquals(
			'will@flourishlib.com',
			$album->getEmail()	
		);
		$this->assertEquals(
			2,
			$album->getPosition()	
		);			
	}
	
	public function testSwappedMultiColumnConstruct()
	{
		$album = new FavoriteAlbum(array('album_id' => 1, 'email' => 'will@flourishlib.com'));
		$this->assertEquals(
			'will@flourishlib.com',
			$album->getEmail()	
		);
		$this->assertEquals(
			2,
			$album->getPosition()	
		);			
	}
	
	public function testUniqueKeyConstruct()
	{
		$user = new User(array('email_address' => 'will@flourishlib.com'));
		$this->assertEquals(
			1,
			$user->getUserId()	
		);			
	}
	
	public function testIteratorConstruct()
	{
		$user = new User(fORMDatabase::retrieve()->query("SELECT * FROM users WHERE user_id = 1"));
		$this->assertEquals(
			1,
			$user->getUserId()	
		);			
	}
	
	public function testMultiColumnUniqueConstruct()
	{
		$album = new Album(array('artist_id' => 1, 'name' => 'Give Up'));
		$this->assertEquals(
			1,
			$album->getAlbumId()	
		);			
	}
	
	public function testSwappedMultiColumnUniqueConstruct()
	{
		$album = new Album(array('name' => 'Give Up', 'artist_id' => 1));
		$this->assertEquals(
			1,
			$album->getAlbumId()	
		);			
	}
	
	public function testCloneAutoIncrement()
	{
		$user = new User(1);
		$new_user = clone $user;
		$this->assertEquals(
			NULL,
			$new_user->getUserId()	
		);
		$this->assertEquals(
			'Will',
			$new_user->getFirstName()	
		);			
	}
	
	public function testCloneNonAutoIncrement()
	{
		$label = new RecordLabel('EMI');
		$new_label = clone $label;
		$this->assertEquals(
			'EMI',
			$new_label->getName()	
		);
		$this->assertEquals(
			'EMI',
			$label->getName()	
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
			$this->sharedFixture->query('SELECT user_id FROM users WHERE user_id = %i', $id)->countReturnedRows()
		);		
	}
	
	public function testDelete2()
	{
		$label = new RecordLabel();
		$label->setName('Will’s Label');
		$label->store();
		
		$label->delete();
		
		$this->assertEquals(
			0,
			$this->sharedFixture->query('SELECT name FROM record_labels WHERE name = %s', 'Will’s Label')->countReturnedRows()
		);		
	}
	
	public function testExists()
	{
		$user = new User(1);
		$this->assertEquals(
			TRUE,
			$user->exists()	
		);		
	}
	
	public function testExistsAfterStoreAndDelete()
	{
		$user = $this->createUser();
		$user->store();
		
		$this->assertEquals(
			TRUE,
			$user->exists()	
		);
		
		$user->delete();
		
		$this->assertEquals(
			FALSE,
			$user->exists()	
		);	
	}
	
	public function testNotExists()
	{
		$user = new User();
		$this->assertEquals(
			FALSE,
			$user->exists()	
		);		
	}
	
	public function testLoad()
	{
		$user = new User(1);
		$user->setFirstName('');
		$user->load();
		
		$this->assertEquals(
			'Will',
			$user->getFirstName()	
		);		
	}
	
	public function testSetChain()
	{
		$user = new User();
		$this->assertEquals(
			TRUE,
			$user->setUserId(2) instanceof User	
		);		
	}
	
	public function testInsert()
	{
		$user = new User();
		$user->setFirstName('testInsert');
		$user->setLastName('User');
		$user->setEmailAddress('testinsert@example.com');
		$user->setDateCreated(new fTimestamp());
		$user->setHashedPassword('abcdefgh');
		$user->store();
		
		$this->assertEquals(
			1,
			$this->sharedFixture->query('SELECT * FROM users WHERE first_name = %s', 'testInsert')->countReturnedRows()
		);
		
		$user->delete();		
	}
	
	public function testInsertSetNullNotNullColumnWithDefault()
	{
		$user = new User();
		$user->setFirstName('testInsertSetNullNotNullColumnWithDefault');
		$user->setMiddleInitial(NULL);
		$user->setLastName('User');
		$user->setEmailAddress('testinsert@example.com');
		$user->setDateCreated(new fTimestamp());
		$user->setHashedPassword('abcdefgh');
		$user->store();
		
		$this->assertEquals(
			1,
			$this->sharedFixture->query('SELECT * FROM users WHERE first_name = %s', 'testInsertSetNullNotNullColumnWithDefault')->countReturnedRows()
		);
		
		$user->delete();		
	}
	
	public function testUpdate()
	{
		$user = new User(1);
		$user->setFirstName('William');
		$user->store();
		
		$this->assertEquals(
			1,
			$this->sharedFixture->query('SELECT * FROM users WHERE first_name = %s', 'William')->countReturnedRows()
		);
		
		$this->sharedFixture->query('UPDATE users SET first_name = %s WHERE user_id = %i', 'Will', 1);		
	}
	
	public function testUpdateWithNoChanges()
	{
		$user = new User(1);
		$user->store();
		
		$this->assertEquals(
			1,
			$this->sharedFixture->query('SELECT * FROM users WHERE user_id = %i', 1)->countReturnedRows()
		);	
	}
	
	public function testBadMapping()
	{
		$this->setExpectedException('fProgrammerException');
		
		eval("class BadUser extends fActiveRecord {	}");
		$user = new BadUser(1);		
	}
	
	public function testCustomMapping()
	{
		eval("class TestUser extends fActiveRecord {
			protected function configure() {
				fORM::mapClassToTable(\$this, 'users');
			}	
		}");
		$user = new TestUser(1);
		$this->assertEquals(
			1,
			$user->getUserId()	
		);		
	}

	public function tearDown()
	{
		$this->sharedFixture->query('DELETE FROM users WHERE user_id > 4');
		__reset();	
	}
}