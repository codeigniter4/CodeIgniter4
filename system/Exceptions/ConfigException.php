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

	public static function forInvalidMigrationType(string $type = null)
	{
		throw new static(lang('Migrations.invalidType', [$type]));
	}

	public static function forDisabledMigrations()
	{
		throw new static(lang('Migrations.disabled'));
	}
}
