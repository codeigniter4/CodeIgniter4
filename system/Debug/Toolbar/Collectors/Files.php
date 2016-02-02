<?php namespace CodeIgniter\Debug\Toolbar\Collectors;

class Files extends BaseCollector
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
	protected $title = 'Files';

	//--------------------------------------------------------------------

	/**
	 * Builds and returns the HTML needed to fill a tab to display
	 * within the Debug Bar
	 *
	 * @return string
	 */
	 public function display(): string
	 {
		$output = "<table><tbody>";

		$files = get_included_files();

		$count = 0;

		foreach ($files as $file)
		{
			++$count;

			$output .= "<td style='width: 3em; text-align: right;'>{$count}</td>";
			$output .= "<td style='width: 20em;'>". htmlspecialchars(str_replace('.php', '', basename($file)), ENT_SUBSTITUTE, 'UTF-8')."</td>";
			$output .= "<td>".htmlspecialchars($this->cleanPath($file), ENT_SUBSTITUTE, 'UTF-8')."</td></tr>";
		}

		$output .= "</tbody></table>";

		return $output;
	 }

	//--------------------------------------------------------------------
}
