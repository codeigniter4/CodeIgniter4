<?php

namespace CodeIgniter\Router\Exceptions;

/**
 * Redirect exception
 */

class RedirectException extends \Exception
{
	public static function forRedirectIntercept(string $route)
	{
		return new static($route);
	}
}
