<?php namespace CodeIgniter\Debug\Toolbar\Collectors;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2018 British Columbia Institute of Technology
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
 * @package      CodeIgniter
 * @author       CodeIgniter Dev Team
 * @copyright    2014-2018 British Columbia Institute of Technology (https://bcit.ca/)
 * @license      https://opensource.org/licenses/MIT	MIT License
 * @link         https://codeigniter.com
 * @since        Version 4.0.0
 * @filesource
 */

/**
 * Base Toolbar collector
 */
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
	 * Whether this collector needs to display
	 * a label or not.
	 *
	 * @var bool
	 */
	protected $hasLabel = false;

	/**
	 * Whether this collector has data that
	 * should be shown in the Vars tab.
	 *
	 * @var bool
	 */
	protected $hasVarData = false;

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
	 * @param bool $safe
	 * @return string
	 */
	public function getTitle($safe = false): string
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
		return (bool) $this->hasTabContent;
	}

	//--------------------------------------------------------------------

	/**
	 * Does this collector have a label?
	 *
	 * @return bool
	 */
	public function hasLabel(): bool
	{
		return (bool) $this->hasLabel;
	}

	//--------------------------------------------------------------------

	/**
	 * Does this collector have information for the timeline?
	 *
	 * @return bool
	 */
	public function hasTimelineData(): bool
	{
		return (bool) $this->hasTimeline;
	}

	//--------------------------------------------------------------------

	/**
	 * Grabs the data for the timeline, properly formatted,
	 * or returns an empty array.
	 *
	 * @return array
	 */
	public function timelineData(): array
	{
		if ( ! $this->hasTimeline)
		{
			return [];
		}

		return $this->formatTimelineData();
	}

	//--------------------------------------------------------------------

	/**
	 * Does this Collector have data that should be shown in the
	 * 'Vars' tab?
	 *
	 * @return mixed
	 */
	public function hasVarData()
	{
		return (bool) $this->hasVarData;
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
		return null;
	}

	//--------------------------------------------------------------------

	/**
	 * Child classes should implement this to return the timeline data
	 * formatted for correct usage.
	 *
	 * Timeline data should be formatted into arrays that look like:
	 *
	 *  [
	 *      'name'      => 'Database::Query',
	 *      'component' => 'Database',
	 *      'start'     => 10       // milliseconds
	 *      'duration'  => 15       // milliseconds
	 *  ]
	 *
	 * @return array
	 */
	protected function formatTimelineData(): array
	{
		return [];
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the data of this collector to be formatted in the toolbar
	 *
	 * @return array
	 */
	public function display(): array
	{
		return [];
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
			$file = 'APPPATH/' . substr($file, strlen(APPPATH));
		}
		elseif (strpos($file, BASEPATH) === 0)
		{
			$file = 'BASEPATH/' . substr($file, strlen(BASEPATH));
		}
		elseif (strpos($file, FCPATH) === 0)
		{
			$file = 'FCPATH/' . substr($file, strlen(FCPATH));
		}

		return $file;
	}

	/**
	 * Gets the "badge" value for the button.
	 *
	 * @return null
	 */
	public function getBadgeValue()
	{
		return null;
	}

	/**
	 * Does this collector have any data collected?
	 *
	 * If not, then the toolbar button won't get shown.
	 *
	 * @return bool
	 */
	public function isEmpty()
	{
		return false;
	}

	/**
	 * Returns the HTML to display the icon. Should either
	 * be SVG, or a base-64 encoded.
	 *
	 * Recommended dimensions are 24px x 24px
	 *
	 * @return string
	 */
	public function icon(): string
	{
		return '';
	}

}
