<?php namespace CodeIgniter\Validation\Rules;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2017, British Columbia Institute of Technology
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
 * @copyright	Copyright (c) 2014 - 2017, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	https://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */

use Config\Database;

/**
 * Rules.
 *
 * @package CodeIgniter\Validation
 */
class DatabaseDependency
{
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
	 * @return bool
	 */
	public function is_unique(string $str=null, string $field, array $data): bool
	{
		// Grab any data for exclusion of a single row.
		list($field, $ignoreField, $ignoreValue) = array_pad(explode(',', $field), 3, null);

		// Break the table and field apart
		sscanf($field, '%[^.].%[^.]', $table, $field);

		$db  = Database::connect();
		$row = $db->table($table)
				  ->where($field, $str);

		if (! empty($ignoreField) && ! empty($ignoreValue))
		{
			$row = $row->where("{$ignoreField} !=", $ignoreValue);
		}

		return (bool)($row->get()
						  ->getRow() === null);
	}
}
