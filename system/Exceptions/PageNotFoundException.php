<?php namespace CodeIgniter\Exceptions;

class PageNotFoundException extends \OutOfBoundsException implements ExceptionInterface
{
	/**
	 * Error code
	 * @var int
	 */
	protected $code = 404;

	public static function forPageNotFound($Message)
	{
		return new static($Message ?? lang('HTTP.pageNotFound'));
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
