<?php
require_once('./support/init.php');
 
class fAuthorizationTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
	}
	
	public function testSetNoAuthLevels()
	{
		$this->setExpectedException('fProgrammerException');
		
		fAuthorization::setUserAuthLevel('foo');
	}
	
	public function testRequireLoggedInException()
	{
		$this->setExpectedException('fProgrammerException');
		
		fAuthorization::requireLoggedIn('admin');
	}
	
	public function testRequireAuthLevelException()
	{
		$this->setExpectedException('fProgrammerException');
		
		fAuthorization::requireAuthLevel('admin');
	}
	
	public function testRequireACLException()
	{
		$this->setExpectedException('fProgrammerException');
		
		fAuthorization::requireACL('foo', 'read');
	}
	
	/**
	* @outputBuffering enabled
	*/
	public function testCheckLoggedInFalse()
	{
		$this->assertEquals(FALSE, fAuthorization::checkLoggedIn());
	}
	
	public function testRequireLoggedInRedirect()
	{
		// This is a gross cli wrapper script since we have to test for exit
		$code  = "fAuthorization::setLoginPage('/login/');";
		$code .= "fAuthorization::requireLoggedIn();";
		$this->assertEquals('http://example.com/login/', shell_exec('php ' . TEST_EXIT_SCRIPT . ' ' . escapeshellarg($code)));
	}
	
	public function testSetAuthLevels()
	{
		fAuthorization::setAuthLevels(array('user' => 20, 'admin' => 50));
		fAuthorization::setUserAuthLevel('user');
		fAuthorization::setUserAuthLevel('admin');
	}
	
	public function testSetInvalidUserAuthLevel()
	{
		$this->setExpectedException('fProgrammerException');
		
		fAuthorization::setAuthLevels(array('user' => 20, 'admin' => 50));
		fAuthorization::setUserAuthLevel('foo');
	}
	
	public function testCheckInvalidUserAuthLevel()
	{
		$this->setExpectedException('fProgrammerException');
		
		fAuthorization::setAuthLevels(array('user' => 20, 'admin' => 50));
		fAuthorization::setUserAuthLevel('user');
		fAuthorization::checkAuthLevel('foo');
	}
	
	public function testUnsetLoginPage()
	{
		$this->setExpectedException('fProgrammerException');
		
		fAuthorization::setAuthLevels(array('user' => 20, 'admin' => 50));
		fAuthorization::setUserAuthLevel('admin');
		$this->assertEquals(NULL, fAuthorization::requireAuthLevel('admin'));
	}

	public function testUserAuthLevel()
	{
		fAuthorization::setAuthLevels(array('user' => 20, 'admin' => 50));
		
		fAuthorization::setUserAuthLevel('user');
		$this->assertEquals('user', fAuthorization::getUserAuthLevel());
	}
	
	public function testCheckUserAuthLevel()
	{
		fAuthorization::setAuthLevels(array('user' => 20, 'admin' => 50));
		
		fAuthorization::setUserAuthLevel('user');
		$this->assertEquals(TRUE, fAuthorization::checkAuthLevel('user'));
		$this->assertEquals(FALSE, fAuthorization::checkAuthLevel('admin'));
		
		fAuthorization::setUserAuthLevel('admin');
		$this->assertEquals(TRUE, fAuthorization::checkAuthLevel('user'));
		$this->assertEquals(TRUE, fAuthorization::checkAuthLevel('admin'));
	}
   
	public function testRequireAuthLevel()
	{
		fAuthorization::setLoginPage('/login/');
		fAuthorization::setAuthLevels(array('user' => 20, 'admin' => 50));
		fAuthorization::setUserAuthLevel('admin');
		$this->assertEquals(NULL, fAuthorization::requireAuthLevel('admin'));

		// This is a gross cli wrapper script since we have to test for exit
		$code  = "fAuthorization::setLoginPage('/login/');";
		$code .= "fAuthorization::setAuthLevels(array('user' => 20, 'admin' => 50));";
		$code .= "fAuthorization::setUserAuthLevel('user');";
		$code .= "fAuthorization::requireAuthLevel('admin');";
		$this->assertEquals('http://example.com/login/', shell_exec('php ' . TEST_EXIT_SCRIPT . ' ' . escapeshellarg($code)));
	}
	
	public function testUserACLs()
	{
		$acls = array(
			'news'   => array('*'),
			'events' => array('read')
		);
		fAuthorization::setUserACLs($acls);
		$this->assertEquals($acls, fAuthorization::getUserACLs());
	}
	
	public function testCheckUserACLs()
	{
		$acls = array(
			'news'   => array('*'),
			'events' => array('read')
		);
		fAuthorization::setUserACLs($acls);
		
		$this->assertEquals(TRUE, fAuthorization::checkACL('news', 'foo'));
		$this->assertEquals(TRUE, fAuthorization::checkACL('news', 'anything'));
		
		$this->assertEquals(TRUE, fAuthorization::checkACL('events', 'read'));
		$this->assertEquals(FALSE, fAuthorization::checkACL('events', 'write'));
	}
	
	public function testRequireACL()
	{
		fAuthorization::setLoginPage('/index.php');
		$acls = array(
			'news'   => array('*'),
			'events' => array('read')
		);
		fAuthorization::setUserACLs($acls);
		
		$this->assertEquals(NULL, fAuthorization::requireACL('news', 'foo'));

		// This is a gross cli wrapper script since we have to test for exit
		$code  = "fAuthorization::setLoginPage('/login/');";
		$code .= "\$acls = array('news' => array('*'), 'events' => array('read'));";
		$code .= "fAuthorization::setUserACLs(\$acls);";
		$code .= "fAuthorization::requireACL('events', 'write');";
		$this->assertEquals('http://example.com/login/', shell_exec('php ' . TEST_EXIT_SCRIPT . ' ' . escapeshellarg($code)));
	}
	
	public function testCheckLoggedIn1()
	{
		$this->assertEquals(FALSE, fAuthorization::checkLoggedIn());
		
		$acls = array(
			'news'   => array('*'),
			'events' => array('read')
		);
		fAuthorization::setUserACLs($acls);
		
		$this->assertEquals(TRUE, fAuthorization::checkLoggedIn());
	}
	
	public function testCheckLoggedIn2()
	{
		$this->assertEquals(FALSE, fAuthorization::checkLoggedIn());
		
		fAuthorization::setAuthLevels(array('user' => 20, 'admin' => 50));
		fAuthorization::setUserAuthLevel('admin');
		
		$this->assertEquals(TRUE, fAuthorization::checkLoggedIn());
	}
	
	public function testUserToken()
	{
		$this->assertEquals(NULL, fAuthorization::getUserToken());
		fAuthorization::setUserToken('will@flourishlib.com');
		$this->assertEquals('will@flourishlib.com', fAuthorization::getUserToken());
	}
	
	public function testRequestedUrl()
	{
		fSession::set('fAuthorization::requested_url', 'test_url.php?query_string=TRUE');
		$this->assertEquals('test_url.php?query_string=TRUE', fAuthorization::getRequestedURL(FALSE));
		$this->assertEquals('test_url.php?query_string=TRUE', fAuthorization::getRequestedURL(TRUE));
		$this->assertEquals(NULL, fAuthorization::getRequestedURL(TRUE));
		$this->assertEquals('test_url2.php?query_string=TRUE', fAuthorization::getRequestedURL(TRUE, 'test_url2.php?query_string=TRUE'));
	}
	
	public function tearDown()
	{
		fSession::reset();
	}
}