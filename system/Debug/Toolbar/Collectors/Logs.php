<?php namespace CodeIgniter\Debug\Toolbar\Collectors;

use App\Config\Services;

class Logs extends BaseCollector
{
	/**
	 * Whether this collector has data that can
	 * be displayed in the Timeline.
	 *
	 * @var bool
	 */
	protected $hasTimeline = false;

	/**
	 * Whether this collector needs to display
	 * content in a tab or not.
	 *
	 * @var bool
	 */
	protected $hasTabContent = true;

	/**
	 * The 'title' of this Collector.
	 * Used to name things in the toolbar HTML.
	 *
	 * @var string
	 */
	protected $title = 'Logs';

	//--------------------------------------------------------------------

	/**
	 * Builds and returns the HTML needed to fill a tab to display
	 * within the Debug Bar
	 *
	 * @return string
	 */
	public function display(): string
	{
		$logger = Services::logger(true);
		$logs = $logger->logCache;

		if (empty($logs) || ! is_array($logs))
		{
			return '';
		}

		$output = "<table><theader><tr><th>Severity</th><th>Message</th></tr></theader><tbody>";

		foreach ($logs as $log)
		{
			$output .= "<tr>";
			$output .= "<td>{$log['level']}</td>";
			$output .= "<td>".htmlspecialchars($log['msg'], ENT_SUBSTITUTE, 'UTF-8')."</td>";
			$output .= "</tr>";
		}

		return $output."</tbody></table>";
	}

	//--------------------------------------------------------------------


}
