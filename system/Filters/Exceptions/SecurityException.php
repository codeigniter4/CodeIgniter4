<?php namespace CodeIgniter\Filters\Exceptions;

use CodeIgniter\Exceptions\ExceptionInterface;
use CodeIgniter\Exceptions\FrameworkException;

class SecurityException extends FrameworkException implements ExceptionInterface
{
	public static function forDisallowedAction()
	{
		return new static(lang('HTTP.disallowedAction'), 403);
	}
}
