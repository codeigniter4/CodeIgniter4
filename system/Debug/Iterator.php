<?php namespace CodeIgniter\Debug;

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

		$this->tests[$name] = $closure;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Runs through all of the tests that have been added, recording
	 * time to execute the desired number of iterations, and the approximate
	 * memory usage used during those iterations.
	 *
	 * @param int $iterations
	 *
	 * @return string
	 */
	public function run($iterations = 1000, $output=true)
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

		if ($output)
		{
			return $this->report();
		}
	}

	//--------------------------------------------------------------------

	public function getReport()
	{
		if (empty($this->results))
		{
			return 'No results to display.';
		}

		// Template
		$tpl = "<table>
			<thead>
				<tr>
					<td>Test</td>
					<td>Time</td>
					<td>Memory</td>
				</tr>
			</thead>
			<tbody>
				{rows}
			</tbody>
		</table>";

		$rows = "";

		foreach ($this->results as $name => $result)
		{
			$rows .= "<tr>
				<td>{$name}</td>
				<td>".number_format($result['time'], 4)."</td>
				<td>{$result['memory']}</td>
			</tr>";
		}

		$tpl = str_replace('{rows}', $rows, $tpl);

		return $tpl ."<br/>";
	}

	//--------------------------------------------------------------------


}