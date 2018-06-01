<?php namespace CodeIgniter\Format\Exceptions;

use CodeIgniter\Exceptions\ExceptionInterface;

class FormatException extends \RuntimeException implements ExceptionInterface
{
	public static function forInvalidJSON(string $error = null)
	{
		return new static(lang('Format.invalidJSON', [$error]));
	}

	public static function forMissingExtension()
	{
		return new static(lang('Format.missingExtension'));
	}

}
