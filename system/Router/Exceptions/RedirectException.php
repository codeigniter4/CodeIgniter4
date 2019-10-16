<?php

namespace CodeIgniter\Router\Exceptions;

/**
 * Redirect exception
 */

class RedirectException extends \Exception
{
	protected $code = 302;
}
