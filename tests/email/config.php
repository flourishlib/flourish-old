<?php
include_once 'support/constants.php';
date_default_timezone_set('America/New_York');

define('EMAIL_SERVER', 'mail.flourishlib.com');
define('EMAIL_USER', 'tests');
define('EMAIL_ADDRESS', 'tests@flourishlib.com');
// The EMAIL_PASSWORD needs to be passed to the test runner as a
// parameter in the form: =EMAIL_PASSWORD:password

// Delete any messages that shouldn't be there
if (defined('EMAIL_PASSWORD')) {
	function email_autoload($class_name)
	{
		$file = '../classes/' . $class_name . '.php';
		if (file_exists($file)) {
			require_once($file);
			return;
		}
	}
	spl_autoload_register('email_autoload');
	
	$reference_date = strtotime('5/1/2010');
	
	$mailbox  = new fMailbox('imap', EMAIL_SERVER, EMAIL_USER, EMAIL_PASSWORD);
	$messages = $mailbox->listMessages();
	foreach ($messages as $uid => $info) {
		$date = strtotime($info['date']);
		if ($date > $reference_date) {
			$mailbox->deleteMessages($uid);
		}
	}
}