<?php namespace CodeIgniter\Database\MySQLi;

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
use CodeIgniter\Database\BaseResult;
use CodeIgniter\Database\ResultInterface;

/**
 * Result for MySQLi
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
		return $this->resultID->field_count;
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
		$this->resultID->field_seek(0);
		while ($field = $this->resultID->fetch_field())
		{
			$fieldNames[] = $field->name;
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
		$retval = [];
		$fieldData = $this->resultID->fetch_fields();

		for ($i = 0, $c = count($fieldData); $i < $c; $i ++ )
		{
			$retval[$i] = new \stdClass();
			$retval[$i]->name = $fieldData[$i]->name;
			$retval[$i]->type = $fieldData[$i]->type;
			$retval[$i]->max_length = $fieldData[$i]->max_length;
			$retval[$i]->primary_key = (int) ($fieldData[$i]->flags & 2);
			$retval[$i]->default = $fieldData[$i]->def;
		}

		return $retval;
	}

	//--------------------------------------------------------------------

	/**
	 * Frees the current result.
	 */
	public function freeResult()
	{
		if (is_object($this->resultID))
		{
			$this->resultID->free();
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
		return $this->resultID->data_seek($n);
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
		return $this->resultID->fetch_assoc();
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
		return $this->resultID->fetch_object($className);
	}

	//--------------------------------------------------------------------
}
