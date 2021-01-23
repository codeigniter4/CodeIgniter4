<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Cache\Exceptions;

use CodeIgniter\Exceptions\DebugTraceableTrait;
use RuntimeException;

/**
 * CacheException
 */
class CacheException extends RuntimeException implements ExceptionInterface
{
	use DebugTraceableTrait;

	/**
	 * Thrown when handler has no permission to write cache.
	 *
	 * @param string $path
	 *
	 * @return CacheException
	 */
	public static function forUnableToWrite(string $path)
	{
		return new static(lang('Cache.unableToWrite', [$path]));
	}

	/**
	 * Thrown when an unrecognized handler is used.
	 *
	 * @return CacheException
	 */
	public static function forInvalidHandlers()
	{
		return new static(lang('Cache.invalidHandlers'));
	}

	/**
	 * Thrown when no backup handler is setup in config.
	 *
	 * @return CacheException
	 */
	public static function forNoBackup()
	{
		return new static(lang('Cache.noBackup'));
	}

	/**
	 * Thrown when specified handler was not found.
	 *
	 * @return CacheException
	 */
	public static function forHandlerNotFound()
	{
		return new static(lang('Cache.handlerNotFound'));
	}
}
