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
	
	
	public function testRandomString()
	{
		$random_string = fCryptography::randomString(8, 'alpha');
		$this->assertEquals(8, strlen($random_string));
		$this->assertEquals(TRUE, ctype_alpha($random_string));	
	}
	
	public function tearDown()
	{
		
	}
}