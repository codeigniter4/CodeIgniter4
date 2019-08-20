<?php
namespace CodeIgniter\Encryption\Exceptions;

use CodeIgniter\Exceptions\ExceptionInterface;

/**
 * Encryption exception
 */
class EncryptionException extends \RuntimeException implements ExceptionInterface
{

	public static function forNoDriverRequested()
	{
		return new static(lang('Encryption.noDriverRequested'));
	}

	public static function forNoHandlerAvailable()
	{
		return new static(lang('Encryption.noHandlerAvailable'));
	}

	public static function forUnKnownHandler(string $driver = null)
	{
		return new static(lang('Encryption.unKnownHandler', [$driver]));
	}

	public static function forNeedsStarterKey()
	{
		return new static(lang('Encryption.starterKeyNeeded'));
	}

	public static function forAuthenticationFailed()
	{
		return new static(lang('Encryption.authenticationFailed'));
	}
	public static function forEncryptionFailed()
	{
		return new static(lang('Encryption.encryptionFailed'));
	}

}
