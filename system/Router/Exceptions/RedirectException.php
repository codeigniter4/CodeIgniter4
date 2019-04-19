<?php

namespace CodeIgniter\Router\Exceptions;

/**
 * Redirect exception
 */
class RedirectException extends \Exception
{
	public static function forUnableToRedirect(string $route, string $code)
	{
		return new static(lang('Redirect.forUnableToRedirect', [$route, $code]));
	}
}