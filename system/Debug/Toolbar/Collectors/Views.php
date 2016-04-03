<?php namespace CodeIgniter\Debug\Toolbar\Collectors;

use CodeIgniter\Services;
use CodeIgniter\View\RenderableInterface;

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
	 * Whether this collector has data that
	 * should be shown in the Vars tab.
	 *
	 * @var bool
	 */
	protected $hasVarData = true;

	/**
	 * The 'title' of this Collector.
	 * Used to name things in the toolbar HTML.
	 *
	 * @var string
	 */
	protected $title = 'Views';

	/**
	 * Instance of the Renderer service
	 * @var RenderableInterface
	 */
	protected $viewer;

	//--------------------------------------------------------------------

	public function __construct()
	{
	    $this->viewer = Services::renderer(null, true);
	}

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

		$rows = $this->viewer->getPerformanceData();

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

	/**
	 * Gets a collection of data that should be shown in the 'Vars' tab.
	 * The format is an array of sections, each with their own array
	 * of key/value pairs:
	 *
	 *  $data = [
	 *      'section 1' => [
	 *          'foo' => 'bar,
	 *          'bar' => 'baz'
	 *      ],
	 *      'section 2' => [
	 *          'foo' => 'bar,
	 *          'bar' => 'baz'
	 *      ],
	 *  ];
	 *
	 * @return null
	 */
	public function getVarData()
	{
		return [
			'View Data' => $this->viewer->getData()
		];
	}

	//--------------------------------------------------------------------


}
