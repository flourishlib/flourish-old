<?php
require_once('./support/init.php');
 
class fSchemaWithMultipleSchemasTest extends PHPUnit_Framework_TestSuite
{
	public static function suite()
	{
		return new fSchemaWithMultipleSchemasTest('fSchemaWithMultipleSchemasTestChild');
	}
 
	protected function setUp()
	{
		$db = new fDatabase(DB_TYPE, DB, DB_USERNAME, DB_PASSWORD, DB_HOST, DB_PORT); 
		
		$result = $db->query(file_get_contents(DB_SETUP_FILE));
		$result = $db->query(file_get_contents(DB_ALTERNATE_SCHEMA_SETUP_FILE));
		
		$this->sharedFixture = array(
			'db' => $db,
			'schema' => fJSON::decode(file_get_contents(DB_ALTERNATE_SCHEMA_SCHEMA_FILE), TRUE)
		);
	}
 
	protected function tearDown()
	{
		$db = $this->sharedFixture['db'];
		$result = $db->query(file_get_contents(DB_ALTERNATE_SCHEMA_TEARDOWN_FILE));
		$result = $db->query(file_get_contents(DB_TEARDOWN_FILE));
	}
}
 
class fSchemaWithMultipleSchemasTestChild extends PHPUnit_Framework_TestCase
{
	public $db;
	public $schema;
	public $schema_obj;
	
	public function setUp()
	{
		$this->db         = $this->sharedFixture['db'];
		$this->schema_obj = new fSchema($this->db);
		$this->schema     = $this->sharedFixture['schema'];
	}
	
	public function tearDown()
	{
			
	}
	
	public function testGetTables()
	{
		$tables = $this->schema_obj->getTables();
		
		$this->assertEquals(
			array(
				"albums",
				"artists",
				"blobs",
				"flourish2.albums",
				"flourish2.artists",
				"flourish2.groups",
				"flourish2.users",
				"flourish2.users_groups",
				"groups",
				"owns_on_cd",
				"owns_on_tape",
				"songs",
				"users",
				"users_groups"
			),
			$tables
		);
	}
	
	public static function getTableProvider()
	{
		$output = array();
		
		$output[] = array("flourish2.albums");
		$output[] = array("flourish2.artists");
		$output[] = array("flourish2.groups");
		$output[] = array("flourish2.users");
		$output[] = array("flourish2.users_groups");
		
		return $output;
	}
	
	/**
	 * @dataProvider getTableProvider
	 */
	public function testGetColumnInfo($table)
	{
		$schema_column_info = $this->schema['column_info'][$table];
		foreach ($schema_column_info as $col => &$info) {
			ksort($info);	
		}
		ksort($schema_column_info);
		
		$column_info = $this->schema_obj->getColumnInfo($table);
		foreach ($column_info as $col => &$info) {
			ksort($info);	
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
		$schema_keys = $this->schema['keys'][$table];
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
		$schema_relationships = $this->schema['relationships'][$table];
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
}