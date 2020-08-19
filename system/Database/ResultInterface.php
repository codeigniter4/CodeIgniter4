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
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

namespace CodeIgniter\Database;

/**
 * Interface ResultInterface
 */
interface ResultInterface
{

	/**
	 * Retrieve the results of the query. Typically an array of
	 * individual data rows, which can be either an 'array', an
	 * 'object', or a custom class name.
	 *
	 * @param string $type The row type. Either 'array', 'object', or a class name to use
	 *
	 * @return array
	 */
	public function getResult(string $type = 'object'): array;

	//--------------------------------------------------------------------

	/**
	 * Returns the results as an array of custom objects.
	 *
	 * @param string $className The name of the class to use.
	 *
	 * @return mixed
	 */
	public function getCustomResultObject(string $className);

	//--------------------------------------------------------------------

	/**
	 * Returns the results as an array of arrays.
	 *
	 * If no results, an empty array is returned.
	 *
	 * @return array
	 */
	public function getResultArray(): array;

	//--------------------------------------------------------------------

	/**
	 * Returns the results as an array of objects.
	 *
	 * If no results, an empty array is returned.
	 *
	 * @return array
	 */
	public function getResultObject(): array;

	//--------------------------------------------------------------------

	/**
	 * Wrapper object to return a row as either an array, an object, or
	 * a custom class.
	 *
	 * If row doesn't exist, returns null.
	 *
	 * @param mixed  $n    The index of the results to return
	 * @param string $type The type of result object. 'array', 'object' or class name.
	 *
	 * @return mixed
	 */
	public function getRow($n = 0, string $type = 'object');

	//--------------------------------------------------------------------

	/**
	 * Returns a row as a custom class instance.
	 *
	 * If row doesn't exists, returns null.
	 *
	 * @param integer $n
	 * @param string  $className
	 *
	 * @return mixed
	 */
	public function getCustomRowObject(int $n, string $className);

	//--------------------------------------------------------------------

	/**
	 * Returns a single row from the results as an array.
	 *
	 * If row doesn't exist, returns null.
	 *
	 * @param integer $n
	 *
	 * @return mixed
	 */
	public function getRowArray(int $n = 0);

	//--------------------------------------------------------------------

	/**
	 * Returns a single row from the results as an object.
	 *
	 * If row doesn't exist, returns null.
	 *
	 * @param integer $n
	 *
	 * @return mixed
	 */
	public function getRowObject(int $n = 0);

	//--------------------------------------------------------------------

	/**
	 * Assigns an item into a particular column slot.
	 *
	 * @param string $key
	 * @param null   $value
	 *
	 * @return mixed
	 */
	public function setRow($key, $value = null);

	//--------------------------------------------------------------------

	/**
	 * Returns the "first" row of the current results.
	 *
	 * @param string $type
	 *
	 * @return mixed
	 */
	public function getFirstRow(string $type = 'object');

	//--------------------------------------------------------------------

	/**
	 * Returns the "last" row of the current results.
	 *
	 * @param string $type
	 *
	 * @return mixed
	 */
	public function getLastRow(string $type = 'object');

	//--------------------------------------------------------------------

	/**
	 * Returns the "next" row of the current results.
	 *
	 * @param string $type
	 *
	 * @return mixed
	 */
	public function getNextRow(string $type = 'object');

	//--------------------------------------------------------------------

	/**
	 * Returns the "previous" row of the current results.
	 *
	 * @param string $type
	 *
	 * @return mixed
	 */
	public function getPreviousRow(string $type = 'object');

	//--------------------------------------------------------------------

	/**
	 * Returns an unbuffered row and move the pointer to the next row.
	 *
	 * @param string $type
	 *
	 * @return mixed
	 */
	public function getUnbufferedRow(string $type = 'object');

	//--------------------------------------------------------------------

	/**
	 * Gets the number of fields in the result set.
	 *
	 * @return integer
	 */
	public function getFieldCount(): int;

	//--------------------------------------------------------------------

	/**
	 * Generates an array of column names in the result set.
	 *
	 * @return array
	 */
	public function getFieldNames(): array;

	//--------------------------------------------------------------------

	/**
	 * Generates an array of objects representing field meta-data.
	 *
	 * @return array
	 */
	public function getFieldData(): array;

	//--------------------------------------------------------------------

	/**
	 * Frees the current result.
	 *
	 * @return void
	 */
	public function freeResult();

	//--------------------------------------------------------------------

	/**
	 * Moves the internal pointer to the desired offset. This is called
	 * internally before fetching results to make sure the result set
	 * starts at zero.
	 *
	 * @param integer $n
	 *
	 * @return mixed
	 */
	public function dataSeek(int $n = 0);

	//--------------------------------------------------------------------
}
