<?php namespace CodeIgniter\Debug;

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

		$totalTime       = $totalTime * 1000;
		$totalMemory     = number_format((memory_get_peak_usage() - $startMemory) / 1048576, 3);
		$segmentDuration = $this->roundTo($totalTime / 7, 5);
		$segmentCount    = (int)ceil($totalTime / $segmentDuration);
		$varData         = $this->collectVarData();

		ob_start();
		include(__DIR__.'/Toolbar/View/toolbar.tpl.php');
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
			$output .= "<td style='text-align: right'>".number_format($row['duration'] * 1000, 2)." ms</td>";
			$output .= "<td colspan='{$segmentCount}' style='overflow: hidden'>";

			$offset = ((($row['start'] - $this->startTime) * 1000) /
					$displayTime)	* 100;
			$length = (($row['duration'] * 1000) / $displayTime) * 100;

			$output .= "<span class='timer' style='left: {$offset}%; width: {$length}%;' title='".number_format($length, 2)."%'></span>";

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
			if (! $collector->hasTimelineData())
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
			if (! $collector->hasVarData())
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
