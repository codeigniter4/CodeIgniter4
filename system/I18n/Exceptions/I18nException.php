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

namespace CodeIgniter\I18n\Exceptions;

use CodeIgniter\Exceptions\ExceptionInterface;
use CodeIgniter\Exceptions\FrameworkException;

/**
 * I18nException
 */
class I18nException extends FrameworkException implements ExceptionInterface
{
	/**
	 * Thrown when the numeric representation of the month falls
	 * outside the range of allowed months.
	 *
	 * @param string $month
	 *
	 * @return static
	 */
	public static function forInvalidMonth(string $month)
	{
		return new static(lang('Time.invalidMonth', [$month]));
	}

	/**
	 * Thrown when the supplied day falls outside the range
	 * of allowed days.
	 *
	 * @param string $day
	 *
	 * @return static
	 */
	public static function forInvalidDay(string $day)
	{
		return new static(lang('Time.invalidDay', [$day]));
	}

	/**
	 * Thrown when the day provided falls outside the allowed
	 * last day for the given month.
	 *
	 * @param string $lastDay
	 * @param string $day
	 *
	 * @return static
	 */
	public static function forInvalidOverDay(string $lastDay, string $day)
	{
		return new static(lang('Time.invalidOverDay', [$lastDay, $day]));
	}

	/**
	 * Thrown when the supplied hour falls outside the
	 * range of allowed hours.
	 *
	 * @param string $hour
	 *
	 * @return static
	 */
	public static function forInvalidHour(string $hour)
	{
		return new static(lang('Time.invalidHour', [$hour]));
	}

	/**
	 * Thrown when the supplied minutes falls outside the
	 * range of allowed minutes.
	 *
	 * @param string $minutes
	 *
	 * @return static
	 */
	public static function forInvalidMinutes(string $minutes)
	{
		return new static(lang('Time.invalidMinutes', [$minutes]));
	}

	/**
	 * Thrown when the supplied seconds falls outside the
	 * range of allowed seconds.
	 *
	 * @param string $seconds
	 *
	 * @return static
	 */
	public static function forInvalidSeconds(string $seconds)
	{
		return new static(lang('Time.invalidSeconds', [$seconds]));
	}
}
