<?php namespace CodeIgniter\Exceptions;

/**
 * Class FrameworkException
 *
 * A collection of exceptions thrown by the framework
 * that can only be determined at run time.
 *
 * @package CodeIgniter\Exceptions
 */
class FrameworkException extends \RuntimeException implements ExceptionInterface
{

	public static function forEmptyBaseURL(): self
	{
		return new static('You have an empty or invalid base URL. The baseURL value must be set in Config\App.php, or through the .env file.');
	}

	public static function forInvalidFile(string $path)
	{
		return new static(lang('Core.invalidFile', [$path]));
	}

	public static function forCopyError()
	{
		return new static(lang('Core.copyError'));
	}

	public static function forMissingExtension(string $extension)
	{
		return new static(lang('Core.missingExtension', [$extension]));
	}

	public static function forNoHandlers(string $class)
	{
		return new static(lang('Core.noHandlers', [$class]));
	}

}
