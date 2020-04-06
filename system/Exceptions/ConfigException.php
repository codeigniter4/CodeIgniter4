<?php namespace CodeIgniter\Exceptions;

/**
 * Exception for automatic logging.
 */

class ConfigException extends CriticalError
{

	/**
	 * Error code
	 *
	 * @var integer
	 */
	protected $code = 3;

	public static function forDisabledMigrations()
	{
		return new static(lang('Migrations.disabled'));
	}
}
