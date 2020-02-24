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
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

namespace CodeIgniter\Debug;

/**
 * Iterator for debugging.
 */
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
	 * @param string   $name
	 * @param \Closure $closure
	 *
	 * @return $this
	 */
	public function add(string $name, \Closure $closure)
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
	 * @param integer $iterations
	 * @param boolean $output
	 *
	 * @return string|null
	 */
	public function run(int $iterations = 1000, bool $output = true)
	{
		foreach ($this->tests as $name => $test)
		{
			// clear memory before start
			gc_collect_cycles();

			$start     = microtime(true);
			$start_mem = $max_memory = memory_get_usage(true);

			for ($i = 0; $i < $iterations; $i ++)
			{
				$result = $test();

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
			return $this->getReport();
		}

		return null;
	}

	//--------------------------------------------------------------------

	/**
	 * Get results.
	 *
	 * @return string
	 */
	public function getReport(): string
	{
		if (empty($this->results))
		{
			return 'No results to display.';
		}

		helper('number');

		// Template
		$tpl = '<table>
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
		</table>';

		$rows = '';

		foreach ($this->results as $name => $result)
		{
			$memory = number_to_size($result['memory'], 4);

			$rows .= "<tr>
				<td>{$name}</td>
				<td>" . number_format($result['time'], 4) . "</td>
				<td>{$memory}</td>
			</tr>";
		}

		$tpl = str_replace('{rows}', $rows, $tpl);

		return $tpl . '<br/>';
	}

	//--------------------------------------------------------------------

}
