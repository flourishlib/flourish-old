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

class fRecordSetTest extends PHPUnit_Framework_TestSuite
{
	public static function suite()
	{
		return new fRecordSetTest('fRecordSetTestChild');
	}
	
	protected function setUp()
	{
		$db = new fDatabase(DB_TYPE, DB, DB_USERNAME, DB_PASSWORD, DB_HOST, DB_PORT); 
		$db->query(file_get_contents(DB_SETUP_FILE));
		$db->query(file_get_contents('./database/setup-extended.sqlite.sql'));
		fORMDatabase::attach($db);
		$this->sharedFixture = $db;
	}
 
	protected function tearDown()
	{
		$db = $this->sharedFixture;
		$db->query(file_get_contents('./database/teardown-extended.sqlite.sql'));		
		$db->query(file_get_contents(DB_TEARDOWN_FILE));
	}
}

class fRecordSetTestChild extends PHPUnit_Framework_TestCase
{
	public $db;
	
	public function setUp()
	{
		$this->db = $this->sharedFixture;
	}
	
	public function testCount()
	{
		$set = fRecordSet::build('User');
		$this->assertEquals(4, $set->count());
	}
	
	public function testCountNonLimited()
	{
		$set = fRecordSet::build('User', NULL, NULL, 2);
		$this->assertEquals(2, $set->count());
		$this->assertEquals(4, $set->count(TRUE));
	}
	
	public function testCountSlice()
	{
		$set = fRecordSet::build('User');
		$set = $set->slice(0, 2);
		$this->assertEquals(2, $set->count());
		$this->assertEquals(2, $set->count(TRUE));
	}
	
	public function testCountFilter()
	{
		$set = fRecordSet::build('User');
		$set = $set->filter(array('getStatus=' => 'Active'));
		$this->assertEquals(3, $set->count());
		$this->assertEquals(3, $set->count(TRUE));
	}
	
	public function testCountDiff()
	{
		$set = fRecordSet::build('User');
		$set = $set->diff(fRecordSet::build('User', array('email_address=' => 'will@flourishlib.com')));
		$this->assertEquals(3, $set->count());
		$this->assertEquals(3, $set->count(TRUE));
	}
	
	public function testCountIntersect()
	{
		$set = fRecordSet::build('User');
		$set = $set->intersect(fRecordSet::build('User', array('email_address=' => 'will@flourishlib.com')));
		$this->assertEquals(1, $set->count());
		$this->assertEquals(1, $set->count(TRUE));
	}
	
	public function testCountUnique()
	{
		$set = fRecordSet::build('User');
		$set = $set->merge(new User(1));
		$set = $set->unique();
		$this->assertEquals(4, $set->count());
		$this->assertEquals(4, $set->count(TRUE));
	}
	
	public function testRememberCountSlice()
	{
		$set = fRecordSet::build('User');
		$set = $set->slice(0, 2, TRUE);
		$this->assertEquals(2, $set->count());
		$this->assertEquals(4, $set->count(TRUE));
	}
	
	public function testRememberCountFilter()
	{
		$set = fRecordSet::build('User');
		$set = $set->filter(array('getStatus=' => 'Active'), TRUE);
		$this->assertEquals(3, $set->count());
		$this->assertEquals(4, $set->count(TRUE));
	}
	
	public function testRememberCountDiff()
	{
		$set = fRecordSet::build('User');
		$set = $set->diff(fRecordSet::build('User', array('email_address=' => 'will@flourishlib.com')), TRUE);
		$this->assertEquals(3, $set->count());
		$this->assertEquals(4, $set->count(TRUE));
	}
	
	public function testRememberCountIntersect()
	{
		$set = fRecordSet::build('User');
		$set = $set->intersect(fRecordSet::build('User', array('email_address=' => 'will@flourishlib.com')), TRUE);
		$this->assertEquals(1, $set->count());
		$this->assertEquals(4, $set->count(TRUE));
	}
	
	public function testRememberCountUnique()
	{
		$set = fRecordSet::build('User');
		$set = $set->merge(new User(1));
		$set = $set->unique(TRUE);
		$this->assertEquals(4, $set->count());
		$this->assertEquals(5, $set->count(TRUE));
	}
}