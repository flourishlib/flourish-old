<?php
require_once('./support/init.php');

class fSQLTranslationTest extends PHPUnit_Framework_TestCase
{
	protected static $db;
	protected static $json_schema;

	public $rollback_statements = array();

	public static function setUpBeforeClass()
	{
		if (defined('SKIPPING')) {
			return;
		}
		$db = new fDatabase(DB_TYPE, DB, DB_USERNAME, DB_PASSWORD, DB_HOST, DB_PORT); 
		$db->execute(file_get_contents(DB_SETUP_FILE));

		self::$db = $db;

		self::$json_schema = fJSON::decode(file_get_contents(DB_SCHEMA_FILE), TRUE);
	}

	public static function tearDownAfterClass()
	{
		if (defined('SKIPPING')) {
			return;
		}
		teardown(self::$db, DB_TEARDOWN_FILE);
	}
	
	public function setUp()
	{
		if (defined('SKIPPING')) {
			$this->markTestSkipped();
		}
		fResult::silenceNotices();
		//self::$db->enableDebugging(TRUE);
		$this->rollback_statements = array();
	}
	
	public function tearDown()
	{
		foreach (array_reverse($this->rollback_statements) as $statement) {
			self::$db->translatedQuery($statement);
		}
	}

	public function testUnicodeSubSelect()
	{
		self::$db->translatedQuery(
			"CREATE TABLE unicode_test_2 (
				unicode_test_id INTEGER AUTOINCREMENT PRIMARY KEY,
				varchar_col VARCHAR(100) NULL,
				varchar_col_2 VARCHAR(100) NULL,
				char_col VARCHAR(10) NULL,
				text_col VARCHAR(100) NULL
			)"
		);
		$this->rollback_statements[] = 'DROP TABLE unicode_test_2';
		if (DB_TYPE == 'oracle') {
			$this->rollback_statements[] = 'DROP SEQUENCE unicode_test_2_unicode_tes_seq';
		}
		
		self::$db->getSQLTranslation()->clearCache();
		self::$db->clearCache();
		
		self::$db->translatedQuery(
			"INSERT INTO unicode_test_2 (varchar_col, varchar_col_2, char_col, text_col) VALUES (%s, %s, %s, %s)",
			"Արամ Խաչատրյան",
			"সুকুমার রায়",
			"Ελλάς",
			"Ђорђе Балашевић"
		);
		self::$db->translatedQuery(
			"INSERT INTO unicode_test_2 (varchar_col, varchar_col_2, char_col, text_col) VALUES (%s, %s, %s, %s)",
			"Արամ Խաչատրյան",
			"Test1",
			"Test1",
			"Test1"
		);
		self::$db->translatedQuery(
			"INSERT INTO unicode_test_2 (varchar_col, varchar_col_2, char_col, text_col) VALUES (%s, %s, %s, %s)",
			"ամ Խաչատրյան",
			"Test2",
			"Test2",
			"Test2"
		);
		
		$res = self::$db->translatedQuery("SELECT varchar_col, varchar_col_2 FROM unicode_test_2 ut INNER JOIN (SELECT ut2.varchar_col AS varchar_col_3 FROM unicode_test_2 ut2 inner join unicode_test_2 ut3 ON ut2.unicode_test_id = ut3.unicode_test_id) ss ON ut.varchar_col = ss.varchar_col_3");

