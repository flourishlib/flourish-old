<?php
require_once('./support/init.php');
 
class fUploadTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{	
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_SERVER['CONTENT_TYPE'] = 'multipart/form-data';
		$_FILES = array();
		$_FILES['field'] = array(
			'name' => '',
			'type' => '',
			'tmp_name' => '',
			'error' => '',
			'size' => 0
		);
		$_FILES['array_field'] = array(
			'name' => array(
				''
			),
			'type' => array(
				''
			),
			'tmp_name' => array(
				''
			),
			'error' => array(
				''
			),
			'size' => array(
				0
			)
		);
	}
	
	public function testValidate()
	{
		$uploader = new fUpload();
		$this->assertEquals('Please upload a file', $uploader->validate('field', TRUE));
	}
	
	public function testValidateOptional()
	{
		$uploader = new fUpload();
		$uploader->setOptional();
		$this->assertEquals(NULL, $uploader->validate('field', TRUE));
	}
	
	public function testValidateFormMaxSize()
	{
		ini_set('upload_max_filesize', '2M');
		$_FILES['field']['name'] = 'test.jpg';
		$_FILES['field']['size'] = 438903322;
		$_FILES['field']['error'] = UPLOAD_ERR_INI_SIZE;
		$uploader = new fUpload();
		$this->assertEquals('The file uploaded is over the limit of 2.0 M', $uploader->validate('field', TRUE));
	}
	
	public function testValidateMaxSize()
	{
		$_FILES['field']['name'] = 'test.jpg';
		$_FILES['field']['size'] = 438903322;
		$uploader = new fUpload();
		$uploader->setMaxSize('2m');
		$this->assertEquals('The file uploaded is over the limit of 2.0 M', $uploader->validate('field', TRUE));
	}
	
	public function testValidateSize()
	{
		$_FILES['field']['name'] = 'test.jpg';
		$_FILES['field']['size'] = 0;
		$uploader = new fUpload();
		$this->assertEquals('Please upload a file', $uploader->validate('field', TRUE));
	}
	
	public function testValidateMimeTypeCsvFail()
	{
		$_FILES['field']['name'] = 'test.txt';
		$_FILES['field']['size'] = 17;
		$_FILES['field']['tmp_name'] = './resources/text/example';
		$uploader = new fUpload();
		$uploader->setMIMETypes(
			array('text/csv'),
			'Please upload a CSV file'
		);
		$this->assertEquals('Please upload a CSV file', $uploader->validate('field', TRUE));
	}
	
	public function testValidateMimeTypeCsvMatch()
	{
		$_FILES['field']['name'] = 'test.csv';
		$_FILES['field']['size'] = 17;
		$_FILES['field']['tmp_name'] = './resources/text/example';
		$uploader = new fUpload();
		$uploader->setMIMETypes(
			array('text/csv'),
			'Please upload a CSV file'
		);
		$this->assertEquals(NULL, $uploader->validate('field', TRUE));
	}
	
	public function testValidateMimeTypeJpgMatch()
	{
		$_FILES['field']['name'] = 'john.jpg';
		$_FILES['field']['size'] = 31066;
		$_FILES['field']['tmp_name'] = './resources/images/john.jpg';
		$uploader = new fUpload();
		$uploader->setMIMETypes(
			array('image/jpeg'),
			'Please upload a JPG image'
		);
		$this->assertEquals(NULL, $uploader->validate('field', TRUE));
	}
	
	public function tearDown()
	{
		$_FILES = array();
	}
}