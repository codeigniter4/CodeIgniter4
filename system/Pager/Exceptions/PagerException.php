<?php namespace CodeIgniter\Pager\Exceptions;

use CodeIgniter\Exceptions\ExceptionInterface;
use CodeIgniter\Exceptions\FrameworkException;

class PagerException extends FrameworkException implements ExceptionInterface
{
	public static function forInvalidTemplate(string $template=null)
	{
		return new self(lang('Pager.invalidTemplate', [$template]));
	}

	public static function forInvalidPaginationGroup(string $group = null)
	{
		return new self(lang('Pager.invalidPaginationGroup', [$group]));
	}
}
