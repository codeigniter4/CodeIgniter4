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

namespace CodeIgniter\I18n;

use DateTime;
use IntlCalendar;

/**
 * Class TimeDifference
 *
 * @package CodeIgniter\I18n
 */
class TimeDifference
{
	/**
	 * The timestamp of the "current" time.
	 *
	 * @var \IntlCalendar
	 */
	protected $currentTime;

	/**
	 * The timestamp to compare the current time to.
	 *
	 * @var float
	 */
	protected $testTime;

	/**
	 * Eras.
	 *
	 * @var float
	 */
	protected $eras = 0;

	/**
	 * Years.
	 *
	 * @var float
	 */
	protected $years = 0;
	/**
	 * Months.
	 *
	 * @var float
	 */
	protected $months = 0;
	/**
	 * Weeks.
	 *
	 * @var integer
	 */
	protected $weeks = 0;
	/**
	 * Days.
	 *
	 * @var integer
	 */
	protected $days = 0;
	/**
	 * Hours.
	 *
	 * @var integer
	 */
	protected $hours = 0;
	/**
	 * Minutes.
	 *
	 * @var integer
	 */
	protected $minutes = 0;
	/**
	 * Seconds.
	 *
	 * @var integer
	 */
	protected $seconds = 0;

	/**
	 * Difference in seconds.
	 *
	 * @var integer
	 */
	protected $difference;

	/**
	 * Note: both parameters are required to be in the same timezone. No timezone
	 * shifting is done internally.
	 *
	 * @param DateTime $currentTime
	 * @param DateTime $testTime
	 */
	public function __construct(DateTime $currentTime, DateTime $testTime)
	{
		$this->difference = $currentTime->getTimestamp() - $testTime->getTimestamp();

		$current = IntlCalendar::fromDateTime($currentTime->format('Y-m-d H:i:s'));
		$time    = IntlCalendar::fromDateTime($testTime->format('Y-m-d H:i:s'))
						->getTime();

		$this->currentTime = $current;
		$this->testTime    = $time;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the number of years of difference between the two.
	 *
	 * @param boolean $raw
	 *
	 * @return float|integer
	 */
	public function getYears(bool $raw = false)
	{
		if ($raw)
		{
			return $this->difference / YEAR;
		}

		$time = clone($this->currentTime);
		return $time->fieldDifference($this->testTime, IntlCalendar::FIELD_YEAR);
	}

	/**
	 * Returns the number of months difference between the two dates.
	 *
	 * @param boolean $raw
	 *
	 * @return float|integer
	 */
	public function getMonths(bool $raw = false)
	{
		if ($raw)
		{
			return $this->difference / MONTH;
		}

		$time = clone($this->currentTime);
		return $time->fieldDifference($this->testTime, IntlCalendar::FIELD_MONTH);
	}

	/**
	 * Returns the number of weeks difference between the two dates.
	 *
	 * @param boolean $raw
	 *
	 * @return float|integer
	 */
	public function getWeeks(bool $raw = false)
	{
		if ($raw)
		{
			return $this->difference / WEEK;
		}

		$time = clone($this->currentTime);
		return (int)($time->fieldDifference($this->testTime, IntlCalendar::FIELD_DAY_OF_YEAR) / 7);
	}

	/**
	 * Returns the number of days difference between the two dates.
	 *
	 * @param boolean $raw
	 *
	 * @return float|integer
	 */
	public function getDays(bool $raw = false)
	{
		if ($raw)
		{
			return $this->difference / DAY;
		}

		$time = clone($this->currentTime);
		return $time->fieldDifference($this->testTime, IntlCalendar::FIELD_DAY_OF_YEAR);
	}

	/**
	 * Returns the number of hours difference between the two dates.
	 *
	 * @param boolean $raw
	 *
	 * @return float|integer
	 */
	public function getHours(bool $raw = false)
	{
		if ($raw)
		{
			return $this->difference / HOUR;
		}

		$time = clone($this->currentTime);
		return $time->fieldDifference($this->testTime, IntlCalendar::FIELD_HOUR_OF_DAY);
	}

	/**
	 * Returns the number of minutes difference between the two dates.
	 *
	 * @param boolean $raw
	 *
	 * @return float|integer
	 */
	public function getMinutes(bool $raw = false)
	{
		if ($raw)
		{
			return $this->difference / MINUTE;
		}

		$time = clone($this->currentTime);
		return $time->fieldDifference($this->testTime, IntlCalendar::FIELD_MINUTE);
	}

	/**
	 * Returns the number of seconds difference between the two dates.
	 *
	 * @param boolean $raw
	 *
	 * @return integer
	 */
	public function getSeconds(bool $raw = false)
	{
		if ($raw)
		{
			return $this->difference;
		}

		$time = clone($this->currentTime);
		return $time->fieldDifference($this->testTime, IntlCalendar::FIELD_SECOND);
	}

	/**
	 * Convert the time to human readable format
	 *
	 * @param string|null $locale
	 *
	 * @return string
	 */
	public function humanize(string $locale = null): string
	{
		$current = clone($this->currentTime);

		$years   = $current->fieldDifference($this->testTime, IntlCalendar::FIELD_YEAR);
		$months  = $current->fieldDifference($this->testTime, IntlCalendar::FIELD_MONTH);
		$days    = $current->fieldDifference($this->testTime, IntlCalendar::FIELD_DAY_OF_YEAR);
		$hours   = $current->fieldDifference($this->testTime, IntlCalendar::FIELD_HOUR_OF_DAY);
		$minutes = $current->fieldDifference($this->testTime, IntlCalendar::FIELD_MINUTE);

		$phrase = null;

		if ($years !== 0)
		{
			$phrase = lang('Time.years', [abs($years)], $locale);
			$before = $years < 0;
		}
		elseif ($months !== 0)
		{
			$phrase = lang('Time.months', [abs($months)], $locale);
			$before = $months < 0;
		}
		elseif ($days !== 0 && (abs($days) >= 7))
		{
			$weeks  = ceil($days / 7);
			$phrase = lang('Time.weeks', [abs($weeks)], $locale);
			$before = $days < 0;
		}
		elseif ($days !== 0)
		{
			$phrase = lang('Time.days', [abs($days)], $locale);
			$before = $days < 0;
		}
		elseif ($hours !== 0)
		{
			$phrase = lang('Time.hours', [abs($hours)], $locale);
			$before = $hours < 0;
		}
		elseif ($minutes !== 0)
		{
			$phrase = lang('Time.minutes', [abs($minutes)], $locale);
			$before = $minutes < 0;
		}
		else
		{
			return lang('Time.now', [], $locale);
		}

		return $before
			? lang('Time.ago', [$phrase], $locale)
			: lang('Time.inFuture', [$phrase], $locale);
	}

	/**
	 * Allow property-like access to our calculated values.
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function __get($name)
	{
		$name   = ucfirst(strtolower($name));
		$method = "get{$name}";

		if (method_exists($this, $method))
		{
			return $this->{$method}($name);
		}

		return null;
	}

	/**
	 * Allow property-like checking for our calculated values.
	 *
	 * @param string $name
	 *
	 * @return boolean
	 */
	public function __isset($name)
	{
		$name   = ucfirst(strtolower($name));
		$method = "get{$name}";

		return method_exists($this, $method);
	}
}
