<?php

namespace Config;

class Mailer
{
	/**
	 * Default values to use for every email.
	 * Valid keys:
	 * - body, subject, from, to, cc, bcc, replyTo, returnPath, priority, date
	 *
	 * @see \CodeIgniter\Mailer\Email::__construct()
	 *
	 * @var array<string,mixed>
	 */
	public $defaults = [];

	/**
	 * The name of the preferred handler to use.
	 * Testing disables mail by default.
	 *
	 * @var string
	 */
	public $handler = ENVIRONMENT === 'testing' ? 'dummy' : 'mail';

	/**
	 * The User Agent to specify when sending.
	 *
	 * @var string
	 */
	public $userAgent = 'CodeIgniter';

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

	/**
	 * System-specific criteria for handling encodings.
	 *
	 * @see \CodeIgniter\Mailer\Encoder::__construct()
	 *
	 * @var array<string,string>
	 */
	public $encoder = [
		'charset'  => 'UTF-8',
		'encoding' => '8bit',
		'newline'  => "\r\n",
		'crlf'     => "\r\n",
	];
}
