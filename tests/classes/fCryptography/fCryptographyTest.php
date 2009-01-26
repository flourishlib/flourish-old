<?php
require_once('./support/init.php');
 
class fCryptographyTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{	
			
	}
	
	public static function symmetricEncryptionProvider()
	{
		$output = array();
		
		$output[] = array('The quick brown fox jumps over the lazy dog', 'j92nsbw01fa0');
		$output[] = array('a', 'kdqposd29sjd90');
		$output[] = array('', 'qmvbo7wks0hde3');
		
		return $output;
	}
	
	/**
	 * @dataProvider symmetricEncryptionProvider
	 */
	public function testSymmetricEncryption($plaintext, $key)
	{
		$ciphertext = fCryptography::symmetricKeyEncrypt($plaintext, $key);
		$this->assertNotEquals($plaintext, $ciphertext);
		$this->assertEquals($plaintext, fCryptography::symmetricKeyDecrypt($ciphertext, $key));	
	}
	
	/*public function testCustomClassTableMapping()
	{
		$this->assertEquals('users', fORM::tablize('User'));
		$this->assertEquals('User', fORM::classize('users'));
		
		fORM::addCustomClassTableMapping('User', 'person');
		$this->assertEquals('person', fORM::tablize('User'));
		$this->assertEquals('User', fORM::classize('person'));
		
		$this->assertNotEquals('users', fORM::tablize('User'));
		
		$this->assertEquals('bicycles', fORM::tablize('Bicycle'));
		$this->assertEquals('Bicycle', fORM::classize('bicycles'));
		
		fORM::addCustomClassTableMapping('Bicycle', 'bike');
		$this->assertEquals('bike', fORM::tablize('Bicycle'));
		$this->assertEquals('Bicycle', fORM::classize('bike'));
		
		$this->assertNotEquals('bicycles', fORM::tablize('Bicycle'));
	}*/
	
	public function tearDown()
	{
		
	}
}
?>