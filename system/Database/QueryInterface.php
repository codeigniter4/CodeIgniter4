<?php namespace CodeIgniter\Database;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2018 British Columbia Institute of Technology
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
 * @package	CodeIgniter
 * @author	CodeIgniter Dev Team
 * @copyright	2014-2018 British Columbia Institute of Technology (https://bcit.ca/)
 * @license	https://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */

/**
 * Interface QueryInterface
 *
 * Represents a single statement that can be executed against the database.
 * Statements are platform-specific and can handle binding of binds.
 *
 * @package CodeIgniter\Database
 */
interface QueryInterface
{

	/**
	 * Sets the raw query string to use for this statement.
	 *
	 * @param string $sql
	 * @param array $binds
	 *
	 * @return mixed
	 */
	public function setQuery(string $sql, $binds = null);

	//--------------------------------------------------------------------

	/**
	 * Returns the final, processed query string after binding, etal
	 * has been performed.
	 *
	 * @return mixed
	 */
	public function getQuery();

	//--------------------------------------------------------------------

	/**
	 * Records the execution time of the statement using microtime(true)
	 * for it's start and end values. If no end value is present, will
	 * use the current time to determine total duration.
	 *
	 * @param float $start
	 * @param float $end
	 *
	 * @return mixed
	 */
	public function setDuration(float $start, float $end = null);

	//--------------------------------------------------------------------

	/**
	 * Returns the duration of this query during execution, or null if
	 * the query has not been executed yet.
	 *
	 * @param int    $decimals  The accuracy of the returned time.
	 *
	 * @return mixed
	 */
	public function getDuration(int $decimals = 6);

	//--------------------------------------------------------------------

	/**
	 * Stores the error description that happened for this query.
	 *
	 * @param int $code
	 * @param string $error
	 */
	public function setError(int $code, string $error);

	//--------------------------------------------------------------------

	/**
	 * Reports whether this statement created an error not.
	 *
	 * @return bool
	 */
	public function hasError(): bool;

	//--------------------------------------------------------------------

	/**
	 * Returns the error code created while executing this statement.
	 *
	 * @return int
	 */
	public function getErrorCode(): int;

	//--------------------------------------------------------------------

	/**
	 * Returns the error message created while executing this statement.
	 *
	 * @return string
	 */
	public function getErrorMessage(): string;

	//--------------------------------------------------------------------

	/**
	 * Determines if the statement is a write-type query or not.
	 *
	 * @return bool
	 */
	public function isWriteType(): bool;

	//--------------------------------------------------------------------

	/**
	 * Swaps out one table prefix for a new one.
	 *
	 * @param string $orig
	 * @param string $swap
	 *
	 * @return mixed
	 */
	public function swapPrefix(string $orig, string $swap);

	//--------------------------------------------------------------------
}
