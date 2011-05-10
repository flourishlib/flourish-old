<?php
require_once('./support/init.php');

class fSchemaTest extends PHPUnit_Framework_TestCase
{
	public $schema_obj;

	protected static $db;
	protected static $schema;

	public static function setUpBeforeClass()
	{
		if (defined('SKIPPING')) {
			return;
		}
		$db = new fDatabase(DB_TYPE, DB, DB_USERNAME, DB_PASSWORD, DB_HOST, DB_PORT); 
		$db->execute(file_get_contents(DB_SETUP_FILE));
		
		self::$db     = $db;
		self::$schema = fJSON::decode(file_get_contents(DB_SCHEMA_FILE), TRUE);
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
		$this->schema_obj = new fSchema(self::$db);
	}
	
	public function tearDown()
	{
			
	}
	
	public function testGetTables()
	{
		$schema_tables = self::$schema['tables'];
		sort($schema_tables);
		
		$tables = $this->schema_obj->getTables();
		sort($tables);
		
		$this->assertEquals(
			$schema_tables,
			$tables
		);
	}
	
	public function testGetTablesInCreationOrder()
	{
		$tables = $this->schema_obj->getTables(TRUE);
		
		$this->assertSame(
			array(
				'artists',
				'blobs',
				'users',
				'albums',
				'groups',
				'owns_on_cd',
				'owns_on_tape',
				'songs',
				'users_groups'
			),
			$tables
		);
	}
	
	public function testGetTablesInCreationOrderFiltered()
	{
		$tables = $this->schema_obj->getTables('groups');
		
		$this->assertSame(
			array(
				'groups',
				'users_groups'
			),
			$tables
		);
	}
	
	public function testGetTablesInCreationOrderFiltered2()
	{
		$tables = $this->schema_obj->getTables('users');
		
		$this->assertSame(
			array(
				'users',
				'groups',
				'owns_on_cd',
				'owns_on_tape',
				'users_groups'
			),
			$tables
		);
	}
	
	public static function getTableProvider()
	{
		$output = array();
		
		$output[] = array("albums");
		$output[] = array("artists");
		$output[] = array("groups");
		$output[] = array("owns_on_cd");
		$output[] = array("owns_on_tape");
		$output[] = array("songs");
		$output[] = array("users");
		$output[] = array("users_groups");
		
		return $output;
	}
	
	/**
	 * @dataProvider getTableProvider
	 */
	public function testGetColumnInfo($table)
	{
		$schema_column_info = self::$schema['column_info'][$table];
		foreach ($schema_column_info as $col => &$info) {
			ksort($info);
		}
		ksort($schema_column_info);
		
		$column_info = $this->schema_obj->getColumnInfo($table);
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
	}
	
	/**
	 * @dataProvider getTableProvider
	 */
	public function testGetKeys($table)
	{
		$schema_keys = self::$schema['keys'][$table];
		foreach ($schema_keys as $type => &$list) {
			sort($list);	
		}
		ksort($schema_keys);
		
		$keys = $this->schema_obj->getKeys($table);
		foreach ($keys as $type => &$list) {
			sort($list);
		}
		ksort($keys);
		
		$this->assertEquals(
			$schema_keys,
			$keys
		);
	}
	
	/**
	 * @dataProvider getTableProvider
	 */
	public function testGetRelationships($table)
	{
		$schema_relationships = self::$schema['relationships'][$table];
		foreach ($schema_relationships as $type => &$list) {
			sort($list);	
		}
		ksort($schema_relationships);
		
		$relationships = $this->schema_obj->getRelationships($table);
		foreach ($relationships as $type => &$list) {
			sort($list);
		}
		ksort($relationships);
		
		$this->assertEquals(
			$schema_relationships,
			$relationships
		);
	}
	
	public static function numericRangeProvider()
	{
		$output = array();
		
		if (DB_TYPE != 'sqlite' && DB_TYPE != 'oracle') {
			$output[] = array('albums', 'year_released', "INSERT INTO albums (name, year_released, msrp, artist_id) VALUES ('Test Album', %i, 9.99, 3)");
		}
		$output[] = array('albums', 'msrp', "INSERT INTO albums (name, year_released, msrp, artist_id) VALUES ('Test Album', 2010, %f, 3)");
		
		return $output;
	}
	
	/**
	 * @dataProvider numericRangeProvider
	 */
	public function testNumericRanges($table, $column, $query)
	{
		$info = $this->schema_obj->getColumnInfo($table, $column);
		
		// Concatting the numbers with : prevents PHP 5.3's poor implicit
		// casting of integer-like strings
		$values = array(
			$info['min_value']->sub(1)->__toString() . ':' => TRUE,
			$info['min_value']->__toString() . ':'         => FALSE,
			$info['min_value']->add(3)->__toString() . ':' => FALSE,
			$info['max_value']->sub(1)->__toString() . ':' => FALSE,
			$info['max_value']->__toString() . ':'         => FALSE,
			$info['max_value']->add(1)->__toString() . ':' => TRUE
		);
		
		$bad_value = FALSE;
		
		foreach ($values as $value => $should_catch) {
			$value = substr($value, 0, -1);
			self::$db->query('BEGIN');
			$exception = FALSE;
			try {
				$res = self::$db->query($query, $value);
				
				// Since MySQL silently mutates data to fit into columns, we have to check the actual value
				if (DB_TYPE == 'mysql') {
					$primary_key = current($this->schema_obj->getKeys($table, 'primary'));
					
					$inserted_value = self::$db->query(
						"SELECT %r FROM %r WHERE %r = %i",
						$column,
						$table,
						$primary_key,
						$res->getAutoIncrementedValue()
					)->fetchScalar();
					
					if ($inserted_value != $value) {
						throw new fSQLException();
					}   
				}
				
				// SQLite will never reject a value, so we just hard code the rejection here for the tests
				if (DB_TYPE == 'sqlite' && ($info['max_value']->lt($value) || $info['min_value']->gt($value))) {
					throw new fSQLException();
				}
			
			} catch (fSQLException $e) {
				$exception = TRUE;
			}
			self::$db->query('ROLLBACK');
			if ($should_catch != $exception) {
				$bad_value = TRUE;
			} else {
				$this->assertEquals($should_catch, $exception);
			}
		}
		
		$this->assertEquals(FALSE, $bad_value);
	}
}