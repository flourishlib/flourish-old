<?php
require_once('./support/init.php');

class fORMTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{	
			
	}
	
	public static function classizeTablizeProvider()
	{
		$output = array();
		
		$output[] = array('User', 'users');
		$output[] = array('Photo', 'photos');
		$output[] = array('PhotoGallery', 'photo_galleries');
		$output[] = array('Person', 'people');
		$output[] = array('SalesPerson', 'sales_people');
		$output[] = array('Bike', 'bikes');
		$output[] = array('News', 'news');
		$output[] = array('Information', 'information');
		$output[] = array('Document', 'documents');
		$output[] = array('FirstGradeTeacher', 'first_grade_teachers');
		$output[] = array('Question8Answer', 'question_8_answers');
		
		return $output;
	}
	
	/**
	 * @dataProvider classizeTablizeProvider
	 */
	public function testClassize($output, $input)
	{
		$this->assertEquals($output, fORM::classize($input));
		$this->assertEquals($output, fORM::classize($input));	
	}
	
	/**
	 * @dataProvider classizeTablizeProvider
	 */
	public function testTablize($input, $output)
	{
		$this->assertEquals($output, fORM::tablize($input));
		$this->assertEquals($output, fORM::tablize($input));	
	}
	
	public function testCustomClassTableMapping()
	{
		$this->assertEquals('users', fORM::tablize('User'));
		$this->assertEquals('User', fORM::classize('users'));
		
		fORM::mapClassToTable('User', 'person');
		$this->assertEquals('person', fORM::tablize('User'));
		$this->assertEquals('User', fORM::classize('person'));
		
		$this->assertNotEquals('users', fORM::tablize('User'));
		
		$this->assertEquals('bicycles', fORM::tablize('Bicycle'));
		$this->assertEquals('Bicycle', fORM::classize('bicycles'));
		
		fORM::mapClassToTable('Bicycle', 'bike');
		$this->assertEquals('bike', fORM::tablize('Bicycle'));
		$this->assertEquals('Bicycle', fORM::classize('bike'));
		
		$this->assertNotEquals('bicycles', fORM::tablize('Bicycle'));
	}
	
	public function testClassToDatabaseMapping()
	{
		$this->assertEquals('default', fORM::getDatabaseName('User'));
		$this->assertEquals('default', fORM::getDatabaseName('PhotoGallery'));
		
		fORM::mapClassToDatabase('User', 'second_db');
		$this->assertEquals('second_db', fORM::getDatabaseName('User'));
	}
	
	public function tearDown()
	{
		__reset();		
	}
}