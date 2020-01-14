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
 * @since      Version 4.0.0
 * @filesource
 */

namespace CodeIgniter\Database\OCI8;

use CodeIgniter\Database\BaseResult;
use CodeIgniter\Database\ResultInterface;
use CodeIgniter\Entity;

/**
 * Result for OCI
 */
class Result extends BaseResult implements ResultInterface
{

	/**
	 * Gets the number of fields in the result set.
	 *
	 * @return integer
	 */
	public function getFieldCount(): int
	{
		return oci_num_fields($this->resultID);
	}

	//--------------------------------------------------------------------

	/**
	 * Generates an array of column names in the result set.
	 *
	 * @return array
	 */
	public function getFieldNames(): array
	{
		return array_map(function ($field_index) {
			return oci_field_name($this->resultID, $field_index);
		}, range(1, $this->getFieldCount()));
	}

	//--------------------------------------------------------------------

	/**
	 * Generates an array of objects representing field meta-data.
	 *
	 * @return array
	 */
	public function getFieldData(): array
	{
		return array_map(function ($field_index) {
			return (object) [
								'name'       => oci_field_name($this->resultID, $field_index),
								'type'       => oci_field_type($this->resultID, $field_index),
								'max_length' => oci_field_size($this->resultID, $field_index),
				// 'primary_key' = (int) ($data->flags & 2),
				// 'default'     = $data->def,
							];
		}, range(1, $this->getFieldCount()));
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
			oci_free_statement($this->resultID);
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
		// We can't support data seek by oci
		return false;
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
		return oci_fetch_assoc($this->resultID);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the result set as an object.
	 *
	 * Overridden by child classes.
	 *
	 * @param string $className
	 *
	 * @return object|boolean|Entity
	 */
	protected function fetchObject(string $className = \stdClass::class)
	{
		$row = oci_fetch_object($this->resultID);

		if ($className === 'stdClass' || ! $row)
		{
			return $row;
		}
		elseif (is_subclass_of($className, Entity::class))
		{
			return (new $className())->setAttributes((array) $row);
		}

		$instance = new $className();
		foreach ($row as $key => $value)
		{
			$instance->$key = $value;
		}

		return $instance;
	}

	//--------------------------------------------------------------------
}
