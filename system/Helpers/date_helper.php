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
 * @license    https://opensource.org/licenses/MIT    MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

/**
 * CodeIgniter Date Helpers
 *
 * @package CodeIgniter
 */

if (! function_exists('now'))
{
	/**
	 * Get "now" time
	 *
	 * Returns time() based on the timezone parameter or on the
	 * app_timezone() setting
	 *
	 * @param string $timezone
	 *
	 * @return integer
	 * @throws \Exception
	 */
	function now(string $timezone = null): int
	{
		$timezone = empty($timezone) ? app_timezone() : $timezone;

		if ($timezone === 'local' || $timezone === date_default_timezone_get())
		{
			return time();
		}

		$datetime = new DateTime('now', new DateTimeZone($timezone));
		sscanf($datetime->format('j-n-Y G:i:s'), '%d-%d-%d %d:%d:%d', $day, $month, $year, $hour, $minute, $second);

		return mktime($hour, $minute, $second, $month, $day, $year);
	}
}

if (! function_exists('timezone_select'))
{
	/**
	 * Generates a select field of all available timezones
	 *
	 * Returns a string with the formatted HTML
	 *
	 * @param string  $class   Optional class to apply to the select field
	 * @param string  $default Default value for initial selection
	 * @param integer $what    One of the DateTimeZone class constants (for listIdentifiers)
	 * @param string  $country A two-letter ISO 3166-1 compatible country code (for listIdentifiers)
	 *
	 * @return string
	 * @throws \Exception
	 */
	function timezone_select(string $class = '', string $default = '', int $what = \DateTimeZone::ALL, string $country = null): string
	{
		$timezones = \DateTimeZone::listIdentifiers($what, $country);

		$buffer = "<select name='timezone' class='{$class}'>" . PHP_EOL;
		foreach ($timezones as $timezone)
		{
			$selected = ($timezone === $default) ? 'selected' : '';
			$buffer  .= "<option value='{$timezone}' {$selected}>{$timezone}</option>" . PHP_EOL;
		}
		$buffer .= '</select>' . PHP_EOL;

		return $buffer;
	}
}
