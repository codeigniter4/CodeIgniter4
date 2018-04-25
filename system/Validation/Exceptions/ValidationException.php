<?php namespace CodeIgniter\Validation\Exceptions;

use CodeIgniter\Exceptions\ExceptionInterface;
use CodeIgniter\Exceptions\FrameworkException;

class ValidationException extends FrameworkException implements ExceptionInterface
{
	public static function forRuleNotFound(string $rule = null)
	{
		return new self(lang('Validation.ruleNotFound', [$rule]));
	}

	public static function forGroupNotFound(string $group = null)
	{
		return new self(lang('Validation.groupNotFound', [$group]));
	}

	public static function forGroupNotArray(string $group = null)
	{
		return new self(lang('Validation.groupNotArray', [$group]));
	}

	public static function forInvalidTemplate(string $template = null)
	{
		return new self(lang('Validation.invalidTemplate', [$template]));
	}

	public static function forNoRuleSets()
	{
		return new self(lang('Validation.noRuleSets'));
	}
}
