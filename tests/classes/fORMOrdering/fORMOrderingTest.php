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
class TopAlbum extends fActiveRecord { }


class fORMOrderingTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$db = new fDatabase(DB_TYPE, DB, DB_USERNAME, DB_PASSWORD, DB_HOST, DB_PORT); 
		$db->query(file_get_contents(DB_SETUP_FILE));
		$db->query(file_get_contents(DB_EXTENDED_SETUP_FILE));
		$this->sharedFixture = $db;
		
		fORMDatabase::attach($this->sharedFixture);
		fORMOrdering::configureOrderingColumn('TopAlbum', 'position');
		fORMOrdering::configureOrderingColumn('FavoriteAlbum', 'position');
	}
	
	
	static public function reorderSingleColumnProvider()
	{
		$output = array();
		// Original order
		//$output[] = array(1, 2, array(1, 4, 5, 6, 2, 3));
		$output[] = array(1, 2, array(4, 1, 5, 6, 2, 3));
		$output[] = array(6, 5, array(1, 4, 5, 6, 3, 2));
		$output[] = array(2, 1, array(4, 1, 5, 6, 2, 3));
		$output[] = array(5, 6, array(1, 4, 5, 6, 3, 2));
		$output[] = array(1, 6, array(4, 5, 6, 2, 3, 1));
		$output[] = array(6, 1, array(3, 1, 4, 5, 6, 2));
		$output[] = array(2, 6, array(1, 5, 6, 2, 3, 4));
		$output[] = array(6, 6, array(1, 4, 5, 6, 2, 3));
		
		return $output;
	}	
	
	/**
	 * @dataProvider reorderSingleColumnProvider
	 */
	public function testReorderSingleColumn($start_position, $end_position, $resulting_order)
	{
		$top_album = new TopAlbum(array('position' => $start_position));
		$top_album->setPosition($end_position);
		$top_album->store();
		
		$expected_result = array();
		foreach ($resulting_order as $index => $album_id) {
			$expected_result[] = array(
				'position' => $index+1,
				'album_id' => $album_id
			);
		}
		
		$actual_result = $this->sharedFixture->translatedQuery("SELECT position, album_id FROM top_albums ORDER BY position ASC")->fetchAllRows();
		
		$this->assertEquals($expected_result, $actual_result);
	}
	
	static public function addSingleColumnProvider()
	{
		$output = array();
		// Original order
		//$output[] = array(NULL, array(1, 4, 5, 6, 2, 3));
		$output[] = array(NULL, array(1, 4, 5, 6, 2, 3, 7));
		$output[] = array(1, array(7, 1, 4, 5, 6, 2, 3));
		$output[] = array(6, array(1, 4, 5, 6, 2, 7, 3));
		$output[] = array(9, array(1, 4, 5, 6, 2, 3, 7));
		
		return $output;
	}
	
	/**
	 * @dataProvider addSingleColumnProvider
	 */
	public function testAddSingleColumn($position, $resulting_order)
	{
		$top_album = new TopAlbum();
		$top_album->setAlbumId(7);
		$top_album->setPosition($position);
		$top_album->store();
		
		$expected_result = array();
		foreach ($resulting_order as $index => $album_id) {
			$expected_result[] = array(
				'position' => $index+1,
				'album_id' => $album_id
			);
		}
		
		$actual_result = $this->sharedFixture->translatedQuery("SELECT position, album_id FROM top_albums ORDER BY position ASC")->fetchAllRows();
		
		$this->assertEquals($expected_result, $actual_result);
	}
	
	
	static public function deleteSingleColumnProvider()
	{
		$output = array();
		// Original order
		//$output[] = array(NULL, array(1, 4, 5, 6, 2, 3));
		$output[] = array(1, array(4, 5, 6, 2, 3));
		$output[] = array(6, array(1, 4, 5, 6, 2));
		$output[] = array(2, array(1, 5, 6, 2, 3));
		$output[] = array(4, array(1, 4, 5, 2, 3));
		
		return $output;
	}
	
	/**
	 * @dataProvider deleteSingleColumnProvider
	 */
	public function testDeleteSingleColumn($position, $resulting_order)
	{
		$top_album = new TopAlbum(array('position' => $position));
		$top_album->delete();
		
		$expected_result = array();
		foreach ($resulting_order as $index => $album_id) {
			$expected_result[] = array(
				'position' => $index+1,
				'album_id' => $album_id
			);
		}
		
		$actual_result = $this->sharedFixture->translatedQuery("SELECT position, album_id FROM top_albums ORDER BY position ASC")->fetchAllRows();
		
		$this->assertEquals($expected_result, $actual_result);
	}
	
	
	static public function reorderMultiColumnProvider()
	{
		$output = array();
		// Original order
		//$output[] = array('will@flourishlib.com', 1, 2, array(2, 1, 3, 7, 4));
		$output[] = array('will@flourishlib.com', 1, 1, array(1, 2, 3, 7, 4));
		$output[] = array('will@flourishlib.com', 2, 2, array(1, 2, 3, 7, 4));
		$output[] = array('will@flourishlib.com', 4, 4, array(2, 1, 3, 4, 7));
		$output[] = array('will@flourishlib.com', 7, 5, array(2, 1, 3, 4, 7));
		$output[] = array('will@flourishlib.com', 2, 5, array(1, 3, 7, 4, 2));
		$output[] = array('will@flourishlib.com', 1, 5, array(2, 3, 7, 4, 1));
		
		return $output;
	}	
	
	/**
	 * @dataProvider reorderMultiColumnProvider
	 */
	public function testReorderMultiColumn($email, $album_id, $end_position, $resulting_order)
	{
		$favorite_album = new FavoriteAlbum(array('email' => $email, 'album_id' => $album_id));
		$favorite_album->setPosition($end_position);
		$favorite_album->store();
		
		$expected_result = array();
		foreach ($resulting_order as $index => $album_id) {
			$expected_result[] = array(
				'email'    => $email,
				'position' => $index+1,
				'album_id' => $album_id
			);
		}
		
		$actual_result = $this->sharedFixture->translatedQuery("SELECT email, position, album_id FROM favorite_albums WHERE email = %s ORDER BY position ASC", $email)->fetchAllRows();
		
		$this->assertEquals($expected_result, $actual_result);
	}
	
	
	static public function addMultiColumnProvider()
	{
		$output = array();
		// Original order
		//$output[] = array('will@flourishlib.com', 6, 1, array(2, 1, 3, 7, 4));
		$output[] = array('will@flourishlib.com', 6, 1, array(6, 2, 1, 3, 7, 4));
		$output[] = array('will@flourishlib.com', 6, 2, array(2, 6, 1, 3, 7, 4));
		$output[] = array('will@flourishlib.com', 6, 3, array(2, 1, 6, 3, 7, 4));
		$output[] = array('will@flourishlib.com', 6, 4, array(2, 1, 3, 6, 7, 4));
		$output[] = array('will@flourishlib.com', 6, 5, array(2, 1, 3, 7, 6, 4));
		$output[] = array('will@flourishlib.com', 6, 6, array(2, 1, 3, 7, 4, 6));
		$output[] = array('will@flourishlib.com', 6, NULL, array(2, 1, 3, 7, 4, 6));
		$output[] = array('will@flourishlib.com', 6, 9, array(2, 1, 3, 7, 4, 6));
		
		return $output;
	}	
	
	/**
	 * @dataProvider addMultiColumnProvider
	 */
	public function testAddMultiColumn($email, $album_id, $position, $resulting_order)
	{
		$favorite_album = new FavoriteAlbum();
		$favorite_album->setAlbumId($album_id);
		$favorite_album->setEmail($email);
		$favorite_album->setPosition($position);
		$favorite_album->store();
		
		$expected_result = array();
		foreach ($resulting_order as $index => $album_id) {
			$expected_result[] = array(
				'email'    => $email,
				'position' => $index+1,
				'album_id' => $album_id
			);
		}
		
		$actual_result = $this->sharedFixture->translatedQuery("SELECT email, position, album_id FROM favorite_albums WHERE email = %s ORDER BY position ASC", $email)->fetchAllRows();
		
		$this->assertEquals($expected_result, $actual_result);
	}
	
	
	static public function deleteMultiColumnProvider()
	{
		$output = array();
		// Original order
		//$output[] = array('will@flourishlib.com', 1, array(2, 1, 3, 7, 4));
		$output[] = array('will@flourishlib.com', 2, array(1, 3, 7, 4));
		$output[] = array('will@flourishlib.com', 1, array(2, 3, 7, 4));
		$output[] = array('will@flourishlib.com', 3, array(2, 1, 7, 4));
		$output[] = array('will@flourishlib.com', 7, array(2, 1, 3, 4));
		$output[] = array('will@flourishlib.com', 4, array(2, 1, 3, 7));
		
		return $output;
	}	
	
	/**
	 * @dataProvider deleteMultiColumnProvider
	 */
	public function testDeleteMultiColumn($email, $album_id, $resulting_order)
	{
		$favorite_album = new FavoriteAlbum(array('email' => $email, 'album_id' => $album_id));
		$favorite_album->delete();
		
		$expected_result = array();
		foreach ($resulting_order as $index => $album_id) {
			$expected_result[] = array(
				'email'    => $email,
				'position' => $index+1,
				'album_id' => $album_id
			);
		}
		
		$actual_result = $this->sharedFixture->translatedQuery("SELECT email, position, album_id FROM favorite_albums WHERE email = %s ORDER BY position ASC", $email)->fetchAllRows();
		
		$this->assertEquals($expected_result, $actual_result);
	}
	
	
	static public function moveSetMultiColumnProvider()
	{
		$output = array();
		// Original order
		//$output[] = array('will@flourishlib.com', 1, 'john@smith.com', 1, array(2, 1, 3, 7, 4), array(2));
		$output[] = array('will@flourishlib.com', 1, 'john@smith.com', 1, array(2, 3, 7, 4), array(1, 2));
		$output[] = array('will@flourishlib.com', 7, 'john@smith.com', 2, array(2, 1, 3, 4), array(2, 7));
		$output[] = array('will@flourishlib.com', 7, 'john@smith.com', NULL, array(2, 1, 3, 4), array(2, 7));
		$output[] = array('will@flourishlib.com', 2, 'bar@example.com', 1, array(1, 3, 7, 4), array(2));
		
		return $output;
	}	
	
	/**
	 * @dataProvider moveSetMultiColumnProvider
	 */
	public function testMoveSetMultiColumn($origin_email, $album_id, $destination_email, $position, $origin_resulting_order, $destination_resulting_order)
	{
		$origin_fa = new FavoriteAlbum(array('email' => $origin_email, 'album_id' => $album_id));
		$origin_fa->setEmail($destination_email);
		if ($position) {
			$origin_fa->setPosition($position);	
		}
		$origin_fa->store();
		
		$expected_origin_result = array();
		foreach ($origin_resulting_order as $index => $album_id) {
			$expected_origin_result[] = array(
				'email'    => $origin_email,
				'position' => $index+1,
				'album_id' => $album_id
			);
		}
		
		$actual_origin_result = $this->sharedFixture->translatedQuery("SELECT email, position, album_id FROM favorite_albums WHERE email = %s ORDER BY position ASC", $origin_email)->fetchAllRows();
		
		$this->assertEquals($expected_origin_result, $actual_origin_result);
		
		$expected_destination_result = array();
		foreach ($destination_resulting_order as $index => $album_id) {
			$expected_destination_result[] = array(
				'email'    => $destination_email,
				'position' => $index+1,
				'album_id' => $album_id
			);
		}
		
		$actual_destination_result = $this->sharedFixture->translatedQuery("SELECT email, position, album_id FROM favorite_albums WHERE email = %s ORDER BY position ASC", $destination_email)->fetchAllRows();
		
		$this->assertEquals($expected_destination_result, $actual_destination_result);
	}
	
	
	static public function inspectProvider()
	{
		$output = array();
		
		$output[] = array('FavoriteAlbum', array('email' => 'will@flourishlib.com', 'album_id' => 1), 'inspectPosition', 5);
		$output[] = array('FavoriteAlbum', array('email' => 'john@smith.com', 'album_id' => 2), 'inspectPosition', 1);
		$output[] = array('TopAlbum', array('position' => 1), 'inspectPosition', 6);
		$output[] = array('TopAlbum', array('position' => 3), 'inspectPosition', 6);
		$output[] = array('FavoriteAlbum', NULL, 'inspectPosition', 1);
		$output[] = array('TopAlbum', NULL, 'inspectPosition', 7);
		
		return $output;
	}
	
	
	/**
	 * @dataProvider inspectProvider
	 */
	public function testInspect($class, $primary_key, $method, $max_ordering_value)
	{
		$object = new $class($primary_key);
		$this->assertEquals('ordering', $object->$method('feature'));
		$this->assertEquals($max_ordering_value, $object->$method('max_ordering_value'));		
	}
	
 
	public function tearDown()
	{
		$db = $this->sharedFixture;
		$db->query(file_get_contents(DB_EXTENDED_TEARDOWN_FILE));		
		$db->query(file_get_contents(DB_TEARDOWN_FILE));
		__reset();
	}
}