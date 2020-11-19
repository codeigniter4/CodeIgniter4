<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Validation\Exceptions;

use CodeIgniter\Exceptions\FrameworkException;

class ValidationException extends FrameworkException
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
	public static function forInvalidRule($rule = null) : self
	{
		return new static(lang('Validation.invalidRule', [ static::convertRuleToString($rule) ]));
	}

	public static function forUnnamedRule($rule = null) : self
	{
		return new static(lang('Validation.unnamedRule', [ static::convertRuleToString($rule) ]));
	}

	/**
	 * Used as a helper to convert anything else than a string to a rule (name)
	 *
	 * @param  mixed $rule
	 * @return string|null
	 */
	protected static function convertRuleToString($rule) : ?string
	{
		if (! is_string($rule))
		{
			// Try to not confuse the developer with something like "Cannot use array as a rule."
			// but dont leak to much
			$rule = ENVIRONMENT !== 'production' ? '"' . var_export($rule, true) . '"' : gettype($rule);
		}

		return $rule;
	}
}
