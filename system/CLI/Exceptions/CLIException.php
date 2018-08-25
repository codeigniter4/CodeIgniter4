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
}
