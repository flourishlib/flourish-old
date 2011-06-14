<?php
require_once('./support/init.php');
 
class User extends fActiveRecord { }
class Group extends fActiveRecord { }
class Artist extends fActiveRecord { }
class Album extends fActiveRecord { }
class Song extends fActiveRecord { }
class UserDetail extends fActiveRecord { }
class RecordLabel extends fActiveRecord { } 
class FavoriteAlbum extends fActiveRecord {
	public function makeName($number) {
		return 'Album ' . $number;
	}
}
class InvalidTable extends fActiveRecord { }
 
class fORMValidationTest extends PHPUnit_Framework_TestCase
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
	
	
	public function testChildren()
	{
		try {
			fORMOrdering::configureOrderingColumn('FavoriteAlbum', 'position');
			$user = $this->createUser();
			$favorite_album_1 = new FavoriteAlbum();
			$favorite_album_2 = new FavoriteAlbum();
			$user->associateFavoriteAlbums(array($favorite_album_1, $favorite_album_2));
			$user->validate();
		} catch (fValidationException $e) {
			$message = preg_replace('#\s+#', ' ', strip_tags($e->getMessage()));
			$this->assertContains('Favorite Album #1 Album ID: Please enter a value', $message);
			$this->assertContains('Favorite Album #2 Album ID: Please enter a value', $message);
		}
	}
	
	
	public function testChildrenReturn()
	{
		fORMOrdering::configureOrderingColumn('FavoriteAlbum', 'position');
		$user = $this->createUser();
		$user->setFirstName(NULL);
		$favorite_album_1 = new FavoriteAlbum();
		$favorite_album_2 = new FavoriteAlbum();
		$user->associateFavoriteAlbums(array($favorite_album_1, $favorite_album_2));
		$messages = $user->validate(TRUE);
		$this->assertSame(
			array(
				'first_name' => 'First Name: Please enter a value',
				'favorite_albums[0]' => array(
					'name' => 'Favorite Album #1',
					'errors' => array(
						'album_id' => 'Album ID: Please enter a value'
					)
				),
				'favorite_albums[1]' => array(
					'name' => 'Favorite Album #2',
					'errors' => array(
						'album_id' => 'Album ID: Please enter a value'
					)
				)
			),
			$messages
		);
	}
	
	
	public function testChildrenReturnRemoveFieldNames()
	{
		fORMOrdering::configureOrderingColumn('FavoriteAlbum', 'position');
		$user = $this->createUser();
		$user->setFirstName(NULL);
		$favorite_album_1 = new FavoriteAlbum();
		$favorite_album_2 = new FavoriteAlbum();
		$user->associateFavoriteAlbums(array($favorite_album_1, $favorite_album_2));
		$messages = $user->validate(TRUE, TRUE);
		$this->assertSame(
			array(
				'first_name' => 'Please enter a value',
				'favorite_albums[0]' => array(
					'name' => 'Favorite Album #1',
					'errors' => array(
						'album_id' => 'Please enter a value'
					)
				),
				'favorite_albums[1]' => array(
					'name' => 'Favorite Album #2',
					'errors' => array(
						'album_id' => 'Please enter a value'
					)
				)
			),
			$messages
		);
	}
	
	
	public function testReplaceWithChildren()
	{
		try {
			fORMOrdering::configureOrderingColumn('FavoriteAlbum', 'position');
			fORMValidation::addRegexReplacement('User', '#(First|Last) Name#', 'Name');
			fORMValidation::addStringReplacement('User', 'Email Address', 'Email');
			$user = $this->createUser();
			$user->setFirstName(NULL);
			$user->setEmailAddress(NULL);
			$favorite_album_1 = new FavoriteAlbum();
			$favorite_album_2 = new FavoriteAlbum();
			$user->associateFavoriteAlbums(array($favorite_album_1, $favorite_album_2));
			$user->validate();
		} catch (fValidationException $e) {
			$message = preg_replace('#\s+#', ' ', strip_tags($e->getMessage()));
			$this->assertContains('The following problems were found: Name: Please enter a value Email: Please enter a value', $message);
			$this->assertContains('Favorite Album #1 Album ID: Please enter a value', $message);
			$this->assertContains('Favorite Album #2 Album ID: Please enter a value', $message);
		}
	}
	
	
	public function testReorderWithChildren()
	{
		try {
			fORMOrdering::configureOrderingColumn('FavoriteAlbum', 'position');
			fORMValidation::setMessageOrder('User', array('Email', 'Album', 'Name'));
			$user = $this->createUser();
			$user->setFirstName(NULL);
			$user->setEmailAddress(NULL);
			$favorite_album_1 = new FavoriteAlbum();
			$favorite_album_2 = new FavoriteAlbum();
			$user->associateFavoriteAlbums(array($favorite_album_1, $favorite_album_2));
			$user->validate();
		} catch (fValidationException $e) {
			$message = preg_replace('#\s+#', ' ', strip_tags($e->getMessage()));
			$this->assertContains('The following problems were found: Email Address: Please enter a value Favorite Album #1 Album ID: Please enter a value Favorite Album #2 Album ID: Please enter a value First Name: Please enter a value', $message);
		}
	}
	
	
	public function testChildValidationName()
	{
		try {
			fORMOrdering::configureOrderingColumn('FavoriteAlbum', 'position');
			fORMRelated::registerValidationNameMethod('User', 'FavoriteAlbum', 'makeName');
			$user = $this->createUser();
			$favorite_album_1 = new FavoriteAlbum();
			$favorite_album_2 = new FavoriteAlbum();
			$user->associateFavoriteAlbums(array($favorite_album_1, $favorite_album_2));
			$user->validate();
		} catch (fValidationException $e) {
			$message = preg_replace('#\s+#', ' ', strip_tags($e->getMessage()));
			$this->assertContains('The following problems were found: Album 1 Album ID: Please enter a value Album 2 Album ID: Please enter a value', $message);
		}
	}
	
	
	public function testOneOrMoreNoValues()
	{
		fORMValidation::addOneOrMoreRule('User', array('is_validated', 'time_of_last_login'));
		$this->setExpectedException('fValidationException');
		$user = $this->createUser();
		$user->validate();
	}
	
	
	public function testReturnErrors()
	{
		$user = new User();
		$errors = $user->validate(TRUE);
		$this->assertEquals(
			array(
				'first_name' => 'First Name: Please enter a value',
				'last_name' => 'Last Name: Please enter a value',
				'email_address' => 'Email Address: Please enter a value',
				'hashed_password' => 'Hashed Password: Please enter a value'
			),
			$errors
		);
	}
	
	
	public function testReturnErrorsWithoutFieldName()
	{
		$user = new User();
		$errors = $user->validate(TRUE, TRUE);
		$this->assertEquals(
			array(
				'first_name' => 'Please enter a value',
				'last_name' => 'Please enter a value',
				'email_address' => 'Please enter a value',
				'hashed_password' => 'Please enter a value'
			),
			$errors
		);
	}
	
	public function testReturnErrorsWithoutFieldNameReordered()
	{
		fORMValidation::setMessageOrder('User', array('Last', 'First', 'Email', 'Password', 'Date'));
		$user = new User();
		$errors = $user->validate(TRUE, TRUE);
		$this->assertSame(
			array(
				'last_name' => 'Please enter a value',
				'first_name' => 'Please enter a value',
				'email_address' => 'Please enter a value',
				'hashed_password' => 'Please enter a value'
			),
			$errors
		);
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


	public function testConditionalRuleWithDefaultSpace()
	{
		fORMValidation::addConditionalRule('User', 'first_name', NULL, 'middle_initial');
		$user = $this->createUser();
		$errors = $user->validate(TRUE);
		$this->assertSame(
			array(
				'middle_initial' => 'Middle Initial: Please enter a value'
			),
			$errors
		);
	}
	
	
	public function testRequiredRuleWithValue()
	{
		fORMValidation::addRequiredRule('User', 'birthday');
		$user = $this->createUser();
		$user->setBirthday('today');
		$user->validate();
	}
	
	
	public function testRequiredRuleWithNoValue()
	{
		fORMValidation::addRequiredRule('User', 'birthday');
		$this->setExpectedException('fValidationException');
		$user = $this->createUser();
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