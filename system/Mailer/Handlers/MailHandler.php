<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Mailer\Handlers;

use CodeIgniter\Mailer\Email;
use CodeIgniter\Mailer\Exceptions\MailerException;
use Config\Mimes;
use Psr\Log\LoggerAwareTrait;

/**
 * Mail Handler
 *
 * Handles sending email using the PHP mail() function.
 */
class MailHandler extends BaseHandler
{
	/**
	 * Protocol for this handler.
	 *
	 * @var string
	 */
	protected $protocol = 'mail';

	/**
	 * Whether this handler is supported on this system.
	 *
	 * @return boolean
	 */
	public function isSupported(): bool
	{
		return function_exists('mail');
	}

	/**
	 * Returns the string version of the Headers from the email,
	 * appending a newline and excluding 'Subject' (mail() handles it directly).
	 *
	 * @param Email
	 *
	 * @return string
	 */
	protected function getHeaderString(Email $email): string
	{
		return $email->getHeaderString($this->config->newline, ['Subject']) . $this->conifg->newline;
	}

/* WIP */

	/**
	 * Spools an Email to the server.
	 *
	 * @param Email $email
	 *
	 * @return boolean
	 */
	protected function spool(Email $email)
	{
		$this->unwrapSpecials();

		try
		{
			$success = $this->sendWithMail();
		}
		catch (\ErrorException $e)
		{
			$success = false;
			$this->logger->error('Mailer: ' . $method . ' throwed ' . $e->getMessage());
		}

		if (! $success)
		{
			throw MailerException::forSendFailure($protocol === 'mail' ? 'PHPMail' : ucfirst($protocol));
		}

		$this->setErrorMessage(lang('Mailer.sent', [$protocol]));

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Send using mail()
	 *
	 * @return boolean
	 */
	protected function sendWithMail()
	{
		if (is_array($this->recipients))
		{
			$this->recipients = implode(', ', $this->recipients);
		}

		// _validate_email_for_shell() below accepts by reference,
		// so this needs to be assigned to a variable
		$from = $this->cleanEmail($this->headers['Return-Path']);

		if (! $this->validateEmailForShell($from))
		{
			return mail($this->recipients, $this->subject, $this->finalBody, $this->headerStr);
		}

		// most documentation of sendmail using the "-f" flag lacks a space after it, however
		// we've encountered servers that seem to require it to be in place.
		return mail($this->recipients, $this->subject, $this->finalBody, $this->headerStr, '-f ' . $from);
	}
}
