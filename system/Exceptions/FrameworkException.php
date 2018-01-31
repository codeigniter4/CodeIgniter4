<?php namespace CodeIgniter\Exceptions;

/**
 * Class FrameworkException
 *
 * A collection of exceptions thrown by the framework
 * that can only be determined at run time.
 *
 * @package CodeIgniter\Exceptions
 */
class FrameworkException extends \RuntimeException
{
	public static function forEmptyBaseURL(): self
	{
		return new self('You have an empty or invalid base URL. The baseURL value must be set in Config\App.php, or through the .env file.');
	}
}
