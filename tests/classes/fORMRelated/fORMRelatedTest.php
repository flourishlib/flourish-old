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
		fORMDatabase::attach($this->sharedFixture);	
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
			$this->sharedFixture->query('SELECT group_id FROM users_groups WHERE user_id = %i ORDER BY group_id ASC', $user->getUserId())->fetchAllRows()
		);
		
		$user->associateGroups(array(1, 2), 'users_groups');
		$user->store();
		
		$this->assertEquals(
			array(
				array('group_id' => 1),
				array('group_id' => 2)
			),
			$this->sharedFixture->query('SELECT group_id FROM users_groups WHERE user_id = %i ORDER BY group_id ASC', $user->getUserId())->fetchAllRows()
		);
		
		$user->associateGroups(array(), 'users_groups');
		$user->store();
		
		$this->assertEquals(
			array(),
			$this->sharedFixture->query('SELECT group_id FROM users_groups WHERE user_id = %i ORDER BY group_id ASC', $user->getUserId())->fetchAllRows()
		);	
	}
	

	public function tearDown()
	{
		$this->sharedFixture->query('DELETE FROM users WHERE user_id > 4');
		__reset();	
	}
}