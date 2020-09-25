<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
 * Copyright (c) 2019-2020 CodeIgniter Foundation
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package    CodeIgniter
 * @author     CodeIgniter Dev Team
 * @copyright  2019-2020 CodeIgniter Foundation
 * @license    https://opensource.org/licenses/MIT - MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

namespace CodeIgniter\Validation\Exceptions;

use CodeIgniter\Exceptions\ExceptionInterface;
use CodeIgniter\Exceptions\FrameworkException;

/**
 * ValidationException
 */
class ValidationException extends FrameworkException implements ExceptionInterface
{
	/*
	 * Thrown when validation rule isn't found.
	 *
	 * @param string|null $rule
	 *
	 * @return \CodeIgniter\Validation\Exceptions\ValidationException
	 */
	public static function forRuleNotFound(string $rule = null)
	{
		return new static(lang('Validation.ruleNotFound', [$rule]));
	}

  	//--------------------------------------------------------------------

	/**
	 * Thrown when validation group isn't found.
	 *
	 * @param string|null $group
	 *
	 * @return \CodeIgniter\Validation\Exceptions\ValidationException
	 */
	public static function forGroupNotFound(string $group = null)
	{
		return new static(lang('Validation.groupNotFound', [$group]));
	}

  	//--------------------------------------------------------------------

	/**
	 * Thrown when validation group type isn't array.
	 *
	 * @param string|null $group
	 *
	 * @return \CodeIgniter\Validation\Exceptions\ValidationException
	 */
	public static function forGroupNotArray(string $group = null)
	{
		return new static(lang('Validation.groupNotArray', [$group]));
	}

  	//--------------------------------------------------------------------

	/**
	 * Thrown when validation template is invalid.
	 *
	 * @param string|null $template
	 *
	 * @return \CodeIgniter\Validation\Exceptions\ValidationException
	 */
	public static function forInvalidTemplate(string $template = null)
	{
		return new static(lang('Validation.invalidTemplate', [$template]));
	}

  	//--------------------------------------------------------------------

	/**
	 * Thrown when validation rules aren't set.
	 *
	 * @return \CodeIgniter\Validation\Exceptions\ValidationException
	 */
	public static function forNoRuleSets()
	{
		return new static(lang('Validation.noRuleSets'));
	}
}
