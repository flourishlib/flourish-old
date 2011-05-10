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
class YearFavoriteAlbum extends fActiveRecord
{
	public function getTwoDigitYear()
	{
		return '<em>"' . substr($this->getYear(), -2) . '"</em>';
	}	
}
class Event extends fActiveRecord { }
class EventSlot extends fActiveRecord { }
class Registration extends fActiveRecord { }
class EventDetail extends fActiveRecord { }
class InvalidTable extends fActiveRecord { }
 
function changed($object, &$values, &$old_values, &$related_records, &$cache, $method, $parameters) {
	return fActiveRecord::changed($values, $old_values, $parameters[0]);	
}
 
class fActiveRecordTest extends PHPUnit_Framework_TestCase
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
		
		self::$db     = $db;
		self::$schema = new fSchema($db);
	}

	public static function tearDownAfterClass()
	{
		if (defined('SKIPPING')) {
			return;	
		}
		teardown(self::$db, DB_EXTENDED_TEARDOWN_FILE);
		teardown(self::$db, DB_TEARDOWN_FILE);
	}

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
		if (defined('SKIPPING')) {
			$this->markTestSkipped();
		}
		fORMDatabase::attach(self::$db);
		fORMSchema::attach(self::$schema);
		if (defined('MAP_TABLES')) {
			fORM::mapClassToTable('User', 'user');
			fORM::mapClassToTable('Group', 'group');
			fORM::mapClassToTable('Artist', 'popular_artists');
			fORM::mapClassToTable('Album', 'records');
		}
		fORM::registerActiveRecordMethod('User', 'hasChanged', 'changed');	
	}
	
	public function tearDown()
	{
		if (defined('SKIPPING')) {
			return;	
		}
		self::$db->query('DELETE FROM events_artists');
		self::$db->query('DELETE FROM event_details');
		self::$db->query('DELETE FROM registrations');
		self::$db->query('DELETE FROM events WHERE event_id > 9');
		self::$db->query('DELETE FROM %r WHERE user_id > 4', fORM::tablize('User'));
		__reset();	
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
		$user = new User(fORMDatabase::retrieve()->query("SELECT * FROM %r WHERE user_id = 1", fORM::tablize('User')));
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
			self::$db->query('SELECT user_id FROM %r WHERE user_id = %i', fORM::tablize('User'), $id)->countReturnedRows()
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
			self::$db->query('SELECT name FROM record_labels WHERE name = %s', 'Will’s Label')->countReturnedRows()
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
			self::$db->query('SELECT * FROM %r WHERE first_name = %s', fORM::tablize('User'), 'testInsert')->countReturnedRows()
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
			self::$db->query('SELECT * FROM %r WHERE first_name = %s', fORM::tablize('User'), 'testInsertSetNullNotNullColumnWithDefault')->countReturnedRows()
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
			self::$db->query('SELECT * FROM %r WHERE first_name = %s', fORM::tablize('User'), 'William')->countReturnedRows()
		);
		
		self::$db->query('UPDATE %r SET first_name = %s WHERE user_id = %i', fORM::tablize('User'), 'Will', 1);		
	}
	
	public function testUpdateWithNoChanges()
	{
		$user = new User(1);
		$user->store();
		
		$this->assertEquals(
			1,
			self::$db->query('SELECT * FROM %r WHERE user_id = %i', fORM::tablize('User'), 1)->countReturnedRows()
		);	
	}
	
	public function testChanged()
	{
		$user = $this->createUser();
		$user->setMiddleInitial('A');
		
		$this->assertEquals(
			TRUE,
			$user->hasChanged('middle_initial')
		);	
	}
	
	public function testNullChangedToZero()
	{
		$user = $this->createUser();
		$user->setMiddleInitial(0);
		
		$this->assertEquals(
			TRUE,
			$user->hasChanged('middle_initial')
		);	
	}
	
	public function testNullChangedToFalse()
	{
		$user = $this->createUser();
		$user->setMiddleInitial(FALSE);
		
		$this->assertEquals(
			TRUE,
			$user->hasChanged('middle_initial')
		);	
	}
	
	public function testNullChanged()
	{
		$user = $this->createUser();
		$user->setMiddleInitial('');
		
		$this->assertEquals(
			FALSE,
			$user->hasChanged('middle_initial')
		);	
	}
	
	public function testNullChangedBlankStringFromDatabase()
	{
		$user = new User(1);
		$user->setMiddleInitial('');
		
		$this->assertEquals(
			FALSE,
			$user->hasChanged('middle_initial')
		);	
	}
	
	public function testEncodeOnCustomGetMethod()
	{
		$user = new YearFavoriteAlbum(array('email' => 'will@flourishlib.com', 'year' => '2009', 'position' => 1));
			
		$this->assertEquals(
			'&lt;em&gt;&quot;09&quot;&lt;/em&gt;',
			$user->encodeTwoDigitYear()
		);	
	}
	
	public function testPrepareOnCustomGetMethod()
	{
		$user = new YearFavoriteAlbum(array('email' => 'will@flourishlib.com', 'year' => '2009', 'position' => 1));
			
		$this->assertEquals(
			'<em>&quot;09&quot;</em>',
			$user->prepareTwoDigitYear()
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
				fORM::mapClassToTable(\$this, '" .  fORM::tablize('User') . "');
			}	
		}");
		$user = new TestUser(1);
		$this->assertEquals(
			1,
			$user->getUserId()	
		);		
	}
	
	public function testDeleteRestrictOneToMany()
	{
		$this->setExpectedException('fValidationException');
		$event = new Event();
		$event->setTitle('Delete Restrict Event');
		$event->setStartDate(new fDate());
		$event->store();
		
		$registration = new Registration();
		$registration->setEventId($event->getEventId());
		$registration->setName('Will');
		$registration->store();
		
		$event->delete();		
	}
	
	public function testDeleteForceCascadeOneToMany()
	{
		$event = new Event();
		$event->setTitle('Delete Restrict Event');
		$event->setStartDate(new fDate());
		$event->store();
		
		$registration = new Registration();
		$registration->setEventId($event->getEventId());
		$registration->setName('Will');
		$registration->store();
		
		$event->delete(TRUE);
		
		$this->assertEquals(
			FALSE,
			$event->exists()	
		);		
	}
	
	public function testDeleteRestrictOneToOne()
	{
		$this->setExpectedException('fValidationException');
		$event = new Event();
		$event->setTitle('Delete Restrict Event');
		$event->setStartDate(new fDate());
		$event->store();
		
		$event_detail = new EventDetail();
		$event_detail->setEventId($event->getEventId());
		$event_detail->setAllowsRegistration(TRUE);
		$event_detail->store();
		
		$event->delete();		
	}
	
	public function testDeleteForceCascadeOneToOne()
	{
		$event = new Event();
		$event->setTitle('Delete Restrict Event');
		$event->setStartDate(new fDate());
		$event->store();
		
		$event_detail = new EventDetail();
		$event_detail->setEventId($event->getEventId());
		$event_detail->setAllowsRegistration(TRUE);
		$event_detail->store();
		
		$event->delete(TRUE);
		
		$this->assertEquals(
			FALSE,
			$event->exists()	
		);		
	}
	
	public function testDeleteRestrictManyToMany()
	{
		$this->setExpectedException('fValidationException');
		$event = new Event();
		$event->setTitle('Delete Restrict Event');
		$event->setStartDate(new fDate());
		$event->associateArtists(array(1));
		$event->store();
		
		$event->delete();		
	}
	
	public function testDeleteForceCascadeManyToMany()
	{
		$event = new Event();
		$event->setTitle('Delete Restrict Event');
		$event->setStartDate(new fDate());
		$event->associateArtists(array(1));
		$event->store();
		
		$event->delete(TRUE);
		
		$this->assertEquals(
			FALSE,
			$event->exists()	
		);		
	}
}