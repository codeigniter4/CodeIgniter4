<?php namespace CodeIgniter\Debug\Toolbar\Collectors;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2016, British Columbia Institute of Technology
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
 * @package	  CodeIgniter
 * @author	  CodeIgniter Dev Team
 * @copyright Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	  http://opensource.org/licenses/MIT	MIT License
 * @link	  http://codeigniter.com
 * @since	  Version 4.0.0
 * @filesource
 */

use Config\Services;

/**
 * Collector for the Database tab of the Debug Toolbar.
 */
class Database extends BaseCollector
{
	/** @var boolean Whether this collector has timeline data. */
	protected $hasTimeline = true;

	/** @var boolean Whether this collector should display its own tab. */
	protected $hasTabContent = true;

	/** @var boolean Whether this collector has data for the Vars tab. */
	protected $hasVarData = false;

	/** @var string The name used to reference this collector in the toolbar. */
	protected $title = 'Database';

	/** @var \system\Data\Database The database object. */
	protected $db;

	/** @var integer The number of queries included in the data. */
	protected $numberOfQueries = 0;

	/** @var array The performance data for the timeline. */
	protected $performanceData;

	/** @var array The debug data (query times and text) for the tab. */
	protected $debugData;

	//--------------------------------------------------------------------

	public function __construct()
	{
		$this->db = Services::database(null, null, true);
		$this->performanceData = $this->db->getPerformanceData();
		$this->debugData = $this->db->getDebugData();
		if ( ! empty($this->debugData['queries']) && is_array($this->debugData['queries']))
		{
			$this->numberOfQueries = count($this->debugData['queries']);
		}
	}

	/**
	 * Returns timeline data formatted for the toolbar.
	 *
	 * @return array The formatted data or an empty array.
	 */
	protected function formatTimelineData(): array
	{
		$data = [];

		foreach ($this->performanceData as $name => $info)
		{
			$data[] = [
				'name'      => $info['tag'],
				'component' => 'Database',
				'start'     => $info['start'],
				'duration'  => $info['end'] - $info['start'],
			];
		}

		return $data;
	}

	/**
	 * Returns the HTML to fill the Database tab in the toolbar.
	 *
	 * @return string The data formatted for the toolbar.
	 */
	public function display(): string
	{
		if (empty($this->debugData) || ! is_array($this->debugData))
		{
			return '<p>No debug data available. Either the database connection does not have this feature enabled, or the connection was not found.</p>';
		}

		$output = '';
		if ( ! empty($this->debugData['queries']) && is_array($this->debugData['queries']))
		{
			$count = 0;
			$output .= "<table class='queries'><thead><tr><th>Time</th><th>Query</th></tr></thead><tbody>";
			foreach ($this->debugData['queries'] as $query)
			{
				$output .= "<tr><td class='query-time'>" . (isset($this->debugData['queryTimes'][$count]) ? $this->debugData['queryTimes'][$count] : '') . "</td><td class='query-text'>" . esc($query, 'html') . "</td></tr>";
				$count++;
			}
			$output .= "</tbody></table>";
			unset($this->debugData['queryTimes'], $this->debugData['queries']);
		}

		if (empty($this->debugData) || ! is_array($this->debugData))
		{
			return $output;
		}

		// placeholder for eventual handling of the data.
		$output .= '<table><thead><tr><th>Heading</th></tr></thead><tbody>';

		foreach ($this->debugData as $data)
		{
			$output .= "<tr><td>" . print_r($data, true) . "</td></tr>";
		}
		return "{$output}</tbody></table>";
	}

	/**
	 * Information to be displayed next to the title.
	 *
	 * @return string The number of queries (in parentheses) or an empty string.
	 */
	public function getTitleDetails(): string
	{
		return $this->numberOfQueries > 0 ? " ({$this->numberOfQueries})" : '';
	}
}
