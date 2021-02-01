<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\CLI\Exceptions;

use CodeIgniter\Exceptions\DebugTraceableTrait;
use RuntimeException;

/**
 * CLIException
 */
class CLIException extends RuntimeException
{
	use DebugTraceableTrait;

	/**
	 * Thrown when `$color` specified for `$type` is not within the
	 * allowed list of colors.
	 *
	 * @param string $type
	 * @param string $color
	 *
	 * @return CLIException
	 */
	public static function forInvalidColor(string $type, string $color)
	{
		return new static(lang('CLI.invalidColor', [$type, $color]));
	}
}
