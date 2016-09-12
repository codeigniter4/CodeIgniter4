<?php namespace CodeIgniter\Database\SQLite3;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2016, British Columbia Institute of Technology
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
 * @copyright	Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	http://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */

use CodeIgniter\Database\BaseResult;
use CodeIgniter\Database\ResultInterface;

/**
 * Result for SQLite3
 */
class Result extends BaseResult implements ResultInterface
{
	/**
	 * Gets the number of fields in the result set.
	 *
	 * @return int
	 */
	public function getFieldCount(): int
	{
		return $this->resultID->numColumns();
	}

	//--------------------------------------------------------------------

	/**
	 * Generates an array of column names in the result set.
	 *
	 * @return array
	 */
	public function getFieldNames(): array
	{
		$fieldNames = array();
		for ($i = 0, $c = $this->getFieldCount(); $i < $c; $i++)
		{
			$fieldNames[] = $this->resultID->columnName($i);
		}

		return $fieldNames;
	}

	//--------------------------------------------------------------------

	/**
	 * Generates an array of objects representing field meta-data.
	 *
	 * @return array
	 */
	public function getFieldData(): array
	{
		static $dataTypes = array(
			SQLITE3_INTEGER	=> 'integer',
			SQLITE3_FLOAT	=> 'float',
			SQLITE3_TEXT	=> 'text',
			SQLITE3_BLOB	=> 'blob',
			SQLITE3_NULL	=> 'null'
		);

		$retval    = [];

		for ($i = 0, $c = $this->getFieldCount(); $i < $c; $i++)
		{
			$retval[$i]              = new \stdClass();
			$retval[$i]->name        = $this->resultID->columnName($i);
			$type					 = $this->resultID->columnType($i);
			$retval[$i]->type        = isset($dataTypes[$type]) ? $dataTypes[$type] : $type;
			$retval[$i]->max_length  = NULL;
		}

		return $retval;
	}

	//--------------------------------------------------------------------

	/**
	 * Frees the current result.
	 *
	 * @return mixed
	 */
	public function freeResult()
	{
		if (is_object($this->resultID))
		{
			$this->resultID->finalize();
			$this->resultID = false;
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Moves the internal pointer to the desired offset. This is called
	 * internally before fetching results to make sure the result set
	 * starts at zero.
	 *
	 * @param int $n
	 *
	 * @return mixed
	 */
	public function dataSeek($n = 0)
	{
		// Only resetting to the start of the result set is supported
		return ($n > 0) ? FALSE : $this->result_id->reset();
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the result set as an array.
	 *
	 * Overridden by driver classes.
	 *
	 * @return array
	 */
	protected function fetchAssoc()
	{
		return $this->resultID->fetchArray(SQLITE3_ASSOC);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the result set as an object.
	 *
	 * Overridden by child classes.
	 *
	 * @param string $className
	 *
	 * @return object
	 */
	protected function fetchObject($className = 'stdClass')
	{
		// No native support for fetching rows as objects
		if (($row = $this->resultID->fetchArray(SQLITE3_ASSOC)) === FALSE)
		{
			return FALSE;
		}
		elseif ($className === 'stdClass')
		{
			return (object) $row;
		}

		$className = new $className();
		foreach (array_keys($row) as $key)
		{
			$className->$key = $row[$key];
		}

		return $className;
	}

	//--------------------------------------------------------------------
}
