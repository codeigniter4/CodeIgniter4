<?php namespace CodeIgniter\Exceptions;

/**
 * Exception for automatic logging.
 */
class ConfigException extends CriticalError
{

	/**
	 * Error code
	 * @var int
	 */
	protected $code = 3;

	public static function forMissingMigrationsTable()
	{
		throw new self(lang('Migrations.missingTable'));
	}

	public static function forInvalidMigrationType(string $type = null)
	{
		throw new self(lang('Migrations.invalidType', [$type]));
	}

	public static function forDisabledMigrations()
	{
		throw new self(lang('Migrations.disabled'));
	}
}
