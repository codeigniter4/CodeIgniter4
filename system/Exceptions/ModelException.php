<?php namespace CodeIgniter\Exceptions;

/**
 * Model Exceptions.
 */

class ModelException extends FrameworkException
{
	public static function forNoPrimaryKey(string $modelName)
	{
		return new static(lang('Database.noPrimaryKey', [$modelName]));
	}

	public static function forNoDateFormat(string $modelName)
	{
		return new static(lang('Database.noDateFormat', [$modelName]));
	}
}
