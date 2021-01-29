<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Exceptions;

use RuntimeException;

/**
 * Class DownloadException
 */
class DownloadException extends RuntimeException implements ExceptionInterface
{
	use DebugTraceableTrait;

	public static function forCannotSetFilePath(string $path)
	{
		return new static(lang('HTTP.cannotSetFilepath', [$path]));
	}

	public static function forCannotSetBinary()
	{
		return new static(lang('HTTP.cannotSetBinary'));
	}

	public static function forNotFoundDownloadSource()
	{
		return new static(lang('HTTP.notFoundDownloadSource'));
	}

	public static function forCannotSetCache()
	{
		return new static(lang('HTTP.cannotSetCache'));
	}

	public static function forCannotSetStatusCode(int $code, string $reason)
	{
		return new static(lang('HTTP.cannotSetStatusCode', [$code, $reason]));
	}
}
