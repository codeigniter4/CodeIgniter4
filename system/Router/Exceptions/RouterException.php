<?php namespace CodeIgniter\Router\Exceptions;

use CodeIgniter\Exceptions\ExceptionInterface;
use CodeIgniter\Exceptions\FrameworkException;

class RouterException extends FrameworkException implements ExceptionInterface
{
	public static function forInvalidParameterType()
	{
		return new static(lang('Router.invalidParameterType'));
	}

	public static function forMissingDefaultRoute()
	{
		return new static(lang('Router.missingDefaultRoute'));
	}
}
