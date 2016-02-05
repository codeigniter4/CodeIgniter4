<?php namespace CodeIgniter\Debug\Toolbar\Collectors;

use App\Config\Services;

class Views extends BaseCollector
{
	/**
	 * Whether this collector has data that can
	 * be displayed in the Timeline.
	 *
	 * @var bool
	 */
	protected $hasTimeline = true;

	/**
	 * Whether this collector needs to display
	 * content in a tab or not.
	 *
	 * @var bool
	 */
	protected $hasTabContent = false;

	/**
	 * The 'title' of this Collector.
	 * Used to name things in the toolbar HTML.
	 *
	 * @var string
	 */
	protected $title = 'Views';

	//--------------------------------------------------------------------

	/**
	 * Child classes should implement this to return the timeline data
	 * formatted for correct usage.
	 *
	 * @return mixed
	 */
	protected function formatTimelineData(): array
	{
		$data = [];

		$viewer = Services::renderer(null, true);
		$rows = $viewer->getPerformanceData();

		foreach ($rows as $name => $info)
		{
			$data[] = [
				'name' => 'View: '.$info['view'],
				'component' => 'Views',
				'start'     => $info['start'],
				'duration'  => $info['end'] - $info['start']
			];
		}

		return $data;
	}

	//--------------------------------------------------------------------
}
