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
use CodeIgniter\Mailer\MailerInterface;
use Config\Mailer;

class DummyHandler implements MailerInterface
{
	/**
	 * @param Mailer $config
	 */
	public function __construct(Mailer $config)
	{
	}

	/**
	 * Does not send an Email
	 *
	 * @param Email $email
	 */
	public function send(Email $email)
	{
	}
}
