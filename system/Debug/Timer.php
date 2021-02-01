<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Debug;

use RuntimeException;

/**
 * Class Timer
 *
 * Provides a simple way to measure the amount of time
 * that elapses between two points.
 */
class Timer
{
	/**
	 * List of all timers.
	 *
	 * @var array
	 */
	protected $timers = [];

	//--------------------------------------------------------------------

	/**
	 * Starts a timer running.
	 *
	 * Multiple calls can be made to this method so that several
	 * execution points can be measured.
	 *
	 * @param string $name The name of this timer.
	 * @param float  $time Allows user to provide time.
	 *
	 * @return Timer
	 */
	public function start(string $name, float $time = null)
	{
		$this->timers[strtolower($name)] = [
			'start' => ! empty($time) ? $time : microtime(true),
			'end'   => null,
		];

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Stops a running timer.
	 *
	 * If the timer is not stopped before the timers() method is called,
	 * it will be automatically stopped at that point.
	 *
	 * @param string $name The name of this timer.
	 *
	 * @return Timer
	 */
	public function stop(string $name)
	{
		$name = strtolower($name);

		if (empty($this->timers[$name]))
		{
			throw new RuntimeException('Cannot stop timer: invalid name given.');
		}

		$this->timers[$name]['end'] = microtime(true);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the duration of a recorded timer.
	 *
	 * @param string  $name     The name of the timer.
	 * @param integer $decimals Number of decimal places.
	 *
	 * @return null|float       Returns null if timer exists by that name.
	 *                          Returns a float representing the number of
	 *                          seconds elapsed while that timer was running.
	 */
	public function getElapsedTime(string $name, int $decimals = 4)
	{
		$name = strtolower($name);

		if (empty($this->timers[$name]))
		{
			return null;
		}

		$timer = $this->timers[$name];

		if (empty($timer['end']))
		{
			$timer['end'] = microtime(true);
		}

		return (float) number_format($timer['end'] - $timer['start'], $decimals);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the array of timers, with the duration pre-calculated for you.
	 *
	 * @param integer $decimals Number of decimal places
	 *
	 * @return array
	 */
	public function getTimers(int $decimals = 4): array
	{
		$timers = $this->timers;

		foreach ($timers as &$timer)
		{
			if (empty($timer['end']))
			{
				$timer['end'] = microtime(true);
			}

			$timer['duration'] = (float) number_format($timer['end'] - $timer['start'], $decimals);
		}

		return $timers;
	}

	//--------------------------------------------------------------------

	/**
	 * Checks whether or not a timer with the specified name exists.
	 *
	 * @param string $name
	 *
	 * @return boolean
	 */
	public function has(string $name): bool
	{
		return array_key_exists(strtolower($name), $this->timers);
	}

	//--------------------------------------------------------------------
}
