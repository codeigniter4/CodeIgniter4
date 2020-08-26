<?php namespace CodeIgniter\Security\Exceptions;

use CodeIgniter\Exceptions\ExceptionInterface;
use CodeIgniter\Exceptions\FrameworkException;

class SecurityException extends FrameworkException implements ExceptionInterface
{
	public static function forDisallowedAction()
	{
		return new static(lang('HTTP.disallowedAction'), 403);
	}

	public static function forInvalidSameSiteSetting(string $samesite)
	{
		return new static(lang('HTTP.invalidSameSiteSetting', [$samesite]));
	}
}
