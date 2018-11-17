<?php declare(strict_types=1);
namespace CodeIgniter\Security\Exceptions;

use CodeIgniter\Exceptions\ExceptionInterface;
use CodeIgniter\Exceptions\FrameworkException;

class SecurityException extends FrameworkException implements ExceptionInterface
{
	public static function forDisallowedAction()
	{
		return new static(lang('HTTP.disallowedAction'), 403);
	}
}
