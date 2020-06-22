<?php

namespace CodeIgniter\Format\Exceptions;

use CodeIgniter\Exceptions\ExceptionInterface;
use RuntimeException;

class FormatException extends RuntimeException implements ExceptionInterface
{
	public static function forInvalidFormatter(string $class)
	{
		return new static(lang('Format.invalidFormatter', [$class]));
	}

	public static function forInvalidJSON(string $error = null)
	{
		return new static(lang('Format.invalidJSON', [$error]));
	}

	public static function forInvalidMime(string $mime)
	{
		return new static(lang('Format.invalidMime', [$mime]));
	}

	/**
	 * This will never be thrown in travis-ci
	 *
	 * @codeCoverageIgnore
	 */
	public static function forMissingExtension()
	{
		return new static(lang('Format.missingExtension'));
	}

}
