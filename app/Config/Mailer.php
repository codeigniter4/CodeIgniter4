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
}
