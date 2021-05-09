<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Debug\Deprecation;

use ErrorException;

/**
 * ErrorException for the deprecation messages.
 *
 * @internal
 */
final class DeprecationException extends ErrorException
{
	/**
	 * Constructor.
	 *
	 * @param string $message
	 */
	public function __construct(string $message)
	{
		// Always make sure the message is prepended
		if (substr($message, 0, 12) !== 'DEPRECATED: ')
		{
			$message = 'DEPRECATED: ' . $message;
		}

		parent::__construct($message, 0, E_USER_DEPRECATED);
	}
}
