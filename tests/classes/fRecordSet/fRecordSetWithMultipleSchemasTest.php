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
class Event extends fActiveRecord { }

class Flourish2User extends fActiveRecord { }
class Flourish2Group extends fActiveRecord { }
class Flourish2Artist extends fActiveRecord { }
class Flourish2Album extends fActiveRecord { }

function Album($album_id)
{
	return new Album($album_id);	
}

function _tally($value, $record)
{
	$value += $record->getTimesLoggedIn();
	return $value;	
}

class fRecordSetWithMultipleSchemasTest extends PHPUnit_Framework_TestSuite
{
	public static function suite()
	{
		return new fRecordSetWithMultipleSchemasTest('fRecordSetWithMultipleSchemasTestChild');
	}
	
	protected function setUp()
	{
		$db = new fDatabase(DB_TYPE, DB, DB_USERNAME, DB_PASSWORD, DB_HOST, DB_PORT); 
		$db->query(file_get_contents(DB_SETUP_FILE));
		$db->query(file_get_contents(DB_EXTENDED_SETUP_FILE));
		$db->query(file_get_contents(DB_ALTERNATE_SCHEMA_SETUP_FILE));
		$db->clearCache();
		fORMDatabase::attach($db);
		$this->sharedFixture = $db;
		
		fORM::mapClassToTable('Flourish2User', 'flourish2.users');
		fORM::mapClassToTable('Flourish2Group', 'flourish2.groups');
		fORM::mapClassToTable('Flourish2Artist', 'flourish2.artists');
		fORM::mapClassToTable('Flourish2Album', 'flourish2.albums');
	}
 
	protected function tearDown()
	{
		$db = $this->sharedFixture;
		$db->query(file_get_contents(DB_ALTERNATE_SCHEMA_TEARDOWN_FILE));		
		$db->query(file_get_contents(DB_EXTENDED_TEARDOWN_FILE));		
		$db->query(file_get_contents(DB_TEARDOWN_FILE));
	}
}

class fRecordSetWithMultipleSchemasTestChild extends PHPUnit_Framework_TestCase
{
	public $db;
	
	public function setUp()
	{
		$this->db = $this->sharedFixture;
	}
	
	public function tearDown()
	{
		fORMDatabase::retrieve()->enableDebugging(FALSE);
		fORMRelated::reset();
	}
	
	public function testCount()
	{
		$set = fRecordSet::build('Flourish2User');
		$this->assertEquals(2, $set->count());
	}
	
	public function testCountNonLimited()
	{
		$set = fRecordSet::build('Flourish2User', NULL, NULL, 1);
		$this->assertEquals(1, $set->count());
		$this->assertEquals(2, $set->count(TRUE));
	}
	
	public function testGetPrimaryKeys()
	{
		$set = fRecordSet::build('Flourish2User');
		$this->assertEquals(
			array(1, 2),
			$set->getPrimaryKeys()
		);
	}
	
	public function testGetRecords()
	{
		$set = fRecordSet::build('Flourish2User');
		$records = $set->getRecords();
		$this->assertEquals(TRUE, $records[0] instanceof Flourish2User);
		$this->assertEquals(TRUE, $records[1] instanceof Flourish2User);
		$this->assertEquals(2, count($records));
	}
	
	public function testPrebuildManyToMany()
	{
		fORMRelated::setOrderBys('Flourish2User', 'Flourish2Group', array('group_id' => 'desc'), 'flourish2.users_groups');
		
		$set = fRecordSet::build('Flourish2User');
		$set->prebuildFlourish2Groups('flourish2.users_groups');
		
		ob_start();
		
		fORMDatabase::retrieve()->enableDebugging(TRUE);
		foreach ($set as $user) {
			$group_ids = $user->listFlourish2Groups('flourish2.users_groups');
			switch ($user->getUserId()) {
				case 1:
					$expected_group_ids = array(1);
					break;
				case 2:
					$expected_group_ids = array();
					break;			
			}
			$this->assertEquals($expected_group_ids, $group_ids);
		}
		fORMDatabase::retrieve()->enableDebugging(FALSE);
		
		$output = ob_get_clean();
		$this->assertEquals('', $output);
	}
	
