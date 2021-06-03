<?php

namespace Config;

/**
 * Mailer Config
 *
 * Configuration settings for using the Mailer classes.
 * This file has four sections:
 *  1. Email Settings: Default fallback values to apply to each Email
 *  3. Spool Settings: Options for format and bulk handling
 *  3. System Settings: Environment settings that affect email encoding
 *  2. Handler Settings: Configurations specific to each handler
 */
class Mailer
{
	//--------------------------------------------------------------------
	// Email Settings
	//--------------------------------------------------------------------

	/**
	 * Default values to use for every email (still overriden by parameters).
	 *
	 * Valid keys:
	 * - body, subject, from, to, cc, bcc, replyTo, returnPath, priority, date
	 *
	 * @see \CodeIgniter\Mailer\Email::__construct()
	 *
	 * @var array<string,mixed>
	 */
	public $defaults = [];

	/**
	 * String to use for the User Agent header.
	 *
	 * @var string
	 */
	public $userAgent = 'CodeIgniter';

	//--------------------------------------------------------------------
	// Spool Settings
	//--------------------------------------------------------------------

	/**
	 * Mail format, either 'text' or 'html'.
	 *
	 * @var string
	 */
	public $mailFormat = 'text';

	/**
	 * Enable word-wrap for text emails.
	 *
	 * @var boolean
	 */
	public $wordWrap = true;

	/**
	 * Character count to wrap at
	 *
	 * @var integer
	 */
	public $wrapChars = 76;

	/**
	 * Whether to send bulk BCC emails in batches.
	 *
	 * @var boolean
	 */
	public $batchMode = false;

	/**
	 * Number of emails in each BCC batch.
	 *
	 * @var integer
	 */
	public $batchSize = 200;

	//--------------------------------------------------------------------
	// System Settings
	//--------------------------------------------------------------------

	/**
	 * Character set (default: UTF-8).
	 *
	 * @var string
	 */
	public $charset = 'UTF-8';

	/**
	 * Mail encoding.
	 *
	 * @var string '8bit' or '7bit'
	 */
	public $encoding = '8bit';

	/**
	 * Newline character sequence.
	 * Use "\r\n" to comply with RFC 822.
	 *
	 * @link http://www.ietf.org/rfc/rfc822.txt
	 * @var  string "\r\n" or "\n"
	 */
	public $newline = "\r\n";

	/**
	 * CRLF character sequence.
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
	public $crlf = "\r\n";

	//--------------------------------------------------------------------
	// Handler Settings
	//--------------------------------------------------------------------

	/**
	 * The name of the preferred handler to use.
	 * Note: Email is disable during testing by the Dummy handler.
	 *
	 * @var string
	 */
	public $handler = ENVIRONMENT === 'testing' ? 'dummy' : 'mail';

	/**
	 * @var array<string,mixed>
	 */
	public $sendmail = [
		// The server path to Sendmail
		'mailpath' => '/usr/sbin/sendmail',
	];

	/**
	 * @var array<string,mixed>
	 */
	public $smtp = [
		'hostname'  => '',
		'username'  => '',
		'password'  => '',
		'port'      => 25,
		'timeout'   => 5,
		'keepalive' => false,
		'encrypt'   => 'tls', // tls or ssl
	];
}
