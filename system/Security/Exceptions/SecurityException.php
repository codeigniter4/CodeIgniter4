<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Security\Exceptions;

use CodeIgniter\Exceptions\FrameworkException;

class SecurityException extends FrameworkException
{
	public static function forDisallowedAction()
	{
		return new static(lang('HTTP.disallowedAction'), 403);
	}

	public static function forInvalidSameSiteSetting(string $samesite)
	{
		return new static(lang('HTTP.invalidSameSiteSetting', [$samesite]));
	}
}
