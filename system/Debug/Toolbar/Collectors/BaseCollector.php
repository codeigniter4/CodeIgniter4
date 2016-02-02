<?php namespace CodeIgniter\Debug\Toolbar\Collectors;

abstract class BaseCollector
{
	/**
	 * Whether this collector has data that can
	 * be displayed in the Timeline.
	 *
	 * @var bool
	 */
	protected $hasTimeline = false;

	//--------------------------------------------------------------------

	/**
	 * Grabs the data for the timeline, properly formatted,
	 * or returns an empty array.
	 *
	 * @return bool
	 */
	public function timelineData(): array
	{
	    if (! $this->hasTimeline)
	    {
		    return [];
	    }
	}

	//--------------------------------------------------------------------

	/**
	 * Child classes should implement this to return the timeline data
	 * formatted for correct usage.
	 *
	 * @return mixed
	 */
	abstract protected function formatTimelineData();

	//--------------------------------------------------------------------


}
