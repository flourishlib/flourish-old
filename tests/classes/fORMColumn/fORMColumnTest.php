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
class EventSlot extends fActiveRecord { }
class Registration extends fActiveRecord { }
class EventDetail extends fActiveRecord { }
 
class fORMColumnTest extends PHPUnit_Framework_TestSuite
{
	public static function suite()
	{
		return new fORMColumnTest('fORMColumnTestChild');
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
 
class fORMColumnTestChild extends PHPUnit_Framework_TestCase
{
	protected function createEvent()
	{
		$event = new Event();
		$event->setTitle('Test Event #1');
		$event->setStartDate('2010-06-01');
		return $event;
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
		
		
	public static function validateLinkProvider()
	{
		$output = array();
		
		$output[] = array('http://foobar', FALSE);
		$output[] = array('http://foobar.com/baz', FALSE);
		$output[] = array('http://192.168.1.1', FALSE);
		$output[] = array('http://192.168.1.1/', FALSE);
		$output[] = array('http://foo.bar.baz.co.uk', FALSE);
		$output[] = array('https://foobar.com', FALSE);
		$output[] = array('foobar.com', FALSE);
		$output[] = array('foo-bar.co.uk', FALSE);
		$output[] = array('foobar.com/', FALSE);
		$output[] = array('/', FALSE);
		$output[] = array('/foo/bar', FALSE);
		$output[] = array('foobar', TRUE);
		$output[] = array('http://', TRUE);
		$output[] = array('http://<a href="http://www.imarc.net">imarc.net</a>', TRUE);
		
		return $output;
	}
	
	/**
	 * @dataProvider validateLinkProvider
	 */
	public function testValidateLink($link, $throws_exception)
	{
		fORMColumn::configureLinkColumn('Event', 'registration_url');
		if ($throws_exception) {
			$this->setExpectedException('fValidationException');
		}
		$event = $this->createEvent();
		$event->setRegistrationUrl($link);
		$event->validate();
	}
}