<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Cookie\Exceptions;

use CodeIgniter\Exceptions\ExceptionInterface;
use CodeIgniter\Exceptions\FrameworkException;

class CookieException extends FrameworkException implements ExceptionInterface
{
	public static function forInvalidSameSite(string $samesite)
	{
		return new static(lang('Cookie.invalidSameSite', [$samesite]));
	}
}
