<?php
require_once('./support/init.php');
 
class fSMTPTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{	
		if (defined('SKIPPING') || !defined('EMAIL_PASSWORD')) {
			$this->markTestSkipped();
		}
		$_SERVER['SERVER_NAME'] = 'flourishlib.com';
		if (defined('EMAIL_DEBUG')) {
			fCore::enableDebugging(TRUE);
		}
	}
	
	public function tearDown()
	{
		
	}
	
	private function findMessage($token, $user)
	{
		$i = 0;
		do {
			$mailbox = new fMailbox('imap', EMAIL_SERVER, $user, EMAIL_PASSWORD);
			$messages = $mailbox->listMessages();
			foreach ($messages as $number => $headers) {
				if (strpos($headers['subject'], $token) !== FALSE) {
					$message = $mailbox->fetchMessage($number, TRUE);
					$mailbox->deleteMessages($number);
					return $message;
				}
			}
			$mailbox->close();
			usleep(500000);
		} while ($i < 20);
		
		throw new Exception('Email message ' . $token . ' never arrived');
	}
	
	private function generateSubjectToken()
	{
		return uniqid('', TRUE);
	}
	
	static public function serverProvider()
	{
		$output = array();
		
		if (defined('THIRD_PARTY_EMAIL_PASSWORD')) {
			$output[] = array('mail.gmx.com', 25, FALSE, 'flourishlib@gmx.com', THIRD_PARTY_EMAIL_PASSWORD);
			$output[] = array('smtp.aim.com', 587, FALSE, 'flourishlib@aim.com', THIRD_PARTY_EMAIL_PASSWORD);
			$output[] = array('smtp.zoho.com', 465, TRUE, 'flourishlib@zoho.com', THIRD_PARTY_EMAIL_PASSWORD);
			$output[] = array('smtp.gmail.com', 465, TRUE, 'flourishlib@gmail.com', THIRD_PARTY_EMAIL_PASSWORD);
			$output[] = array('smtp.live.com', 587, FALSE, 'flourishlib@live.com', THIRD_PARTY_EMAIL_PASSWORD);
		}
		
		if (ini_get('SMTP')) {
			$output[] = array(ini_get('SMTP'), ini_get('smtp_port'), FALSE, NULL, NULL);
		} else {
			$output[] = array('localhost', 25, FALSE, 10, NULL, NULL);
		}
		
		return $output;
	}
	
	/**
	 * @dataProvider serverProvider
	 */	
	public function testBadCredentials($server, $port, $secure, $username, $password)
	{
		if (!$username) {
			$this->markTestSkipped();
		}
		
		$this->setExpectedException('fValidationException');
		$token = $this->generateSubjectToken();
		
		$smtp = new fSMTP($server, $port, $secure, 10);
		$smtp->authenticate($username, $password . 'dhjskdhsaku');
		
		$email = new fEmail();
		$email->setFromEmail($username ? $username : 'will@flourishlib.com');
		$email->addRecipient(EMAIL_ADDRESS, 'Test User');
		$email->setSubject($token . ': Testing Simple Email');
		$email->setBody('This is a simple test');
		$email->send($smtp);
	}
	
	
	public function testBadSSLOption()
	{
		$this->setExpectedException('fConnectivityException');
		
		$smtp = new fSMTP('localhost', 25, TRUE, 10);
		$smtp->authenticate('test', 'dhjskdhsaku');
		
		$token = $this->generateSubjectToken();
		
		$email = new fEmail();
		$email->setFromEmail('will@flourishlib.com');
		$email->addRecipient(EMAIL_ADDRESS, 'Test User');
		$email->setSubject($token . ': Testing Simple Email');
		$email->setBody('This is a simple test');
		$email->send($smtp);
	}
	
	/**
	 * @dataProvider serverProvider
	 */	
	public function testSendSimple($server, $port, $secure, $username, $password)
	{
		$token = $this->generateSubjectToken();
		
		$smtp = new fSMTP($server, $port, $secure, 10);
		if ($username) {
			$smtp->authenticate($username, $password);	
		}
		
		$email = new fEmail();
		$email->setFromEmail($username ? $username : 'will@flourishlib.com');
		$email->addRecipient(EMAIL_ADDRESS, 'Test User');
		$email->setSubject($token . ': Testing Simple Email');
		$email->setBody('This is a simple test');
		$message_id = $email->send($smtp);
		
		$message = $this->findMessage($token, EMAIL_USER);
		$this->assertEquals($message_id, $message['headers']['message-id']);
		$this->assertEquals($username ? $username : 'will@flourishlib.com', $message['headers']['from']['mailbox'] . '@' . $message['headers']['from']['host']);
		$this->assertEquals($token . ': Testing Simple Email', $message['headers']['subject']);
		$this->assertEquals('This is a simple test', trim($message['text']));
		
		$smtp->close();
	}
	
	/**
	 * @dataProvider serverProvider
	 */	
	public function testLineStartingWithPeriod($server, $port, $secure, $username, $password)
	{
		$token = $this->generateSubjectToken();
		
		$smtp = new fSMTP($server, $port, $secure, 10);
		if ($username) {
			$smtp->authenticate($username, $password);	
		}
		
		$email = new fEmail();
		$email->setFromEmail($username ? $username : 'will@flourishlib.com');
		$email->addRecipient(EMAIL_ADDRESS, 'Test User');
		$email->setSubject($token . ': Testing Single Periods on a Line');
		$email->setBody('.This is a test of a line starting with a period and then there is a period. on the next line too');
		$message_id = $email->send($smtp);
		
		$message = $this->findMessage($token, EMAIL_USER);
		$this->assertEquals($message_id, $message['headers']['message-id']);
		$this->assertEquals($username ? $username : 'will@flourishlib.com', $message['headers']['from']['mailbox'] . '@' . $message['headers']['from']['host']);
		$this->assertEquals($token . ': Testing Single Periods on a Line', $message['headers']['subject']);
		$this->assertEquals('.This is a test of a line starting with a period and then there is a period. on the next line too', trim($message['text']));
		
		$smtp->close();
	}
	
	/**
	 * @dataProvider serverProvider
	 */	
	public function testSendMultipleToCcBcc($server, $port, $secure, $username, $password)
	{
		$token = $this->generateSubjectToken();
		
		$smtp = new fSMTP($server, $port, $secure, 10);
		if ($username) {
			$smtp->authenticate($username, $password);	
		}
		
		$email = new fEmail();
		$email->setFromEmail($username ? $username : 'will@flourishlib.com');
		$email->addRecipient(EMAIL_ADDRESS, 'Test User');
		$email->addRecipient(str_replace('@', '_2@', EMAIL_ADDRESS), 'Test User 2');
		$email->addCCRecipient(str_replace('@', '_3@', EMAIL_ADDRESS), 'Test User 3');
		$email->addBCCRecipient(str_replace('@', '_4@', EMAIL_ADDRESS), 'Test User 4');
		$email->setSubject($token . ': Testing Multiple Recipients');
		$email->setBody('This is a test of sending multiple recipients');
		$message_id = $email->send($smtp);
		
		$message = $this->findMessage($token, EMAIL_USER);
		$this->assertEquals($message_id, $message['headers']['message-id']);
		$this->assertEquals($username ? $username : 'will@flourishlib.com', $message['headers']['from']['mailbox'] . '@' . $message['headers']['from']['host']);
		$this->assertEquals($token . ': Testing Multiple Recipients', $message['headers']['subject']);
		$this->assertEquals('This is a test of sending multiple recipients', trim($message['text']));
		
		$message = $this->findMessage($token, str_replace('tests', 'tests_2', EMAIL_USER));
		$this->assertEquals(
			array(
				'personal' => 'Test User',
				'mailbox' => 'tests',
				'host' => 'flourishlib.com'
			), 
			$message['headers']['to'][0]
		);
		
		$message = $this->findMessage($token, str_replace('tests', 'tests_3', EMAIL_USER));
		$this->assertEquals(
			array(
				'personal' => 'Test User 3',
				'mailbox' => 'tests_3',
				'host' => 'flourishlib.com'
			), 
			$message['headers']['cc'][0]
		);
		
		$message = $this->findMessage($token, str_replace('tests', 'tests_4', EMAIL_USER));
		$this->assertEquals(FALSE, isset($message['headers']['bcc']));
		
		$smtp->close();
	}
}