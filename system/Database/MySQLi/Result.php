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

namespace CodeIgniter\Database\MySQLi;

use CodeIgniter\Database\BaseResult;
use CodeIgniter\Database\ResultInterface;
use CodeIgniter\Entity;

/**
 * Result for MySQLi
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
		static $data_types = [
			MYSQLI_TYPE_DECIMAL     => 'decimal',
			MYSQLI_TYPE_NEWDECIMAL  => 'newdecimal',
			MYSQLI_TYPE_FLOAT       => 'float',
			MYSQLI_TYPE_DOUBLE      => 'double',
			
			MYSQLI_TYPE_BIT         => 'bit',
			MYSQLI_TYPE_TINY        => 'tiny',
			MYSQLI_TYPE_SHORT       => 'short',
			MYSQLI_TYPE_LONG        => 'long',
			MYSQLI_TYPE_LONGLONG    => 'longlong',
			MYSQLI_TYPE_INT24       => 'int24',
			
			MYSQLI_TYPE_YEAR        => 'year',
			
			MYSQLI_TYPE_TIMESTAMP   => 'timestamp',
			MYSQLI_TYPE_DATE        => 'date',
			MYSQLI_TYPE_TIME        => 'time',
			MYSQLI_TYPE_DATETIME    => 'datetime',
			MYSQLI_TYPE_NEWDATE     => 'newdate',
			
			MYSQLI_TYPE_INTERVAL    => 'interval',
			MYSQLI_TYPE_SET         => 'set',
			MYSQLI_TYPE_ENUM        => 'enum',
			
			MYSQLI_TYPE_VAR_STRING  => 'var_string',
			MYSQLI_TYPE_STRING      => 'string',
			MYSQLI_TYPE_CHAR        => 'char',
			
			MYSQLI_TYPE_GEOMETRY    => 'geometry',
			MYSQLI_TYPE_TINY_BLOB   => 'tiny_blob',
			MYSQLI_TYPE_MEDIUM_BLOB => 'medium_blob',
			MYSQLI_TYPE_LONG_BLOB   => 'long_blob',
			MYSQLI_TYPE_BLOB        => 'blob',
		];
		
		$retVal    = [];
		$fieldData = $this->resultID->fetch_fields();

		foreach ($fieldData as $i => $data)
		{
			$retVal[$i]              = new \stdClass();
			$retVal[$i]->name        = $data->name;
			$retVal[$i]->type        = $data->type;
			$retVal[$i]->type_name   = isset($data_types[$data->type]) ? $data_types[$data->type] : null;
			$retVal[$i]->max_length  = $data->max_length;
			$retVal[$i]->primary_key = (int) ($data->flags & 2);
			$retVal[$i]->length      = $data->length;
			$retVal[$i]->default     = $data->def;
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
	 * @param integer $n
	 *
	 * @return mixed
	 */
	public function dataSeek(int $n = 0)
	{
		return $this->resultID->data_seek($n);
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
	 * @return object|boolean|Entity
	 */
	protected function fetchObject(string $className = 'stdClass')
	{
		if (is_subclass_of($className, Entity::class))
		{
			return empty($data = $this->fetchAssoc()) ? false : (new $className())->setAttributes($data);
		}
		return $this->resultID->fetch_object($className);
	}

	//--------------------------------------------------------------------
}
