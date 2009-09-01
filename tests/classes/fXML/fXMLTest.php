<?php
require_once('./support/init.php');
 
class fXMLTest extends PHPUnit_Framework_TestCase
{
	private $ns_xml = '<?xml version="1.0" encoding="UTF-8"?>
<foo:book xmlns:foo="http://foo" xmlns:bar="http://bar" xmlns="http://will" publishdate="2009" foo:author="Will Bond" foo:publisher="iMarc">
<foo:chapter>This is text<page foo:number="1"/><page foo:number="2"/>in the element</foo:chapter>
</foo:book>
';
	
	public function setUp()
	{	
			
	}
	
	public function testConstruct()
	{
		new fXML($this->ns_xml);
	}
	
	public function testBadConstruct()
	{
		$this->setExpectedException('fValidationException');
		new fXML('');
	}
	
	public function testBadConstruct2()
	{
		$this->setExpectedException('fValidationException');
		new fXML('<?xml version="1.0" encoding="UTF-8"?>
<book></bok>');
	}
	
	public function testBadConstruct3()
	{
		$this->setExpectedException('fValidationException');
		new fXML('<?xml version="1.0" encoding="UTF-8"?>
<book xmlns:foo="http://foo"><foo:chapter></chapter></book>');
	}
	
	public function testAttribute()
	{
		$book = new fXML($this->ns_xml);
		$this->assertEquals('2009', $book['publishdate']);
	}
	
	public function testNSPrefixAttribute()
	{
		$book = new fXML($this->ns_xml);
		$this->assertEquals('Will Bond', $book['foo:author']);
	}
	
	public function testAttributeIsset()
	{
		$book = new fXML($this->ns_xml);
		$this->assertEquals(TRUE, isset($book['foo:author']));
		$this->assertEquals(FALSE, isset($book['bar:author']));
	}
	
	public function testAttributeUnset()
	{
		$this->setExpectedException('fProgrammerException');
		$book = new fXML($this->ns_xml);
		unset($book['foo:chapter']);
	}
	
	public function testAttributeSet()
	{
		$this->setExpectedException('fProgrammerException');
		$book = new fXML($this->ns_xml);
		$book['foo:chapter'] = 'bar';
	}
	
	public function testGetWrongNS()
	{
		$book = new fXML($this->ns_xml);
		$this->assertEquals(NULL, $book->chapter);
	}
	
	public function testGetMissing()
	{
		$book = new fXML($this->ns_xml);
		$this->assertEquals(NULL, $book->page);
	}
	
	public function testGet()
	{
		$book = new fXML($this->ns_xml);
		$this->assertEquals('This is textin the element', $book->{'foo:chapter'});
	}
	
	public function testIsset()
	{
		$book = new fXML($this->ns_xml);
		$this->assertEquals(
			TRUE,
			isset($book->{'foo:chapter'})
		);
		$this->assertEquals(
			FALSE,
			isset($book->{'bar:chapter'})
		);
	}
	
	public function testElementUnset()
	{
		$this->setExpectedException('fProgrammerException');
		$book = new fXML($this->ns_xml);
		unset($book->{'foo:chapter'});
	}
	
	public function testElementSet()
	{
		$this->setExpectedException('fProgrammerException');
		$book = new fXML($this->ns_xml);
		$book->{'foo:chapter'} = 'bar';
	}
	
	public function testSleepWakeup()
	{
		$book = new fXML($this->ns_xml);
		$this->assertContains(
			'O:4:"fXML"',
			$serialized = serialize($book)
		);
		$this->assertEquals(
			'This is textin the element',
			unserialize($serialized)->{'foo:chapter'}
		);
	}
	
	public function testXPathChildrenTextNodes()
	{
		$book = new fXML($this->ns_xml);
		$children = $book->xpath('foo:chapter/text()');
		$this->assertEquals('This is text', $children[0]);
		$this->assertEquals('in the element', $children[1]);
	}
	
	public function testXPathChildren()
	{
		$book = new fXML($this->ns_xml);
		$children = $book->xpath('foo:chapter', TRUE)->xpath('node()|text()');
		$this->assertEquals('This is text', $children[0]);
		$this->assertEquals(TRUE, $children[1] instanceof fXML);
		$this->assertEquals(TRUE, $children[2] instanceof fXML);
		$this->assertEquals('in the element', $children[3]);
	}
	
	public function testXPathAttributes()
	{
		$book = new fXML($this->ns_xml);
		$this->assertEquals(
			array(
				'publishdate' => '2009',
				'foo:author' => 'Will Bond',
				'foo:publisher' => 'iMarc'
			),
			$book->xpath('@*')
		);
	}
	
	public function testXPathAttributes2()
	{
		$book = new fXML($this->ns_xml);
		$this->assertEquals(
			array(
				'publishdate' => '2009',
				'foo:author' => 'Will Bond',
				'foo:publisher' => 'iMarc',
				'foo:number[1]' => '1',
				'foo:number[2]' => '2'
			),
			$book->xpath('//@*')
		);
	}
	
	public function testToXML()
	{
		$book = new fXML($this->ns_xml);
		$this->assertEquals(
			$this->ns_xml,
			$book->toXML()
		);
	}
	
	public function testGetName()
	{
		$book = new fXML($this->ns_xml);
		$this->assertEquals(
			'book',
			$book->getName()
		);
	}
	
	public function testGetNamespace()
	{
		$book = new fXML($this->ns_xml);
		$this->assertEquals(
			'http://foo',
			$book->getNamespace()
		);
	}
	
	public function testGetPrefix()
	{
		$book = new fXML($this->ns_xml);
		$this->assertEquals(
			'foo',
			$book->getPrefix()
		);
	}
	
	public function testGetText()
	{
		$book = new fXML($this->ns_xml);
		$this->assertEquals(
			'This is textin the element',
			$book->xpath('foo:chapter', TRUE)->getText()
		);
	}
	
	public function testAddCustomPrefix()
	{
		$book = new fXML($this->ns_xml);
		$book->addCustomPrefix('_', 'http://will');
		$pages = $book->xpath('foo:chapter/_:page');
		$this->assertEquals(2, count($pages));
		$this->assertEquals(TRUE, $pages[0] instanceof fXML);
		$this->assertEquals(TRUE, $pages[1] instanceof fXML);
	}
	
	public function tearDown()
	{
			
	}
}