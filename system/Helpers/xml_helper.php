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
 * @license    https://opensource.org/licenses/MIT    MIT License
 * @link       https://codeigniter.com
 * @since      Version 1.0.0
 * @filesource
 */

/**
 * CodeIgniter XML Helpers
 *
 * @package CodeIgniter
 */

if (! function_exists('xml_convert'))
{
	/**
	 * Convert Reserved XML characters to Entities
	 *
	 * @param  string  $str
	 * @param  boolean $protect_all
	 * @return string
	 */
	function xml_convert(string $str, bool $protect_all = false): string
	{
		$temp = '__TEMP_AMPERSANDS__';

		// Replace entities to temporary markers so that
		// ampersands won't get messed up
		$str = preg_replace('/&#(\d+);/', $temp . '\\1;', $str);

		if ($protect_all === true)
		{
			$str = preg_replace('/&(\w+);/', $temp . '\\1;', $str);
		}

		$original    = [
			'&',
			'<',
			'>',
			'"',
			"'",
			'-',
		];
		$replacement = [
			'&amp;',
			'&lt;',
			'&gt;',
			'&quot;',
			'&apos;',
			'&#45;',
		];
		$str         = str_replace($original, $replacement, $str);

		// Decode the temp markers back to entities
		$str = preg_replace('/' . $temp . '(\d+);/', '&#\\1;', $str);

		if ($protect_all === true)
		{
			return preg_replace('/' . $temp . '(\w+);/', '&\\1;', $str);
		}

		return $str;
	}
}
