<?php
namespace CodeIgniter\Encryption\Exceptions;

use CodeIgniter\Exceptions\ExceptionInterface;

/**
 * Encryption exception
 */
class EncryptionException extends \RuntimeException implements ExceptionInterface
{

	public static function forDriverNotAvailable(string $driver = null)
	{
		return new static(lang('exception.driverNotAvailable', [$driver]));
	}

	public static function forNoDriverRequested()
	{
		return new static(lang('exception.noDriverRequested'));
	}

	public static function forNoHandlerAvailable()
	{
		return new static(lang('exception.noHandlerAvailable'));
	}

	public static function forUnKnownHandler(string $driver = null)
	{
		return new static(lang('exception.unKnownHandler', [$driver]));
	}

}
