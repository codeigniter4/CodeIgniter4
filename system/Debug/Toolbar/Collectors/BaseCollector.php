<?php namespace CodeIgniter\Debug\Toolbar\Collectors;

class BaseCollector
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
	protected $hasTabContent = false;

	/**
	 * The 'title' of this Collector.
	 * Used to name things in the toolbar HTML.
	 *
	 * @var string
	 */
	protected $title = '';

	//--------------------------------------------------------------------

	/**
	 * Gets the Collector's title.
	 *
	 * @return string
	 */
	public function getTitle($safe=false): string
	{
		if ($safe)
		{
			return str_replace(' ', '-', strtolower($this->title));
		}

	    return $this->title;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns any information that should be shown next to the title.
	 *
	 * @return string
	 */
	public function getTitleDetails(): string
	{
	    return '';
	}

	//--------------------------------------------------------------------



	/**
	 * Does this collector need it's own tab?
	 *
	 * @return bool
	 */
	public function hasTabContent(): bool
	{
		return (bool)$this->hasTabContent;
	}

	//--------------------------------------------------------------------

	/**
	 * Does this collector have information for the timeline?
	 *
	 * @return bool
	 */
	public function hasTimelineData(): bool
	{
	    return (bool)$this->hasTimeline;
	}

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

		return $this->formatTimelineData();
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
		 return [];
	 }

	//--------------------------------------------------------------------

	/**
	 * Builds and returns the HTML needed to fill a tab to display
	 * within the Debug Bar
	 *
	 * @return string
	 */
	public function display(): string
	{
		return '';
	}

	//--------------------------------------------------------------------

	/**
	 * Clean Path
	 *
	 * This makes nicer looking paths for the error output.
	 *
	 * @param    string $file
	 *
	 * @return    string
	 */
	public function cleanPath($file)
	{
		if (strpos($file, APPPATH) === 0)
		{
			$file = 'APPPATH/'.substr($file, strlen(APPPATH));
		}
		elseif (strpos($file, BASEPATH) === 0)
		{
			$file = 'BASEPATH/'.substr($file, strlen(BASEPATH));
		}
		elseif (strpos($file, SYSDIR) === 0)
		{
			$file = 'SYSDIR/'.substr($file, strlen(SYSDIR));
		}
		elseif (strpos($file, FCPATH) === 0)
		{
			$file = 'FCPATH/'.substr($file, strlen(FCPATH));
		}

		return $file;
	}

}
