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
class AlphaFormat
{
	/**
	 * Alpha
	 *
	 * @param    string
	 *
	 * @return    bool
	 */
	public function alpha(string $str=null): bool
	{
		return ctype_alpha($str);
	}

	//--------------------------------------------------------------------

	/**
	 * Alpha with spaces.
	 *
	 * @param string $value Value.
	 *
	 * @return bool True if alpha with spaces, else false.
	 */
	public function alpha_space(string $value = null): bool
	{
		if ($value === null)
		{
			return true;
		}

		return (bool)preg_match('/^[A-Z ]+$/i', $value);
	}

	//--------------------------------------------------------------------

	/**
	 * Alpha-numeric with underscores and dashes
	 *
	 * @param    string
	 *
	 * @return    bool
	 */
	public function alpha_dash(string $str=null): bool
	{
		return (bool)preg_match('/^[a-z0-9_-]+$/i', $str);
	}

	//--------------------------------------------------------------------

	/**
	 * Alpha-numeric
	 *
	 * @param    string
	 *
	 * @return    bool
	 */
	public function alpha_numeric(string $str=null): bool
	{
		return ctype_alnum((string)$str);
	}

	//--------------------------------------------------------------------

	/**
	 * Alpha-numeric w/ spaces
	 *
	 * @param    string
	 *
	 * @return    bool
	 */
	public function alpha_numeric_spaces(string $str=null): bool
	{
		return (bool)preg_match('/^[A-Z0-9 ]+$/i', $str);
	}
}
