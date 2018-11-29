<?php namespace CodeIgniter\Cache\Exceptions;

class CacheException extends \RuntimeException implements ExceptionInterface
{
	/**
	 * @return \CodeIgniter\Cache\Exceptions\CacheException
	 */
	public static function forUnableToWrite(string $path)
	{
		return new static(lang('Cache.unableToWrite', [$path]));
	}

	/**
	 * @return \CodeIgniter\Cache\Exceptions\CacheException
	 */
	public static function forInvalidHandlers()
	{
		return new static(lang('Cache.invalidHandlers'));
	}

	/**
	 * @return \CodeIgniter\Cache\Exceptions\CacheException
	 */
	public static function forNoBackup()
	{
		return new static(lang('Cache.noBackup'));
	}

	/**
	 * @return \CodeIgniter\Cache\Exceptions\CacheException
	 */
	public static function forHandlerNotFound()
	{
		return new static(lang('Cache.handlerNotFound'));
	}
}