	public function testPrebuildOneToMany()
	{
		fORMRelated::setOrderBys('Flourish2Artist', 'Flourish2Album', array('album_id' => 'desc'));
		
		$set = fRecordSet::build('Flourish2Artist');
		$set->prebuildFlourish2Albums();
		
		ob_start();
		
		fORMDatabase::retrieve()->enableDebugging(TRUE);
		foreach ($set as $artist) {
			$album_ids = $artist->listFlourish2Albums();
			switch ($artist->getArtistId()) {
				case 1:
					$expected_album_ids = array(3, 2, 1);
					break;
				case 2:
					$expected_album_ids = array(5, 4);
					break;
				case 3:
					$expected_album_ids = array(6);
					break;		
			}
			$this->assertEquals($expected_album_ids, $album_ids);
		}
		fORMDatabase::retrieve()->enableDebugging(FALSE);
		
		$output = ob_get_clean();
		$this->assertEquals('', $output);
	}
	
	public function testPrecountManyToMany()
	{
		$set = fRecordSet::build('Flourish2User');
		$set->precountFlourish2Groups('flourish2.users_groups');
		
		ob_start();
		
		fORMDatabase::retrieve()->enableDebugging(TRUE);
		foreach ($set as $user) {
			$count = $user->countFlourish2Groups('flourish2.users_groups');
			switch ($user->getUserId()) {
				case 1:
					$expected_count = 1;
					break;
				case 2:
					$expected_count = 0;
					break;		
			}
			$this->assertEquals($expected_count, $count);
		}
		fORMDatabase::retrieve()->enableDebugging(FALSE);
		
		$output = ob_get_clean();
		$this->assertEquals('', $output);
	}
	
	public function testPrecountOneToMany()
	{
		$set = fRecordSet::build('Flourish2Artist');
		$set->precountFlourish2Albums();
		
		ob_start();
		
		fORMDatabase::retrieve()->enableDebugging(TRUE);
		foreach ($set as $artist) {
			$count = $artist->countFlourish2Albums();
			switch ($artist->getArtistId()) {
				case 1:
					$expected_count = 3;
					break;
				case 2:
					$expected_count = 2;
					break;
				case 3:
					$expected_count = 1;
					break;	
			}
			$this->assertEquals($expected_count, $count);
		}
		fORMDatabase::retrieve()->enableDebugging(FALSE);
		
		$output = ob_get_clean();
		$this->assertEquals('', $output);
	}
	
	public function testBuild()
	{
		$set = fRecordSet::build('Flourish2User');
		$this->assertEquals(
			array(1, 2),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionColumnConcat()
	{
		$set = fRecordSet::build('Flourish2User', array('first_name||last_name=' => 'JamesDoe'));
		$this->assertEquals(
			array(1),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionFullyQualified()
	{
		$set = fRecordSet::build('User', array('users.email_address=' => 'will@flourishlib.com'));
		$this->assertEquals(
			array(1),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionRelatedTableManyToManyRoute()
	{
		$set = fRecordSet::build('Flourish2User', array('flourish2.groups{flourish2.users_groups}.name=' => 'Sound Engineers'));
		$this->assertEquals(
			array(1),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionRelatedTableOneToManyRoute()
	{
		$set = fRecordSet::build('Flourish2User', array('flourish2.groups{group_leader}.name=' => 'Sound Engineers'));
		$this->assertEquals(
			array(1),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionRelatedTableOneToManyRoute2()
	{
		$set = fRecordSet::build('Flourish2User', array('flourish2.groups{group_founder}.name=' => 'Sound Engineers'));
		$this->assertEquals(
			array(2),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionAggregateFunctionCount()
	{
		$set = fRecordSet::build('Flourish2User', array('count(flourish2.groups{flourish2.users_groups}.group_id)=' => 1));
		$this->assertEquals(
			array(1),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithOrderBy()
	{
		$set = fRecordSet::build('Flourish2User', NULL, array('first_name' => 'asc'));
		$this->assertEquals(
			array(1, 2),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildFromSQL()
	{
		$set = fRecordSet::buildFromSQL('Flourish2User', "SELECT * FROM flourish2.users");
		$this->assertEquals(
			array(1, 2),
			$set->getPrimaryKeys()
		);
	}
}