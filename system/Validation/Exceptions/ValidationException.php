<?php namespace CodeIgniter\Validation\Exceptions;

use CodeIgniter\Exceptions\ExceptionInterface;
use CodeIgniter\Exceptions\FrameworkException;

class ValidationException extends FrameworkException implements ExceptionInterface
{
	public static function forRuleNotFound(string $rule = null)
	{
		return new static(lang('Validation.ruleNotFound', [$rule]));
	}

	public static function forGroupNotFound(string $group = null)
	{
		return new static(lang('Validation.groupNotFound', [$group]));
	}

	public static function forGroupNotArray(string $group = null)
	{
		return new static(lang('Validation.groupNotArray', [$group]));
	}

	public static function forInvalidTemplate(string $template = null)
	{
		return new static(lang('Validation.invalidTemplate', [$template]));
	}

	public static function forNoRuleSets()
	{
		return new static(lang('Validation.noRuleSets'));
	}
}
