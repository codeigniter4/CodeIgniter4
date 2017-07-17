<?php namespace CodeIgniter\Debug;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2017 British Columbia Institute of Technology
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
 * @package	CodeIgniter
 * @author	CodeIgniter Dev Team
 * @copyright	2014-2017 British Columbia Institute of Technology (https://bcit.ca/)
 * @license	https://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */
use CodeIgniter\Config\BaseConfig;

/**
 * Debug Toolbar
 *
 * Displays a toolbar with bits of stats to aid a developer in debugging.
 *
 * Inspiration: http://prophiler.fabfuel.de
 *
 * @package CodeIgniter\Debug
 */
class Toolbar
{

	/**
	 * Collectors to be used and displayed.
	 *
	 * @var array
	 */
	protected $collectors = [];

	/**
	 * @var float App start time
	 */
	protected $startTime;

	//--------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @param BaseConfig $config
	 */
	public function __construct(BaseConfig $config)
	{
		foreach ($config->toolbarCollectors as $collector)
		{
			if ( ! class_exists($collector))
			{
				// @todo Log this!
				continue;
			}

			$this->collectors[] = new $collector();
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Run
	 *
	 * @param type $startTime
	 * @param type $totalTime
	 * @param type $startMemory
	 * @param type $request
	 * @param type $response
	 * @return type
	 */
	public function run($startTime, $totalTime, $startMemory, $request, $response): string
	{
		$this->startTime = $startTime;

		// Data items used within the view.
		$collectors = $this->collectors;

		$totalTime = $totalTime * 1000;
		$totalMemory = number_format((memory_get_peak_usage() - $startMemory) / 1048576, 3);
		$segmentDuration = $this->roundTo($totalTime / 7, 5);
		$segmentCount = (int) ceil($totalTime / $segmentDuration);
		$varData = $this->collectVarData();

		ob_start();
		include(__DIR__ . '/Toolbar/Views/toolbar.tpl.php');
		$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}

	//--------------------------------------------------------------------

	/**
	 * Called within the view to display the timeline itself.
	 *
	 * @param int $segmentCount
	 * @param int $segmentDuration
	 * @return string
	 */
	protected function renderTimeline(int $segmentCount, int $segmentDuration): string
	{
		$displayTime = $segmentCount * $segmentDuration;

		$rows = $this->collectTimelineData();

		$output = '';

		foreach ($rows as $row)
		{
			$output .= "<tr>";
			$output .= "<td>{$row['name']}</td>";
			$output .= "<td>{$row['component']}</td>";
			$output .= "<td style='text-align: right'>" . number_format($row['duration'] * 1000, 2) . " ms</td>";
			$output .= "<td colspan='{$segmentCount}' style='overflow: hidden'>";

			$offset = ((($row['start'] - $this->startTime) * 1000) /
					$displayTime) * 100;
			$length = (($row['duration'] * 1000) / $displayTime) * 100;

			$output .= "<span class='timer' style='left: {$offset}%; width: {$length}%;' title='" . number_format($length, 2) . "%'></span>";

			$output .= "</td>";

			$output .= "</tr>";
		}

		return $output;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a sorted array of timeline data arrays from the collectors.
	 *
	 * @return array
	 */
	protected function collectTimelineData(): array
	{
		$data = [];

		// Collect it
		foreach ($this->collectors as $collector)
		{
			if ( ! $collector->hasTimelineData())
			{
				continue;
			}

			$data = array_merge($data, $collector->timelineData());
		}

		// Sort it


		return $data;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns an array of data from all of the modules
	 * that should be displayed in the 'Vars' tab.
	 *
	 * @return array
	 */
	protected function collectVarData()// : array
	{
		$data = [];

		foreach ($this->collectors as $collector)
		{
			if ( ! $collector->hasVarData())
			{
				continue;
			}

			$data = array_merge($data, $collector->getVarData());
		}

		return $data;
	}

	//--------------------------------------------------------------------

	/**
	 * Rounds a number to the nearest incremental value.
	 *
	 * @param     $number
	 * @param int $increments
	 *
	 * @return float
	 */
	protected function roundTo($number, $increments = 5)
	{
		$increments = 1 / $increments;

		return (ceil($number * $increments) / $increments);
	}

	//--------------------------------------------------------------------
}
