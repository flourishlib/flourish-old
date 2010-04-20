<?php
require_once('./support/init.php');
include('./support/fMailbox.php');
 
class fSMTPTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{	
		$_SERVER['SERVER_NAME'] = 'flourishlib.com';
		//fCore::enableDebugging(TRUE);
	}
	
	public function tearDown()
	{
		
	}
	
	private function findMessage($token, $user)
	{
		$mailbox = new fMailbox(EMAIL_SERVER, $user, EMAIL_PASSWORD);
		
		$i = 0;
		do {
			sleep(1);
			$messages = $mailbox->listMessages();
			foreach ($messages as $number => $headers) {
				if (strpos($headers['subject'], $token) !== FALSE) {
					$message = $mailbox->getMessage($number, TRUE);
					$mailbox->deleteMessage($number);
					return $message;
				}
			}
			$i++;
		} while ($i < 60);
		
		throw new Exception('Email message ' . $token . ' never arrived');
	}
	
	private function generateSubjectToken()
	{
		return uniqid('', TRUE);
	}
	
	static public function serverProvider()
	{
		$output = array();
		/*
		// This server uses a secure connection
		$output[] = array('smtp.gmail.com', 465, TRUE, 'flourishlib@gmail.com', '');
		
		// This server uses smarttls
		$output[] = array('smtp.live.com', 587, FALSE, 'flourishlib@live.com', '');
		
		// This server offers CRAM-MD5
		$output[] = array('smtp.comcast.net', 25, FALSE, NULL, NULL);
		*/
		if (ini_get('SMTP')) {
			$output[] = array(ini_get('SMTP'), ini_get('smtp_port'), FALSE, NULL, NULL);
		} else {
			$output[] = array('localhost', 25, FALSE, 5, NULL, NULL);
		}
		
		return $output;
	}
	
	/**
	 * @dataProvider serverProvider
	 */	
	public function testSendSimple($server, $port, $secure, $username, $password)
	{
		$token = $this->generateSubjectToken();
		
		$smtp = new fSMTP($server, $port, $secure, 5);
		if ($username) {
			$smtp->authenticate($username, $password);	
		}
		
		$email = new fEmail();
		$email->setFromEmail('will@flourishlib.com');
		$email->addRecipient(EMAIL_ADDRESS, 'Test User');
		$email->setSubject($token . ': Testing Simple Email');
		$email->setBody('This is a simple test');
		$email->send($smtp);
		
		$message = $this->findMessage($token, EMAIL_USER);
		$this->assertEquals($username == 'flourishlib@gmail.com' ? 'flourishlib@gmail.com' : 'will@flourishlib.com', $message['headers']['From']);
		$this->assertEquals($token . ': Testing Simple Email', $message['headers']['Subject']);
		$this->assertEquals('This is a simple test', $message['plain']);
		
		$smtp->close();
	}
	
	/**
	 * @dataProvider serverProvider
	 */	
	public function testSendSinglePeriodOnLine($server, $port, $secure, $username, $password)
	{
		$token = $this->generateSubjectToken();
		
		$smtp = new fSMTP($server, $port, $secure, 5);
		if ($username) {
			$smtp->authenticate($username, $password);	
		}
		
		$email = new fEmail();
		$email->setFromEmail('will@flourishlib.com');
		$email->addRecipient(EMAIL_ADDRESS, 'Test User');
		$email->setSubject($token . ': Testing Single Periods on a Line');
		$email->setBody('This is a test of single periods on a line
.
.');
		$email->send($smtp);
		
		$message = $this->findMessage($token, EMAIL_USER);
		$this->assertEquals($username == 'flourishlib@gmail.com' ? 'flourishlib@gmail.com' : 'will@flourishlib.com', $message['headers']['From']);
		$this->assertEquals($token . ': Testing Single Periods on a Line', $message['headers']['Subject']);
		$this->assertEquals('This is a test of single periods on a line
.
.', $message['plain']);
		
		$smtp->close();
	}
	
	/**
	 * @dataProvider serverProvider
	 */	
	public function testSendMultipleToCcBcc($server, $port, $secure, $username, $password)
	{
		$token = $this->generateSubjectToken();
		
		$smtp = new fSMTP($server, $port, $secure, 5);
		if ($username) {
			$smtp->authenticate($username, $password);	
		}
		
		$email = new fEmail();
		$email->setFromEmail('will@flourishlib.com');
		$email->addRecipient(EMAIL_ADDRESS, 'Test User');
		$email->addRecipient(str_replace('@', '_2@', EMAIL_ADDRESS), 'Test User 2');
		$email->addCCRecipient(str_replace('@', '_3@', EMAIL_ADDRESS), 'Test User 3');
		$email->addBCCRecipient(str_replace('@', '_4@', EMAIL_ADDRESS), 'Test User 4');
		$email->setSubject($token . ': Testing Multiple Recipients');
		$email->setBody('This is a test of sending multiple recipients');
		$email->send($smtp);
		
		$message = $this->findMessage($token, EMAIL_USER);
		$this->assertEquals($username == 'flourishlib@gmail.com' ? 'flourishlib@gmail.com' : 'will@flourishlib.com', $message['headers']['From']);
		$this->assertEquals($token . ': Testing Multiple Recipients', $message['headers']['Subject']);
		$this->assertEquals('This is a test of sending multiple recipients', $message['plain']);
		
		$message = $this->findMessage($token, str_replace('tests', 'tests_2', EMAIL_USER));
		// It seems the windows imap extension doesn't support the personal part of an email address
		$is_windows = stripos(php_uname('a'), 'windows') !== FALSE;
		$this->assertEquals($is_windows ? 'tests@flourishlib.com' : '"Test User" <tests@flourishlib.com>', $message['headers']['To']);
		
		$message = $this->findMessage($token, str_replace('tests', 'tests_3', EMAIL_USER));
		$this->assertEquals($is_windows ? 'tests_3@flourishlib.com' : '"Test User 3" <tests_3@flourishlib.com>', $message['headers']['Cc']);
		
		$message = $this->findMessage($token, str_replace('tests', 'tests_4', EMAIL_USER));
		$this->assertEquals(FALSE, isset($message['headers']['Bcc']));
		
		$smtp->close();
	}
}