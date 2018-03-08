<?php namespace CodeIgniter\Cache\Exceptions;

class CacheException extends \RuntimeException implements ExceptionInterface
{
	/**
	 * @return \CodeIgniter\Cache\Exceptions\CacheException
	 */
	public static function forInvalidHandlers()
	{
		return new self(lang('Cache.invalidHandlers'));
	}

	/**
	 * @return \CodeIgniter\Cache\Exceptions\CacheException
	 */
	public static function forNoBackup()
	{
		return new self(lang('Cache.noBackup'));
	}

	/**
	 * @return \CodeIgniter\Cache\Exceptions\CacheException
	 */
	public static function forHandlerNotFound()
	{
		return new self(lang('Cache.handlerNotFound'));
	}
}
