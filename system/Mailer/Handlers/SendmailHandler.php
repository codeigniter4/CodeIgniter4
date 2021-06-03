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

class SendmailHandler extends BaseHandler
{
	/**
	 * Whether this handler is supported on this system.
	 *
	 * @return boolean
	 */
	public function isSupported(): bool
	{
		return true;
	}

	/**
	 * Spools an Email to the server.
	 *
	 * @param Email $email
	 *
	 * @return boolean
	 */
	protected function spool(Email $email)
	{
	}

	//--------------------------------------------------------------------

/* WIP */

	/**
	 * Send using Sendmail
	 *
	 * @return boolean
	 */
	protected function sendWithSendmail()
	{
		// _validate_email_for_shell() below accepts by reference,
		// so this needs to be assigned to a variable
		$from = $this->cleanEmail($this->headers['From']);
		if ($this->validateEmailForShell($from))
		{
			$from = '-f ' . $from;
		}
		else
		{
			$from = '';
		}

		// is popen() enabled?
		if (! function_usable('popen') || false === ($fp = @popen($this->mailPath . ' -oi ' . $from . ' -t', 'w')))
		{
			// server probably has popen disabled, so nothing we can do to get a verbose error.
			return false;
		}

		fputs($fp, $this->headerStr);
		fputs($fp, $this->finalBody);

		$status = pclose($fp);

		if ($status !== 0)
		{
			throw MailerException::forNosocket($status);
		}

		return true;
	}
}
