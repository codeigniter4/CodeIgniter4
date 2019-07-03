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

/**
 * CodeIgniter Array Helpers
 *
 * @package CodeIgniter
 */

if (! function_exists('dot_array_search'))
{
	/**
	 * Searches an array through dot syntax. Supports
	 * wildcard searches, like foo.*.bar
	 *
	 * @param string $index
	 * @param array  $array
	 *
	 * @return mixed|null
	 */
	function dot_array_search(string $index, array $array)
	{
		$segments = explode('.', rtrim(rtrim($index, '* '), '.'));

		return _array_search_dot($segments, $array);
	}
}

if (! function_exists('_array_search_dot'))
{
	/**
	 * Used by dot_array_search to recursively search the
	 * array with wildcards.
	 *
	 * @param array $indexes
	 * @param array $array
	 *
	 * @return mixed|null
	 */
	function _array_search_dot(array $indexes, array $array)
	{
		// Grab the current index
		$currentIndex = $indexes
			? array_shift($indexes)
			: null;

		if (empty($currentIndex) || (! isset($array[$currentIndex]) && $currentIndex !== '*'))
		{
			return null;
		}

		// Handle Wildcard (*)
		if ($currentIndex === '*')
		{
			// If $array has more than 1 item, we have to loop over each.
			if (is_array($array))
			{
				foreach ($array as $key => $value)
				{
					$answer = _array_search_dot($indexes, $value);

					if ($answer !== null)
					{
						return $answer;
					}
				}

				// Still here after searching all child nodes?
				return null;
			}
		}

		// If this is the last index, make sure to return it now,
		// and not try to recurse through things.
		if (empty($indexes))
		{
			return $array[$currentIndex];
		}

		// Do we need to recursively search this value?
		if (is_array($array[$currentIndex]) && $array[$currentIndex])
		{
			return _array_search_dot($indexes, $array[$currentIndex]);
		}

		// Otherwise we've found our match!
		return $array[$currentIndex];
	}
}
