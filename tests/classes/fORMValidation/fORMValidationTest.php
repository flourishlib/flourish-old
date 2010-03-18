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
 
class fORMValidationTest extends PHPUnit_Framework_TestSuite
{
	public static function suite()
	{
		return new fORMValidationTest('fORMValidationTestChild');
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
 
class fORMValidationTestChild extends PHPUnit_Framework_TestCase
{
	protected function createAlbum()
	{
		$album = new Album();
		$album->setName('Test Album');
		$album->setArtistId(3);
		return $album;	
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
		
	
	public static function numberValueProvider()
	{
		$output = array();
		
		$output[] = array("2010", '-99999999.99', FALSE);
		$output[] = array("2010", '99999999.99', FALSE);
		$output[] = array("2010", '100000000.00', TRUE);
		$output[] = array("2010", '0.00', FALSE);
		$output[] = array("2010", '9.9999999999999999', FALSE);
		$output[] = array("2010", '-100000000.00', TRUE);
		
		if (DB_TYPE != 'sqlite' && DB_TYPE != 'oracle') {
			$output[] = array("2010", '9.99', FALSE);
			$output[] = array("0", '9.99', FALSE);
			$output[] = array("2147483647", '9.99', FALSE);
			$output[] = array("-2147483648", '9.99', FALSE);
			$output[] = array("-2147483649", '9.99', TRUE);
			$output[] = array("2147483648", '9.99', TRUE);
			$output[] = array("4294967295", '9.99', TRUE);
		}
		
		return $output;
	}
	
	
	/**
	 * @dataProvider numberValueProvider
	 */
	public function testNumberValues($year, $msrp, $should_throw_exception)
	{
		if ($should_throw_exception) {
			$this->setExpectedException('fValidationException');	
		}
		$album = $this->createAlbum();
		$album->setYearReleased($year);
		$album->setMsrp($msrp);
		$album->validate();
	}
	
	
	public function testOneOrMoreNoValues()
	{
		fORMValidation::addOneOrMoreRule('User', array('is_validated', 'time_of_last_login'));
		$this->setExpectedException('fValidationException');
		$user = $this->createUser();
		$user->validate();
	}
	
	
	public function testOneOrMoreOneValue()
	{
		fORMValidation::addOneOrMoreRule('User', array('is_validated', 'time_of_last_login'));
		$user = $this->createUser();
		$user->setIsValidated(TRUE);
		$user->validate();
	}
	
	
	public function testOneOrMoreOneEmptyNonNullValue()
	{
		fORMValidation::addOneOrMoreRule('User', array('is_validated', 'time_of_last_login'));
		$this->setExpectedException('fValidationException');
		$user = $this->createUser();
		$user->setIsValidated(FALSE);
		$user->validate();
	}
	
	
	public function testOneOrMoreBothValues()
	{
		fORMValidation::addOneOrMoreRule('User', array('is_validated', 'time_of_last_login'));
		$user = $this->createUser();
		$user->setIsValidated(TRUE);
		$user->setTimeOfLastLogin(new fTimestamp());
		$user->validate();
	}
	
	
	public function testOnlyOneNoValues()
	{
		fORMValidation::addOnlyOneRule('User', array('is_validated', 'time_of_last_login'));
		$this->setExpectedException('fValidationException');
		$user = $this->createUser();
		$user->validate();
	}
	
	
	public function testOnlyOneOneValue()
	{
		fORMValidation::addOnlyOneRule('User', array('is_validated', 'time_of_last_login'));
		$user = $this->createUser();
		$user->setIsValidated(TRUE);
		$user->validate();
	}
	
	
	public function testOnlyOneOneEmptyNonNullValue()
	{
		fORMValidation::addOnlyOneRule('User', array('is_validated', 'time_of_last_login'));
		$this->setExpectedException('fValidationException');
		$user = $this->createUser();
		$user->setIsValidated(FALSE);
		$user->validate();
	}
	
	
	public function testOnlyOneOneEmptyNonNullValueOneNonEmptyValue()
	{
		fORMValidation::addOnlyOneRule('User', array('is_validated', 'time_of_last_login'));
		$user = $this->createUser();
		$user->setIsValidated(FALSE);
		$user->setTimeOfLastLogin(new fTimestamp());
		$user->validate();
	}
	
	
	public function testOnlyOneTwoNotNullColumnsBothEmpty()
	{
		fORMValidation::addOnlyOneRule('User', array('is_validated', 'middle_initial'));
		$this->setExpectedException('fValidationException');
		$user = $this->createUser();
		$user->setIsValidated(FALSE);
		$user->setMiddleInitial('');
		$user->validate();
	}
	
	
	public function testOnlyOneTwoNotNullColumnsBothEmptyOneNotSet()
	{
		fORMValidation::addOnlyOneRule('User', array('is_validated', 'middle_initial'));
		$this->setExpectedException('fValidationException');
		$user = $this->createUser();
		$user->setIsValidated(FALSE);
		$user->validate();
	}
	
	
	public function testOnlyOneTwoNotNullColumnsBothEmptyBothNotSet()
	{
		fORMValidation::addOnlyOneRule('User', array('is_validated', 'middle_initial'));
		$this->setExpectedException('fValidationException');
		$user = $this->createUser();
		$user->validate();
	}
	
	
	public function testOnlyOneTwoNotNullColumnsOneNotSet()
	{
		fORMValidation::addOnlyOneRule('User', array('is_validated', 'middle_initial'));
		$user = $this->createUser();
		$user->setMiddleInitial('A');
		$user->validate();
	}
	
	
	public function testOnlyOneBothValues()
	{
		fORMValidation::addOnlyOneRule('User', array('is_validated', 'time_of_last_login'));
		$this->setExpectedException('fValidationException');
		$user = $this->createUser();
		$user->setIsValidated(TRUE);
		$user->setTimeOfLastLogin(new fTimestamp());
		$user->validate();
	}
}