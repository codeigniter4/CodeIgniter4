<?php namespace CodeIgniter\View\Exceptions;

use CodeIgniter\Exceptions\ExceptionInterface;
use CodeIgniter\Exceptions\FrameworkException;

class ViewException extends FrameworkException implements ExceptionInterface
{
	public static function forInvalidCellMethod(string $class, string $method)
	{
		return new self(lang('View.invalidCellMethod', ['class' => $class, 'method' => $method]));
	}

	public static function forMissingCellParameters(string $class, string $method)
	{
		return new self(lang('View.missingCellParameters', ['class' => $class, 'method' => $method]));
	}

	public static function forInvalidCellParameter(string $key)
	{
		return new self(lang('View.invalidCellParameter', [$key]));
	}

	public static function forNoCellClass()
	{
		return new self(lang('View.noCellClass'));
	}

	public static function forInvalidCellClass(string $class = null)
	{
		return new self(lang('View.invalidCellClass', [$class]));
	}

	public static function forTagSyntaxError(string $output)
	{
		return new self(lang('View.tagSyntaxError', [$output]));
	}
}
