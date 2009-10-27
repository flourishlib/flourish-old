<?php
require_once('./support/init.php');
 
class fImageTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{	
			
	}
	
	public static function fileProvider()
	{
		$output = array();
		
		$output[] = array('resources/images/bar.gif');
		$output[] = array('resources/images/foo.gif');
		$output[] = array('resources/images/john.jpg');
		$output[] = array('resources/images/will.png');
		
		return $output;
	}
	
	/**
	 * @dataProvider fileProvider
	 */
	public function testResize($file_path)
	{
		$image = new fImage($file_path);
		$new_image = $image->duplicate('output/');
		
		list ($base, $extension) = explode('.', $new_image->getFilename());
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
		
		list ($base, $extension) = explode('.', $new_image->getFilename());
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
		
		list ($base, $extension) = explode('.', $new_image->getFilename());
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
		
		list ($base, $extension) = explode('.', $new_image->getFilename());
		$new_image->rename($base . FILE_PREFIX . '_crop_300x300-200x200.' . $extension, FALSE);
		
		$new_image->crop(300, 300, 200, 200);
		$new_image->saveChanges();
	}
	
	public function tearDown()
	{
		fImage::reset();
	}
}