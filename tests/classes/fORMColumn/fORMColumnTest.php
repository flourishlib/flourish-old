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
 
class fORMColumnTest extends PHPUnit_Framework_TestCase
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
		fORMDatabase::attach(self::$db);
		fORMSchema::attach(self::$schema);
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
	
	public static function prepareLinkProvider()
	{
		$output = array();
		
		$output[] = array('http://foobar', 'http://foobar');
		$output[] = array('http://foobar.com/baz', 'http://foobar.com/baz');
		$output[] = array('http://192.168.1.1', 'http://192.168.1.1');
		$output[] = array('http://foo.bar.baz.co.uk/?foo=1&bar=2', 'http://foo.bar.baz.co.uk/?foo=1&amp;bar=2');
		$output[] = array('https://foobar.com', 'https://foobar.com');
		$output[] = array('', '');
		$output[] = array('foobar.com/', 'http://foobar.com/');
		$output[] = array('/', '/');
		$output[] = array('/foo/bar', '/foo/bar');
		
		return $output;
	}
	
	/**
	 * @dataProvider prepareLinkProvider
	 */
	public function testPrepareLink($link, $prepared_link)
	{
		fORMColumn::configureLinkColumn('Event', 'registration_url');
		$event = $this->createEvent();
		$event->setRegistrationUrl($link);
		$this->assertEquals($prepared_link, $event->prepareRegistrationUrl());
	}
	
	public static function validateEmailProvider()
	{
		$output = array();
		
		$output[] = array('tests@flourishlib.com', FALSE);
		$output[] = array('will+foo@flourishlib.com', FALSE);
		$output[] = array("o'brien@example.com", FALSE);
		$output[] = array('john.smith@subdomain.example.co.uk', FALSE);
		$output[] = array("this-is.a+strange'email@example.com", FALSE);
		$output[] = array(' tests@flourishlib.com  ', FALSE);
		$output[] = array('foobar', TRUE);
		$output[] = array('john.smith@example', TRUE);
		$output[] = array('john @ smith dot com', TRUE);
		
		return $output;
	}
	
	/**
	 * @dataProvider validateEmailProvider
	 */
	public function testValidateEmail($email, $throws_exception)
	{
		fORMColumn::configureEmailColumn('User', 'email_address');
		if ($throws_exception) {
			$this->setExpectedException('fValidationException');
		}
		$user = $this->createUser();
		$user->setEmailAddress($email);
		$user->validate();
	}
}