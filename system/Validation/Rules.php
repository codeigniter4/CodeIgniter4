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

namespace CodeIgniter\Validation;

use Config\Database;

/**
 * Validation Rules.
 *
 * @package CodeIgniter\Validation
 */
class Rules
{
	//--------------------------------------------------------------------

	/**
	 * The value does not match another field in $data.
	 *
	 * @param string $str
	 * @param string $field
	 * @param array  $data  Other field/value pairs
	 *
	 * @return boolean
	 */
	public function differs(string $str = null, string $field, array $data): bool
	{
		return array_key_exists($field, $data) ? ($str !== $data[$field]) : false;
	}

	//--------------------------------------------------------------------

	/**
	 * Equals the static value provided.
	 *
	 * @param string $str
	 * @param string $val
	 *
	 * @return boolean
	 */
	public function equals(string $str = null, string $val): bool
	{
		return $str === $val;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns true if $str is $val characters long.
	 * $val = "5" (one) | "5,8,12" (multiple values)
	 *
	 * @param string $str
	 * @param string $val
	 * @param array  $data
	 *
	 * @return boolean
	 */
	public function exact_length(string $str = null, string $val, array $data): bool
	{
		$val = explode(',', $val);
		foreach ($val as $tmp)
		{
			if (is_numeric($tmp) && (int)$tmp === mb_strlen($str))
			{
				return true;
			}
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Greater than
	 *
	 * @param string $str
	 * @param string $min
	 * @param array  $data
	 *
	 * @return boolean
	 */
	public function greater_than(string $str = null, string $min, array $data): bool
	{
		return is_numeric($str) ? ($str > $min) : false;
	}

	//--------------------------------------------------------------------

	/**
	 * Equal to or Greater than
	 *
	 * @param string $str
	 * @param string $min
	 * @param array  $data
	 *
	 * @return boolean
	 */
	public function greater_than_equal_to(string $str = null, string $min, array $data): bool
	{
		return is_numeric($str) ? ($str >= $min) : false;
	}

	//--------------------------------------------------------------------

	/**
	 * Checks the database to see if the given value exist.
	 * Can ignore records by field/value to filter (currently
	 * accept only one filter).
	 *
	 * Example:
	 *    is_not_unique[table.field,where_field,where_value]
	 *    is_not_unique[menu.id,active,1]
	 *
	 * @param string $str
	 * @param string $field
	 * @param array  $data
	 *
	 * @return boolean
	 */
	public function is_not_unique(string $str = null, string $field, array $data): bool
	{
		// Grab any data for exclusion of a single row.
		list($field, $where_field, $where_value) = array_pad(explode(',', $field), 3, null);

		// Break the table and field apart
		sscanf($field, '%[^.].%[^.]', $table, $field);

		$db = Database::connect($data['DBGroup'] ?? null);

		$row = $db->table($table)
				  ->select('1')
				  ->where($field, $str)
				  ->limit(1);

		if (! empty($where_field) && ! empty($where_value))
		{
			$row = $row->where($where_field, $where_value);
		}

		return (bool) ($row->get()
						->getRow() !== null);
	}

	//--------------------------------------------------------------------

	/**
	 * Value should be within an array of values
	 *
	 * @param  string $value
	 * @param  string $list
	 * @param  array  $data
	 * @return boolean
	 */
	public function in_list(string $value = null, string $list, array $data): bool
	{
		$list = explode(',', $list);
		$list = array_map(function ($value) {
			return trim($value);
		}, $list);
		return in_array($value, $list, true);
	}

	//--------------------------------------------------------------------

	/**
	 * Checks the database to see if the given value is unique. Can
	 * ignore a single record by field/value to make it useful during
	 * record updates.
	 *
	 * Example:
	 *    is_unique[table.field,ignore_field,ignore_value]
	 *    is_unique[users.email,id,5]
	 *
	 * @param string $str
	 * @param string $field
	 * @param array  $data
	 *
	 * @return boolean
	 */
	public function is_unique(string $str = null, string $field, array $data): bool
	{
		// Grab any data for exclusion of a single row.
		list($field, $ignoreField, $ignoreValue) = array_pad(explode(',', $field), 3, null);

		// Break the table and field apart
		sscanf($field, '%[^.].%[^.]', $table, $field);

		$db = Database::connect($data['DBGroup'] ?? null);

		$row = $db->table($table)
				  ->select('1')
				  ->where($field, $str)
				  ->limit(1);

		if (! empty($ignoreField) && ! empty($ignoreValue))
		{
			$row = $row->where("{$ignoreField} !=", $ignoreValue);
		}

		return (bool) ($row->get()
						->getRow() === null);
	}

	//--------------------------------------------------------------------

	/**
	 * Less than
	 *
	 * @param string $str
	 * @param string $max
	 *
	 * @return boolean
	 */
	public function less_than(string $str = null, string $max): bool
	{
		return is_numeric($str) ? ($str < $max) : false;
	}

	//--------------------------------------------------------------------

	/**
	 * Equal to or Less than
	 *
	 * @param string $str
	 * @param string $max
	 *
	 * @return boolean
	 */
	public function less_than_equal_to(string $str = null, string $max): bool
	{
		return is_numeric($str) ? ($str <= $max) : false;
	}

	//--------------------------------------------------------------------

	/**
	 * Matches the value of another field in $data.
	 *
	 * @param string $str
	 * @param string $field
	 * @param array  $data  Other field/value pairs
	 *
	 * @return boolean
	 */
	public function matches(string $str = null, string $field, array $data): bool
	{
		return array_key_exists($field, $data) ? ($str === $data[$field]) : false;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns true if $str is $val or fewer characters in length.
	 *
	 * @param string $str
	 * @param string $val
	 * @param array  $data
	 *
	 * @return boolean
	 */
	public function max_length(string $str = null, string $val, array $data): bool
	{
		return ($val >= mb_strlen($str));
	}

	//--------------------------------------------------------------------

	/**
	 * Returns true if $str is at least $val length.
	 *
	 * @param string $str
	 * @param string $val
	 * @param array  $data
	 *
	 * @return boolean
	 */
	public function min_length(string $str = null, string $val, array $data): bool
	{
		return ($val <= mb_strlen($str));
	}

	//--------------------------------------------------------------------

	/**
	 * Does not equal the static value provided.
	 *
	 * @param string $str
	 * @param string $val
	 *
	 * @return boolean
	 */
	public function not_equals(string $str = null, string $val): bool
	{
		return $str !== $val;
	}

	//--------------------------------------------------------------------

	/**
	 * Required
	 *
	 * @param mixed $str Value
	 *
	 * @return boolean          True if valid, false if not
	 */
	public function required($str = null): bool
	{
		if (is_object($str))
		{
			return true;
		}

		return is_array($str) ? ! empty($str) : (trim($str) !== '');
	}

	//--------------------------------------------------------------------

	/**
	 * The field is required when any of the other required fields are present
	 * in the data.
	 *
	 * Example (field is required when the password field is present):
	 *
	 *     required_with[password]
	 *
	 * @param $str
	 * @param string $fields List of fields that we should check if present
	 * @param array  $data   Complete list of fields from the form
	 *
	 * @return boolean
	 */
	public function required_with($str = null, string $fields, array $data): bool
	{
		$fields = explode(',', $fields);

		// If the field is present we can safely assume that
		// the field is here, no matter whether the corresponding
		// search field is present or not.
		$present = $this->required($str ?? '');

		if ($present)
		{
			return true;
		}

		// Still here? Then we fail this test if
		// any of the fields are present in $data
		// as $fields is the lis
		$requiredFields = [];

		foreach ($fields as $field)
		{
			if (array_key_exists($field, $data))
			{
				$requiredFields[] = $field;
			}
		}

		// Remove any keys with empty values since, that means they
		// weren't truly there, as far as this is concerned.
		$requiredFields = array_filter($requiredFields, function ($item) use ($data) {
			return ! empty($data[$item]);
		});

		return empty($requiredFields);
	}

	//--------------------------------------------------------------------

	/**
	 * The field is required when all of the other fields are present
	 * in the data but not required.
	 *
	 * Example (field is required when the id or email field is missing):
	 *
	 *     required_without[id,email]
	 *
	 * @param $str
	 * @param string $fields
	 * @param array  $data
	 *
	 * @return boolean
	 */
	public function required_without($str = null, string $fields, array $data): bool
	{
		$fields = explode(',', $fields);

		// If the field is present we can safely assume that
		// the field is here, no matter whether the corresponding
		// search field is present or not.
		$present = $this->required($str ?? '');

		if ($present)
		{
			return true;
		}

		// Still here? Then we fail this test if
		// any of the fields are not present in $data
		foreach ($fields as $field)
		{
			if (! array_key_exists($field, $data))
			{
				return false;
			}
		}

		return true;
	}

	//--------------------------------------------------------------------
}
