<?php
require_once('./support/init.php');
 
class fMailboxTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{	
		if (defined('SKIPPING') || !defined('EMAIL_PASSWORD')) {
			$this->markTestSkipped();
		}
		if (defined('EMAIL_DEBUG')) {
			fCore::enableDebugging(TRUE);
		}
	}
	
	public function tearDown()
	{
	}
	
	/*public function testOneTime()
	{
		$mailbox = new fMailbox('localhost', 'will', '');
		$mailbox->addSMIMEPair('tests@flourishlib.com', './email/tests@flourishlib.com.crt', './email/tests@flourishlib.com.key', EMAIL_PASSWORD);
		$messages = array();
		foreach ($mailbox->listMessages() as $uid => $overview) {
			fCore::expose($mailbox->fetchMessage($uid));
		}
	}*/
	
	
	/*public function testImapPop3()
	{
		$imap_messages = array();
		$pop3_messages = array();
		
		$imap_mailbox = new fMailbox('imap', 'imap.gmx.com', 'flourishlib@gmx.com', THIRD_PARTY_EMAIL_PASSWORD);
		$imap_mailbox->addSMIMEPair('tests@flourishlib.com', './email/tests@flourishlib.com.crt', './email/tests@flourishlib.com.key', EMAIL_PASSWORD);
		$messages = array();
		foreach ($imap_mailbox->listMessages(5) as $uid => $overview) {
			$info = $imap_mailbox->fetchMessage($uid);
			
			foreach (array('attachment', 'inline', 'related') as $file_type) {
				if (!isset($info[$file_type])) {
					continue;
				}
				foreach (array_keys($info[$file_type]) as $key) {
					$info[$file_type][$key]['data'] = '{' . strlen($info[$file_type][$key]['data']) . ' bytes}';
				}
			}
			unset($info['uid']);
			
			$imap_messages[] = $info;
		}
		$imap_mailbox->close();
		
		$pop3_mailbox = new fMailbox('pop3', 'pop.gmx.com', 'flourishlib@gmx.com', THIRD_PARTY_EMAIL_PASSWORD);
		$pop3_mailbox->addSMIMEPair('tests@flourishlib.com', './email/tests@flourishlib.com.crt', './email/tests@flourishlib.com.key', EMAIL_PASSWORD);
		$messages = array();
		foreach ($pop3_mailbox->listMessages(5) as $uid => $overview) {
			$info = $pop3_mailbox->fetchMessage($uid);
			
			foreach (array('attachment', 'inline', 'related') as $file_type) {
				if (!isset($info[$file_type])) {
					continue;
				}
				foreach (array_keys($info[$file_type]) as $key) {
					$info[$file_type][$key]['data'] = '{' . strlen($info[$file_type][$key]['data']) . ' bytes}';
				}
			}
			unset($info['uid']);
			
			$pop3_messages[] = $info;
		}
		$pop3_mailbox->close();
		
		$this->assertEquals($imap_messages, $pop3_messages);
	}*/
	
	
	static public function serverProvider()
	{
		$output = array();
		
		if (defined('THIRD_PARTY_EMAIL_PASSWORD')) {
			$output[] = array('imap', 'imap.gmx.com', 143, FALSE, 'flourishlib@gmx.com', THIRD_PARTY_EMAIL_PASSWORD);
			$output[] = array('imap', 'imap.aim.com', 143, FALSE, 'flourishlib@aim.com', THIRD_PARTY_EMAIL_PASSWORD);
			// This IMAP service was still in beta, and seems to be fairly broken
			//$output[] = array('imap', 'imap.zoho.com', 993, TRUE, 'flourishlib@zoho.com', THIRD_PARTY_EMAIL_PASSWORD);
			$output[] = array('imap', 'imap.gmail.com', 993, TRUE, 'flourishlib@gmail.com', THIRD_PARTY_EMAIL_PASSWORD);
			
			// Gmail only allows retrieving a message via POP3 once, so you have to reset the pop access to test via this
			//$output[] = array('pop3', 'pop.gmail.com', 995, TRUE, 'flourishlib@gmail.com', THIRD_PARTY_EMAIL_PASSWORD);
			// This does not have the same messages as the other servers
			//$output[] = array('pop3', 'pop3.live.com', 995, TRUE, 'flourishlib@live.com', THIRD_PARTY_EMAIL_PASSWORD);
			$output[] = array('pop3', 'pop.gmx.com', 110, FALSE, 'flourishlib@gmx.com', THIRD_PARTY_EMAIL_PASSWORD);
			$output[] = array('pop3', 'pop.aim.com', 110, FALSE, 'flourishlib@aim.com', THIRD_PARTY_EMAIL_PASSWORD);
			$output[] = array('pop3', 'pop.zoho.com', 995, TRUE, 'flourishlib@zoho.com', THIRD_PARTY_EMAIL_PASSWORD);
		}
		
		$output[] = array('imap', EMAIL_SERVER, 143, FALSE, EMAIL_ADDRESS, defined('EMAIL_PASSWORD') ? EMAIL_PASSWORD : NULL);
		$output[] = array('pop3', EMAIL_SERVER, 110, FALSE, EMAIL_ADDRESS, defined('EMAIL_PASSWORD') ? EMAIL_PASSWORD : NULL);
		
		return $output;
	}
	
	/**
	 * @dataProvider serverProvider
	 */	
	public function testBadCredentials($type, $host, $port, $secure, $username, $password)
	{
		$this->setExpectedException('fValidationException');
		
		$mailbox = new fMailbox($type, $host, $username, $password . 'hjdkshkdjas', $port, $secure, 5);
		$mailbox->addSMIMEPair('tests@flourishlib.com', './email/tests@flourishlib.com.crt', './email/tests@flourishlib.com.key', EMAIL_PASSWORD);
		$mailbox->listMessages();
	}	
	
	/**
	 * @dataProvider serverProvider
	 */	
	public function testListGet($type, $host, $port, $secure, $username, $password)
	{
		$mailbox = new fMailbox($type, $host, $username, $password, $port, $secure, 5);
		fMailbox::addSMIMEPair('tests@flourishlib.com', './email/tests@flourishlib.com.crt', './email/tests@flourishlib.com.key', EMAIL_PASSWORD);
		$messages = array();
		foreach ($mailbox->listMessages() as $uid => $overview) {
			$info = $mailbox->fetchMessage($uid);
			if (!isset($info['headers'])) {
				fCore::expose($info);
			}
			$messages[$info['headers']['message-id']] = array(
				'subject'   => $info['headers']['subject'],
				'from'      => $info['headers']['from']['mailbox'] . '@' . $info['headers']['from']['host'],
				'to'        => $info['headers']['to'][0]['mailbox'] . '@' . $info['headers']['from']['host'],
				'verified'  => !empty($info['verified']) ? $info['verified'] : NULL,
				'decrypted' => !empty($info['decrypted']) ? $info['decrypted'] : NULL
			);
		}
		
		
		$expected_output = array(
			'<q2gf11c048f1004281807ra1bfdc89j7c0275bcfdbc5f34@mail.gmail.com>' => array(
				'subject' => 'A Tést of Iñtërnâtiônàlizætiøn',
				'from' => 'will@flourishlib.com',
				'to' => 'tests@flourishlib.com',
				'verified' => NULL,
				'decrypted' => NULL
			),
			'<x2rf11c048f1004281814pf5f5b26erc4d91f2bebdbd882@mail.gmail.com>' => array(
				'subject' => 'UTF-8 …',
				'from' => 'will@flourishlib.com',
				'to' => 'tests@flourishlib.com',
				'verified' => NULL,
				'decrypted' => NULL
			),
			'<u2uf11c048f1004281816u3a62bfc7n1961eb403c5eac93@mail.gmail.com>' => array(
				'subject' => 'More UTF-8: ⅞ ⅝ ⅜ ⅛',
				'from' => 'will@flourishlib.com',
				'to' => 'tests@flourishlib.com',
				'verified' => NULL,
				'decrypted' => NULL
			),
			'<j2kf11c048f1004281820u126d756avb7df16d022e237eb@mail.gmail.com>' => array(
				'subject' => 'Attachments Test',
				'from' => 'will@flourishlib.com',
				'to' => 'tests@flourishlib.com',
				'verified' => NULL,
				'decrypted' => NULL
			),
			'<r2xf11c048f1004281822n5370c981r8d1c80953684dd77@mail.gmail.com>' => array(
				'subject' => 'Inline Images and Attachments',
				'from' => 'will@flourishlib.com',
				'to' => 'tests@flourishlib.com',
				'verified' => NULL,
				'decrypted' => NULL
			),
			'<z2lf11c048f1004281828u48cb2c8ci33108197a8db660c@mail.gmail.com>' => array(
				'subject' => 'Multiple To and Cc',
				'from' => 'will@flourishlib.com',
				'to' => 'tests@flourishlib.com',
				'verified' => NULL,
				'decrypted' => NULL
			),
			'<h2rd0cf2b3e1004281830q66be99f1j9b08abacf56b6c04@mail.gmail.com>' => array(
				'subject' => 'Re: Multiple To and Cc',
				'from' => 'flourishlib@gmail.com',
				'to' => 'flourishlib@gmail.com',
				'verified' => NULL,
				'decrypted' => NULL
			),
			'<1284737a820.1113498215007594659.3298566658971949728@zoho.com>' => array(
				'subject' => 'Re: Multiple To and Cc',
				'from' => 'flourishlib@zoho.com',
				'to' => 'flourishlib@zoho.com',
				'verified' => NULL,
				'decrypted' => NULL
			),
			'<8CCB5542832A7C9-1F1C-35E4@webmail-d017.sysops.aol.com>' => array(
				'subject' => 'Re: Multiple To and Cc',
				'from' => 'flourishlib@aim.com',
				'to' => 'flourishlib@aim.com',
				'verified' => NULL,
				'decrypted' => NULL
			),
			'<4BD8E4BF.4090207@flourishlib.com>' => array(
				'subject' => 'Re: Multiple To and Cc',
				'from' => 'tests@flourishlib.com',
				'to' => 'flourishlib@flourishlib.com',
				'verified' => NULL,
				'decrypted' => NULL
			),
			'<20100429014944.259410@gmx.com>' => array(
				'subject' => 'Re: Multiple To and Cc',
				'from' => 'flourishlib@gmx.com',
				'to' => 'tests@gmx.com',
				'verified' => NULL,
				'decrypted' => NULL
			),
			'<4BD8E692.1000308@flourishlib.com>' => array(
				'subject' => 'This message has been signed',
				'from' => 'tests@flourishlib.com',
				'to' => 'tests@flourishlib.com',
				'verified' => TRUE,
				'decrypted' => NULL
			),
			'<4BD8E723.2090502@flourishlib.com>' => array(
				'subject' => 'This message has also been signed',
				'from' => 'tests@flourishlib.com',
				'to' => 'tests@flourishlib.com',
				'verified' => TRUE,
				'decrypted' => NULL
			)
		);
		
		if ($username == 'tests@flourishlib.com') {
			$expected_output['<4BD8E7EF.1010806@flourishlib.com>'] = array(
				'subject' => 'This message is signed and encrypted',
				'from' => 'tests@flourishlib.com',
				'to' => 'tests@flourishlib.com',
				'verified' => TRUE,
				'decrypted' => TRUE
			);
			$expected_output['<4BD8E815.1050209@flourishlib.com>'] = array(
				'subject' => 'This message is encrypted',
				'from' => 'tests@flourishlib.com',
				'to' => 'tests@flourishlib.com',
				'verified' => NULL,
				'decrypted' => TRUE
			);
		}
		
		$expected_output = array_merge($expected_output, array(
			'<14776209491fdb20ccc4f78438756ee3@flourish.wbond.net>' => array(
				'subject' => 'This is a test of fEmail signing',
				'from' => 'tests@flourishlib.com',
				'to' => 'tests@flourishlib.com',
				'verified' => TRUE,
				'decrypted' => NULL
			),
			'<e92c457a98a30d85fcb4c98b169dd31f@flourish.wbond.net>' => array(
				'subject' => 'This is a test of fEmail encryption',
				'from' => 'tests@flourishlib.com',
				'to' => 'tests@flourishlib.com',
				'verified' => NULL,
				'decrypted' => TRUE
			),
			'<3f6a8d86e841be31e25c1df8fd303a5d@flourish.wbond.net>' => array(
				'subject'   => 'This is a test of fEmail encryption + signing',
				'from'      => 'tests@flourishlib.com',
				'to'        => 'tests@flourishlib.com',
				'verified'  => TRUE,
				'decrypted' => TRUE,
			)
		));
		
		$this->assertEquals(
			$expected_output,
			$messages
		);
	}
}