		$this->assertEquals(
			array(
				'varchar_col'     => "Արամ Խաչատրյան",
				'varchar_col_2'   => "সুকুমার রায়"
			),
			$res->fetchRow()	
		);
	}

	public function testUnicode()
	{
		self::$db->translatedQuery(
			"CREATE TABLE unicode_test (
				unicode_test_id INTEGER AUTOINCREMENT PRIMARY KEY,
				varchar_col VARCHAR(100) NULL,
				varchar_col_2 VARCHAR(100) NULL,
				char_col VARCHAR(10) NULL,
				text_col VARCHAR(100) NULL
			)"
		);
		$this->rollback_statements[] = 'DROP TABLE unicode_test';
		if (DB_TYPE == 'oracle') {
			$this->rollback_statements[] = 'DROP SEQUENCE unicode_test_unicode_test__seq';
		}
		
		self::$db->getSQLTranslation()->clearCache();
		self::$db->clearCache();
		
		self::$db->translatedQuery(
			"INSERT INTO unicode_test (varchar_col, varchar_col_2, char_col, text_col) VALUES (%s, %s, %s, %s)",
			"Արամ Խաչատրյան",
			"সুকুমার রায়",
			"Ελλάς",
			"Ђорђе Балашевић"
		);
		
		$res = self::$db->translatedQuery("SELECT * FROM unicode_test");

		$statement = self::$db->prepare("INSERT INTO unicode_test (varchar_col, varchar_col_2, char_col, text_col) VALUES (%s, %s, %s, %s)");
		self::$db->query(
			$statement,
			"Արամ Խաչատրյան",
			"সুকুমার রায়",
			"Ελλάς",
			"Ђорђе Балашевић"
		);

		$res2 = self::$db->translatedQuery("SELECT * FROM unicode_test ORDER BY unicode_test_id ASC");
			
		$this->assertEquals(
			array(
				'unicode_test_id' => 1,
				'varchar_col'     => "Արամ Խաչատրյան",
				'varchar_col_2'   => "সুকুমার রায়",
				'char_col'        => "Ελλάς",
				'text_col'        => "Ђорђе Балашевић"
			),
			$res->fetchRow()	
		);
		
		$this->assertEquals(
			array(
				array(
					'unicode_test_id' => 1,
					'varchar_col'     => "Արամ Խաչատրյան",
					'varchar_col_2'   => "সুকুমার রায়",
					'char_col'        => "Ελλάς",
					'text_col'        => "Ђорђе Балашевић"
				),
				array(
					'unicode_test_id' => 2,
					'varchar_col'     => "Արամ Խաչատրյան",
					'varchar_col_2'   => "সুকুমার রায়",
					'char_col'        => "Ελλάς",
					'text_col'        => "Ђорђе Балашевић"
				)
			),
			$res2->fetchAllRows()	
		);
	}

	public function testCreateTable()
	{
		self::$db->translatedQuery(
			"CREATE TABLE translation_test (
				translation_test_id INTEGER AUTOINCREMENT PRIMARY KEY,
				bigint_col BIGINT NULL,
				char_col CHAR(40) NULL,
				varchar_col VARCHAR(100) NULL,
				text_col TEXT NULL,
				blob_col BLOB NULL,
				timestamp_col TIMESTAMP NULL,
				time_col TIME NULL,
				date_col DATE NULL,
				boolean_col BOOLEAN NULL
			)"
		);

		$this->rollback_statements[] = 'DROP TABLE translation_test';
		if (DB_TYPE == 'oracle') {
			$this->rollback_statements[] = 'DROP SEQUENCE translation_test_translati_seq';
		}			
		
		self::$db->translatedQuery(
			"CREATE TABLE translation_test_2 (
				translation_test_2_id INTEGER NOT NULL PRIMARY KEY,
				translation_test_id INTEGER NOT NULL REFERENCES translation_test(translation_test_id) ON DELETE CASCADE,
				name VARCHAR(100) NULL
			)"
		);

		$this->rollback_statements[] = 'DROP TABLE translation_test_2';
		
		$schema = new fSchema(self::$db);
		
		$translation_test_schema   = $schema->getColumnInfo('translation_test');
		$translation_test_2_schema = $schema->getColumnInfo('translation_test_2');
		$translation_test_keys     = $schema->getKeys('translation_test');
		$translation_test_2_keys   = $schema->getKeys('translation_test_2');
		
		foreach ($translation_test_schema as $type => &$list) {
			ksort($list);	
		}
		ksort($translation_test_schema);
		
		$max_blob_length = 0;
		$max_text_length = 0;
		switch (DB_TYPE) {
			case 'sqlite':
				$max_blob_length = 1000000000;
				$max_text_length = 1000000000;
				break;
			
			case 'oracle':
				$max_blob_length = 4294967295;
				$max_text_length = 4294967295;
				break;
			
			case 'mysql':
				$max_blob_length = 4294967295;
				$max_text_length = 4294967295;
				break;
			
			case 'postgresql':
				$max_blob_length = 1073741824;
				$max_text_length = 1073741824;
				break;
			
			case 'mssql':
				$max_blob_length = 2147483647;
				$max_text_length = 1073741823;
				break;
			
			case 'db2':
				$max_blob_length = 2147483647;
				$max_text_length = 1073741824;
				break;
		}
		
		$this->assertEquals(
			array(
				'bigint_col' => array(
					"auto_increment" => FALSE,
					"comment"        => "",
					"decimal_places" => NULL,
					"default"        => NULL,
					"max_length"     => NULL,
					"max_value"      => (DB_TYPE != 'sqlite' && DB_TYPE != 'oracle') ? new fNumber('9223372036854775807') : null,
					"min_value"      => (DB_TYPE != 'sqlite' && DB_TYPE != 'oracle') ? new fNumber('-9223372036854775808') : null,
					"not_null"       => FALSE,
					"placeholder"    => "%i",
					"type"           => "integer",
					"valid_values"   => NULL
				),
				'blob_col' => array(
					"auto_increment" => FALSE,
					"comment"        => "",
					"decimal_places" => NULL,
					"default"        => NULL,
					"max_length"     => $max_blob_length,
					"max_value"      => NULL,
					"min_value"      => NULL,
					"not_null"       => FALSE,
					"placeholder"    => "%l",
					"type"           => "blob",
					"valid_values"   => NULL
				),
				'boolean_col' => array(
					"auto_increment" => FALSE,
					"comment"        => "",
					"decimal_places" => NULL,
					"default"        => NULL,
					"max_length"     => NULL,
					"max_value"      => NULL,
					"min_value"      => NULL,
					"not_null"       => FALSE,
					"placeholder"    => "%b",
					"type"           => "boolean",
					"valid_values"   => NULL
				),
				'char_col' => array(
					"auto_increment" => FALSE,
					"comment"        => "",
					"decimal_places" => NULL,
					"default"        => NULL,
					"max_length"     => 40,
					"max_value"      => NULL,
					"min_value"      => NULL,
					"not_null"       => FALSE,
					"placeholder"    => "%s",
					"type"           => "char",
					"valid_values"   => NULL
				),
				'date_col' => array(
					"auto_increment" => FALSE,
					"comment"        => "",
					"decimal_places" => NULL,
					"default"        => NULL,
					"max_length"     => NULL,
					"max_value"      => NULL,
					"min_value"      => NULL,
					"not_null"       => FALSE,
					"placeholder"    => (DB_TYPE == 'mssql') ? "%p" : "%d",
					"type"           => (DB_TYPE == 'mssql') ? "timestamp" : "date",
					"valid_values"   => NULL
				),
				'text_col' => array(
					"auto_increment" => FALSE,
					"comment"        => "",
					"decimal_places" => NULL,
					"default"        => NULL,
					"max_length"     => $max_text_length,
					"max_value"      => NULL,
					"min_value"      => NULL,
					"not_null"       => FALSE,
					"placeholder"    => "%s",
					"type"           => "text",
					"valid_values"   => NULL
				),
				'time_col' => array(
					"auto_increment" => FALSE,
					"comment"        => "",
					"decimal_places" => NULL,
					"default"        => NULL,
					"max_length"     => NULL,
					"max_value"      => NULL,
					"min_value"      => NULL,
					"not_null"       => FALSE,
					"placeholder"    => (in_array(DB_TYPE, array('mssql', 'oracle'))) ? "%p" : "%t",
					"type"           => (in_array(DB_TYPE, array('mssql', 'oracle'))) ? "timestamp" : "time",
					"valid_values"   => NULL
				),
				'timestamp_col' => array(
					"auto_increment" => FALSE,
					"comment"        => "",
					"decimal_places" => NULL,
					"default"        => NULL,
					"max_length"     => NULL,
					"max_value"      => NULL,
					"min_value"      => NULL,
					"not_null"       => FALSE,
					"placeholder"    => "%p",
					"type"           => "timestamp",
					"valid_values"   => NULL
				),
				'translation_test_id' => array(
					"auto_increment" => TRUE,
					"comment"        => "",
					"decimal_places" => NULL,
					"default"        => NULL,
					"max_length"     => NULL,
					"max_value"      => (DB_TYPE != 'sqlite' && DB_TYPE != 'oracle') ? new fNumber(2147483647) : null,
					"min_value"      => (DB_TYPE != 'sqlite' && DB_TYPE != 'oracle') ? new fNumber(-2147483648) : null,
					"not_null"       => TRUE,
					"placeholder"    => "%i",
					"type"           => "integer",
					"valid_values"   => NULL
				),
				'varchar_col' => array(
					"auto_increment" => FALSE,
					"comment"        => "",
					"decimal_places" => NULL,
					"default"        => NULL,
					"max_length"     => 100,
					"max_value"      => NULL,
					"min_value"      => NULL,
					"not_null"       => FALSE,
					"placeholder"    => "%s",
					"type"           => "varchar",
					"valid_values"   => NULL
				)
			),
			$translation_test_schema
		);
		
		foreach ($translation_test_2_schema as $type => &$list) {
			ksort($list);	
		}
		ksort($translation_test_2_schema);
		
		$this->assertEquals(
			array(
				'name' => array(
					"auto_increment" => FALSE,
					"comment"        => "",
					"decimal_places" => NULL,
					"default"        => NULL,
					"max_length"     => 100,
					"max_value"      => NULL,
					"min_value"      => NULL,
					"not_null"       => FALSE,
					"placeholder"    => "%s",
					"type"           => "varchar",
					"valid_values"   => NULL
				),
				'translation_test_2_id' => array(
					"auto_increment" => (DB_TYPE == 'sqlite') ? TRUE : FALSE,
					"comment"        => "",
					"decimal_places" => NULL,
					"default"        => NULL,
					"max_length"     => NULL,
					"max_value"      => (DB_TYPE != 'sqlite' && DB_TYPE != 'oracle') ? new fNumber(2147483647) : null,
					"min_value"      => (DB_TYPE != 'sqlite' && DB_TYPE != 'oracle') ? new fNumber(-2147483648) : null,
					"not_null"       => TRUE,
					"placeholder"    => "%i",
					"type"           => "integer",
					"valid_values"   => NULL
				),
				'translation_test_id' => array(
					"auto_increment" => FALSE,
					"comment"        => "",
					"decimal_places" => NULL,
					"default"        => NULL,
					"max_length"     => NULL,
					"max_value"      => (DB_TYPE != 'sqlite' && DB_TYPE != 'oracle') ? new fNumber(2147483647) : null,
					"min_value"      => (DB_TYPE != 'sqlite' && DB_TYPE != 'oracle') ? new fNumber(-2147483648) : null,
					"not_null"       => TRUE,
					"placeholder"    => "%i",
					"type"           => "integer",
					"valid_values"   => NULL
				)
			),
			$translation_test_2_schema
		);
		
		ksort($translation_test_keys);
		$this->assertEquals(
			array(
				'foreign' => array(),
				'primary' => array('translation_test_id'),
				'unique' => array()
			),
			$translation_test_keys
		);
		
		ksort($translation_test_2_keys);
		$this->assertEquals(
			array(
				'foreign' => array(
					array(
						"column"         => "translation_test_id",
						"foreign_table"  => "translation_test",
						"foreign_column" => "translation_test_id",
						"on_delete"      => "cascade",
						"on_update"      => "no_action"
					)
				),
				'primary' => array('translation_test_2_id'),
				'unique' => array()
			),
			$translation_test_2_keys
		);
	}

	public function testSubstrMultibyte()
	{
		self::$db->translatedQuery("CREATE TABLE foo (name VARCHAR(20) NOT NULL PRIMARY KEY)");
		$this->rollback_statements[] = "DROP TABLE foo";

		self::$db->translatedQuery("INSERT INTO foo (name) VALUES (%s)", 'Résumé');
		$res = self::$db->translatedQuery("SELECT substr(name, 2, 3) AS name FROM foo");

		$this->assertEquals(
			'ésu',
			$res->fetchScalar()
		);
	}

	public function testDropDefault()
	{
		self::$db->translatedQuery("ALTER TABLE users ALTER COLUMN middle_initial DROP DEFAULT");
		$this->rollback_statements[] = "ALTER TABLE users ALTER COLUMN middle_initial SET NOT NULL DEFAULT ''";
		$schema = new fSchema(self::$db);

		$this->assertSame(
			NULL,
			$schema->getColumnInfo('users', 'middle_initial', 'default')
		);
	}

	public function testDropDefaultNoDefault()
	{
		self::$db->translatedQuery("ALTER TABLE users ALTER COLUMN first_name DROP DEFAULT");
		$schema = new fSchema(self::$db);

		$this->assertSame(
			NULL,
			$schema->getColumnInfo('users', 'first_name', 'default')
		);
	}

	public function testSetDefault()
	{
		self::$db->translatedQuery("ALTER TABLE users ALTER COLUMN first_name SET DEFAULT 'This is a test'");
		$this->rollback_statements[] = "ALTER TABLE users ALTER COLUMN first_name DROP DEFAULT";

		$schema = new fSchema(self::$db);

		$this->assertSame(
			'This is a test',
			$schema->getColumnInfo('users', 'first_name', 'default')
		);
	}

	public function testOverrideDefault()
	{
		self::$db->translatedQuery("ALTER TABLE users ALTER COLUMN times_logged_in SET DEFAULT 1");
		$this->rollback_statements[] = "ALTER TABLE users ALTER COLUMN times_logged_in SET DEFAULT 0";

		$schema = new fSchema(self::$db);

		$this->assertSame(
			'1',
			$schema->getColumnInfo('users', 'times_logged_in', 'default')
		);
	}

	public function testDropNotNull()
	{
		self::$db->translatedQuery("ALTER TABLE users ALTER COLUMN first_name DROP NOT NULL");
		$this->rollback_statements[] = "ALTER TABLE users ALTER COLUMN first_name SET NOT NULL";

		$schema = new fSchema(self::$db);

		$this->assertSame(
			FALSE,
			$schema->getColumnInfo('users', 'first_name', 'not_null')
		);
	}

	public function testDropNotNullNoNotNull()
	{
		self::$db->translatedQuery("ALTER TABLE users ALTER COLUMN birthday DROP NOT NULL");

		$schema = new fSchema(self::$db);

		$this->assertSame(
			FALSE,
			$schema->getColumnInfo('users', 'birthday', 'not_null')
		);
	}

	public function testSetNotNull()
	{
		self::$db->translatedQuery("UPDATE users SET birthday = %d WHERE birthday IS NULL", date('Y-m-d'));
		$this->rollback_statements[] = self::$db->escape("UPDATE users SET birthday = NULL WHERE birthday = %d", date('Y-m-d'));
		self::$db->translatedQuery("ALTER TABLE users ALTER COLUMN birthday SET NOT NULL");
		$this->rollback_statements[] = "ALTER TABLE users ALTER COLUMN birthday DROP NOT NULL";

		$schema = new fSchema(self::$db);

		$this->assertSame(
			TRUE,
			$schema->getColumnInfo('users', 'birthday', 'not_null')
		);
	}

	public function testSetNotNullDefault()
	{
		self::$db->translatedQuery("ALTER TABLE users ALTER COLUMN hashed_password SET NOT NULL DEFAULT ''");
		$this->rollback_statements[] = "ALTER TABLE users ALTER COLUMN hashed_password DROP DEFAULT";
		$this->rollback_statements[] = "ALTER TABLE users ALTER COLUMN hashed_password SET NOT NULL";
		$schema = new fSchema(self::$db);

		$this->assertSame(
			(DB_TYPE == "oracle") ? FALSE : TRUE,
			$schema->getColumnInfo('users', 'hashed_password', 'not_null')
		);
		$this->assertSame(
			(DB_TYPE == "oracle") ? NULL : "",
			$schema->getColumnInfo('users', 'hashed_password', 'default')
		);
	}

	public function testSetNotNullAlreadyNotNull()
	{
		self::$db->translatedQuery("ALTER TABLE users ALTER COLUMN is_validated SET NOT NULL");
		$schema = new fSchema(self::$db);

		$this->assertSame(
			TRUE,
			$schema->getColumnInfo('users', 'is_validated', 'not_null')
		);
	}

	public function testDropPrimaryKey()
	{
		self::$db->translatedQuery("ALTER TABLE blobs DROP PRIMARY KEY");
		$this->rollback_statements[] = "ALTER TABLE blobs ADD PRIMARY KEY (blob_id)";

		$schema = new fSchema(self::$db);

		$this->assertSame(
			array(),
			$schema->getKeys('blobs', 'primary')
		);

		$this->assertEquals(
			array(
				'type'           => 'integer',
				'placeholder'    => '%i',
				'not_null'       => TRUE,
				'default'        => NULL,
				'valid_values'   => NULL,
				'max_length'     => NULL,
				'max_value'      => (DB_TYPE != 'sqlite' && DB_TYPE != 'oracle') ? new fNumber(2147483647) : NULL,
				'min_value'      => (DB_TYPE != 'sqlite' && DB_TYPE != 'oracle') ? new fNumber(-2147483648) : NULL,
				'decimal_places' => NULL,
				'auto_increment' => FALSE,
				'comment'        => ''
			),
			$schema->getColumnInfo('blobs', 'blob_id')
		);
	}

	public function testDropNonExistentPrimaryKey()
	{
		self::$db->translatedQuery("ALTER TABLE blobs DROP PRIMARY KEY");
		$this->rollback_statements[] = "ALTER TABLE blobs ADD PRIMARY KEY (blob_id)";

		$this->setExpectedException('fSQLException');
		self::$db->translatedQuery("ALTER TABLE blobs DROP PRIMARY KEY");
	}

	public function testDropAutoIncrementPrimaryKey()
	{
		self::$db->translatedQuery('ALTER TABLE "songs" DROP PRIMARY KEY');
		$this->rollback_statements[] = "ALTER TABLE songs ADD PRIMARY KEY (song_id) AUTOINCREMENT";

		$schema = new fSchema(self::$db);

		$this->assertSame(
			array(),
			$schema->getKeys('songs', 'primary')
		);

		$this->assertEquals(
			array(
				'type'           => 'integer',
				'placeholder'    => '%i',
				'not_null'       => TRUE,
				'default'        => NULL,
				'valid_values'   => NULL,
				'max_length'     => NULL,
				'max_value'      => (DB_TYPE != 'sqlite' && DB_TYPE != 'oracle') ? new fNumber(2147483647) : NULL,
				'min_value'      => (DB_TYPE != 'sqlite' && DB_TYPE != 'oracle') ? new fNumber(-2147483648) : NULL,
				'decimal_places' => NULL,
				'auto_increment' => FALSE,
				'comment'        => ''
			),
			$schema->getColumnInfo('songs', 'song_id')
		);
	}

	public function testDropMultiColumnPrimaryKey()
	{
		self::$db->translatedQuery("ALTER TABLE users_groups DROP PRIMARY KEY");
		$this->rollback_statements[] = "ALTER TABLE users_groups ADD PRIMARY KEY (user_id, group_id)";

		$schema = new fSchema(self::$db);

		$this->assertSame(
			array(),
			$schema->getKeys('users_groups', 'primary')
		);
	}

	public function testAddPrimaryKey()
	{
		self::$db->translatedQuery('ALTER TABLE "artists" DROP PRIMARY KEY');
		$this->rollback_statements[] = "ALTER TABLE albums ADD FOREIGN KEY (artist_id) REFERENCES artists(artist_id) ON UPDATE CASCADE ON DELETE CASCADE";
		$this->rollback_statements[] = "ALTER TABLE artists ADD PRIMARY KEY (artist_id) AUTOINCREMENT";

		self::$db->translatedQuery('ALTER TABLE "artists" DROP UNIQUE (name)');
		$this->rollback_statements[] = "ALTER TABLE artists ADD UNIQUE(name)";

		self::$db->translatedQuery('ALTER TABLE "artists" ADD PRIMARY KEY ("name")');
		$this->rollback_statements[] = "ALTER TABLE artists DROP PRIMARY KEY";

		$schema = new fSchema(self::$db);

		$this->assertSame(
			array('name'),
			$schema->getKeys('artists', 'primary')
		);
	}

	public function testAddPrimaryKeyAutoIncrement()
	{
		self::$db->translatedQuery('ALTER TABLE blobs DROP PRIMARY KEY');
		$this->rollback_statements[] = "ALTER TABLE blobs ADD PRIMARY KEY (blob_id)";
		self::$db->translatedQuery('ALTER TABLE blobs ADD PRIMARY KEY (blob_id) AUTOINCREMENT');
		$this->rollback_statements[] = "ALTER TABLE blobs DROP PRIMARY KEY";

		$schema = new fSchema(self::$db);

		$columns = array_keys($schema->getColumnInfo('blobs'));
		sort($columns);
		$this->assertEquals(
			array(
				'blob_id',
				'data'
			),
			$columns
		);
		$this->assertEquals(
			array(
				'type'           => 'integer',
				'placeholder'    => '%i',
				'not_null'       => TRUE,
				'default'        => NULL,
				'valid_values'   => NULL,
				'max_length'     => NULL,
				'max_value'      => (DB_TYPE != 'sqlite' && DB_TYPE != 'oracle') ? new fNumber(2147483647) : NULL,
				'min_value'      => (DB_TYPE != 'sqlite' && DB_TYPE != 'oracle') ? new fNumber(-2147483648) : NULL,
				'decimal_places' => NULL,
				'auto_increment' => TRUE,
				'comment'        => ''
			),
			$schema->getColumnInfo('blobs', 'blob_id')
		);
		$this->assertEquals(
			array(1),
			array_map('current', self::$db->query("SELECT blob_id FROM blobs")->fetchAllRows())
		);

		self::$db->query("INSERT INTO blobs (data) VALUES (%l)", 'foobar');
		$this->rollback_statements[] = "DELETE FROM blobs WHERE blob_id > 1";

		$this->assertEquals(
			array(1, 2),
			array_map('current', self::$db->query("SELECT blob_id FROM blobs")->fetchAllRows())
		);
	}

	public function testAddMultiColumnPrimaryKey()
	{
		self::$db->translatedQuery('ALTER TABLE "artists" DROP PRIMARY KEY');
		$this->rollback_statements[] = "ALTER TABLE albums ADD FOREIGN KEY (artist_id) REFERENCES artists(artist_id) ON UPDATE CASCADE ON DELETE CASCADE";
		$this->rollback_statements[] = "ALTER TABLE artists ADD PRIMARY KEY (artist_id) AUTOINCREMENT";
		self::$db->translatedQuery('ALTER TABLE "artists" ADD PRIMARY KEY (artist_id, "name")');
		$this->rollback_statements[] = "ALTER TABLE artists DROP PRIMARY KEY";

		$schema = new fSchema(self::$db);

		$this->assertEquals(
			array('artist_id', 'name'),
			$schema->getKeys('artists', 'primary')
		);
	}

	public function testDropUnique()
	{
		self::$db->translatedQuery("ALTER TABLE artists DROP UNIQUE (name)");
		$this->rollback_statements[] = "ALTER TABLE artists ADD UNIQUE (name)";
		$schema = new fSchema(self::$db);

		$this->assertSame(
			array(),
			$schema->getKeys('artists', 'unique')
		);
	}

	public function testDropMultiColumnUnique()
	{
		self::$db->translatedQuery('ALTER TABLE albums DROP UNIQUE ("name", "artist_id")');
		$this->rollback_statements[] = "ALTER TABLE albums ADD UNIQUE (name, artist_id)";
		$schema = new fSchema(self::$db);

		$this->assertSame(
			array(),
			$schema->getKeys('albums', 'unique')
		);
	}

	public function testDropMultiColumnUniqueDifferentOrder()
	{
		self::$db->translatedQuery('ALTER TABLE albums DROP UNIQUE (artist_id, name)');
		$this->rollback_statements[] = "ALTER TABLE albums ADD UNIQUE (name, artist_id)";
		$schema = new fSchema(self::$db);

		$this->assertSame(
			array(),
			$schema->getKeys('albums', 'unique')
		);
	}

	public function testAddUnique()
	{
		self::$db->translatedQuery("ALTER TABLE albums ADD UNIQUE (name)");
		$this->rollback_statements[] = "ALTER TABLE albums DROP UNIQUE (name)";
		$schema = new fSchema(self::$db);

		$this->assertEquals(
			sort_array(array(
				array('name'),
				array('artist_id', 'name')
			)),
			sort_array($schema->getKeys('albums', 'unique'))
		);
	}

	public function testAddUniqueQuoted()
	{
		self::$db->translatedQuery('ALTER TABLE "albums" ADD UNIQUE ("name")');
		$this->rollback_statements[] = "ALTER TABLE albums DROP UNIQUE (name)";
		$schema = new fSchema(self::$db);

		$this->assertEquals(
			sort_array(array(
				array('name'),
				array('artist_id', 'name')
			)),
			sort_array($schema->getKeys('albums', 'unique'))
		);
	}

	public function testAddMultipleColumnUnique()
	{
		self::$db->translatedQuery('ALTER TABLE "songs" ADD UNIQUE ("song_id", "album_id")');
		$this->rollback_statements[] = "ALTER TABLE songs DROP UNIQUE (song_id, album_id)";
		$schema = new fSchema(self::$db);

		$this->assertEquals(
			sort_array(array(
				array('track_number', 'album_id'),
				array('song_id', 'album_id'))
			),
			sort_array($schema->getKeys('songs', 'unique'))
		);
	}

	public function testCommentOnColumn()
	{
		self::$db->translatedQuery('COMMENT ON COLUMN "users".email_address IS %s', 'This is a test on email_address');
		$this->rollback_statements[] = "COMMENT ON COLUMN users.email_address IS ''";

		$schema = new fSchema(self::$db);

		$this->assertSame(
			'This is a test on email_address',
			$schema->getColumnInfo('users', 'email_address', 'comment')
		);
		$this->assertSame(
			'This hash is generated using fCryptography::hashPassword()',
			$schema->getColumnInfo('users', 'hashed_password', 'comment')
		);
		$this->assertSame(
			'When the user last logged in',
			$schema->getColumnInfo('users', 'time_of_last_login', 'comment')
		);
	}

	public function testDropCheckConstraint()
	{
		self::$db->translatedQuery("ALTER TABLE users ALTER COLUMN status DROP CHECK");
		$this->rollback_statements[] = "ALTER TABLE users ALTER COLUMN status SET CHECK IN ('Active', 'Inactive', 'Pending')";

		$schema = new fSchema(self::$db);

		$this->assertEquals(
			array(
				'type'           => 'varchar',
				'placeholder'    => '%s',
				'not_null'       => TRUE,
				'default'        => 'Active',
				'valid_values'   => NULL,
				'max_length'     => 8,
				'max_value'      => NULL,
				'min_value'      => NULL,
				'decimal_places' => NULL,
				'auto_increment' => FALSE,
				'comment'        => ''
			),
			$schema->getColumnInfo('users', 'status')
		);
	}

	public function testSetCheckConstraint()
	{
		self::$db->translatedQuery("ALTER TABLE users ALTER COLUMN middle_initial SET CHECK IN ('', 'A', 'B', 'C')");
		$this->rollback_statements[] = "ALTER TABLE users ALTER COLUMN middle_initial TYPE VARCHAR(100)";
		$this->rollback_statements[] = "ALTER TABLE users ALTER COLUMN middle_initial DROP CHECK";

		$schema = new fSchema(self::$db);

		$this->assertEquals(
			array(
				'type'           => 'varchar',
				'placeholder'    => '%s',
				'not_null'       => DB_TYPE == 'oracle' ? FALSE : TRUE,
				'default'        => '',
				'valid_values'   => array('', 'A', 'B', 'C'),
				'max_length'     => DB_TYPE == 'mysql' ? 1 : 100,
				'max_value'      => NULL,
				'min_value'      => NULL,
				'decimal_places' => NULL,
				'auto_increment' => FALSE,
				'comment'        => ''
			),
			$schema->getColumnInfo('users', 'middle_initial')
		);
	}

	public function testOverwriteCheckConstraint()
	{
		self::$db->translatedQuery("ALTER TABLE users ALTER COLUMN status SET CHECK IN ('Active', 'Inactive', 'Pending', 'New')");
		$this->rollback_statements[] = "ALTER TABLE users ALTER COLUMN status SET CHECK IN ('Active', 'Inactive', 'Pending')";

		$schema = new fSchema(self::$db);

		$this->assertEquals(
			array(
				'type'           => 'varchar',
				'placeholder'    => '%s',
				'not_null'       => TRUE,
				'default'        => 'Active',
				'valid_values'   => array('Active', 'Inactive', 'Pending', 'New'),
				'max_length'     => 8,
				'max_value'      => NULL,
				'min_value'      => NULL,
				'decimal_places' => NULL,
				'auto_increment' => FALSE,
				'comment'        => ''
			),
			$schema->getColumnInfo('users', 'status')
		);
	}

	public function testRenameTable()
	{
		self::$db->translatedQuery("ALTER TABLE users RENAME TO foosers");
		$this->rollback_statements[] = "ALTER TABLE foosers RENAME TO users";

		$schema = new fSchema(self::$db);

		$this->assertSame(
			array(
				"albums", "artists", "blobs", "foosers", "groups", "owns_on_cd", "owns_on_tape", "songs", "users_groups"
			),
			$schema->getTables()
		);

		$this->assertEquals(
			sort_array(array(
				array(
					'column'         => 'group_leader',
					'foreign_table'  => 'foosers',
					'foreign_column' => 'user_id',
					'on_delete'      => 'cascade',
					'on_update'      => DB_TYPE == 'oracle' || DB_TYPE == 'db2' ? 'no_action' : 'cascade'
				),
				array(
					'column'         => 'group_founder',
					'foreign_table'  => 'foosers',
					'foreign_column' => 'user_id',
					'on_delete'      => DB_TYPE == 'mssql' ? 'no_action' : 'cascade',
					'on_update'      => in_array(DB_TYPE, array('mssql', 'oracle', 'db2')) ? 'no_action' : 'cascade'
				)
			)),
			sort_array($schema->getKeys('groups', 'foreign'))
		);

		$this->assertEquals(
			sort_array(array(
				array(
					'column'         => 'user_id',
					'foreign_table'  => 'foosers',
					'foreign_column' => 'user_id',
					'on_delete'      => 'cascade',
					'on_update'      => DB_TYPE == 'oracle' || DB_TYPE == 'db2' ? 'no_action' : 'cascade'
				),
				array(
					'column'         => 'group_id',
					'foreign_table'  => 'groups',
					'foreign_column' => 'group_id',
					'on_delete'      => DB_TYPE == 'mssql' ? 'no_action' : 'cascade',
					'on_update'      => in_array(DB_TYPE, array('mssql', 'oracle', 'db2')) ? 'no_action' : 'cascade'
				)
			)),
			sort_array($schema->getKeys('users_groups', 'foreign'))
		);

		$this->assertEquals(
			sort_array(array(
				array(
					'column'         => 'user_id',
					'foreign_table'  => 'foosers',
					'foreign_column' => 'user_id',
					'on_delete'      => 'cascade',
					'on_update'      => DB_TYPE == 'oracle' || DB_TYPE == 'db2' ? 'no_action' : 'cascade'
				),
				array(
					'column'         => 'album_id',
					'foreign_table'  => 'albums',
					'foreign_column' => 'album_id',
					'on_delete'      => 'cascade',
					'on_update'      => DB_TYPE == 'oracle' || DB_TYPE == 'db2' ? 'no_action' : 'cascade'
				)
			)),
			sort_array($schema->getKeys('owns_on_cd', 'foreign'))
		);

		$this->assertEquals(
			sort_array(array(
				array(
					'column'         => 'user_id',
					'foreign_table'  => 'foosers',
					'foreign_column' => 'user_id',
					'on_delete'      => 'cascade',
					'on_update'      => DB_TYPE == 'oracle' || DB_TYPE == 'db2' ? 'no_action' : 'cascade'
				),
				array(
					'column'         => 'album_id',
					'foreign_table'  => 'albums',
					'foreign_column' => 'album_id',
					'on_delete'      => 'cascade',
					'on_update'      => DB_TYPE == 'oracle' || DB_TYPE == 'db2' ? 'no_action' : 'cascade'
				)
			)),
			sort_array($schema->getKeys('owns_on_tape', 'foreign'))
		);
	}

	public function testRenameTable2()
	{
		self::$db->translatedQuery('ALTER TABLE "artists" RENAME TO "bands"');
		$this->rollback_statements[] = "ALTER TABLE bands RENAME TO artists";

		$schema = new fSchema(self::$db);

		$this->assertSame(
			array(
				"albums", "bands", "blobs", "groups", "owns_on_cd", "owns_on_tape", "songs", "users", "users_groups"
			),
			$schema->getTables()
		);

		$this->assertSame(
			array(
				array(
					'column'         => 'artist_id',
					'foreign_table'  => 'bands',
					'foreign_column' => 'artist_id',
					'on_delete'      => 'cascade',
					'on_update'      => DB_TYPE == 'oracle' || DB_TYPE == 'db2' ? 'no_action' : 'cascade'
				)
			),
			$schema->getKeys('albums', 'foreign')
		);

		$this->assertSame(
			array(
				array('name')
			),
			$schema->getKeys('bands', 'unique')
		);

		$this->assertEquals(
			3,
			self::$db->query("SELECT count(*) FROM bands")->fetchScalar()
		);
	}

	public function testRenameColumn()
	{
		self::$db->translatedQuery('ALTER TABLE users RENAME COLUMN "user_id" TO id');
		$this->rollback_statements[] = "ALTER TABLE users RENAME COLUMN id TO user_id";

		$schema = new fSchema(self::$db);

		$this->assertSame(
			array(
				'id',
				'first_name',
				'middle_initial',
				'last_name',
				'email_address',
				'status',
				'times_logged_in',
				'date_created',
				'birthday',
				'time_of_last_login',
				'is_validated',
				'hashed_password'
			),
			array_keys($schema->getColumnInfo('users'))
		);

		$this->assertEquals(
			sort_array(array(
				array(
					'column'         => 'group_leader',
					'foreign_table'  => 'users',
					'foreign_column' => 'id',
					'on_delete'      => 'cascade',
					'on_update'      => DB_TYPE == 'oracle' || DB_TYPE == 'db2' ? 'no_action' : 'cascade'
				),
				array(
					'column'         => 'group_founder',
					'foreign_table'  => 'users',
					'foreign_column' => 'id',
					'on_delete'      => DB_TYPE == 'mssql' ? 'no_action' : 'cascade',
					'on_update'      => in_array(DB_TYPE, array('mssql', 'oracle', 'db2')) ? 'no_action' : 'cascade'
				)
			)),
			sort_array($schema->getKeys('groups', 'foreign'))
		);

		$this->assertEquals(
			sort_array(array(
				array(
					'column'         => 'user_id',
					'foreign_table'  => 'users',
					'foreign_column' => 'id',
					'on_delete'      => 'cascade',
					'on_update'      => DB_TYPE == 'oracle' || DB_TYPE == 'db2' ? 'no_action' : 'cascade'
				),
				array(
					'column'         => 'group_id',
					'foreign_table'  => 'groups',
					'foreign_column' => 'group_id',
					'on_delete'      => DB_TYPE == 'mssql' ? 'no_action' : 'cascade',
					'on_update'      => in_array(DB_TYPE, array('mssql', 'oracle', 'db2')) ? 'no_action' : 'cascade'
				)
			)),
			sort_array($schema->getKeys('users_groups', 'foreign'))
		);

		$this->assertEquals(
			sort_array(array(
				array(
					'column'         => 'user_id',
					'foreign_table'  => 'users',
					'foreign_column' => 'id',
					'on_delete'      => 'cascade',
					'on_update'      => DB_TYPE == 'oracle' || DB_TYPE == 'db2' ? 'no_action' : 'cascade'
				),
				array(
					'column'         => 'album_id',
					'foreign_table'  => 'albums',
					'foreign_column' => 'album_id',
					'on_delete'      => 'cascade',
					'on_update'      => DB_TYPE == 'oracle' || DB_TYPE == 'db2' ? 'no_action' : 'cascade'
				)
			)),
			sort_array($schema->getKeys('owns_on_cd', 'foreign'))
		);

		$this->assertEquals(
			sort_array(array(
				array(
					'column'         => 'user_id',
					'foreign_table'  => 'users',
					'foreign_column' => 'id',
					'on_delete'      => 'cascade',
					'on_update'      => DB_TYPE == 'oracle' || DB_TYPE == 'db2' ? 'no_action' : 'cascade'
				),
				array(
					'column'         => 'album_id',
					'foreign_table'  => 'albums',
					'foreign_column' => 'album_id',
					'on_delete'      => 'cascade',
					'on_update'      => DB_TYPE == 'oracle' || DB_TYPE == 'db2' ? 'no_action' : 'cascade'
				)
			)),
			sort_array($schema->getKeys('owns_on_tape', 'foreign'))
		);

		$this->assertSame(
			array(
				'id'
			),
			$schema->getKeys('users', 'primary')
		);
	}

	public function testRenameColumnPartOfMultiColumnPrimaryKey()
	{
		self::$db->translatedQuery('ALTER TABLE users_groups RENAME COLUMN "user_id" TO id');
		$this->rollback_statements[] = "ALTER TABLE users_groups RENAME COLUMN id TO user_id";

		$schema = new fSchema(self::$db);

		$this->assertSame(
			array(
				'id',
				'group_id'
			),
			array_keys($schema->getColumnInfo('users_groups'))
		);

		$this->assertEquals(
			sort_array(array(
				array(
					'column'         => 'id',
					'foreign_table'  => 'users',
					'foreign_column' => 'user_id',
					'on_delete'      => 'cascade',
					'on_update'      => DB_TYPE == 'oracle' || DB_TYPE == 'db2' ? 'no_action' : 'cascade'
				),
				array(
					'column'         => 'group_id',
					'foreign_table'  => 'groups',
					'foreign_column' => 'group_id',
					'on_delete'      => DB_TYPE == 'mssql' ? 'no_action' : 'cascade',
					'on_update'      => in_array(DB_TYPE, array('mssql', 'oracle', 'db2')) ? 'no_action' : 'cascade'
				)
			)),
			sort_array($schema->getKeys('users_groups', 'foreign'))
		);

		$expected = array('id', 'group_id');
		sort($expected);
		$result = $schema->getKeys('users_groups', 'primary');
		sort($result);

		$this->assertEquals(
			$expected,
			$result
		);
	}

	public function testRenameColumnPartOfTableLevelForeignKey()
	{
		self::$db->translatedQuery('ALTER TABLE groups RENAME COLUMN "group_founder" TO founder');
		$this->rollback_statements[] = "ALTER TABLE groups RENAME COLUMN founder TO group_founder";

		$schema = new fSchema(self::$db);

		$this->assertSame(
			array(
				'group_id',
				'name',
				'group_leader',
				'founder'
			),
			array_keys($schema->getColumnInfo('groups'))
		);

		$this->assertEquals(
			sort_array(array(
				array(
					'column'         => 'group_leader',
					'foreign_table'  => 'users',
					'foreign_column' => 'user_id',
					'on_delete'      => 'cascade',
					'on_update'      => DB_TYPE == 'oracle' || DB_TYPE == 'db2' ? 'no_action' : 'cascade'
				),
				array(
					'column'         => 'founder',
					'foreign_table'  => 'users',
					'foreign_column' => 'user_id',
					'on_delete'      => DB_TYPE == 'mssql' ? 'no_action' : 'cascade',
					'on_update'      => in_array(DB_TYPE, array('mssql', 'oracle', 'db2')) ? 'no_action' : 'cascade'
				)
			)),
			sort_array($schema->getKeys('groups', 'foreign'))
		);

		$this->assertSame(
			array(
				'group_id'
			),
			$schema->getKeys('groups', 'primary')
		);
	}

	public function testRenameColumnPartOfUniqueConstraint()
	{
		self::$db->translatedQuery('ALTER TABLE albums RENAME COLUMN "artist_id" TO artist');
		$this->rollback_statements[] = "ALTER TABLE albums RENAME COLUMN artist TO artist_id";

		$schema = new fSchema(self::$db);

		$this->assertSame(
			array(
				'album_id',
				'name',
				'year_released',
				'msrp',
				'genre',
				'artist'
			),
			array_keys($schema->getColumnInfo('albums'))
		);

		$this->assertSame(
			array(
				array(
					'column'         => 'artist',
					'foreign_table'  => 'artists',
					'foreign_column' => 'artist_id',
					'on_delete'      => 'cascade',
					'on_update'      => DB_TYPE == 'oracle' || DB_TYPE == 'db2' ? 'no_action' : 'cascade'
				)
			),
			$schema->getKeys('albums', 'foreign')
		);

		$this->assertSame(
			array(
				'album_id'
			),
			$schema->getKeys('albums', 'primary')
		);

		$this->assertEquals(
			sort_array(array(
				array('artist', 'name')
			)),
			sort_array($schema->getKeys('albums', 'unique'))
		);
	}

	public function testRenameColumnPartOfIndex()
	{
		self::$db->translatedQuery('ALTER TABLE artists RENAME COLUMN name TO nam');
		$this->rollback_statements[] = "ALTER TABLE artists RENAME COLUMN nam TO name";

		$schema = new fSchema(self::$db);

		$this->assertSame(
			sort_array(array(
				'artist_id',
				'nam'
			)),
			sort_array(array_keys($schema->getColumnInfo('artists')))
		);

		$this->assertSame(
			array(),
			$schema->getKeys('artists', 'foreign')
		);

		$this->assertSame(
			array(
				'artist_id'
			),
			$schema->getKeys('artists', 'primary')
		);

		$this->assertSame(
			array(
				array('nam')
			),
			$schema->getKeys('artists', 'unique')
		);
	}

	public function testRenameColumnWithCheckConstraint()
	{
		self::$db->translatedQuery('ALTER TABLE users RENAME COLUMN status TO visibility');
		$this->rollback_statements[] = "ALTER TABLE users RENAME COLUMN visibility TO status";

		$schema = new fSchema(self::$db);

		$this->assertSame(
			array(
				'user_id',
				'first_name',
				'middle_initial',
				'last_name',
				'email_address',
				'visibility',
				'times_logged_in',
				'date_created',
				'birthday',
				'time_of_last_login',
				'is_validated',
				'hashed_password'
			),
			array_keys($schema->getColumnInfo('users'))
		);

		$this->assertEquals(
			array(
				'type'           => 'varchar',
				'placeholder'    => '%s',
				'not_null'       => TRUE,
				'default'        => 'Active',
				'valid_values'   => array('Active', 'Inactive', 'Pending'),
				// For MySQL the length is calculated by the maximum length of the enumerated values
				'max_length'     => 8,
				'max_value'      => NULL,
				'min_value'      => NULL,
				'decimal_places' => NULL,
				'auto_increment' => FALSE,
				'comment'        => ''
			),
			$schema->getColumnInfo('users', 'visibility')
		);
	}

	public function testAddColumnVarchar()
	{
		self::$db->translatedQuery('ALTER TABLE users ADD COLUMN "reset_code" VARCHAR(16)');
		$this->rollback_statements[] = "ALTER TABLE users DROP COLUMN reset_code";

		$schema = new fSchema(self::$db);

		$this->assertSame(
			array(
				'user_id',
				'first_name',
				'middle_initial',
				'last_name',
				'email_address',
				'status',
				'times_logged_in',
				'date_created',
				'birthday',
				'time_of_last_login',
				'is_validated',
				'hashed_password',
				'reset_code'
			),
			array_keys($schema->getColumnInfo('users'))
		);
		$this->assertEquals(
			array(
				'type'           => 'varchar',
				'placeholder'    => '%s',
				'not_null'       => FALSE,
				'default'        => NULL,
				'valid_values'   => NULL,
				'max_length'     => 16,
				'max_value'      => NULL,
				'min_value'      => NULL,
				'decimal_places' => NULL,
				'auto_increment' => FALSE,
				'comment'        => ''
			),
			$schema->getColumnInfo('users', 'reset_code')
		);
		$this->assertEquals(
			sort_array(array(
				array('email_address')
			)),
			sort_array($schema->getKeys('users', 'unique'))
		);
	}

	public function testAddColumnBigint()
	{
		self::$db->translatedQuery('ALTER TABLE users ADD COLUMN "reset_code" BIGINT');
		$this->rollback_statements[] = "ALTER TABLE users DROP COLUMN reset_code";

		$schema = new fSchema(self::$db);

		$this->assertSame(
			array(
				'user_id',
				'first_name',
				'middle_initial',
				'last_name',
				'email_address',
				'status',
				'times_logged_in',
				'date_created',
				'birthday',
				'time_of_last_login',
				'is_validated',
				'hashed_password',
				'reset_code'
			),
			array_keys($schema->getColumnInfo('users'))
		);
		$this->assertEquals(
			array(
				'type'           => 'integer',
				'placeholder'    => '%i',
				'not_null'       => FALSE,
				'default'        => NULL,
				'valid_values'   => NULL,
				'max_length'     => NULL,
				'max_value'      => (DB_TYPE != 'sqlite' && DB_TYPE != 'oracle') ? new fNumber('9223372036854775807') : NULL,
				'min_value'      => (DB_TYPE != 'sqlite' && DB_TYPE != 'oracle') ? new fNumber('-9223372036854775808') : NULL,
				'decimal_places' => NULL,
				'auto_increment' => FALSE,
				'comment'        => ''
			),
			$schema->getColumnInfo('users', 'reset_code')
		);
	}

	public function testAddColumnChar()
	{
		self::$db->translatedQuery('ALTER TABLE users ADD COLUMN "reset_code" CHAR(5)');
		$this->rollback_statements[] = "ALTER TABLE users DROP COLUMN reset_code";

		$schema = new fSchema(self::$db);

		$this->assertSame(
			array(
				'user_id',
				'first_name',
				'middle_initial',
				'last_name',
				'email_address',
				'status',
				'times_logged_in',
				'date_created',
				'birthday',
				'time_of_last_login',
				'is_validated',
				'hashed_password',
				'reset_code'
			),
			array_keys($schema->getColumnInfo('users'))
		);
		$this->assertEquals(
			array(
				'type'           => 'char',
				'placeholder'    => '%s',
				'not_null'       => FALSE,
				'default'        => NULL,
				'valid_values'   => NULL,
				'max_length'     => 5,
				'max_value'      => NULL,
				'min_value'      => NULL,
				'decimal_places' => NULL,
				'auto_increment' => FALSE,
				'comment'        => ''
			),
			$schema->getColumnInfo('users', 'reset_code')
		);
	}

	public function testAddColumnText()
	{
		self::$db->translatedQuery('ALTER TABLE users ADD COLUMN "reset_code" TEXT');
		$this->rollback_statements[] = "ALTER TABLE users DROP COLUMN reset_code";

		$schema = new fSchema(self::$db);

		$max_text_length = 0;
		switch (DB_TYPE) {
			case 'sqlite':
				$max_text_length = 1000000000;
				break;
			
			case 'oracle':
				$max_text_length = 4294967295;
				break;
			
			case 'mysql':
				$max_text_length = 4294967295;
				break;
			
			case 'postgresql':
				$max_text_length = 1073741824;
				break;
			
			case 'mssql':
				$max_text_length = 1073741823;
				break;
			
			case 'db2':
				$max_text_length = 1073741824;
				break;
		}

		$this->assertSame(
			array(
				'user_id',
				'first_name',
				'middle_initial',
				'last_name',
				'email_address',
				'status',
				'times_logged_in',
				'date_created',
				'birthday',
				'time_of_last_login',
				'is_validated',
				'hashed_password',
				'reset_code'
			),
			array_keys($schema->getColumnInfo('users'))
		);
		$this->assertEquals(
			array(
				'type'           => 'text',
				'placeholder'    => '%s',
				'not_null'       => FALSE,
				'default'        => NULL,
				'valid_values'   => NULL,
				'max_length'     => $max_text_length,
				'max_value'      => NULL,
				'min_value'      => NULL,
				'decimal_places' => NULL,
				'auto_increment' => FALSE,
				'comment'        => ''
			),
			$schema->getColumnInfo('users', 'reset_code')
		);
	}

	public function testAddColumnBlob()
	{
		self::$db->translatedQuery('ALTER TABLE users ADD COLUMN "reset_code" BLOB');
		$this->rollback_statements[] = "ALTER TABLE users DROP COLUMN reset_code";

		$schema = new fSchema(self::$db);

		$max_blob_length = 0;
		switch (DB_TYPE) {
			case 'sqlite':
				$max_blob_length = 1000000000;
				break;
			
			case 'oracle':
				$max_blob_length = 4294967295;
				break;
			
			case 'mysql':
				$max_blob_length = 4294967295;
				break;
			
			case 'postgresql':
				$max_blob_length = 1073741824;
				break;
			
			case 'mssql':
				$max_blob_length = 2147483647;
				break;
			
			case 'db2':
				$max_blob_length = 2147483647;
				break;
		}

		$this->assertSame(
			array(
				'user_id',
				'first_name',
				'middle_initial',
				'last_name',
				'email_address',
				'status',
				'times_logged_in',
				'date_created',
				'birthday',
				'time_of_last_login',
				'is_validated',
				'hashed_password',
				'reset_code'
			),
			array_keys($schema->getColumnInfo('users'))
		);
		$this->assertEquals(
			array(
				'type'           => 'blob',
				'placeholder'    => '%l',
				'not_null'       => FALSE,
				'default'        => NULL,
				'valid_values'   => NULL,
				'max_length'     => $max_blob_length,
				'max_value'      => NULL,
				'min_value'      => NULL,
				'decimal_places' => NULL,
				'auto_increment' => FALSE,
				'comment'        => ''
			),
			$schema->getColumnInfo('users', 'reset_code')
		);
	}

	public function testAddColumnBoolean()
	{
		self::$db->translatedQuery('ALTER TABLE users ADD COLUMN "reset_code" BOOLEAN NOT NULL DEFAULT TRUE');
		$this->rollback_statements[] = "ALTER TABLE users DROP COLUMN reset_code";

		$schema = new fSchema(self::$db);

		$this->assertSame(
			array(
				'user_id',
				'first_name',
				'middle_initial',
				'last_name',
				'email_address',
				'status',
				'times_logged_in',
				'date_created',
				'birthday',
				'time_of_last_login',
				'is_validated',
				'hashed_password',
				'reset_code'
			),
			array_keys($schema->getColumnInfo('users'))
		);
		$this->assertEquals(
			array(
				'type'           => 'boolean',
				'placeholder'    => '%b',
				'not_null'       => TRUE,
				'default'        => TRUE,
				'valid_values'   => NULL,
				'max_length'     => NULL,
				'max_value'      => NULL,
				'min_value'      => NULL,
				'decimal_places' => NULL,
				'auto_increment' => FALSE,
				'comment'        => ''
			),
			$schema->getColumnInfo('users', 'reset_code')
		);
	}

	public function testAddColumnDate()
	{
		self::$db->translatedQuery('ALTER TABLE users ADD COLUMN "reset_code" DATE');
		$this->rollback_statements[] = "ALTER TABLE users DROP COLUMN reset_code";

		$schema = new fSchema(self::$db);

		$this->assertSame(
			array(
				'user_id',
				'first_name',
				'middle_initial',
				'last_name',
				'email_address',
				'status',
				'times_logged_in',
				'date_created',
				'birthday',
				'time_of_last_login',
				'is_validated',
				'hashed_password',
				'reset_code'
			),
			array_keys($schema->getColumnInfo('users'))
		);

		$type        = 'date';
		$placeholder = '%d';
		if (DB_TYPE == 'mssql' && version_compare(self::$db->getVersion(), 10.0, '<')) {
			$type        = 'timestamp';
			$placeholder = '%p';
		}

		$this->assertEquals(
			array(
				'type'           => $type,
				'placeholder'    => $placeholder,
				'not_null'       => FALSE,
				'default'        => NULL,
				'valid_values'   => NULL,
				'max_length'     => NULL,
				'max_value'      => NULL,
				'min_value'      => NULL,
				'decimal_places' => NULL,
				'auto_increment' => FALSE,
				'comment'        => ''
			),
			$schema->getColumnInfo('users', 'reset_code')
		);
	}

	public function testAddColumnTime()
	{
		self::$db->translatedQuery('ALTER TABLE users ADD COLUMN "reset_code" TIME');
		$this->rollback_statements[] = "ALTER TABLE users DROP COLUMN reset_code";

		$schema = new fSchema(self::$db);

		$this->assertSame(
			array(
				'user_id',
				'first_name',
				'middle_initial',
				'last_name',
				'email_address',
				'status',
				'times_logged_in',
				'date_created',
				'birthday',
				'time_of_last_login',
				'is_validated',
				'hashed_password',
				'reset_code'
			),
			array_keys($schema->getColumnInfo('users'))
		);
		
		$type        = 'time';
		$placeholder = '%t';

		if (DB_TYPE == 'oracle' || (DB_TYPE == 'mssql' && version_compare(self::$db->getVersion(), 10.0, '<'))) {
			$type        = 'timestamp';
			$placeholder = '%p';
		}

		$this->assertEquals(
			array(
				'type'           => $type,
				'placeholder'    => $placeholder,
				'not_null'       => FALSE,
				'default'        => NULL,
				'valid_values'   => NULL,
				'max_length'     => NULL,
				'max_value'      => NULL,
				'min_value'      => NULL,
				'decimal_places' => NULL,
				'auto_increment' => FALSE,
				'comment'        => ''
			),
			$schema->getColumnInfo('users', 'reset_code')
		);
	}

	public function testAddColumnTimestamp()
	{
		self::$db->translatedQuery('ALTER TABLE users ADD COLUMN "reset_code" TIMESTAMP');
		$this->rollback_statements[] = "ALTER TABLE users DROP COLUMN reset_code";

		$schema = new fSchema(self::$db);

		$this->assertSame(
			array(
				'user_id',
				'first_name',
				'middle_initial',
				'last_name',
				'email_address',
				'status',
				'times_logged_in',
				'date_created',
				'birthday',
				'time_of_last_login',
				'is_validated',
				'hashed_password',
				'reset_code'
			),
			array_keys($schema->getColumnInfo('users'))
		);
		$this->assertEquals(
			array(
				'type'           => 'timestamp',
				'placeholder'    => '%p',
				'not_null'       => FALSE,
				'default'        => NULL,
				'valid_values'   => NULL,
				'max_length'     => NULL,
				'max_value'      => NULL,
				'min_value'      => NULL,
				'decimal_places' => NULL,
				'auto_increment' => FALSE,
				'comment'        => ''
			),
			$schema->getColumnInfo('users', 'reset_code')
		);
	}

	public function testAddColumnAutoIncrement()
	{
		self::$db->translatedQuery('ALTER TABLE blobs DROP PRIMARY KEY');
		$this->rollback_statements[] = "ALTER TABLE blobs ADD PRIMARY KEY (blob_id)";
		self::$db->translatedQuery('ALTER TABLE blobs ADD COLUMN id INTEGER AUTOINCREMENT PRIMARY KEY');
		$this->rollback_statements[] = "ALTER TABLE blobs DROP COLUMN id";

		$schema = new fSchema(self::$db);

		$this->assertSame(
			sort_array(array(
				'blob_id',
				'data',
				'id'
			)),
			sort_array(array_keys($schema->getColumnInfo('blobs')))
		);
		$this->assertEquals(
			array(
				'type'           => 'integer',
				'placeholder'    => '%i',
				'not_null'       => TRUE,
				'default'        => NULL,
				'valid_values'   => NULL,
				'max_length'     => NULL,
				'max_value'      => (DB_TYPE != 'sqlite' && DB_TYPE != 'oracle') ? new fNumber(2147483647) : NULL,
				'min_value'      => (DB_TYPE != 'sqlite' && DB_TYPE != 'oracle') ? new fNumber(-2147483648) : NULL,
				'decimal_places' => NULL,
				'auto_increment' => TRUE,
				'comment'        => ''
			),
			$schema->getColumnInfo('blobs', 'id')
		);


		$this->assertEquals(
			array(1),
			array_map('current', self::$db->query("SELECT id FROM blobs")->fetchAllRows())
		);

		self::$db->query("INSERT INTO blobs (blob_id, data) VALUES (%i, %l)", 2, 'foobar');
		$this->assertEquals(
			array(1, 2),
			array_map('current', self::$db->query("SELECT id FROM blobs")->fetchAllRows())
		);
	}

	public function testAddColumnForeignKey()
	{
		self::$db->translatedQuery('ALTER TABLE blobs ADD COLUMN id INTEGER REFERENCES "users"(user_id) ON DELETE CASCADE');
		$this->rollback_statements[] = "ALTER TABLE blobs DROP COLUMN id";
		
		$schema = new fSchema(self::$db);

		$this->assertSame(
			sort_array(array(
				'blob_id',
				'data',
				'id'
			)),
			sort_array(array_keys($schema->getColumnInfo('blobs')))
		);
		$this->assertEquals(
			array(
				'type'           => 'integer',
				'placeholder'    => '%i',
				'not_null'       => FALSE,
				'default'        => NULL,
				'valid_values'   => NULL,
				'max_length'     => NULL,
				'max_value'      => (DB_TYPE != 'sqlite' && DB_TYPE != 'oracle') ? new fNumber(2147483647) : NULL,
				'min_value'      => (DB_TYPE != 'sqlite' && DB_TYPE != 'oracle') ? new fNumber(-2147483648) : NULL,
				'decimal_places' => NULL,
				'auto_increment' => FALSE,
				'comment'        => ''
			),
			$schema->getColumnInfo('blobs', 'id')
		);

		$this->assertSame(
			array(
				array(
					'column'         => 'id',
					'foreign_table'  => 'users',
					'foreign_column' => 'user_id',
					'on_delete'      => 'cascade',
					'on_update'      => 'no_action'
				)
			),
			$schema->getKeys('blobs', 'foreign')
		);
	}

	public function testAddColumnAfterConstraint()
	{
		self::$db->translatedQuery('ALTER TABLE albums ADD COLUMN total_sales INTEGER NOT NULL DEFAULT 0');
		$this->rollback_statements[] = "ALTER TABLE albums DROP COLUMN total_sales";

		$schema = new fSchema(self::$db);

		$this->assertSame(
			array(
				'album_id',
				'name',
				'year_released',
				'msrp',
				'genre',
				'artist_id',
				'total_sales'
			),
			array_keys($schema->getColumnInfo('albums'))
		);
		$this->assertEquals(
			array(
				'type'           => 'integer',
				'placeholder'    => '%i',
				'not_null'       => TRUE,
				'default'        => 0,
				'valid_values'   => NULL,
				'max_length'     => NULL,
				'max_value'      => (DB_TYPE != 'sqlite' && DB_TYPE != 'oracle') ? new fNumber(2147483647) : NULL,
				'min_value'      => (DB_TYPE != 'sqlite' && DB_TYPE != 'oracle') ? new fNumber(-2147483648) : NULL,
				'decimal_places' => NULL,
				'auto_increment' => FALSE,
				'comment'        => ''
			),
			$schema->getColumnInfo('albums', 'total_sales')
		);
	}

	public function testDropForeignKey()
	{
		self::$db->translatedQuery("ALTER TABLE groups DROP FOREIGN KEY (group_leader)");
		$this->rollback_statements[] = "ALTER TABLE groups ADD FOREIGN KEY (group_leader) REFERENCES users(user_id) ON UPDATE CASCADE ON DELETE CASCADE";

		$schema = new fSchema(self::$db);

		$this->assertSame(
			array(
				array(
					'column'         => 'group_founder',
					'foreign_table'  => 'users',
					'foreign_column' => 'user_id',
					'on_delete'      => DB_TYPE == 'mssql' ? 'no_action' : 'cascade',
					'on_update'      => in_array(DB_TYPE, array('mssql', 'oracle', 'db2')) ? 'no_action' : 'cascade'
				)
			),
			$schema->getKeys('groups', 'foreign')
		);
	}

	public function testDropMultipleForeignKeys()
	{
		self::$db->translatedQuery("ALTER TABLE groups DROP FOREIGN KEY (group_leader)");
		$this->rollback_statements[] = "ALTER TABLE groups ADD FOREIGN KEY (group_leader) REFERENCES users(user_id) ON UPDATE CASCADE ON DELETE CASCADE";

		self::$db->translatedQuery("ALTER TABLE groups DROP FOREIGN KEY (group_founder)");
		if (DB_TYPE == 'mssql') {
			$this->rollback_statements[] = "ALTER TABLE groups ADD FOREIGN KEY (group_founder) REFERENCES users(user_id) ON UPDATE NO ACTION ON DELETE NO ACTION";
		} else {
			$this->rollback_statements[] = "ALTER TABLE groups ADD FOREIGN KEY (group_founder) REFERENCES users(user_id) ON UPDATE CASCADE ON DELETE CASCADE";
		}

		$schema = new fSchema(self::$db);

		$this->assertSame(
			array(),
			$schema->getKeys('groups', 'foreign')
		);
	}

	public function testAddForeignKey()
	{
		self::$db->translatedQuery("ALTER TABLE groups DROP FOREIGN KEY (group_leader)");
		$this->rollback_statements[] = "ALTER TABLE groups ADD FOREIGN KEY (group_leader) REFERENCES users(user_id) ON UPDATE CASCADE ON DELETE CASCADE";

		self::$db->translatedQuery("ALTER TABLE groups ADD FOREIGN KEY (group_leader) REFERENCES users(user_id) ON UPDATE CASCADE ON DELETE CASCADE");
		$this->rollback_statements[] = "ALTER TABLE groups DROP FOREIGN KEY (group_leader)";

		$schema = new fSchema(self::$db);

		$this->assertEquals(
			sort_array(array(
				array(
					'column'         => 'group_leader',
					'foreign_table'  => 'users',
					'foreign_column' => 'user_id',
					'on_delete'      => 'cascade',
					'on_update'      => DB_TYPE == 'oracle' || DB_TYPE == 'db2' ? 'no_action' : 'cascade'
				),
				array(
					'column'         => 'group_founder',
					'foreign_table'  => 'users',
					'foreign_column' => 'user_id',
					'on_delete'      => DB_TYPE == 'mssql' ? 'no_action' : 'cascade',
					'on_update'      => in_array(DB_TYPE, array('mssql', 'oracle', 'db2')) ? 'no_action' : 'cascade'
				)
			)),
			sort_array($schema->getKeys('groups', 'foreign'))
		);
	}




	public static function badSqlProvider()
	{
		$output   = array();
		$output[] = array("COMMENT ON COLUMN foo.bar IS 'testing'");
		$output[] = array("COMMENT ON COLUMN users.bar IS 'testing'");
		$output[] = array("ALTER TABLE foo RENAME TO bar");
		$output[] = array("ALTER TABLE users RENAME TO groups");
		$output[] = array("ALTER TABLE users RENAME COLUMN fooser_id TO fooser");
		$output[] = array("ALTER TABLE users RENAME COLUMN user_id TO first_name");
		$output[] = array("ALTER TABLE users ADD COLUMN not_null");
		$output[] = array("ALTER TABLE foo DROP COLUMN bar");
		$output[] = array("ALTER TABLE users DROP COLUMN id");
		$output[] = array("ALTER TABLE foo ALTER COLUMN id TYPE VARCHAR(10)");
		$output[] = array("ALTER TABLE users ALTER COLUMN id TYPE VARCHAR(10)");
		$output[] = array("ALTER TABLE foo ALTER COLUMN id DROP DEFAULT");
		$output[] = array("ALTER TABLE users ALTER COLUMN id DROP DEFAULT");
		$output[] = array("ALTER TABLE foo ALTER COLUMN id SET DEFAULT 1");
		$output[] = array("ALTER TABLE users ALTER COLUMN id SET DEFAULT 1");
		$output[] = array("ALTER TABLE foo ALTER COLUMN id DROP NOT NULL");
		$output[] = array("ALTER TABLE users ALTER COLUMN id DROP NOT NULL");
		$output[] = array("ALTER TABLE foo ALTER COLUMN id SET NOT NULL");
		$output[] = array("ALTER TABLE users ALTER COLUMN id SET NOT NULL");
		$output[] = array("ALTER TABLE foo ALTER COLUMN id DROP CHECK");
		$output[] = array("ALTER TABLE users ALTER COLUMN id DROP CHECK");
		$output[] = array("ALTER TABLE foo ALTER COLUMN id SET CHECK IN ('foo', 'bar')");
		$output[] = array("ALTER TABLE users ALTER COLUMN id SET CHECK IN ('foo', 'bar')");
		$output[] = array("ALTER TABLE foo DROP PRIMARY KEY");
		$output[] = array("ALTER TABLE albums ADD PRIMARY KEY (name)");
		$output[] = array("ALTER TABLE foo DROP FOREIGN KEY (id)");
		$output[] = array("ALTER TABLE users DROP FOREIGN KEY (id)");
		$output[] = array("ALTER TABLE users DROP FOREIGN KEY (first_name)");
		$output[] = array("ALTER TABLE foo ADD FOREIGN KEY (id) REFERENCES users(user_id)");
		$output[] = array("ALTER TABLE users ADD FOREIGN KEY (id) REFERENCES users(user_id)");
		$output[] = array("ALTER TABLE blobs ADD FOREIGN KEY (blob_id) REFERENCES foo(id)");
		$output[] = array("ALTER TABLE blobs ADD FOREIGN KEY (blob_id) REFERENCES users(id)");
		$output[] = array("ALTER TABLE foo DROP UNIQUE (id)");
		$output[] = array("ALTER TABLE users DROP UNIQUE (id)");
		$output[] = array("ALTER TABLE users DROP UNIQUE(first_name)");
		$output[] = array('ALTER TABLE users DROP UNIQUE(first_name, "last_name")');
		$output[] = array('ALTER TABLE users DROP UNIQUE(first_name, unknown_column)');
		return $output;
	}
	
	/**
	 * @dataProvider badSqlProvider
	 */
	public function testBadSql($query)
	{
		$this->setExpectedException('fSQLException');
		
		try {
			self::$db->translatedExecute($query);

			throw new Exception('SQL did not fail');

		} catch (fSQLException $e) {
			
			$schema = new fSchema(self::$db);
			
			$tables = array("albums", "artists", "groups", "owns_on_cd", "owns_on_tape", "songs", "users", "users_groups");

			foreach ($tables as $table) {
				$schema_column_info = self::$json_schema['column_info'][$table];
				foreach ($schema_column_info as $col => &$info) {
					ksort($info);
				}
				ksort($schema_column_info);
				
				$column_info = $schema->getColumnInfo($table);
				foreach ($column_info as $col => &$info) {
					ksort($info);
					foreach ($info as $key => $value) {
						if ($value instanceof fNumber) {
							$info[$key] = $value->__toString();	
						}
					}
				}
				ksort($column_info);
				
				$this->assertEquals(
					$schema_column_info,
					$column_info
				);

				$schema_keys = self::$json_schema['keys'][$table];
				foreach ($schema_keys as $type => &$list) {
					$list = sort_array($list);	
				}
				ksort($schema_keys);
				
				$keys = $schema->getKeys($table);
				foreach ($keys as $type => &$list) {
					$list = sort_array($list);
				}
				ksort($keys);
				
				$this->assertEquals(
					$schema_keys,
					$keys
				);
			}

			throw $e;
		}
	}


	public function testDropColumn()
	{
		self::$db->translatedQuery('ALTER TABLE users DROP COLUMN "hashed_password"');
		$this->rollback_statements[] = "COMMENT ON COLUMN users.hashed_password IS 'This hash is generated using fCryptography::hashPassword()'";
		$this->rollback_statements[] = "ALTER TABLE users ALTER COLUMN hashed_password SET NOT NULL";
		$this->rollback_statements[] = "UPDATE users SET hashed_password = '5527939aca3e9e80d5ab3bee47391f0f' WHERE user_id = 1";
		$this->rollback_statements[] = "UPDATE users SET hashed_password = 'a722c63db8ec8625af6cf71cb8c2d939' WHERE user_id = 2";
		$this->rollback_statements[] = "UPDATE users SET hashed_password = 'c1572d05424d0ecb2a65ec6a82aeacbf' WHERE user_id = 3";
		$this->rollback_statements[] = "UPDATE users SET hashed_password = '3afc79b597f88a72528e864cf81856d2' WHERE user_id = 4";
		$this->rollback_statements[] = "ALTER TABLE users ADD COLUMN hashed_password VARCHAR(100)";

		$schema = new fSchema(self::$db);

		$this->assertSame(
			array(
				'user_id',
				'first_name',
				'middle_initial',
				'last_name',
				'email_address',
				'status',
				'times_logged_in',
				'date_created',
				'birthday',
				'time_of_last_login',
				'is_validated'
			),
			array_keys($schema->getColumnInfo('users'))
		);
		$this->assertSame(
			NULL,
			$schema->getColumnInfo('users', 'is_validated', 'comment')
		);
	}

	public function testDropColumn2()
	{
		self::$db->translatedQuery('ALTER TABLE users DROP COLUMN is_validated');
		$this->rollback_statements[] = "UPDATE users SET is_validated = TRUE WHERE user_id <= 3";
		$this->rollback_statements[] = "ALTER TABLE users ADD COLUMN is_validated BOOLEAN NOT NULL DEFAULT FALSE";

		$schema = new fSchema(self::$db);

		$this->assertEquals(
			array(
				'user_id',
				'first_name',
				'middle_initial',
				'last_name',
				'email_address',
				'status',
				'times_logged_in',
				'date_created',
				'birthday',
				'time_of_last_login',
				'hashed_password'
			),
			array_keys($schema->getColumnInfo('users'))
		);

		$this->assertSame(
			'This hash is generated using fCryptography::hashPassword()',
			$schema->getColumnInfo('users', 'hashed_password', 'comment')
		);
		$this->assertSame(
			'When the user last logged in',
			$schema->getColumnInfo('users', 'time_of_last_login', 'comment')
		);
	}

	public function testDropColumn3()
	{
		self::$db->translatedQuery('ALTER TABLE users DROP COLUMN time_of_last_login');
		$this->rollback_statements[] = self::$db->escape("UPDATE users SET time_of_last_login = %t WHERE user_id = 1", '17:00:00');
		$this->rollback_statements[] = self::$db->escape("UPDATE users SET time_of_last_login = %t WHERE user_id = 2", '12:00:00');
		$this->rollback_statements[] = "ALTER TABLE users ADD COLUMN time_of_last_login TIME";

		$schema = new fSchema(self::$db);

		$this->assertEquals(
			sort_array(array(
				'user_id',
				'first_name',
				'middle_initial',
				'last_name',
				'email_address',
				'status',
				'times_logged_in',
				'date_created',
				'birthday',
				'is_validated',
				'hashed_password'
			)),
			sort_array(array_keys($schema->getColumnInfo('users')))
		);
		$this->assertSame(
			'The birthday',
			$schema->getColumnInfo('users', 'birthday', 'comment')
		);
	}

	public function testDropColumnCheckConstraint()
	{
		self::$db->translatedQuery('ALTER TABLE users DROP COLUMN "status"');
		$this->rollback_statements[] = "UPDATE users SET status = 'Inactive' WHERE user_id = 3";
		$this->rollback_statements[] = "ALTER TABLE users ADD COLUMN status VARCHAR(8) NOT NULL DEFAULT 'Active' CHECK(status IN ('Active', 'Inactive', 'Pending'))";

		$schema = new fSchema(self::$db);

		$this->assertEquals(
			sort_array(array(
				'user_id',
				'first_name',
				'middle_initial',
				'last_name',
				'email_address',
				'times_logged_in',
				'date_created',
				'birthday',
				'time_of_last_login',
				'is_validated',
				'hashed_password'
			)),
			sort_array(array_keys($schema->getColumnInfo('users')))
		);
	}

	public function testDropForeignKeyColumn()
	{
		self::$db->translatedQuery('ALTER TABLE "groups" DROP COLUMN "group_founder"');
		$this->rollback_statements[] = "ALTER TABLE groups ALTER COLUMN group_founder SET NOT NULL";
		$this->rollback_statements[] = "UPDATE groups SET group_founder = 2";
		if (DB_TYPE == 'mssql') {
			$this->rollback_statements[] = "ALTER TABLE groups ADD COLUMN group_founder INTEGER REFERENCES users(user_id) ON UPDATE NO ACTION ON DELETE NO ACTION";
		} else {
			$this->rollback_statements[] = "ALTER TABLE groups ADD COLUMN group_founder INTEGER REFERENCES users(user_id) ON UPDATE CASCADE ON DELETE CASCADE";
		}

		$schema = new fSchema(self::$db);

		$this->assertSame(
			array(
				'group_id',
				'name',
				'group_leader'
			),
			array_keys($schema->getColumnInfo('groups'))
		);
		$this->assertSame(
			array(
				array(
					'column'         => 'group_leader',
					'foreign_table'  => 'users',
					'foreign_column' => 'user_id',
					'on_delete'      => 'cascade',
					'on_update'      => DB_TYPE == 'oracle' || DB_TYPE == 'db2' ? 'no_action' : 'cascade'
				)
			),
			$schema->getKeys('groups', 'foreign')
		);
	}

	public function testDropPrimaryKeyColumn()
	{
		self::$db->translatedQuery('ALTER TABLE "groups" DROP COLUMN "group_id"');
		if (DB_TYPE == 'mssql') {
			$this->rollback_statements[] = "ALTER TABLE users_groups ADD FOREIGN KEY (group_id) REFERENCES groups(group_id) ON UPDATE NO ACTION ON DELETE NO ACTION";
		} else {
			$this->rollback_statements[] = "ALTER TABLE users_groups ADD FOREIGN KEY (group_id) REFERENCES groups(group_id) ON UPDATE CASCADE ON DELETE CASCADE";
		}
		$this->rollback_statements[] = "ALTER TABLE groups ADD COLUMN group_id INTEGER AUTOINCREMENT PRIMARY KEY";

		$schema = new fSchema(self::$db);

		$this->assertSame(
			array(
				'name',
				'group_leader',
				'group_founder'
			),
			array_keys($schema->getColumnInfo('groups'))
		);
		$this->assertSame(
			array(
				array(
					'column'         => 'user_id',
					'foreign_table'  => 'users',
					'foreign_column' => 'user_id',
					'on_delete'      => 'cascade',
					'on_update'      => DB_TYPE == 'oracle' || DB_TYPE == 'db2' ? 'no_action' : 'cascade'
				)
			),
			$schema->getKeys('users_groups', 'foreign')
		);
		$this->assertEquals(
			6,
			self::$db->query("SELECT count(*) FROM users_groups")->fetchScalar()
		);
	}

	public function testDropForeignKeyColumnPartOfUniqueConstraint()
	{
		self::$db->translatedQuery('ALTER TABLE "albums" DROP COLUMN "artist_id"');
		$this->rollback_statements[] = "ALTER TABLE albums ALTER COLUMN artist_id SET NOT NULL";
		$this->rollback_statements[] = "UPDATE albums SET artist_id = 3 WHERE album_id IN (4, 5, 6, 7)";
		$this->rollback_statements[] = "UPDATE albums SET artist_id = 2 WHERE album_id IN (2, 3)";
		$this->rollback_statements[] = "UPDATE albums SET artist_id = 1 WHERE album_id = 1";
		$this->rollback_statements[] = "ALTER TABLE albums ADD COLUMN artist_id INTEGER REFERENCES artists(artist_id) ON DELETE CASCADE";

		$schema = new fSchema(self::$db);

		$this->assertSame(
			array(
				'album_id',
				'name',
				'year_released',
				'msrp',
				'genre'
			),
			array_keys($schema->getColumnInfo('albums'))
		);
		$this->assertSame(
			array(),
			$schema->getKeys('albums', 'foreign')
		);
		$this->assertSame(
			array(),
			$schema->getKeys('albums', 'unique')
		);
		$this->assertEquals(
			7,
			self::$db->query("SELECT count(*) FROM albums")->fetchScalar()
		);
	}


	public function testAlterTypeVarcharLength()
	{
		self::$db->translatedQuery("ALTER TABLE users ALTER COLUMN status TYPE VARCHAR(50)");
		$this->rollback_statements[] = "ALTER TABLE users RENAME COLUMN status2 TO status";
		$this->rollback_statements[] = "ALTER TABLE users DROP COLUMN status";
		$this->rollback_statements[] = "UPDATE users SET status2 = status";
		$this->rollback_statements[] = "ALTER TABLE users ADD COLUMN status2 VARCHAR(8) NOT NULL DEFAULT 'Active' CHECK(status2 IN ('Active', 'Inactive', 'Pending'))";

		$schema = new fSchema(self::$db);

		$this->assertEquals(
			array(
				'type'           => 'varchar',
				'placeholder'    => '%s',
				'not_null'       => TRUE,
				'default'        => 'Active',
				'valid_values'   => array('Active', 'Inactive', 'Pending'),
				// For MySQL the length is calculated by the maximum length of the enumerated values
				'max_length'     => DB_TYPE == 'mysql' ? 8 : 50,
				'max_value'      => NULL,
				'min_value'      => NULL,
				'decimal_places' => NULL,
				'auto_increment' => FALSE,
				'comment'        => ''
			),
			$schema->getColumnInfo('users', 'status')
		);
	}

	public function testAlterTypeDateToTimestamp()
	{
		self::$db->translatedQuery("ALTER TABLE users ALTER COLUMN birthday TYPE TIMESTAMP");
		$this->rollback_statements[] = "ALTER TABLE users RENAME COLUMN birthday2 TO birthday";
		$this->rollback_statements[] = "ALTER TABLE users DROP COLUMN birthday";
		$this->rollback_statements[] = "UPDATE users SET birthday2 = CAST(birthday as DATE)";
		$this->rollback_statements[] = "ALTER TABLE users ADD COLUMN birthday2 DATE";

		$schema = new fSchema(self::$db);

		$this->assertEquals(
			array(
				'type'           => 'timestamp',
				'placeholder'    => '%p',
				'not_null'       => FALSE,
				'default'        => NULL,
				'valid_values'   => NULL,
				'max_length'     => NULL,
				'max_value'      => NULL,
				'min_value'      => NULL,
				'decimal_places' => NULL,
				'auto_increment' => FALSE,
				'comment'        => 'The birthday'
			),
			$schema->getColumnInfo('users', 'birthday')
		);
	}

	public function testAlterTypeIncreaseIntegerSizeUniqueConstraint()
	{
		self::$db->translatedQuery("ALTER TABLE songs ALTER COLUMN track_number TYPE BIGINT");
		$this->rollback_statements[] = "ALTER TABLE songs ADD UNIQUE (track_number, album_id)";
		$this->rollback_statements[] = "ALTER TABLE songs ALTER COLUMN track_number SET NOT NULL";
		$this->rollback_statements[] = "ALTER TABLE songs RENAME COLUMN track_number2 TO track_number";
		$this->rollback_statements[] = "ALTER TABLE songs DROP COLUMN track_number";
		$this->rollback_statements[] = "UPDATE songs SET track_number2 = track_number";
		$this->rollback_statements[] = "ALTER TABLE songs ADD COLUMN track_number2 INTEGER";

		$schema = new fSchema(self::$db);

		$this->assertEquals(
			array(
				'type'           => 'integer',
				'placeholder'    => '%i',
				'not_null'       => TRUE,
				'default'        => NULL,
				'valid_values'   => NULL,
				'max_length'     => NULL,
				'max_value'      => (DB_TYPE != 'sqlite' && DB_TYPE != 'oracle') ? new fNumber('9223372036854775807') : NULL,
				'min_value'      => (DB_TYPE != 'sqlite' && DB_TYPE != 'oracle') ? new fNumber('-9223372036854775808') : NULL,
				'decimal_places' => NULL,
				'auto_increment' => FALSE,
				'comment'        => ''
			),
			$schema->getColumnInfo('songs', 'track_number')
		);
	}

	public function testAlterTypeIncreaseIntegerSizePrimaryKeyConstraint()
	{
		self::$db->translatedQuery("ALTER TABLE blobs ALTER COLUMN blob_id TYPE BIGINT");
		$this->rollback_statements[] = "ALTER TABLE blobs ADD PRIMARY KEY(blob_id)";
		$this->rollback_statements[] = "ALTER TABLE blobs ALTER COLUMN blob_id SET NOT NULL";
		$this->rollback_statements[] = "ALTER TABLE blobs RENAME COLUMN blob_id2 TO blob_id";
		$this->rollback_statements[] = "ALTER TABLE blobs DROP COLUMN blob_id";
		$this->rollback_statements[] = "UPDATE blobs SET blob_id2 = blob_id";
		$this->rollback_statements[] = "ALTER TABLE blobs ADD COLUMN blob_id2 INTEGER";

		$schema = new fSchema(self::$db);

		$this->assertEquals(
			array(
				'type'           => 'integer',
				'placeholder'    => '%i',
				'not_null'       => TRUE,
				'default'        => NULL,
				'valid_values'   => NULL,
				'max_length'     => NULL,
				'max_value'      => (DB_TYPE != 'sqlite' && DB_TYPE != 'oracle') ? new fNumber('9223372036854775807') : NULL,
				'min_value'      => (DB_TYPE != 'sqlite' && DB_TYPE != 'oracle') ? new fNumber('-9223372036854775808') : NULL,
				'decimal_places' => NULL,
				'auto_increment' => DB_TYPE == 'sqlite' ? TRUE : FALSE,
				'comment'        => ''
			),
			$schema->getColumnInfo('blobs', 'blob_id')
		);
	}

	public function testAlterTypeIncreaseDecimalSize()
	{
		self::$db->translatedQuery('ALTER TABLE albums ALTER COLUMN "msrp" TYPE DECIMAL(12,2)');
		$this->rollback_statements[] = "ALTER TABLE albums ALTER COLUMN msrp SET NOT NULL";
		$this->rollback_statements[] = "ALTER TABLE albums RENAME COLUMN msrp2 TO msrp";
		$this->rollback_statements[] = "ALTER TABLE albums DROP COLUMN msrp";
		$this->rollback_statements[] = "UPDATE albums SET msrp2 = msrp";
		$this->rollback_statements[] = "ALTER TABLE albums ADD COLUMN msrp2 DECIMAL(10,2)";

		$schema = new fSchema(self::$db);

		$this->assertEquals(
			array(
				'type'           => 'float',
				'placeholder'    => '%f',
				'not_null'       => TRUE,
				'default'        => NULL,
				'valid_values'   => NULL,
				'max_length'     => NULL,
				'max_value'      => new fNumber('9999999999.99'),
				'min_value'      => new fNumber('-9999999999.99'),
				'decimal_places' => 2,
				'auto_increment' => FALSE,
				'comment'        => ''
			),
			$schema->getColumnInfo('albums', 'msrp')
		);
	}
}