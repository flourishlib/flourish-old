<?php
class fMailbox
{
	/**
	 * The imap resource
	 * 
	 * @var resource
	 */
	private $resource;
	
	
	/**
	 * Connects to an IMAP or POP3 server
	 * 
	 * Please note that a connection to a POP3 server will have data remain
	 * static over the lifetime of the connection, but an IMAP connection will
	 * update in real time.
	 * 
	 * @param  string  $host            The POP3 host name
	 * @param  string  $user            The user to log in as
	 * @param  string  $password        The user's password
	 * @param  string  $type            The type of connection: 'imap' or 'pop3'
	 * @param  integer $port            The POP3 - only required if non-standard
	 * @param  boolean $ssl             If SSL should be used for the connection
	 * @param  boolean $silence_errors  If imap errors should be silenced
	 * @return fPOP3
	 */
	public function __construct($host, $user, $password, $type='imap', $port=NULL, $ssl=FALSE, $silence_errors=FALSE)
	{
		if ($type == 'imap' && $port === NULL) {
			$port = 143;	
		} elseif ($type == 'pop3' && $port === NULL) {
			$port = 110;	
		}
		
		$this->resource = imap_open('{' . $host . ':' . $port. '/' . $type . ((!$ssl) ? "/novalidate-cert" : "") . '}INBOX', $user, $password);
		
		$errors = imap_errors();
		if ($errors) {
			$errors = array_diff($errors, array('Mailbox is empty'));
		}
		if ($errors && !$silence_errors) {
			throw new fConnectivityException(
				"The following IMAP errors occurred while connecting:\n%s",
				join("\n", $errors)
			);	
		}
	}
	
	
	public function __destruct()
	{
		if (!$this->resource) {
			return;
		}
		imap_close($this->resource);
	}
	
	
	public function clearCache()
	{
		imap_gc(IMAP_GC_ENV);	
	}
	
	
	/**
	 * Decodes encoded word elements from a header
	 * 
	 * @param mixed $value
	 * @return string
	 */
	private function decodeHeader($value)
	{
		if (substr($value, 0, 2) != '=?') {
			return $value;
		}
		
		$value_objects = imap_mime_header_decode($value);
		$new_value = '';
		$charset   = 'WINDOWS-1252';
		foreach ($value_objects as $value_object) {
			if (preg_match('#^\s+$#D', $value_object->text)) {
				continue;
			}
			$charset    = ($value_object->charset != 'default') ? $value_object->charset : $charset;
			$new_value .= $value_object->text;
		}
		return iconv($charset, 'UTF-8', $new_value);
	}
	
	
	/**
	 * Deletes a message from the server
	 * 
	 * @param  integer $message  The integer message number to retrieve
	 * @return boolean  If the message was successfully deleted
	 */
	public function deleteMessage($message)
	{
		$res = imap_delete($this->resource, $message);
		imap_expunge($this->resource);
		return $res;
	}
	
	
	/**
	 * Extracts all attachments from a message
	 * 
	 * @param  integer $message  The message to get attachments for
	 * @param  array   $parts    The array of part object from imap_fetchstructure()
	 * @return array  An array of attachment info for a message
	 */
	private function extractAttachments($message, $parts)
	{
		$attachments = array();
		for ($i = 0; $i < sizeof($parts); $i++) {
			$part = $parts[$i];
			if (!isset($part->disposition) || strtolower($part->disposition) != 'attachment') {
				continue;
			}
			$attachment = array();
			foreach ($part->dparameters as $parameter) {
				if (strtolower($parameter->attribute) == 'filename') {
					$attachment['filename'] = $parameter->value;
					break;
				}	
			}
			$attachment['mimetype'] = strtolower(
				strtr($part->type, array(
					0 => 'text',
					1 => 'multipart',
					2 => 'message',
					3 => 'application',
					4 => 'audio',
					5 => 'image',
					6 => 'video',
					7 => 'other'
				)) . '/' . strtolower($part->subtype)
			);
			
			$contents = imap_fetchbody($this->resource, $message, $i + 1);
			if ($part->encoding == 3) {
				$attachment['contents'] = base64_decode($contents);		
			} elseif ($part->encoding == 4) {
				$attachment['contents'] = quoted_printable_decode($contents);
			} else {
				$attachment['contents'] = $contents;
			}
			$attachments[] = $attachment;
		}
		return $attachments;
	}
	
	
	/**
	 * Extracts a text (plain or HTML) part of the message
	 * 
	 * @param  integer  $message           The message to retrieve part of
	 * @param  stdClass $part              The object from imap_fetchstructure()
	 * @param  string   $part_num          The IMAP 4 part number - 0-based starting with headers
	 * @param  boolean  $convert_newlines  If \r\n should be converted to \n
	 * @return string  The extracted text, converted to UTF-8
	 */
	private function extractText($message, $part, $part_num, $convert_newlines)
	{
		$charset = 'WINDOWS-1252';
		foreach ($part->parameters as $parameter) {
			if (strtolower($parameter->attribute) == 'charset') {
				$charset = $parameter->value;
				break;
			}	
		}
		$contents = imap_fetchbody($this->resource, $message, $part_num);
		if ($part->encoding == 3) {
			$contents = base64_decode($contents);
		} elseif ($part->encoding == 4) {
			$contents = quoted_printable_decode($contents);
		} else {
			$contents = $contents;
		}
		$contents = iconv($charset, 'UTF-8', preg_replace('#\r\n$#D', '', $contents));
		if ($convert_newlines) {
			$contents = str_replace("\r\n", "\n", $contents);	
		}
		return $contents;
	}
	
	
	/**
	 * Gets message headers from the server
	 * 
	 * @param  integer  $message  The integer message number to retrieve
	 * @return array  The message headers
	 */
	public function getHeaders($message)
	{
		$headers = (array) imap_header($this->resource, $message);
		unset($headers['date']);
		unset($headers['subject']);
		unset($headers['Recent']);
		unset($headers['Unseen']);
		unset($headers['Flagged']);
		unset($headers['Answered']);
		unset($headers['Draft']);
		unset($headers['udate']);
		unset($headers['Deleted']);
		unset($headers['MailDate']);
		
		$headers['Message-Number'] = trim($headers['Msgno']);
		unset($headers['Msgno']);
		
		if (isset($headers['message_id'])) {
			$headers['Message-ID'] = $headers['message_id'];
			unset($headers['message_id']);
		}
		
		$headers['To'] = $headers['to'];
		unset($headers['to']);
		unset($headers['toaddress']);
		
		if (isset($headers['cc'])) {
			$headers['Cc'] = $headers['cc'];
			unset($headers['cc']);
		}
		
		$headers['From'] = $headers['from'];
		unset($headers['from']);
		unset($headers['fromaddress']);
		
		if (isset($headers['reply_toaddress'])) {	
			$headers['Reply-To'] = $headers['reply_to'];
			unset($headers['reply_to']);
			unset($headers['reply_toaddress']);
		}
		
		if (isset($headers['senderaddress'])) {	
			$headers['Sender'] = $headers['sender'];
			unset($headers['sender']);
			unset($headers['senderaddress']);
		}
		
		foreach ($headers as $header => $value) {
			if (is_array($value)) {
				$new_value = '';
				foreach ($value as $sub_value) {
					if ($sub_value instanceof stdClass && isset($sub_value->mailbox) && isset($sub_value->host)) {
						if ($new_value) {
							$new_value .= ', ';
						}
						if (isset($sub_value->personal)) {
							$new_value .= '"' . $this->decodeHeader($sub_value->personal) . '" <';
						}
						$new_value .= $sub_value->mailbox . '@' . $sub_value->host;
						if (isset($sub_value->personal)) {
							$new_value .= '>';
						}
					}
				}
				$value = $new_value;
				$headers[$header] = $value;
			} else {
				$headers[$header] = $this->decodeHeader($value);
			}
		}
		
		return $headers;
	}

	
	/**
	 * Returns info about the mailbox
	 * 
	 * @return array  The mailbox info
	 */
	public function getMailboxInfo()       
	{
		return (array) imap_mailboxmsginfo($this->resource);
	}
	
	
	/**
	 * Gets a message from the server
	 * 
	 * The output includes the following keys:
	 * 
	 *  - `'headers'`: An array of mail headers
	 * 
	 * And one or more of the following:
	 * 
	 *  - `'plain'`: The plaintext body
	 *  - `'html'`: The HTML body
	 *  - `'attachments'`: An array of attachments, each containing:
	 *   - `'filename'`: The name of the file
	 *   - `'mimetype'`: The mimetype of the file
	 *   - `'contents'`: The raw contents of the file
	 * 
	 * @param  integer $message           The integer message number to retrieve
	 * @param  boolean $convert_newlines  If \r\n should be converted to \n
	 * @return array  The message - see method description for details
	 */
	public function getMessage($message, $convert_newlines=FALSE)
	{
		$structure = imap_fetchstructure($this->resource, $message);
		
		$output = array();
		$output['headers'] = $this->getHeaders($message);
		if ($structure->type == 1 && strtolower($structure->subtype) == 'mixed' && $structure->parts[0]->type == 1 && $structure->parts[0]->subtype == 'ALTERNATIVE') {
			$output['plain']       = $this->extractText($message, $structure->parts[0]->parts[0], '1.1', $convert_newlines);
			$output['html']        = $this->extractText($message, $structure->parts[0]->parts[1], '1.2', $convert_newlines);
			$output['attachments'] = $this->extractAttachments($message, $structure->parts);
			
		} elseif ($structure->type == 1 && strtolower($structure->subtype) == 'mixed' && $structure->parts[0]->type == 0) {
			
			$output[strtolower($structure->parts[0]->subtype)] = $this->extractText($message, $structure->parts[0], '1', $convert_newlines);
			$output['attachments'] = $this->extractAttachments($message, $structure->parts);
				
		} elseif ($structure->type == 1 && strtolower($structure->subtype) == 'alternative' && $structure->parts[0]->type == 0) {
			
			$output['plain'] = $this->extractText($message, $structure->parts[0], '1', $convert_newlines);
			$output['html']  = $this->extractText($message, $structure->parts[1], '2', $convert_newlines);
				
		} elseif ($structure->type == 0) {
			
			$output[strtolower($structure->subtype)] = $this->extractText($message, $structure, '1', $convert_newlines);
			
		} else {
			throw new fValidationException('Unknown message format detected');	
		}
		
		return $output;
	}
	
	
	/**
	 * Gets a list of messages from the server
	 * 
	 * @param  integer|string $range  The integer message number, or {integer}:{integer} range of messages to retrieve
	 * @return array  A list of messages on the server
	 */
	public function listMessages($start=1, $limit=NULL)
	{
		if (!$limit) {
			$end = imap_check($this->resource)->Nmsgs;
		} else {
			$end = $limit;
		}
		
		if (!$end) {
			return array();	
		}
		
		if ($start > $end) {
			throw new fProgrammerException('The start, %s, is greater than the number of available messages', $start);	
		}
		
		$output = array();
		foreach (imap_fetch_overview($this->resource, $start . ':' . $end) as $message) {
			$output[$message->msgno] = (array) $message;
		}
		return $output;
	}
}