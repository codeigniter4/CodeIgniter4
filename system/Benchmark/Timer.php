<?php namespace CodeIgniter\Benchmark;

/**
 * Class Timer
 *
 * Provides a simple way to measure the amount of time
 * that elapses between two points.
 *
 * NOTE: All methods are static since the class is intended
 * to measure throughout an entire application's life cycle.
 *
 * @package CodeIgniter\Benchmark
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
	 * @param string $name  The name of this timer.
	 */
	public function start($name)
	{
		$this->timers[strtolower($name)][] = [
			'start' => microtime(true),
			'end'   => null,
		];
	}

	//--------------------------------------------------------------------

	/**
	 * Stops a running timer.
	 *
	 * If the timer is not stopped before the timers() method is called,
	 * it will be automatically stopped at that point.
	 *
	 * @param string $name   The name of this timer.
	 */
	public function stop($name)
	{
		$name = strtolower($name);

		if (empty($this->timers[$name]))
		{
			throw new \RuntimeException('Cannot stop timer: invalid name given.');
		}

		$this->timers[$name]['end'] = microtime(true);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the duration of a recorded timer.
	 *
	 * @param     $name         The name of the timer.
	 * @param int $decimals     Number of decimal places.
	 *
	 * @return null|float       Returns null if timer exists by that name.
	 *                          Returns a float representing the number of
	 *                          seconds elapsed while that timer was running.
	 */
	public function elapsedTime($name, $decimals = 4)
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

		return number_format($timer['start'] - $timer['start'], $decimals);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the array of timers, with the duration pre-calculated for you.
	 *
	 * @param int $decimals     Number of decimal places
	 *
	 * @return array
	 */
	public function timers($decimals = 4)
	{
		$timers = $this->timers;

		foreach ($timers as &$timer)
		{
			if (empty($timer['end']))
			{
				$timer['end'] = microtime(true);
			}

			$timer['duration'] = number_format($timer['end'] - $timer['start'], $decimals);
		}

		return $timers;
	}

	//--------------------------------------------------------------------

}