<?php
require_once('./support/init.php');

class User extends fActiveRecord { }
class Group extends fActiveRecord { }
class Artist extends fActiveRecord { }
class Album extends fActiveRecord { }
class Song extends fActiveRecord { }

class Db2User extends fActiveRecord { }
class Db2Group extends fActiveRecord { }

function Album($album_id)
{
	return new Album($album_id);	
}

function _tally($value, $record)
{
	$value += $record->getTimesLoggedIn();
	return $value;	
}

class fRecordSetWithMultipleDatabasesTest extends PHPUnit_Framework_TestSuite
{
	public static function suite()
	{
		return new fRecordSetWithMultipleDatabasesTest('fRecordSetWithMultipleDatabasesTestChild');
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
		
		fORMDatabase::attach($db);
		fORMDatabase::attach($db2, 'db2');
		fORM::mapClassToTable('Db2User', 'users');
		fORM::mapClassToDatabase('Db2User', 'db2');
		fORM::mapClassToTable('Db2Group', 'groups');
		fORM::mapClassToDatabase('Db2Group', 'db2');
	}
 
	protected function tearDown()
	{
		$db = $this->sharedFixture[0];
		$db->query(file_get_contents(DB_TEARDOWN_FILE));
		
		$db2 = $this->sharedFixture[1];
		$db2->query(file_get_contents(DB_2_TEARDOWN_FILE));
		
		__reset();
	}
}

class fRecordSetWithMultipleDatabasesTestChild extends PHPUnit_Framework_TestCase
{
	public function testCount()
	{
		$set = fRecordSet::build('Db2User');
		$this->assertEquals(2, $set->count());
	}
	
	public function testCountNonLimited()
	{
		$set = fRecordSet::build('Db2User', NULL, NULL, 1);
		$this->assertEquals(1, $set->count());
		$this->assertEquals(2, $set->count(TRUE));
	}
	
	public function testGetPrimaryKeys()
	{
		$set = fRecordSet::build('Db2User');
		$this->assertEquals(
			array(1, 2),
			$set->getPrimaryKeys()
		);
	}
	
	public function testGetRecords()
	{
		$set = fRecordSet::build('Db2User');
		$records = $set->getRecords();
		$this->assertEquals(TRUE, $records[0] instanceof Db2User);
		$this->assertEquals(TRUE, $records[1] instanceof Db2User);
		$this->assertEquals(2, count($records));
	}
	
	public function testBuild()
	{
		$set = fRecordSet::build('Db2User');
		$this->assertEquals(
			array(1, 2),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionColumnConcat()
	{
		$set = fRecordSet::build('Db2User', array('first_name||last_name=' => 'FrankSmith'));
		$this->assertEquals(
			array(1),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionFullyQualified()
	{
		$set = fRecordSet::build('Db2User', array('users.email_address=' => 'frank@example.com'));
		$this->assertEquals(
			array(1),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionRelatedTableManyToManyRoute()
	{
		$set = fRecordSet::build('Db2User', array('groups{users_groups}.name=' => 'Music Haters'));
		$this->assertEquals(
			array(1, 2),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionRelatedTableOneToManyRoute()
	{
		$set = fRecordSet::build('Db2User', array('groups{group_leader}.name=' => 'Music Haters'));
		$this->assertEquals(
			array(1),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionRelatedTableOneToManyRoute2()
	{
		$set = fRecordSet::build('Db2User', array('groups{group_founder}.name=' => 'Music Haters'));
		$this->assertEquals(
			array(2),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionAggregateFunctionCount()
	{
		$set = fRecordSet::build('Db2User', array('count(groups{users_groups}.group_id)=' => 1));
		$this->assertEquals(
			array(1, 2),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithOrderBy()
	{
		$set = fRecordSet::build('Db2User', NULL, array('first_name' => 'desc'));
		$this->assertEquals(
			array(2, 1),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildFromSQL()
	{
		$set = fRecordSet::buildFromSQL('Db2User', "SELECT * FROM users ORDER BY user_id ASC");
		$this->assertEquals(
			array(1, 2),
			$set->getPrimaryKeys()
		);
	}
}