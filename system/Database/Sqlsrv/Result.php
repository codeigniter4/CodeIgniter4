<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
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
 * @copyright  2014-2019 British Columbia Institute of Technology (https://bcit.ca/)
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 3.0.0
 * @filesource
 */

namespace CodeIgniter\Database\Sqlsrv;

use CodeIgniter\Database\BaseResult;
use CodeIgniter\Database\ResultInterface;
use stdClass;

/**
 * Result for Sqlsrv
 */
class Result extends BaseResult implements ResultInterface
{
	/**
	 * Row offset
	 *
	 * @var integer
	 */
	private $rowOffset = 0;

	/**
	 * Gets the number of fields in the result set.
	 *
	 * @return integer
	 */
	public function getFieldCount(): int
	{
		return @sqlsrv_num_fields($this->resultID);
	}

	//--------------------------------------------------------------------

	/**
	 * Generates an array of column names in the result set.
	 *
	 * @return array
	 */
	public function getFieldNames(): array
	{
		$fieldNames = [];
		foreach (sqlsrv_field_metadata($this->resultID) as $offset => $field)
		{
			$fieldNames[] = $field['Name'];
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
		$retVal = [];
		foreach (sqlsrv_field_metadata($this->resultID) as $i => $field)
		{
			$retVal[$i]             = new stdClass();
			$retVal[$i]->name       = $field['Name'];
			$retVal[$i]->type       = $field['Type'];
			$retVal[$i]->max_length = $field['Size'];
		}

		return $retVal;
	}

	//--------------------------------------------------------------------

	/**
	 * Frees the current result.
	 *
	 * @return void
	 */
	public function freeResult()
	{
		if (is_resource($this->resultID))
		{
			sqlsrv_free_stmt($this->resultID);
			$this->resultID = false;
		}
	}

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
	public function dataSeek(int $n = 0)
	{
		$this->rowOffset = $n;
		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the result set as an array.
	 *
	 * Overridden by driver classes.
	 *
	 * @return mixed
	 */
	protected function fetchAssoc()
	{
		//return sqlsrv_fetch_array($this->resultID, SQLSRV_FETCH_ASSOC, SQLSRV_SCROLL_RELATIVE, $this->rowOffset );
		return sqlsrv_fetch_array($this->resultID, SQLSRV_FETCH_ASSOC);
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
	protected function fetchObject(string $className = 'stdClass')
	{
		//return sqlsrv_fetch_object($this->resultID, $className, null, SQLSRV_SCROLL_RELATIVE, $this->rowOffset );
		return sqlsrv_fetch_object($this->resultID, $className );
	}

	//--------------------------------------------------------------------
}
