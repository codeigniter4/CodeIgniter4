<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Publisher\Exceptions;

use CodeIgniter\Exceptions\FrameworkException;

/**
 * Publisher Exception Class
 *
 * Handles exceptions related to actions taken by a Publisher.
 */
class PublisherException extends FrameworkException
{
	/**
	 * Throws when a file should be overwritten yet cannot.
	 *
	 * @param string $from The source file
	 * @param string $to   The destination file
	 */
	public static function forCollision(string $from, string $to)
	{
		return new static(lang('Publisher.collision', [filetype($to), $from, $to]));
	}

	/**
	 * Throws when an object is expected to be a directory but is not or is missing.
	 *
	 * @param string $caller The method causing the exception
	 */
	public static function forExpectedDirectory(string $caller)
	{
		return new static(lang('Publisher.expectedDirectory', [$caller]));
	}

	/**
	 * Throws when an object is expected to be a file but is not or is missing.
	 *
	 * @param string $caller The method causing the exception
	 */
	public static function forExpectedFile(string $caller)
	{
		return new static(lang('Publisher.expectedFile', [$caller]));
	}
}
