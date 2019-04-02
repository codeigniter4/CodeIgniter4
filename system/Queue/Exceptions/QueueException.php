<?php namespace CodeIgniter\Queue\Exceptions;

use CodeIgniter\Exceptions\ExceptionInterface;
use CodeIgniter\Exceptions\FrameworkException;

class QueueException extends FrameworkException implements ExceptionInterface
{
	public static function forInvalidGroup(string $group)
	{
		return new static(lang('Queue.invalidGroup', [$group]));
	}

	public static function forInvalidExchangeName(string $exchange_name)
	{
		return new static(lang('Queue.invalidExchangeName', [$exchange_name]));
	}

	public static function forFailGetQueueDatabase(string $table)
	{
		return new static(lang('Queue.failGetQueueDatabase', [$table]));
	}
}
