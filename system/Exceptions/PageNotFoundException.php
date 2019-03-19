<?php namespace CodeIgniter\Exceptions;

class PageNotFoundException extends \OutOfBoundsException implements ExceptionInterface
{
	/**
	 * Error code
	 *
	 * @var integer
	 */
	protected $code = 404;

	public static function forPageNotFound(string $message = null)
	{
		return new static($message ?? lang('HTTP.pageNotFound'));
	}

	public static function forEmptyController()
	{
		return new static(lang('HTTP.emptyController'));
	}

	public static function forControllerNotFound(string $controller, string $method)
	{
		return new static(lang('HTTP.controllerNotFound', [$controller, $method]));
	}

	public static function forMethodNotFound(string $method)
	{
		return new static(lang('HTTP.methodNotFound', [$method]));
	}
}
