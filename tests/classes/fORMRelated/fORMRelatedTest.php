<?php
require_once('./support/init.php');
 
class User extends fActiveRecord { }
class Group extends fActiveRecord { }
class Artist extends fActiveRecord { }
class Album extends fActiveRecord { }
class Song extends fActiveRecord { }
class UserDetail extends fActiveRecord { }
class OtherUserDetail extends fActiveRecord { }
class RecordLabel extends fActiveRecord { } 
class FavoriteAlbum extends fActiveRecord { }
class InvalidTable extends fActiveRecord { }
class Event extends fActiveRecord { }
class EventSlot extends fActiveRecord { }
class EventDetail extends fActiveRecord { }
class Certification extends fActiveRecord { }
class CertificationLevel extends fActiveRecord { }
class Category extends fActiveRecord { }
class Model_Person extends fActiveRecord { }
 
class fORMRelatedTest extends PHPUnit_Framework_TestCase
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
		fORM::mapClassToTable('Model_Person', 'people');
		if (defined('MAP_TABLES')) {
			fORM::mapClassToTable('User', 'user');
			fORM::mapClassToTable('Group', 'group');
			fORM::mapClassToTable('Artist', 'popular_artists');
			fORM::mapClassToTable('Album', 'records');
		}
	}
	
	public function tearDown()
	{
		if (defined('SKIPPING')) {
			return;
		}
		self::$db->query('DELETE FROM %r WHERE user_id > 4', fORM::tablize('User'));
		__reset();	
	}
	
	public function testBuildOneToManyRecursive()
	{
		$category = new Category(1);
		$this->assertEquals(3, $category->buildCategories()->count());
		$category_2 = new Category(2);
		$this->assertEquals(0, $category_2->buildCategories()->count());
	}
	
	static public function countOneToManyProvider()
	{
		$output = array();
		$output[] = array(1, 5);
		$output[] = array(2, 1);
		$output[] = array(3, 0);
		$output[] = array(4, 0);
		
		return $output;	
	}
	
	/**
	 * @dataProvider countOneToManyProvider
	 */
	public function testCountOneToMany($user_id, $count)
	{
		$user = new User($user_id);
		$this->assertEquals($count, $user->countFavoriteAlbums());
	}
	
	public function testUnderscoreCountOneToMany()
	{
		$category = new Category(1);
		$this->assertEquals(3, $category->countModel_People());
	}
	
	static public function countManyToManyProvider()
	{
		$output = array();
		$output[] = array(1, 2);
		$output[] = array(2, 2);
		$output[] = array(3, 1);
		$output[] = array(4, 1);
		
		return $output;	
	}
	
	/**
	 * @dataProvider countManyToManyProvider
	 */
	public function testCountManyToMany($user_id, $count)
	{
		$user = new User($user_id);
		$this->assertEquals($count, $user->countGroups('users_groups'));
	}
	
	
	public function testCountManyToManyMultipleRoutesNotSpecified()
	{
		$this->setExpectedException('fProgrammerException');
		$user = new User(1);
		$user->countGroups();
	}
	
	public function testCountOneToManyRecursive()
	{
		$category = new Category(1);
		$this->assertEquals(3, $category->countCategories());
		$category_2 = new Category(2);
		$this->assertEquals(0, $category_2->countCategories());
	}
	
	
	static public function hasOneToOneProvider()
	{
		$output = array();
		$output[] = array(1, TRUE);
		$output[] = array(2, TRUE);
		$output[] = array(3, TRUE);
		$output[] = array(4, TRUE);
		
		return $output;	
	}
	
	/**
	 * @dataProvider hasOneToOneProvider
	 */
	public function testHasOneToOne($user_id, $output)
	{
		$user = new User($user_id);
		$this->assertEquals($output, $user->hasUserDetail());
	}
	
	
	public function testHasOneToOnePlural()
	{
		$this->setExpectedException('fProgrammerException');
		$user = new User(1);
		$user->hasUserDetails();
	}
	
	
	static public function hasOneToManyProvider()
	{
		$output = array();
		$output[] = array(1, TRUE);
		$output[] = array(2, TRUE);
		$output[] = array(3, FALSE);
		$output[] = array(4, FALSE);
		
		return $output;	
	}
	
	/**
	 * @dataProvider hasOneToManyProvider
	 */
	public function testHasOneToMany($user_id, $output)
	{
		$user = new User($user_id);
		$this->assertEquals($output, $user->hasFavoriteAlbums());
	}
	
	public function testUnderscoreHasOneToMany()
	{
		$category = new Category(1);
		$this->assertEquals(TRUE, $category->hasModel_People());
	}
	
	public function testHasOneToManySingular()
	{
		$this->setExpectedException('fProgrammerException');
		$user = new User(1);
		$user->hasFavoriteAlbum();
	}
	
	static public function hasManyToManyProvider()
	{
		$output = array();
		$output[] = array(1, TRUE);
		$output[] = array(2, TRUE);
		$output[] = array(3, TRUE);
		$output[] = array(4, TRUE);
		
		return $output;	
	}
	
	/**
	 * @dataProvider hasManyToManyProvider
	 */
	public function testHasManyToMany($user_id, $output)
	{
		$user = new User($user_id);
		$this->assertEquals($output, $user->hasGroups('users_groups'));
	}
	
	
	public function testHasManyToManyMultipleRoutesNotSpecified()
	{
		$this->setExpectedException('fProgrammerException');
		$user = new User(1);
		$user->hasGroups();
	}
	
	public function testHasManyToManySingular()
	{
		$this->setExpectedException('fProgrammerException');
		$user = new User(1);
		$user->hasGroup();
	}
	
	public function testHasOneToManyRecursive()
	{
		$category = new Category(1);
		$this->assertEquals(TRUE, $category->hasCategories());
		$category_2 = new Category(2);
		$this->assertEquals(FALSE, $category_2->hasCategories());
	}
	
	
	static public function listOneToManyProvider()
	{
		$output = array();
		$output[] = array(1, array(
			array('email' => 'will@flourishlib.com', 'album_id' => 1),
			array('email' => 'will@flourishlib.com', 'album_id' => 2),
			array('email' => 'will@flourishlib.com', 'album_id' => 3),
			array('email' => 'will@flourishlib.com', 'album_id' => 4),
			array('email' => 'will@flourishlib.com', 'album_id' => 7)
		));
		$output[] = array(2, array(
			array('email' => 'john@smith.com', 'album_id' => 2)
		));
		$output[] = array(3, array());
		$output[] = array(4, array());
		
		return $output;	
	}
	
	/**
	 * @dataProvider listOneToManyProvider
	 */
	public function testListOneToMany($user_id, $list)
	{
		fORMRelated::setOrderBys('User', 'FavoriteAlbum', array('album_id' => 'asc'));
		$user = new User($user_id);
		$this->assertEquals($list, $user->listFavoriteAlbums());
	}
	
	public function testUnderscoreListOneToMany()
	{
		$category = new Category(1);
		$this->assertEquals(array(1, 2, 3), $category->listModel_People());
	}
	
	static public function listManyToManyProvider()
	{
		$output = array();
		$output[] = array(1, array(1, 2));
		$output[] = array(2, array(1, 2));
		$output[] = array(3, array(1));
		$output[] = array(4, array(1));
		
		return $output;	
	}
	
	/**
	 * @dataProvider listManyToManyProvider
	 */
	public function testListManyToMany($user_id, $list)
	{
		fORMRelated::setOrderBys('User', 'Group', array('group_id' => 'asc'), 'users_groups');
		$user = new User($user_id);
		$this->assertEquals($list, $user->listGroups('users_groups'));
	}
	
	
	public function testListManyToManyMultipleRoutesNotSpecified()
	{
		$this->setExpectedException('fProgrammerException');
		$user = new User(1);
		$user->listGroups();
	}
	
	public function testListOneToManyRecursive()
	{
		$category = new Category(1);
		$this->assertEquals(array(3, 4, 5), $category->listCategories());
		$category_2 = new Category(2);
		$this->assertEquals(array(), $category_2->listCategories());
	}
	
	
	public function testPopulateOneToOne()
	{
		$user = $this->createUser();
		$user->populateOtherUserDetail();
		$user->store();
	}
	
	public function testPopulateReverseOneToOne()
	{
		$event = new Event();
		$event->setTitle('Test Event');
		$event->setStartDate('today');
		$event->populateEventSlot();
		$event->populateEventDetail();
		$event->store();
	}
	
	
	public function testAssociateMultiColumnPrimaryKey()
	{
		$album = new Album(1);
		
		$certification_level = new CertificationLevel();
		$certification_level->setName('Gold');
		
		$certification = new Certification();
		$certification->setAlbumId($album->getAlbumId());
		$certification->setYear(2010);
		
		$certification_level->associateCertifications($certification);
		$certification_level->store();
		
		$this->assertEquals(
			array(
				array('name' => 'Gold')
			),
			self::$db->query('SELECT * FROM certification_levels')->fetchAllRows()
		);
		$this->assertEquals(
			array(
				array(
					'level' => 'Gold',
					'album_id' => 1,
					'year' => '2010'
				)
			),
			self::$db->query('SELECT %r, album_id, year FROM certifications', 'level')->fetchAllRows()
		);
	}


	public function testAssociateOneToManyPrimaryKey()
	{
		$category = new Category(3);
		
		$person = new Model_Person();
		$person->setName('Bob');
		$person->store();

		$person_2 = new Model_Person();
		$person_2->setName("Barbara");
		$person_2->store();

		$person_ids = array($person->getPersonId(), $person_2->getPersonId());

		$category->associateModel_People($person_ids);
		$category->store();
		
		$db_person_ids = array_map(
			'current',
			self::$db->query('SELECT person_id FROM people WHERE category_id = 3')->fetchAllRows()
		);

		self::$db->query("DELETE FROM people WHERE person_id > 4");

		$this->assertEquals(
			$person_ids,
			$db_person_ids
		);
	}
	
	
	public function testInsertUpdateStoreManyToMany()
	{
		$user = $this->createUser();
		$user->associateGroups(array(1), 'users_groups');
		$user->store();
		
		$this->assertEquals(
			array(
				array('group_id' => 1)
			),
			self::$db->query('SELECT group_id FROM users_groups WHERE user_id = %i ORDER BY group_id ASC', $user->getUserId())->fetchAllRows()
		);
		
		$user->associateGroups(array(1, 2), 'users_groups');
		$user->store();
		
		$this->assertEquals(
			array(
				array('group_id' => 1),
				array('group_id' => 2)
			),
			self::$db->query('SELECT group_id FROM users_groups WHERE user_id = %i ORDER BY group_id ASC', $user->getUserId())->fetchAllRows()
		);
		
		$user->associateGroups(array(), 'users_groups');
		$user->store();
		
		$this->assertEquals(
			array(),
			self::$db->query('SELECT group_id FROM users_groups WHERE user_id = %i ORDER BY group_id ASC', $user->getUserId())->fetchAllRows()
		);	
	}
}