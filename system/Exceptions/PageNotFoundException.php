<?php namespace CodeIgniter\Exceptions;

class PageNotFoundException extends \OutOfBoundsException implements ExceptionInterface
{
	/**
	 * Error code
	 * @var int
	 */
	protected $code = 404;

	public static function forPageNotFound()
	{
		return new self(lang('HTTP.pageNotFound'));
	}

	public static function forEmptyController()
	{
		return new self(lang('HTTP.emptyController'));
	}

	public static function forControllerNotFound(string $controller, string $method)
	{
		return new self(lang('HTTP.controllerNotFound', [$controller, $method]));
	}

	public static function forMethodNotFound(string $method)
	{
		return new self(lang('HTTP.methodNotFound', [$method]));
	}
}
