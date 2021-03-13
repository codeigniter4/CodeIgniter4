<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter;

final class PhpRequirement
{
	/**
	 * @var string
	 */
	private const MIN_PHP_VERSION = '7.3';

	public static function validatePHPVersion(): void
	{
		if (! version_compare(PHP_VERSION, self::MIN_PHP_VERSION, '<'))
		{
			return;
		}

		die(
			sprintf(
				'Your PHP version must be %s or higher to run CodeIgniter. Current version: %s',
				self::MIN_PHP_VERSION,
				PHP_VERSION
			)
		);
	}
}
