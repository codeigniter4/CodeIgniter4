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

/**
 * RequiredRules.
 *
 * @package CodeIgniter\Validation
 */
class Required
{
	/**
	 * Required
	 *
	 * @param    string
	 *
	 * @return    bool
	 */
	public function required($str=null): bool
	{
		return is_array($str) ? (bool)count($str) : (trim($str) !== '');
	}

	//--------------------------------------------------------------------

	/**
	 * The field is required when any of the other fields are present
	 * in the data.
	 *
	 * Example (field is required when the password field is present):
	 *
	 * 	required_with[password]
	 *
	 * @param        $str
	 * @param string $fields
	 * @param array  $data
	 *
	 * @return bool
	 */
	public function required_with($str=null, string $fields, array $data): bool
	{
	    $fields = explode(',', $fields);

		// If the field is present we can safely assume that
		// the field is here, no matter whether the corresponding
		// search field is present or not.
		$present = $this->required($data[$str] ?? null);

		if ($present === true)
		{
			return true;
		}

		// Still here? Then we fail this test if
		// any of the fields are present in $data
		$requiredFields = array_intersect($fields, $data);

		$requiredFields = array_filter($requiredFields, function($item)
		{
			return ! empty($item);
		});

		return ! (bool)count($requiredFields);
	}

	//--------------------------------------------------------------------

	/**
	 * The field is required when all of the other fields are not present
	 * in the data.
	 *
	 * Example (field is required when the id or email field is missing):
	 *
	 * 	required_without[id,email]
	 *
	 * @param        $str
	 * @param string $fields
	 * @param array  $data
	 *
	 * @return bool
	 */
	public function required_without($str=null, string $fields, array $data): bool
	{
		$fields = explode(',', $fields);

		// If the field is present we can safely assume that
		// the field is here, no matter whether the corresponding
		// search field is present or not.
		$present = $this->required($data[$str] ?? null);

		if ($present === true)
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

}
