<?php
require_once('./support/init.php');
 
class User extends fActiveRecord { }
class Group extends fActiveRecord { }
class Artist extends fActiveRecord { }
class Album extends fActiveRecord { }
class Song extends fActiveRecord { }
class UserDetail extends fActiveRecord { }
class RecordLabel extends fActiveRecord { } 
class FavoriteAlbum extends fActiveRecord { }
class InvalidTable extends fActiveRecord { }
 
class fORMRelatedTest extends PHPUnit_Framework_TestSuite
{
	public static function suite()
	{
		return new fORMRelatedTest('fORMRelatedTestChild');
	}
 
	protected function setUp()
	{
		if (defined('SKIPPING')) {
			return;
		}
		$db = new fDatabase(DB_TYPE, DB, DB_USERNAME, DB_PASSWORD, DB_HOST, DB_PORT); 
		$db->execute(file_get_contents(DB_SETUP_FILE));
		$db->execute(file_get_contents(DB_EXTENDED_SETUP_FILE));
		
		$schema = new fSchema($db);
		
		$this->sharedFixture = array(
			'db' => $db,
			'schema' => $schema
		);
	}
 
	protected function tearDown()
	{
		if (defined('SKIPPING')) {
			return;
		}
		$db = $this->sharedFixture['db'];
		$db->execute(file_get_contents(DB_EXTENDED_TEARDOWN_FILE));		
		$db->execute(file_get_contents(DB_TEARDOWN_FILE));
	}
}
 
class fORMRelatedTestChild extends PHPUnit_Framework_TestCase
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
		if (defined('SKIPPING')) {
			$this->markTestSkipped();
		}
		fORMDatabase::attach($this->sharedFixture['db']);
		fORMSchema::attach($this->sharedFixture['schema']);
	}
	
	public function tearDown()
	{
		if (defined('SKIPPING')) {
			return;
		}
		$this->sharedFixture['db']->query('DELETE FROM users WHERE user_id > 4');
		__reset();	
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
	
	
	public function testInsertUpdateStoreManyToMany()
	{
		$user = $this->createUser();
		$user->associateGroups(array(1), 'users_groups');
		$user->store();
		
		$this->assertEquals(
			array(
				array('group_id' => 1)
			),
			$this->sharedFixture['db']->query('SELECT group_id FROM users_groups WHERE user_id = %i ORDER BY group_id ASC', $user->getUserId())->fetchAllRows()
		);
		
		$user->associateGroups(array(1, 2), 'users_groups');
		$user->store();
		
		$this->assertEquals(
			array(
				array('group_id' => 1),
				array('group_id' => 2)
			),
			$this->sharedFixture['db']->query('SELECT group_id FROM users_groups WHERE user_id = %i ORDER BY group_id ASC', $user->getUserId())->fetchAllRows()
		);
		
		$user->associateGroups(array(), 'users_groups');
		$user->store();
		
		$this->assertEquals(
			array(),
			$this->sharedFixture['db']->query('SELECT group_id FROM users_groups WHERE user_id = %i ORDER BY group_id ASC', $user->getUserId())->fetchAllRows()
		);	
	}
}