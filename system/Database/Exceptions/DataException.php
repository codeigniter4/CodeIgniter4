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

namespace CodeIgniter\Database\Exceptions;

use CodeIgniter\Exceptions\ExceptionInterface;
use CodeIgniter\Exceptions\FrameworkException;

class DataException extends FrameworkException implements ExceptionInterface
{
	/**
	 * Used by the Model's `trigger` method when the callback cannot be found.
	 *
	 * @param string $method
	 *
	 * @return \CodeIgniter\Database\Exceptions\DataException
	 */
	public static function forInvalidMethodTriggered(string $method)
	{
		return new static(lang('Database.invalidEvent', [$method]));
	}

  	//--------------------------------------------------------------------

	/**
	 * Used by Model's `insert/update` methods when there isn't any data
	 * to actually work with.
	 *
	 * @param string $mode
	 *
	 * @return \CodeIgniter\Database\Exceptions\DataException
	 */
	public static function forEmptyDataset(string $mode)
	{
		return new static(lang('Database.emptyDataset', [$mode]));
	}

  	//--------------------------------------------------------------------

	/**
	 * Used by Model's insert/update methods when there is no primary key
	 * defined and Model has option `useAutoIncrement` set to false.
	 *
	 * @param string $mode
	 *
	 * @return \CodeIgniter\Database\Exceptions\DataException
	 */
	public static function forEmptyPrimaryKey(string $mode)
	{
		return new static(lang('Database.emptyPrimaryKey', [$mode]));
	}

  	//--------------------------------------------------------------------

	/**
	 * Thrown when an argument for any of the Model's methods were empty or
	 * invalid, and they could not be to work correctly for that method.
	 *
	 * @param string $argument
	 *
	 * @return \CodeIgniter\Database\Exceptions\DataException
	 */
	public static function forInvalidArgument(string $argument)
	{
		return new static(lang('Database.invalidArgument', [$argument]));
	}

  	//--------------------------------------------------------------------

	/**
	 * Thrown when allowed field name is not equal to table column name.
	 *
	 * @param string $model
	 *
	 * @return \CodeIgniter\Database\Exceptions\DataException
	 */
	public static function forInvalidAllowedFields(string $model)
	{
		return new static(lang('Database.invalidAllowedFields', [$model]));
	}

  	//--------------------------------------------------------------------

	/**
	 * Thrown when used table is not found in the current database.
	 *
	 * @param string $table
	 *
	 * @return \CodeIgniter\Database\Exceptions\DataException
	 */
	public static function forTableNotFound(string $table)
	{
		return new static(lang('Database.tableNotFound', [$table]));
	}

  	//--------------------------------------------------------------------

	/**
	 * Thrown when empty statement is given for the field.
	 *
	 * @param string $argument
	 *
	 * @return \CodeIgniter\Database\Exceptions\DataException
	 */
	public static function forEmptyInputGiven(string $argument)
	{
		return new static(lang('Database.forEmptyInputGiven', [$argument]));
	}

  	//--------------------------------------------------------------------

	/**
	 * Thrown when multiple columns names are given to a column name.
	 *
	 * @return \CodeIgniter\Database\Exceptions\DataException
	 */
	public static function forFindColumnHaveMultipleColumns()
	{
		return new static(lang('Database.forFindColumnHaveMultipleColumns'));
	}
}
