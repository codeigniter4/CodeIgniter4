<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Router\Exceptions;

use CodeIgniter\Exceptions\ExceptionInterface;
use CodeIgniter\Exceptions\FrameworkException;

/**
 * RouterException
 */
class RouterException extends FrameworkException implements ExceptionInterface
{
	/**
	 * Thrown when the actual parameter type does not match
	 * the expected types.
	 *
	 * @return RouterException
	 */
	public static function forInvalidParameterType()
	{
		return new static(lang('Router.invalidParameterType'));
	}

	/**
	 * Thrown when a default route is not set.
	 *
	 * @return RouterException
	 */
	public static function forMissingDefaultRoute()
	{
		return new static(lang('Router.missingDefaultRoute'));
	}
}
