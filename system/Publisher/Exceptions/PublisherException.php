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

class PublisherException extends FrameworkException
{
	public static function forCollision(string $from, string $to)
	{
		return new static(lang('Publisher.collision', [filetype($to), $from, $to]));
	}

	public static function forExpectedDirectory(string $caller)
	{
		return new static(lang('Publisher.expectedDirectory', [$caller]));
	}

	public static function forExpectedFile(string $caller)
	{
		return new static(lang('Publisher.expectedFile', [$caller]));
	}
}
