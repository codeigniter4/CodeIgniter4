<?php namespace CodeIgniter\Benchmark;

class Iterator
{

	/**
	 * Stores the tests that we are to run.
	 *
	 * @var array
	 */
	protected $tests = [];

	/**
	 * Stores the results of each of the tests.
	 *
	 * @var array
	 */
	protected $results = [];

	//--------------------------------------------------------------------

	/**
	 * Adds a test to run.
	 *
	 * Tests are simply closures that the user can define any sequence of
	 * things to happen during the test.
	 *
	 * @param          $name
	 * @param \Closure $closure
	 *
	 * @return $this
	 */
	public function add($name, \Closure $closure)
	{
		$name = strtolower($name);

		$tests[$name] = $closure;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Runs through all of the tests that have been added, recording
	 * time to execute the desired number of iterations, and the approximate
	 * memory usage used during those iterations.
	 *
	 * @param int $iterations
	 */
	public function run($iterations = 1000)
	{
		foreach ($this->tests as $name => $test)
		{
			// clear memory before start
			gc_collect_cycles();

			$start     = microtime(true);
			$start_mem = $max_memory = memory_get_usage(true);

			for ($i = 0; $i < $iterations; $i++)
			{
				$result = call_user_func($test);

				$max_memory = max($max_memory, memory_get_usage(true));

				unset($result);
			}

			$this->results[$name] = [
				'time'   => microtime(true) - $start,
				'memory' => $max_memory - $start_mem,
				'n'      => $iterations,
			];
		}
	}

	//--------------------------------------------------------------------

}