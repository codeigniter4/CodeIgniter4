<?php namespace CodeIgniter\Session\Exceptions;

use CodeIgniter\Exceptions\ExceptionInterface;
use CodeIgniter\Exceptions\FrameworkException;

class SessionException extends FrameworkException implements ExceptionInterface
{
	public static function forMissingDatabaseTable()
	{
		return new self(lang('Session.missingDatabaseTable'));
	}

	public static function forInvalidSavePath(string $path = null)
	{
		return new self(lang('Session.invalidSavePath', [$path]));
	}

	public static function forWriteProtectedSavePath(string $path = null)
	{
		return new self(lang('Session.writeProtectedSavePath', [$path]));
	}

	public static function forEmptySavepath()
	{
		return new self(lang('Session.emptySavePath'));
	}

	public static function forInvalidSavePathFormat(string $path)
	{
		return new self(lang('Session.invalidSavePathFormat', [$path]));
	}
}
