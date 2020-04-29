<?php namespace CodeIgniter\CLI\Exceptions;

class CLIException extends \RuntimeException
{
	/**
	 * @param string $type
	 * @param string $color
	 *
	 * @return \CodeIgniter\CLI\Exceptions\CLIException
	 */
	public static function forInvalidColor(string $type, string $color)
	{
		return new static(lang('CLI.invalidColor', [$type, $color]));
	}

	/**
	 * @param string $option
	 *
	 * @return \CodeIgniter\CLI\Exceptions\CLIException
	 */
	public static function forInvalidOption(string $option)
	{
		return new static(lang('CLI.invalidOption', [$option]));
	}
}
