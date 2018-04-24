<?php namespace CodeIgniter\Filters\Exceptions;

use CodeIgniter\Exceptions\ConfigException;
use CodeIgniter\Exceptions\ExceptionInterface;

class FilterException extends ConfigException implements ExceptionInterface
{
	public static function forNoAlias(string $alias)
	{
		return new self(lang('Filters.noFilter', [$alias]));
	}

	public static function forIncorrectInterface(string $class)
	{
		return new self(lang('Filters.incorrectInterface', [$class]));
	}
}
