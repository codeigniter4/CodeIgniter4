<?php namespace CodeIgniter\Mailer;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2019, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package    CodeIgniter
 * @author     EllisLab Dev Team
 * @copyright  Copyright (c) 2008 - 2014, EllisLab, Inc. (https://ellislab.com/)
 * @copyright  Copyright (c) 2014 - 2020, British Columbia Institute of Technology (http://bcit.ca/)
 * @license    http://opensource.org/licenses/MIT    MIT License
 * @link       https://codeigniter.com
 * @since      Version 1.0.0
 * @filesource
 */

use CodeIgniter\Config\Services;
use CodeIgniter\Mailer\Exceptions\MailerException;
use Config\Mailer as MailerConfig;
use Config\Mimes;
use Psr\Log\LoggerAwareTrait;

/**
 * CodeIgniter Message Class
 *
 * Object model for a message to be sent using Mail, Sendmail, or SMTP.
 */
class Message
{
	use LoggerAwareTrait;

	/**
	 * @var string
	 */
	public $fromEmail;

	/**
	 * @var string
	 */
	public $fromName;

	/**
	 * Whether to apply word-wrapping to the message body.
	 *
	 * @var boolean
	 */
	public $wordWrap = true;

	/**
	 * Number of characters to wrap at.
	 *
	 * @see self::$wordWrap
	 * @var integer
	 */
	public $wrapChars = 76;

	/**
	 * Message format.
	 *
	 * @var string 'text' or 'html'
	 */
	public $mailType = 'text';

	/**
	 * Character set (default: utf-8)
	 *
	 * @var string
	 */
	public $charset = 'utf-8';

	/**
	 * Alternative message (for HTML messages only)
	 *
	 * @var string
	 */
	public $altMessage = '';

	/**
	 * Whether to validate e-mail addresses.
	 *
	 * @var boolean
	 */
	public $validate = true;

	/**
	 * X-Priority header value.
	 *
	 * @var integer 1-5
	 */
	public $priority = 3;   // Default priority (1 - 5)

	/**
	 * Newline character sequence.
	 * Use "\r\n" to comply with RFC 822.
	 *
	 * @link http://www.ietf.org/rfc/rfc822.txt
	 * @var  string "\r\n" or "\n"
	 */
	public $newline = "\n";   // Default newline. "\r\n" or "\n" (Use "\r\n" to comply with RFC 822)

	/**
	 * CRLF character sequence
	 *
	 * RFC 2045 specifies that for 'quoted-printable' encoding,
	 * "\r\n" must be used. However, it appears that some servers
	 * (even on the receiving end) don't handle it properly and
	 * switching to "\n", while improper, is the only solution
	 * that seems to work for all environments.
	 *
	 * @link http://www.ietf.org/rfc/rfc822.txt
	 * @var  string
	 */
	public $CRLF = "\n";

	/**
	 * Whether to use Delivery Status Notification.
	 *
	 * @var boolean
	 */
	public $DSN = false;

	/**
	 * Whether to send multipart alternatives.
	 * Yahoo! doesn't seem to like these.
	 *
	 * @var boolean
	 */
	public $sendMultipart = true;

	//--------------------------------------------------------------------

	/**
	 * Subject header
	 *
	 * @var string
	 */
	protected $subject = '';

	/**
	 * Message body
	 *
	 * @var string
	 */
	protected $body = '';

	/**
	 * Final message body to be sent.
	 *
	 * @var string
	 */
	protected $finalBody = '';

	/**
	 * Final headers to send
	 *
	 * @var string
	 */
	protected $headerStr = '';

	/**
	 * Mail encoding
	 *
	 * @var string '8bit' or '7bit'
	 */
	protected $encoding = '8bit';

	/**
	 * Whether to send a Reply-To header
	 *
	 * @var boolean
	 */
	protected $replyToFlag = false;

	/**
	 * Debug messages
	 *
	 * @see self::printDebugger()
	 * @var array
	 */
	protected $debugMessage = [];

	/**
	 * Recipients
	 *
	 * @var array
	 */
	protected $recipients = [];

	/**
	 * CC Recipients
	 *
	 * @var array
	 */
	protected $CCArray = [];

	/**
	 * BCC Recipients
	 *
	 * @var array
	 */
	protected $BCCArray = [];

	/**
	 * Message headers
	 *
	 * @var array
	 */
	protected $headers = [];

	/**
	 * Attachment data
	 *
	 * @var array
	 */
	protected $attachments = [];

	/**
	 * Which protocol are we using?
	 *
	 * @var string
	 */
	protected $protocol = 'mail';

	/**
	 * Valid $protocol values
	 *
	 * @see self::$protocol
	 * @var array
	 */
	protected $protocols = [
		'mail',
		'sendmail',
		'smtp',
	];

	/**
	 * Base charsets
	 *
	 * Character sets valid for 7-bit encoding,
	 * excluding language suffix.
	 *
	 * @var array
	 */
	protected $baseCharsets = [
		'us-ascii',
		'iso-2022-',
	];

	/**
	 * Bit depths
	 *
	 * Valid mail encodings
	 *
	 * @see self::$encoding
	 * @var array
	 */
	protected $bitDepths = [
		'7bit',
		'8bit',
	];

	/**
	 * $priority translations
	 *
	 * Actual values to send with the X-Priority header
	 *
	 * @var array
	 */
	protected $priorities = [
		1 => '1 (Highest)',
		2 => '2 (High)',
		3 => '3 (Normal)',
		4 => '4 (Low)',
		5 => '5 (Lowest)',
	];

	/**
	 * Logger instance to record error messages and awarnings.
	 *
	 * @var \Psr\Log\LoggerInterface
	 */
	protected $logger;

	/**
	 * Mailer that this message is bound to
	 *
	 * @var \CodeIgniter\Mailer\MailerInterface
	 */
	protected $handler;

	/**
	 * Config settings, used if protocol changed.
	 *
	 * @var MailerConfig
	 */
	protected $config = null;

	//--------------------------------------------------------------------

	/**
	 * Constructor - Sets Preferences
	 *
	 * The constructor can be passed an array of config values
	 *
	 * @param array|null $config
	 */
	public function __construct($config = null)
	{
		if ($config === null)
		{
			$config       = new \Config\Mailer();
			$this->config = $config;
		}
		else if (is_array($config))
		{
			$mergedConfig = new \Config\Mailer();
			foreach ($config as $key => $value)
			{
				$mergedConfig->$key = $value;
			}
			$this->config = $mergedConfig;
		}
		$this->initialize($config);
	}

	//--------------------------------------------------------------------

	/**
	 * Initialize preferences
	 *
	 * @param array|\Config\Mailer $config
	 *
	 * @return $this
	 */
	public function initialize($config)
	{
		$this->clear();
		if ($config instanceof \Config\Mailer)
		{
			$config = get_object_vars($config);
		}

		foreach (get_class_vars(get_class($this)) as $key => $value)
		{
			if (property_exists($this, $key) && isset($config[$key]))
			{
				$method = 'set' . ucfirst($key);

				if (method_exists($this, $method))
				{
					$this->$method($config[$key]);
				}
				else
				{
					$this->$key = $config[$key];
				}
			}
		}

		$this->charset = strtoupper($this->charset);
		$this->handler = Services::mailer($config);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Set the protocol to use.
	 * If valid, get an appropriate handler for it.
	 *
	 * @param string $protocol
	 *
	 * @returns $this
	 */
	public function setProtocol(string $protocol = 'mail')
	{
		if (! in_array($protocol, $this->protocols))
		{
			throw MailerException::forInvalidProtocol($protocol);
		}

		$this->protocol         = $protocol;
		$this->config->protocol = $protocol; // update config too
		$this->handler          = Services::mailer($this->config, false);

		return $this;
	}

	/**
	 * Get the protocol our handler is using.
	 * Note: the protocol property here is only the requested one.
	 *
	 * @return string
	 */
	public function getProtocol(): string
	{
		return $this->handler->getProtocol();
	}

	//--------------------------------------------------------------------

	/**
	 * Initialize the Data
	 *
	 * @param boolean $clearAttachments
	 *
	 * @return $this
	 */
	public function clear($clearAttachments = false)
	{
		$this->subject     = '';
		$this->body        = '';
		$this->finalBody   = '';
		$this->headerStr   = '';
		$this->replyToFlag = false;
		$this->recipients  = [];
		$this->CCArray     = [];
		$this->BCCArray    = [];
		$this->headers     = [];

		$this->setHeader('Date', $this->setDate());

		if ($clearAttachments !== false)
		{
			$this->attachments = [];
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Set FROM
	 *
	 * @param string      $from
	 * @param string      $name
	 * @param string|null $returnPath Return-Path
	 *
	 * @return $this
	 */
	public function setFrom($from, $name = '', $returnPath = null)
	{
		if (preg_match('/\<(.*)\>/', $from, $match))
		{
			$from = $match[1];
		}

		if ($this->validate)
		{
			$this->validateEmail($from);
			if ($returnPath)
			{
				$this->validateEmail($returnPath);
			}
		}

		// prepare the display name
		if ($name !== '')
		{
			// only use Q encoding if there are characters that would require it
			if (! preg_match('/[\200-\377]/', $name))
			{
				// add slashes for non-printing characters, slashes, and double quotes, and surround it in double quotes
				$name = '"' . addcslashes($name, "\0..\37\177'\"\\") . '"';
			}
			else
			{
				$name = $this->prepQEncoding($name);
			}
		}

		$this->setHeader('From', $name . ' <' . $from . '>');
		isset($returnPath) || $returnPath = $from;
		$this->setHeader('Return-Path', '<' . $returnPath . '>');

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Prep Q Encoding
	 *
	 * Performs "Q Encoding" on a string for use in headers.
	 * It's related but not identical to quoted-printable, so it has its
	 * own method.
	 *
	 * @param string $str
	 *
	 * @return string
	 */
	protected function prepQEncoding($str)
	{
		$str = str_replace(["\r", "\n"], '', $str);

		if ($this->charset === 'UTF-8')
		{
			// Note: We used to have mb_encode_mimeheader() as the first choice
			//       here, but it turned out to be buggy and unreliable. DO NOT
			//       re-add it! -- Narf
			if (extension_loaded('iconv'))
			{
				$output = @iconv_mime_encode(
								'', $str, [
									'scheme'           => 'Q',
									'line-length'      => 76,
									'input-charset'    => $this->charset,
									'output-charset'   => $this->charset,
									'line-break-chars' => $this->CRLF,
								]);

				// There are reports that iconv_mime_encode() might fail and return FALSE
				if ($output !== false)
				{
					// iconv_mime_encode() will always put a header field name.
					// We've passed it an empty one, but it still prepends our
					// encoded string with ': ', so we need to strip it.
					return mb_substr($output, 2);
				}

				$chars = iconv_strlen($str, 'UTF-8');
			}
			elseif (extension_loaded('mbstring'))
			{
				$chars = mb_strlen($str, 'UTF-8');
			}
		}

		// We might already have this set for UTF-8
		isset($chars) || $chars = mb_strlen($str);

		$output = '=?' . $this->charset . '?Q?';
		for ($i = 0, $length = mb_strlen($output); $i < $chars; $i ++)
		{
			$chr = ($this->charset === 'UTF-8' && ICONV_ENABLED === true) ? '=' . implode('=', str_split(strtoupper(bin2hex(iconv_substr($str, $i, 1, $this->charset))), 2)) : '=' . strtoupper(bin2hex($str[$i]));

			// RFC 2045 sets a limit of 76 characters per line.
			// We'll append ?= to the end of each line though.
			if ($length + ($l = mb_strlen($chr)) > 74)
			{
				$output .= '?=' . $this->CRLF // EOL
						. ' =?' . $this->charset . '?Q?' . $chr; // New line
				$length  = 6 + mb_strlen($this->charset) + $l; // Reset the length for the new line
			}
			else
			{
				$output .= $chr;
				$length += $l;
			}
		}

		// End the header
		return $output . '?=';
	}

	//--------------------------------------------------------------------

	/**
	 * Set Reply-to
	 *
	 * @param string $replyto
	 * @param string $name
	 *
	 * @return $this
	 */
	public function setReplyTo($replyto, $name = '')
	{
		if (preg_match('/\<(.*)\>/', $replyto, $match))
		{
			$replyto = $match[1];
		}

		if ($this->validate)
		{
			$this->validateEmail($this->stringToArray($replyto));
		}

		if ($name !== '')
		{
			// only use Q encoding if there are characters that would require it
			if (! preg_match('/[\200-\377]/', $name))
			{
				// add slashes for non-printing characters, slashes, and double quotes, and surround it in double quotes
				$name = '"' . addcslashes($name, "\0..\37\177'\"\\") . '"';
			}
			else
			{
				$name = $this->prepQEncoding($name);
			}
		}

		$this->setHeader('Reply-To', $name . ' <' . $replyto . '>');
		$this->replyToFlag = true;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Set Recipients
	 *
	 * @param string|array $to One or more recipients
	 *
	 * @return $this
	 */
	public function setTo($to)
	{
		$to = $this->stringToArray($to);
		$to = $this->cleanEmail($to);

		if ($this->validate)
		{
			$this->validateEmail($to);
		}

		if ($this->handler->getProtocol() !== 'mail')
		{
			$this->setHeader('To', implode(', ', $to));
		}

		$this->recipients = $to;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Set CC
	 *
	 * @param string|array $cc
	 *
	 * @return $this
	 */
	public function setCC($cc)
	{
		$cc = $this->cleanEmail($this->stringToArray($cc));

		if ($this->validate)
		{
			$this->validateEmail($cc);
		}

		$this->setHeader('Cc', implode(', ', $cc));

		//      if ($this->handler->getProtocol() === 'smtp')
		//      {
		$this->CCArray = $cc;
		//      }

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Set BCC
	 *
	 * @param string|array $bcc
	 * @param integer|null $limit
	 *
	 * @return $this
	 */
	public function setBCC($bcc, int $limit = null)
	{
		if ($limit !== null)
		{
			$this->BCCBatchMode = true;
			$this->BCCBatchSize = $limit;
		}

		$bcc = $this->cleanEmail($this->stringToArray($bcc));

		if ($this->validate)
		{
			$this->validateEmail($bcc);
		}

		//      if ($this->handler->getProtocol() === 'smtp' ||
		//              if ($this->BCCBatchMode && count($bcc) > $this->BCCBatchSize)
		//      {
		$this->BCCArray = $bcc;
		//      }
		//      else
		//      {
		$this->setHeader('Bcc', implode(', ', $bcc));
		//      }

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Set Subject
	 *
	 * @param string $subject
	 *
	 * @return $this
	 */
	public function setSubject($subject)
	{
		$subject = $this->prepQEncoding($subject);
		$this->setHeader('Subject', $subject);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Set Body
	 *
	 * @param string $body
	 *
	 * @return $this
	 */
	public function setMessage($body)
	{
		$this->body = rtrim(str_replace("\r", '', $body));

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Assign file attachments
	 *
	 * @param string $file        Can be local path, URL or buffered content
	 * @param string $disposition 'attachment'
	 * @param string $newName
	 * @param string $mime        Not-empty if $file is buffered content
	 *
	 * @return $this
	 */
	public function attach(string $file, string $disposition = '', string $newName = '', string $mime = '')
	{
		if ($mime === '')
		{
			if (strpos($file, '://') === false && ! is_file($file))
			{
				throw MailerException::forAttachmentMissing($file);
			}

			if (! $fp = @fopen($file, 'rb'))
			{
				throw MailerException::forAttachmentUnreadable($file);
			}

			$fileContent = stream_get_contents($fp);
			$mime        = $this->mimeTypes(pathinfo($file, PATHINFO_EXTENSION));
			fclose($fp);
		}
		else
		{
			$fileContent = & $file; // buffered file
		}

		// declare names on their own, to make phpcbf happy
		$namesAttached       = [
			$file,
			$newName,
		];
		$this->attachments[] = [
			'name'        => $namesAttached,
			'disposition' => empty($disposition) ? 'attachment' : $disposition,
			// Can also be 'inline'  Not sure if it matters
			'type'        => $mime,
			'content'     => chunk_split(base64_encode($fileContent)),
			'multipart'   => 'mixed',
		];

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Add a Header Item
	 *
	 * @param string $header
	 * @param string $value
	 *
	 * @return $this
	 */
	public function setHeader($header, $value)
	{
		$this->headers[$header] = str_replace(["\n", "\r"], '', $value);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Convert a String to an Array
	 *
	 * @param string $email
	 *
	 * @return array
	 */
	protected function stringToArray($email)
	{
		if (! is_array($email))
		{
			return (strpos($email, ',') !== false) ? preg_split('/[\s,]/', $email, -1, PREG_SPLIT_NO_EMPTY) : (array) trim($email);
		}

		return $email;
	}

	//--------------------------------------------------------------------

	/**
	 * Set Multipart Value
	 *
	 * @param string $str
	 *
	 * @return $this
	 */
	public function setAltMessage($str)
	{
		$this->altMessage = (string) $str;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Set Mailtype
	 *
	 * @param string $type
	 *
	 * @return $this
	 */
	public function setMailType($type = 'text')
	{
		$this->mailType = ($type === 'html') ? 'html' : 'text';

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Set Wordwrap
	 *
	 * @param boolean $wordWrap
	 *
	 * @return $this
	 */
	public function setWordWrap($wordWrap = true)
	{
		$this->wordWrap = (bool) $wordWrap;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Set Priority
	 *
	 * @param integer $n
	 *
	 * @return $this
	 */
	public function setPriority($n = 3)
	{
		$this->priority = preg_match('/^[1-5]$/', $n) ? (int) $n : 3;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Set Newline Character
	 *
	 * @param string $newline
	 *
	 * @return $this
	 */
	public function setNewline($newline = "\n")
	{
		$this->newline = in_array($newline, ["\n", "\r\n", "\r"]) ? $newline : "\n";

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Set CRLF
	 *
	 * @param string $CRLF
	 *
	 * @return $this
	 */
	public function setCRLF($CRLF = "\n")
	{
		$this->CRLF = ($CRLF !== "\n" && $CRLF !== "\r\n" && $CRLF !== "\r") ? "\n" : $CRLF;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Set RFC 822 Date
	 *
	 * @return string
	 */
	protected function setDate()
	{
		$timezone = date('Z');
		$operator = ($timezone[0] === '-') ? '-' : '+';
		$timezone = abs($timezone);
		$timezone = floor($timezone / 3600) * 100 + ($timezone % 3600) / 60;

		return sprintf('%s %s%04d', date('D, j M Y H:i:s'), $operator, $timezone);
	}

	//--------------------------------------------------------------------

	/**
	 * Validate Email Address(es)
	 *
	 * @param string|array $email
	 *
	 * @return boolean
	 */
	public function validateEmail($email)
	{
		if (! is_array($email))
		{
			$email = [$email];
		}

		foreach ($email as $val)
		{
			if (! $this->isValidEmail($val))
			{
				throw MailerException::forInvalidAddress($val);
			}
		}

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Email Validation
	 *
	 * @param string $email
	 *
	 * @return boolean
	 */
	public function isValidEmail($email)
	{
		$email = $this->cleanEMail($email);

		// sanitize the domain name
		$atpos = strpos($email, '@');
		$email = mb_substr($email, 0, ++ $atpos, '8bit') . idn_to_ascii(
						mb_substr($email, $atpos), 0, INTL_IDNA_VARIANT_UTS46
		);

		return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
	}

	//--------------------------------------------------------------------

	/**
	 * Clean Extended Email Address: Joe Smith <joe@smith.com>
	 *
	 * @param string|array $email An email string, or array of them
	 *
	 * @return string|array The "clean" email-only address(es)
	 */
	public function cleanEmail($email)
	{
		if (! is_array($email))
		{
			return preg_match('/\<(.*)\>/', $email, $match) ? $match[1] : $email;
		}

		$cleanEmail = [];

		foreach ($email as $addy)
		{
			$cleanEmail[] = preg_match('/\<(.*)\>/', $addy, $match) ? $match[1] : $addy;
		}

		return $cleanEmail;
	}

	//--------------------------------------------------------------------

	/**
	 * Word Wrap
	 *
	 * @param string       $str
	 * @param integer|null $charlim Line-length limit
	 *
	 * @return string
	 */
	public function wordWrap($str, $charlim = null)
	{
		// Set the character limit, if not already present
		if (empty($charlim))
		{
			$charlim = empty($this->wrapChars) ? 76 : $this->wrapChars;
		}

		// Standardize newlines
		if (strpos($str, "\r") !== false)
		{
			$str = str_replace(["\r\n", "\r"], "\n", $str);
		}

		// Reduce multiple spaces at end of line
		$str = preg_replace('| +\n|', "\n", $str);

		// If the current word is surrounded by {unwrap} tags we'll
		// strip the entire chunk and replace it with a marker.
		$unwrap = [];
		if (preg_match_all('|\{unwrap\}(.+?)\{/unwrap\}|s', $str, $matches))
		{
			for ($i = 0, $c = count($matches[0]); $i < $c; $i ++)
			{
				$unwrap[] = $matches[1][$i];
				$str      = str_replace($matches[0][$i], '{{unwrapped' . $i . '}}', $str);
			}
		}

		// Use PHP's native function to do the initial wordwrap.
		// We set the cut flag to FALSE so that any individual words that are
		// too long get left alone. In the next step we'll deal with them.
		$str = wordwrap($str, $charlim, "\n", false);

		// Split the string into individual lines of text and cycle through them
		$output = '';
		foreach (explode("\n", $str) as $line)
		{
			// Is the line within the allowed character count?
			// If so we'll join it to the output and continue
			if (mb_strlen($line) <= $charlim)
			{
				$output .= $line . $this->newline;
				continue;
			}

			$temp = '';
			do
			{
				// If the over-length word is a URL we won't wrap it
				if (preg_match('!\[url.+\]|://|www\.!', $line))
				{
					break;
				}

				// Trim the word down
				$temp .= mb_substr($line, 0, $charlim - 1, '8bit');
				$line  = mb_substr($line, $charlim - 1);
			}
			while (mb_strlen($line) > $charlim);

			// If $temp contains data it means we had to split up an over-length
			// word into smaller chunks so we'll add it back to our current line
			if ($temp !== '')
			{
				$output .= $temp . $this->newline;
			}

			$output .= $line . $this->newline;
		}

		// Put our markers back
		if ($unwrap)
		{
			foreach ($unwrap as $key => $val)
			{
				$output = str_replace('{{unwrapped' . $key . '}}', $val, $output);
			}
		}

		return $output;
	}

	//--------------------------------------------------------------------

	/**
	 * Send the message
	 *
	 * @param boolean $autoClear
	 * @param boolean $reallySend
	 *
	 * @return boolean
	 */
	public function send($autoClear = true, bool $reallySend = true)
	{
		// our decision here, whether to send or fake it
		$result = $reallySend ? $this->handler->send($this, $autoClear) : true;

		if ($result && $autoClear)
		{
			$this->clear();
		}

		return $result;
	}

	//--------------------------------------------------------------------

	/**
	 * Mime Types
	 *
	 * @param string $ext
	 *
	 * @return string
	 */
	protected function mimeTypes($ext = '')
	{
		$mime = Mimes::guessTypeFromExtension(strtolower($ext));

		return ! empty($mime) ? $mime : 'application/x-unknown-content-type';
	}

	//--------------------------------------------------------------------

	/**
	 * Lookup an email header and return its value.
	 *
	 * @param string $key
	 *
	 * @return string|null
	 */
	public function getHeader(string $key = ''): ?string
	{
		return $this->headers[$key] ?? null;
	}

	//--------------------------------------------------------------------

	/**
	 * Magic method to all protected/private class properties to be easily set,
	 * if a property has a setter method.
	 *
	 * @param string $key
	 * @param null   $value
	 *
	 * @return $this
	 */
	public function __set(string $key, $value = null)
	{
		$method = 'set' . str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $key)));
		if (method_exists($this, $method))
		{
			$this->$method($value);
		}

		return $this;
	}

	/**
	 * Magic method to allow retrieval of protected
	 * class properties either by their name, or through a `getCamelCasedProperty()`
	 * method.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function __get(string $key)
	{
		// Convert to CamelCase for the method
		$method = 'get' . str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $key)));
		if (method_exists($this, $method))
		{
			$result = $this->$method();
		}
		else if (property_exists($this, $key))
		{
			$result = $this->$key;
		}

		return $result ?? null;
	}

}
