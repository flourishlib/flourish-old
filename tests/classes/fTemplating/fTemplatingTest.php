<?php
require_once('./support/init.php');
 
class fTemplatingTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{	
		mkdir('./output/minification_cache/');
		mkdir('./output/php_cache/');
	}
	
	public function testSet()
	{
		$tmpl = new fTemplating();
		$tmpl->set('foo', FALSE);
		$this->assertEquals(FALSE, $tmpl->get('foo'));
	}
	
	public function testSetArraySyntax()
	{
		$tmpl = new fTemplating();
		$tmpl->set('foo[bar]', FALSE);
		$this->assertEquals(array('bar' => FALSE), $tmpl->get('foo'));
	}
	
	public function testSetMestedArraySyntax()
	{
		$tmpl = new fTemplating();
		$tmpl->set('foo[bar][baz]', TRUE);
		$this->assertEquals(
			array('bar' => array('baz' => TRUE)),
			$tmpl->get('foo')
		);
	}
	
	public function testSetArray()
	{
		$tmpl = new fTemplating();
		$tmpl->set('foo', FALSE);
		$tmpl->set(array(
			'foo' => TRUE,
			'bar' => '2'
		));
		$this->assertEquals(TRUE, $tmpl->get('foo'));
		$this->assertEquals('2', $tmpl->get('bar'));
	}
	
	public function testAdd()
	{
		$tmpl = new fTemplating();
		$tmpl->add('foo', TRUE);
		$this->assertEquals(
			array(TRUE),
			$tmpl->get('foo')
		);
		$tmpl->add('foo', FALSE);
		$this->assertEquals(
			array(TRUE, FALSE),
			$tmpl->get('foo')
		);
	}
	
	public function testAddBeginning()
	{
		$tmpl = new fTemplating();
		$tmpl->add('foo', TRUE);
		$this->assertEquals(
			array(TRUE),
			$tmpl->get('foo')
		);
		$tmpl->add('foo', FALSE, TRUE);
		$this->assertEquals(
			array(FALSE, TRUE),
			$tmpl->get('foo')
		);
	}
	
	public function testAddArraySyntax()
	{
		$tmpl = new fTemplating();
		$tmpl->add('foo[bar]', TRUE);
		$this->assertEquals(
			array('bar' => array(TRUE)),
			$tmpl->get('foo')
		);
		$tmpl->add('foo', FALSE);
		$this->assertEquals(
			array('bar' => array(TRUE), FALSE),
			$tmpl->get('foo')
		);
	}
	
	public function testAddNestedArraySyntax()
	{
		$tmpl = new fTemplating();
		$tmpl->add('foo[bar][baz]', TRUE);
		$this->assertEquals(
			array('bar' => array('baz' => array(TRUE))),
			$tmpl->get('foo')
		);
		$tmpl->add('foo', FALSE);
		$this->assertEquals(
			array('bar' => array('baz' => array(TRUE)), FALSE),
			$tmpl->get('foo')
		);
	}
	
	public function testRemove()
	{
		$tmpl = new fTemplating();
		$tmpl->add('foo', TRUE);
		$this->assertEquals(
			array(TRUE),
			$tmpl->get('foo')
		);
		$tmpl->remove('foo');
		$this->assertEquals(
			array(),
			$tmpl->get('foo')
		);
	}
	
	public function testRemoveBeginning()
	{
		$tmpl = new fTemplating();
		$tmpl->add('foo', TRUE);
		$this->assertEquals(
			array(TRUE),
			$tmpl->get('foo')
		);
		$tmpl->add('foo', FALSE);
		$this->assertEquals(
			array(TRUE, FALSE),
			$tmpl->get('foo')
		);
		$tmpl->remove('foo', TRUE);
		$this->assertEquals(
			array(FALSE),
			$tmpl->get('foo')
		);
	}
	
	public function testRemoveArraySyntax()
	{
		$tmpl = new fTemplating();
		$tmpl->add('foo[bar]', TRUE);
		$this->assertEquals(
			array('bar' => array(TRUE)),
			$tmpl->get('foo')
		);
		$tmpl->remove('foo[bar]');
		$this->assertEquals(
			array('bar' => array()),
			$tmpl->get('foo')
		);
	}
	
	public function testRemoveNestedArraySyntax()
	{
		$tmpl = new fTemplating();
		$tmpl->add('foo[bar][baz]', TRUE);
		$this->assertEquals(
			array('bar' => array('baz' => array(TRUE))),
			$tmpl->get('foo')
		);
		$tmpl->add('foo', FALSE);
		$this->assertEquals(
			array('bar' => array('baz' => array(TRUE)), FALSE),
			$tmpl->get('foo')
		);
		$tmpl->remove('foo[bar][baz]');
		$this->assertEquals(
			array('bar' => array('baz' => array()), FALSE),
			$tmpl->get('foo')
		);
	}
	
	public function testGet()
	{
		$tmpl = new fTemplating();
		$tmpl->set('foo', TRUE);
		$tmpl->set('bar', '2');
		$this->assertEquals(
			TRUE,
			$tmpl->get('foo')
		);
	}
	
	public function testGetArraySyntax()
	{
		$tmpl = new fTemplating();
		$tmpl->set('foo', array('bar' => TRUE));
		$tmpl->set('bar', '2');
		$this->assertEquals(
			TRUE,
			$tmpl->get('foo[bar]')
		);
	}
	
	public function testGetNestedArraySyntax()
	{
		$tmpl = new fTemplating();
		$tmpl->set('foo', array('bar' => array('baz' => TRUE)));
		$tmpl->set('bar', '2');
		$this->assertEquals(
			TRUE,
			$tmpl->get('foo[bar][baz]')
		);
	}
	
	public function testGetDefault()
	{
		$tmpl = new fTemplating();
		$tmpl->set('foo', TRUE);
		$tmpl->set('bar', '2');
		$this->assertEquals(
			'3',
			$tmpl->get('baz', '3')
		);
	}
	
	public function testGetArray()
	{
		$tmpl = new fTemplating();
		$tmpl->set('foo', TRUE);
		$tmpl->set('bar', '2');
		$this->assertEquals(
			array(
				'foo' => TRUE,
				'bar' => '2'
			),
			$tmpl->get(array('foo', 'bar'))
		);
	}
	
	public function testGetArrayDefaults()
	{
		$tmpl = new fTemplating();
		$tmpl->set('foo', TRUE);
		$tmpl->set('bar', '2');
		$this->assertEquals(
			array(
				'foo' => TRUE,
				'bar' => '2',
				'baz' => '3'
			),
			$tmpl->get(array(
				'foo' => NULL,
				'bar' => '1',
				'baz' => '3'
			))
		);
	}
	
	public function testDelete()
	{
		$tmpl = new fTemplating();
		$tmpl->set('foo', 1);
		$tmpl->set('bar', 2);
		$tmpl->delete('foo');
		$this->assertEquals(
			array(
				'foo' => NULL,
				'bar' => 2
			),
			$tmpl->get(array('foo', 'bar'))
		);
	}
	
	public function testDeleteDefault()
	{
		$tmpl = new fTemplating();
		$tmpl->set('foo', 1);
		$tmpl->set('bar', 2);
		$this->assertEquals(3, $tmpl->delete('baz', 3));
	}
	
	public function testDeleteMultiple()
	{
		$tmpl = new fTemplating();
		$tmpl->set('foo', 1);
		$tmpl->set('bar', 2);
		$this->assertEquals(
			array('foo' => 1, 'bar' => 2),
			$tmpl->delete(array('foo', 'bar'))
		);
		$this->assertEquals(
			array(
				'foo' => NULL,
				'bar' => NULL
			),
			$tmpl->get(array('foo', 'bar'))
		);
	}
	
	public function testDeleteMultipleDefault()
	{
		$tmpl = new fTemplating();
		$tmpl->set('foo', 1);
		$tmpl->set('bar', 2);
		$this->assertEquals(
			array('foo' => 1, 'bar' => 2, 'baz' => 4),
			$tmpl->delete(array('foo' => 2, 'bar' => 3, 'baz' => 4))
		);
		$this->assertEquals(
			array(
				'foo' => NULL,
				'bar' => NULL
			),
			$tmpl->get(array('foo', 'bar'))
		);
	}
	
	public function testDeleteArraySyntax()
	{
		$tmpl = new fTemplating();
		$tmpl->set('foo', array('bar' => TRUE, 'baz' => FALSE));
		$tmpl->delete('foo[bar]');
		$this->assertEquals(
			array(
				'baz' => FALSE
			),
			$tmpl->get('foo')
		);
	}
	
	public function testDeleteNestedArraySyntax()
	{
		$tmpl = new fTemplating();
		$tmpl->set('foo', array('bar' => array('qux' => TRUE), 'baz' => FALSE));
		$tmpl->delete('foo[bar][qux]');
		$this->assertEquals(
			array(
				'bar' => array(),
				'baz' => FALSE
			),
			$tmpl->get('foo')
		);
	}
	
	public function testFilter()
	{
		$tmpl = new fTemplating();
		$tmpl->set('foo', array(1, 2, 3));
		$tmpl->filter('foo', 1);
		$this->assertEquals(
			array(
				2,
				3
			),
			$tmpl->get('foo')
		);
	}
	
	public function testFilterMultiple()
	{
		$tmpl = new fTemplating();
		$tmpl->set('foo', array(1, 2, 1, 3, 1));
		$tmpl->filter('foo', 1);
		$this->assertEquals(
			array(
				2,
				3
			),
			$tmpl->get('foo')
		);
	}
	
	public function testFilterFuzzy()
	{
		$tmpl = new fTemplating();
		$tmpl->set('foo', array(0, 1, 2, 3));
		$tmpl->filter('foo', '');
		$this->assertEquals(
			array(
				1,
				2,
				3
			),
			$tmpl->get('foo')
		);
	}
	
	public function testFilterNonExistent()
	{
		$tmpl = new fTemplating();
		$tmpl->set('foo', array(0, 1, 2, 3));
		$tmpl->filter('bar', '');
	}
	
	public function testFilterNonArray()
	{
		$this->setExpectedException('fProgrammerException');
		
		$tmpl = new fTemplating();
		$tmpl->set('foo', 1);
		$tmpl->filter('foo', 1);
	}
	
	public function testPlaceSubTemplate()
	{
		$tmpl = new fTemplating();
		$tmpl2 = new fTemplating($_SERVER['DOCUMENT_ROOT'], './resources/php/main.php');
		$tmpl->set('foo', $tmpl2);
		
		ob_start();
		$tmpl->place('foo');
		$output = ob_get_clean();
		
		$this->assertEquals('file path: ' . $_SERVER['DOCUMENT_ROOT'] . str_replace('/', DIRECTORY_SEPARATOR, '/resources/php/main.php'), $output);
	}
	
	public function testCssMinification()
	{
		$tmpl = new fTemplating();
		$tmpl->enableMinification('development', './output/minification_cache/', $_SERVER['DOCUMENT_ROOT']);
		$tmpl->add('css', '/resources/css/foo.css');
		$tmpl->add('css', '/resources/css/bar.css');
		ob_start();
		$tmpl->place('css');
		$output = ob_get_clean();
		preg_match('#/(\w+\.css)#', $output, $match);
		$file = $match[1];
		$this->assertEquals(file_get_contents('./resources/css/foo-min.css') . "\n" . file_get_contents('./resources/css/bar-min.css'), file_get_contents('./output/minification_cache/' . $file));
	}
	
	public function testCssMinificationDifferentMedia()
	{
		$tmpl = new fTemplating();
		$tmpl->enableMinification('development', './output/minification_cache/', $_SERVER['DOCUMENT_ROOT']);
		$tmpl->add('css', '/resources/css/foo.css');
		$tmpl->add('css', array('path' => '/resources/css/bar.css', 'media' => 'print'));
		ob_start();
		$tmpl->place('css');
		$output = ob_get_clean();
		preg_match_all('#/(\w+\.css)#', $output, $matches, PREG_SET_ORDER);
		$this->assertEquals(file_get_contents('./resources/css/foo-min.css'), file_get_contents('./output/minification_cache/' . $matches[0][1]));
		$this->assertEquals(file_get_contents('./resources/css/bar-min.css'), file_get_contents('./output/minification_cache/' . $matches[1][1]));
	}
	
	public function testJsMinification()
	{
		$tmpl = new fTemplating();
		$tmpl->enableMinification('development', './output/minification_cache/', $_SERVER['DOCUMENT_ROOT']);
		$tmpl->add('js', '/resources/js/swfobject.js');
		ob_start();
		$tmpl->place('js');
		$output = ob_get_clean();
		preg_match('#/(\w+\.js)#', $output, $match);
		$file = $match[1];
		$this->assertEquals(file_get_contents('./resources/js/swfobject-min.js'), file_get_contents('./output/minification_cache/' . $file));
	}
	
	public function testJsMinificationMultiple()
	{
		$tmpl = new fTemplating();
		$tmpl->enableMinification('development', './output/minification_cache/', $_SERVER['DOCUMENT_ROOT']);
		$tmpl->add('js', '/resources/js/foo.js');
		$tmpl->add('js', '/resources/js/bar.js');
		ob_start();
		$tmpl->place('js');
		$output = ob_get_clean();
		preg_match('#/(\w+\.js)#', $output, $match);
		$this->assertEquals(file_get_contents('./resources/js/foo-min.js') . "\n" . file_get_contents('./resources/js/bar-min.js'), file_get_contents('./output/minification_cache/' . $match[1]));
	}
	
	public function testFixShortTags()
	{
		// This is a gross cli wrapper script since we have to test for exit
		$code  = "require_once './support/init.php'; \$tmpl = new fTemplating(); \$tmpl->enablePHPShortTags('development', './output/php_cache/'); \$tmpl->set('view', './resources/php/short_tags.php'); \$tmpl->place('view');";
		$this->assertEquals('<html><body class="foo">hi! how are you<? echo $foo ?><?= echo $bar ?><?= $baz ?><?
echo $qux</body></html>', shell_exec('php -d short_open_tag=0 -r ' . escapeshellarg($code)));
	}
	
	public function tearDown()
	{
		$cache_dir = './output/minification_cache/';
		$files = array_diff(scandir($cache_dir), array('.', '..'));
		foreach ($files as $file) {
			unlink($cache_dir . $file);
		}
		rmdir($cache_dir);
		
		
		$php_cache_dir = './output/php_cache/';
		$files = array_diff(scandir($php_cache_dir), array('.', '..'));
		foreach ($files as $file) {
			unlink($php_cache_dir . $file);
		}
		rmdir($php_cache_dir);
	}
}