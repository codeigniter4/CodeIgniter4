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
 * Rules.
 *
 * @package CodeIgniter\Validation
 */
class Comparison
{
	/**
	 * The value does not match another field in $data.
	 *
	 * @param string $str
	 * @param string $field
	 * @param array  $data Other field/value pairs
	 *
	 * @return bool
	 */
	public function differs(string $str=null, string $field, array $data): bool
	{
		return array_key_exists($field, $data)
			? ($str !== $data[$field])
			: false;
	}

	//--------------------------------------------------------------------

	/**
	 * Greater than
	 *
	 * @param    string
	 * @param    int
	 *
	 * @return    bool
	 */
	public function greater_than(string $str=null, string $min, array $data): bool
	{
		return is_numeric($str) ? ($str > $min) : false;
	}

	//--------------------------------------------------------------------

	/**
	 * Equal to or Greater than
	 *
	 * @param    string
	 * @param    int
	 *
	 * @return    bool
	 */
	public function greater_than_equal_to(string $str=null, string $min, array $data): bool
	{
		return is_numeric($str) ? ($str >= $min) : false;
	}

	//--------------------------------------------------------------------

	/**
	 * Less than
	 *
	 * @param    string
	 * @param    int
	 *
	 * @return    bool
	 */
	public function less_than(string $str=null, string $max): bool
	{
		return is_numeric($str) ? ($str < $max) : false;
	}

	//--------------------------------------------------------------------

	/**
	 * Equal to or Less than
	 *
	 * @param    string
	 * @param    int
	 *
	 * @return    bool
	 */
	public function less_than_equal_to(string $str=null, string $max): bool
	{
		return is_numeric($str) ? ($str <= $max) : false;
	}

	//--------------------------------------------------------------------

	/**
	 * Matches the value of another field in $data.
	 *
	 * @param string $str
	 * @param string $field
	 * @param array  $data Other field/value pairs
	 *
	 * @return bool
	 */
	public function matches(string $str=null, string $field, array $data): bool
	{
		return array_key_exists($field, $data)
			? ($str === $data[$field])
			: false;
	}

}
