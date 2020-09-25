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

namespace CodeIgniter\View\Exceptions;

use CodeIgniter\Exceptions\ExceptionInterface;
use CodeIgniter\Exceptions\FrameworkException;

class ViewException extends FrameworkException implements ExceptionInterface
{
	/**
	 * Thrown when cell method is invalid.
	 *
	 * @param string $class
	 * @param string $method
	 *
	 * @return \CodeIgniter\View\Exceptions\ViewException
	 */
	public static function forInvalidCellMethod(string $class, string $method)
	{
		return new static(lang('View.invalidCellMethod', ['class' => $class, 'method' => $method]));
	}

  	//--------------------------------------------------------------------

	/**
	 * Thrown when cell parameters is missing.
	 *
	 * @param string $class
	 * @param string $method
	 *
	 * @return \CodeIgniter\View\Exceptions\ViewException
	 */
	public static function forMissingCellParameters(string $class, string $method)
	{
		return new static(lang('View.missingCellParameters', ['class' => $class, 'method' => $method]));
	}

  	//--------------------------------------------------------------------

	/**
	 * Thrown when cell parameter is invalid.
	 *
	 * @param string $key
	 *
	 * @return \CodeIgniter\View\Exceptions\ViewException
	 */
	public static function forInvalidCellParameter(string $key)
	{
		return new static(lang('View.invalidCellParameter', [$key]));
	}

  	//--------------------------------------------------------------------

	/**
	 * Thrown when cell class isn't found.
	 *
	 * @return \CodeIgniter\View\Exceptions\ViewException
	 */
	public static function forNoCellClass()
	{
		return new static(lang('View.noCellClass'));
	}

  	//--------------------------------------------------------------------

	/**
	 * Thrown when cell class is invalid.
	 *
	 * @param string|null $class
	 *
	 * @return \CodeIgniter\View\Exceptions\ViewException
	 */
	public static function forInvalidCellClass(string $class = null)
	{
		return new static(lang('View.invalidCellClass', [$class]));
	}

  	//--------------------------------------------------------------------

	/**
	 * Thrown when tag syntax has an error.
	 *
	 * @param string $output
	 *
	 * @return \CodeIgniter\View\Exceptions\ViewException
	 */
	public static function forTagSyntaxError(string $output)
	{
		return new static(lang('View.tagSyntaxError', [$output]));
	}
}
