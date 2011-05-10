<?php
require_once('./support/init.php');
 
class fEmailTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{	
		if (defined('SKIPPING') || !defined('EMAIL_PASSWORD')) {
			$this->markTestSkipped();
		}
		if (stripos(php_uname('s'), 'netbsd') !== FALSE || stripos(php_uname('s'), 'darwin') !== FALSE || file_exists('/etc/SuSE-release')) {
			fEmail::fixQmail();
		}
	}
	
	public function tearDown()
	{
		if (defined('SKIPPING') || !defined('EMAIL_PASSWORD')) {
			return;
		}
	}
	
	private function findMessage($token)
	{
		$i = 0;
		do {
			$mailbox = new fMailbox('imap', EMAIL_SERVER, EMAIL_USER, EMAIL_PASSWORD);
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
		
	public function testSendSimple()
	{
		$token = $this->generateSubjectToken();
		
		$email = new fEmail();
		$email->setFromEmail('will@flourishlib.com');
		$email->addRecipient(EMAIL_ADDRESS, 'Test User');
		$email->setSubject($token . ': Testing Simple Email');
		$email->setBody('This is a simple test');
		$message_id = $email->send();
		
		$message = $this->findMessage($token);
		$this->assertEquals($message_id, $message['headers']['message-id']);
		$this->assertEquals('will@flourishlib.com', $message['headers']['from']['mailbox'] . '@' . $message['headers']['from']['host']);
		$this->assertEquals($token . ': Testing Simple Email', $message['headers']['subject']);
		$this->assertEquals('This is a simple test', $message['text']);
	}
	
	
	public function testSendSinglePeriodOnLine()
	{
		$token = $this->generateSubjectToken();
		
		$email = new fEmail();
		$email->setFromEmail('will@flourishlib.com');
		$email->addRecipient(EMAIL_ADDRESS, 'Test User');
		$email->setSubject($token . ': Testing Single Periods on a Line');
		$email->setBody('.This is a test of a line starting with a period and then a period............... on the next line too');
		$message_id = $email->send();
		
		$message = $this->findMessage($token);
		$this->assertEquals('will@flourishlib.com', $message['headers']['from']['mailbox'] . '@' . $message['headers']['from']['host']);
		$this->assertEquals($token . ': Testing Single Periods on a Line', $message['headers']['subject']);
		$this->assertEquals('.This is a test of a line starting with a period and then a period............... on the next line too', $message['text']);
	}
	
	
	public function testSendFormattedBody()
	{
		$token = $this->generateSubjectToken();
		
		$email = new fEmail();
		$email->setFromEmail('will@flourishlib.com');
		$email->addRecipient(EMAIL_ADDRESS, 'Test User');
		$email->setSubject($token . ': Testing Unindented Bodies');
		define('EMAIL_FORMATTED_BODY', 'set');
		$email->setBody('
			This is a test
			
			It uses the unindent and interpolate constants functionality that is available with fEmail::setBody()
			
			The constant is {EMAIL_FORMATTED_BODY}
			{EMAIL_BODY}
		', TRUE);
		$message_id = $email->send();
		
		$message = $this->findMessage($token);
		$this->assertEquals($message_id, $message['headers']['message-id']);
		$this->assertEquals('will@flourishlib.com', $message['headers']['from']['mailbox'] . '@' . $message['headers']['from']['host']);
		$this->assertEquals($token . ': Testing Unindented Bodies', $message['headers']['subject']);
		$this->assertEquals('This is a test

It uses the unindent and interpolate constants functionality that is available with fEmail::setBody()

The constant is set
{EMAIL_BODY}', $message['text']);
	}
	
	
	public function testSendLoadedBody()
	{
		$token = $this->generateSubjectToken();
		
		$email = new fEmail();
		$email->setFromEmail('will@flourishlib.com');
		$email->addRecipient(EMAIL_ADDRESS, 'Test User');
		$email->setSubject($token . ': Testing Simple Email');
		$email->loadBody('./email/loaded_body.txt', array(
			'$PLACEHOLDER$'   => 'placeholder',
			'%PERCENT_SIGNS%' => 'percent signs',
			'nothing'         => 'anything'
		));
		$message_id = $email->send();
		
		$message = $this->findMessage($token);
		$this->assertEquals($message_id, $message['headers']['message-id']);
		$this->assertEquals('will@flourishlib.com', $message['headers']['from']['mailbox'] . '@' . $message['headers']['from']['host']);
		$this->assertEquals($token . ': Testing Simple Email', $message['headers']['subject']);
		$this->assertEquals('This is a loaded body
With a couple of different placeholder styles, including dollar signs and percent signs

You can replace anything', $message['text']);
	}
	
	
	public function testSendLoadedHtml()
	{
		$token = $this->generateSubjectToken();
		
		$email = new fEmail();
		$email->setFromEmail('will@flourishlib.com');
		$email->addRecipient(EMAIL_ADDRESS, 'Test User');
		$email->setSubject($token . ': Testing Simple Email');
		$email->setBody('This is a test of loading the HTML body');
		$email->loadHTMLBody(new fFile('./email/loaded_body.html'), array('%REPLACE%' => 'This is a test'));
		$message_id = $email->send();
		
		$message = $this->findMessage($token);
		$this->assertEquals($message_id, $message['headers']['message-id']);
		$this->assertEquals('will@flourishlib.com', $message['headers']['from']['mailbox'] . '@' . $message['headers']['from']['host']);
		$this->assertEquals($token . ': Testing Simple Email', $message['headers']['subject']);
		$this->assertEquals('This is a test of loading the HTML body', $message['text']);
		$this->assertEquals('<h1>Loaded HTML</h1><p>This is a test</p>', $message['html']);
	}
	
	
	public function testSendHtml()
	{
		$token = $this->generateSubjectToken();
		
		$email = new fEmail();
		$email->setFromEmail('will@flourishlib.com');
		$email->addRecipient(EMAIL_ADDRESS, 'Test User');
		$email->setSubject($token . ': Testing Simple Email');
		$email->setBody('This is a simple test');
		$email->setHTMLBody('<h1>Test</h1><p>This is a simple test</p>');
		$message_id = $email->send();
		
		$message = $this->findMessage($token);
		$this->assertEquals($message_id, $message['headers']['message-id']);
		$this->assertEquals('will@flourishlib.com', $message['headers']['from']['mailbox'] . '@' . $message['headers']['from']['host']);
		$this->assertEquals($token . ': Testing Simple Email', $message['headers']['subject']);
		$this->assertEquals('This is a simple test', $message['text']);
		$this->assertEquals('<h1>Test</h1><p>This is a simple test</p>', $message['html']);
	}
	
	public function testSendLongSubject()
	{
		$token = $this->generateSubjectToken();
		
		$email = new fEmail();
		$email->setFromEmail('will@flourishlib.com');
		$email->addRecipient(EMAIL_ADDRESS, 'Test User');
		$email->setSubject($token . ': This is a test of sending a long subject that should theoretically cause the email Subject: header to break onto multiple lines using folding whitespace - it should take less than 78 characters but it could be as long as 998 characters');
		$email->setBody('This is a simple test');
		$message_id = $email->send();
		
		$message = $this->findMessage($token);
		$this->assertEquals($message_id, $message['headers']['message-id']);
		$this->assertEquals('will@flourishlib.com', $message['headers']['from']['mailbox'] . '@' . $message['headers']['from']['host']);
		$this->assertEquals($token . ': This is a test of sending a long subject that should theoretically cause the email Subject: header to break onto multiple lines using folding whitespace - it should take less than 78 characters but it could be as long as 998 characters', $message['headers']['subject']);
		$this->assertEquals('This is a simple test', $message['text']);
	}
	
	public function testSendUtf8()
	{
		$token = $this->generateSubjectToken();
		
		$email = new fEmail();
		$email->setFromEmail('will@flourishlib.com', "Wíll");
		$email->addRecipient(EMAIL_ADDRESS, 'Test User');
		$email->setSubject($token . ': This is a test of sending headers and body with UTF-8, such as Iñtërnâtiônàlizætiøn');
		$email->setBody('This is a test with UTF-8 characters, such as:
Iñtërnâtiônàlizætiøn
');
		$message_id = $email->send();
		
		$message = $this->findMessage($token);
		$this->assertEquals($message_id, $message['headers']['message-id']);
		$this->assertEquals('will@flourishlib.com', $message['headers']['from']['mailbox'] . '@' . $message['headers']['from']['host']);
		$this->assertEquals($token . ': This is a test of sending headers and body with UTF-8, such as Iñtërnâtiônàlizætiøn', $message['headers']['subject']);
		$this->assertEquals('This is a test with UTF-8 characters, such as:
Iñtërnâtiônàlizætiøn
', $message['text']);
	}
	
	public function testSendAttachment()
	{
		$token = $this->generateSubjectToken();
		
		$email = new fEmail();
		$email->setFromEmail('will@flourishlib.com');
		$email->addRecipient(EMAIL_ADDRESS, 'Test User');
		$email->setSubject($token . ': Testing Attachments');
		$email->setBody('This is a test of sending an attachment');
		$bar_gif_contents = file_get_contents('./resources/images/bar.gif');
		$email->addAttachment($bar_gif_contents, 'bar.gif');
		$message_id = $email->send();
		
		$message = $this->findMessage($token);
		$this->assertEquals($message_id, $message['headers']['message-id']);
		$this->assertEquals('will@flourishlib.com', $message['headers']['from']['mailbox'] . '@' . $message['headers']['from']['host']);
		$this->assertEquals($token . ': Testing Attachments', $message['headers']['subject']);
		$this->assertEquals('This is a test of sending an attachment', $message['text']);
		$this->assertEquals(
			array(
				array(
					'filename' => 'bar.gif',
					'mimetype' => 'image/gif',
					'data' => $bar_gif_contents
				)
			),
			$message['attachment'],
			'The attachment did not match the original file contents'
		);
	}
	
	public function testSendAttachments()
	{
		$token = $this->generateSubjectToken();
		
		$email = new fEmail();
		$email->setFromEmail('will@flourishlib.com');
		$email->addRecipient(EMAIL_ADDRESS, 'Test User');
		$email->setSubject($token . ': Testing Attachments');
		$email->setBody('This is a test of sending an attachment');
		$bar_gif_contents = file_get_contents('./resources/images/bar.gif');
		$email->addAttachment($bar_gif_contents, 'bar.gif');
		$example_json_contents = '{
	"glossary": {
		"title": "example glossary",
		"GlossDiv": {
			"title": "S",
			"GlossList": {
				"GlossEntry": {
					"ID": "SGML",
					"SortAs": "SGML",
					"GlossTerm": "Standard Generalized Markup Language",
					"Acronym": "SGML",
					"Abbrev": "ISO 8879:1986",
					"GlossDef": {
						"para": "A meta-markup language, used to create markup languages such as DocBook.",
						"GlossSeeAlso": ["GML", "XML"]
					},
					"GlossSee": "markup"
				}
			}
		}
	}
}';
		$email->addAttachment($example_json_contents, 'example.json', 'application/json');
		$message_id = $email->send();
		
		$message = $this->findMessage($token);
		$this->assertEquals($message_id, $message['headers']['message-id']);
		$this->assertEquals('will@flourishlib.com', $message['headers']['from']['mailbox'] . '@' . $message['headers']['from']['host']);
		$this->assertEquals($token . ': Testing Attachments', $message['headers']['subject']);
		$this->assertEquals('This is a test of sending an attachment', $message['text']);
		$this->assertEquals(
			array(
				array(
					'filename' => 'bar.gif',
					'mimetype' => 'image/gif',
					'data' => $bar_gif_contents
				),
				array(
					'filename' => 'example.json',
					'mimetype' => 'application/json',
					'data' => $example_json_contents
				)
			),
			$message['attachment'],
			'The attachment did not match the original files\' contents'
		);
	}
	
	public function testSendHtmlAndAttachment()
	{
		$token = $this->generateSubjectToken();
		
		$email = new fEmail();
		$email->setFromEmail('will@flourishlib.com');
		$email->addRecipient(EMAIL_ADDRESS, 'Test User');
		$email->setSubject($token . ': Testing Attachment/HTML');
		$email->setBody('This is a test of sending an attachment with an HTML body');
		$email->setHTMLBody('<h1>Attachment/HTML Body Test</h1>
<p>
	This is a test of sending both an HTML alternative, while also sending an attachment.
</p>');
		$bar_gif_contents = file_get_contents('./resources/images/bar.gif');
		$email->addAttachment($bar_gif_contents, 'bar.gif');
		$message_id = $email->send();
		
		$message = $this->findMessage($token);
		$this->assertEquals($message_id, $message['headers']['message-id']);
		$this->assertEquals('will@flourishlib.com', $message['headers']['from']['mailbox'] . '@' . $message['headers']['from']['host']);
		$this->assertEquals($token . ': Testing Attachment/HTML', $message['headers']['subject']);
		$this->assertEquals('This is a test of sending an attachment with an HTML body', $message['text']);
		$this->assertEquals('<h1>Attachment/HTML Body Test</h1>
<p>
	This is a test of sending both an HTML alternative, while also sending an attachment.
</p>', $message['html']);
		$this->assertEquals(
			array(
				array(
					'filename' => 'bar.gif',
					'mimetype' => 'image/gif',
					'data' => $bar_gif_contents
				)
			),
			$message['attachment'],
			'The attachment did not match the original file contents'
		);
	}
	
	public function testCustomHeaders()
	{
		$token = $this->generateSubjectToken();
		
		$email = new fEmail();
		$email->setFromEmail('will@flourishlib.com');
		$email->addRecipient(EMAIL_ADDRESS, 'Test User');
		$email->setSubject($token . ': Testing Custom Headers');
		$email->setBody('This is a test of sending custom headers');
		$email->addCustomHeader('X-header-1', 'Old value');
		$email->addCustomHeader(array(
			'X-Header-1' => 'New value',
			'X-Header-2' => 'This is a really long header value that should end up being longer the recommended limit of seventy eight characters. It also contains non-ascii characters such as this é.'
		));
		$message_id = $email->send();
		
		$message = $this->findMessage($token);
		$this->assertEquals($message_id, $message['headers']['message-id']);
		$this->assertEquals('will@flourishlib.com', $message['headers']['from']['mailbox'] . '@' . $message['headers']['from']['host']);
		$this->assertEquals($token . ': Testing Custom Headers', $message['headers']['subject']);
		$this->assertEquals('This is a test of sending custom headers', $message['text']);
		$this->assertEquals('New value', $message['headers']['x-header-1']);
		$this->assertEquals('This is a really long header value that should end up being longer the recommended limit of seventy eight characters. It also contains non-ascii characters such as this é.', $message['headers']['x-header-2']);
	}
	
	
	public function testSendPreventHeaderInjection()
	{
		$token = $this->generateSubjectToken();
		
		$email = new fEmail();
		$email->setFromEmail('will@flourishlib.com');
		$email->addRecipient(str_replace('@', "@\n", EMAIL_ADDRESS), "Test\nUser");
		$email->setSubject($token . ': Testing Header Injection');
		$email->setBody('This is a test of removing newlines from recipients and subject headers to help prevent email header injection');
		$message_id = $email->send();
		
		$message = $this->findMessage($token);
		$this->assertEquals($message_id, $message['headers']['message-id']);
		$this->assertEquals('will@flourishlib.com', $message['headers']['from']['mailbox'] . '@' . $message['headers']['from']['host']);
		$this->assertEquals($token . ': Testing Header Injection', $message['headers']['subject']);
		$this->assertEquals('This is a test of removing newlines from recipients and subject headers to help prevent email header injection', $message['text']);
	}
	
	
	public function testClearRecipients()
	{
		$this->setExpectedException('fValidationException');
		$token = $this->generateSubjectToken();
		
		$email = new fEmail();
		$email->setFromEmail('will@flourishlib.com');
		$email->addRecipient(EMAIL_ADDRESS, 'Test User');
		$email->addRecipient(EMAIL_ADDRESS, 'Test User 2');
		$email->setSubject($token . ': Testing Simple Email');
		$email->setBody('This is a simple test');
		
		$email->clearRecipients();
		$email->send();
	}
}