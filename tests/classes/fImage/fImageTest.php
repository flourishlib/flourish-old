<?php
require_once('./support/init.php');
 
class fImageTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{	
		if (defined('SKIPPING')) {
			$this->markTestSkipped();
		}
		if (!extension_loaded('gd')) {
			try {
				$image = new fImage('resources/images/bar.gif');
				$new_image = $image->duplicate('output/');
				$new_image->cropToRatio(1, 1);
				$new_image->saveChanges();
				$new_image->delete();
			} catch (fEnvironmentException $e) {
				$this->markTestSkipped();
			}
		}
	}
	
	public static function fileProvider()
	{
		$output = array();
		
		$output[] = array('resources/images/bar.gif', 90);
		$output[] = array('resources/images/foo.gif', 180);
		$output[] = array('resources/images/john.jpg', 270);
		$output[] = array('resources/images/will.png', 90);
		
		return $output;
	}
	
	/**
	 * @dataProvider fileProvider
	 */
	public function testResize($file_path)
	{
		$image = new fImage($file_path);
		$new_image = $image->duplicate('output/');
		
		list ($base, $extension) = explode('.', $new_image->getName());
		$new_image->rename($base . FILE_PREFIX . '_resize_200x200.' . $extension, FALSE);
		
		$new_image->resize(200, 200);
		$new_image->saveChanges();
	}
	
	/**
	 * @dataProvider fileProvider
	 */
	public function testDesaturate($file_path)
	{
		$image = new fImage($file_path);
		$new_image = $image->duplicate('output/');
		
		list ($base, $extension) = explode('.', $new_image->getName());
		$new_image->rename($base . FILE_PREFIX . '_desaturate.' . $extension, FALSE);
		
		$new_image->desaturate();
		$new_image->saveChanges();
	}
	
	/**
	 * @dataProvider fileProvider
	 */
	public function testCropToRatio($file_path)
	{
		$image = new fImage($file_path);
		$new_image = $image->duplicate('output/');
		
		list ($base, $extension) = explode('.', $new_image->getName());
		$new_image->rename($base . FILE_PREFIX . '_crop_to_ratio_1x2.' . $extension, FALSE);
		
		$new_image->cropToRatio(100, 200);
		$new_image->saveChanges();
	}
	
	/**
	 * @dataProvider fileProvider
	 */
	public function testCrop($file_path)
	{
		$image = new fImage($file_path);
		$new_image = $image->duplicate('output/');
		
		list ($base, $extension) = explode('.', $new_image->getName());
		$new_image->rename($base . FILE_PREFIX . '_crop_300x300-200x200.' . $extension, FALSE);
		
		$new_image->crop(200, 200, 300, 300);
		$new_image->saveChanges();
	}
	
	/**
	 * @dataProvider fileProvider
	 */
	public function testRotate($file_path, $degrees)
	{
		$image = new fImage($file_path);
		$new_image = $image->duplicate('output/');
		
		list ($base, $extension) = explode('.', $new_image->getName());
		$new_image->rename($base . FILE_PREFIX . '_rotate_' . $degrees . '.' . $extension, FALSE);
		
		$new_image->rotate($degrees);
		$new_image->saveChanges();
	}

	public function testFilename()
	{
		if (stripos(php_uname('s'), 'windows') !== FALSE) {
			$this->markTestSkipped();
		}
		$image = new fImage('resources/images/filename_test.gif');
		$new_image = $image->duplicate('output/');
		$new_image->rename('1::6df23dc03f9b54cc38a0fc1483df6e21.gif', TRUE);
		
		$new_image->rotate(90);
		$new_image->saveChanges();
	}
	
	public function tearDown()
	{
		fImage::reset();
	}
}