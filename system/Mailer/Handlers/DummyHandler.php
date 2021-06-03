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

class DummyHandler extends BaseHandler
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
	 * Does not spools an Email to the server.
	 *
	 * @param Email $email
	 *
	 * @return boolean
	 */
	protected function spool(Email $email)
	{
	}
}
