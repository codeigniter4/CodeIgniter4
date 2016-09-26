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
 * @package      CodeIgniter
 * @author       CodeIgniter Dev Team
 * @copyright    Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 * @license      http://opensource.org/licenses/MIT	MIT License
 * @link         http://codeigniter.com
 * @since        Version 4.0.0
 * @filesource
 */

use CodeIgniter\Services;

/**
 * Collector for the Database tab of the Debug Toolbar.
 */
class Database extends BaseCollector
{
	/**
	 * Whether this collector has timeline data.
	 *
	 * @var boolean
	 */
	protected $hasTimeline = true;

	/**
	 * Whether this collector should display its own tab.
	 *
	 * @var boolean
	 */
	protected $hasTabContent = true;

	/**
	 * Whether this collector has data for the Vars tab.
	 *
	 * @var boolean
	 */
	protected $hasVarData = false;

	/**
	 * The name used to reference this collector in the toolbar.
	 *
	 * @var string
	 */
	protected $title = 'Database';

	/**
	 * Array of database connections.
	 *
	 * @var array
	 */
	protected $connections;


	//--------------------------------------------------------------------

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->connections = \Config\Database::getConnections();
	}

	//--------------------------------------------------------------------

	/**
	 * Returns timeline data formatted for the toolbar.
	 *
	 * @return array The formatted data or an empty array.
	 */
	protected function formatTimelineData(): array
	{
		$data = [];

		foreach ($this->connections as $alias => $connection)
		{
			// Connection Time
			$data[] = [
				'name' => 'Connecting to Database: "'.$alias.'"',
				'component' => 'Database',
				'start' => $connection->getConnectStart(),
				'duration' => $connection->getConnectDuration()
			];

			$queries = $connection->getQueries();

			foreach ($queries as $query)
			{
				$data[] = [
					'name' => 'Query',
				    'component' => 'Database',
				    'start' => $query->getStartTime(true),
				    'duration' => $query->getDuration()
				];
			}
		}

		return $data;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the HTML to fill the Database tab in the toolbar.
	 *
	 * @return string The data formatted for the toolbar.
	 */
	public function display(): string
	{
		$output = '';

		// Key words we want bolded
		$highlight = ['SELECT', 'DISTINCT', 'FROM', 'WHERE', 'AND', 'LEFT&nbsp;JOIN', 'ORDER&nbsp;BY', 'GROUP&nbsp;BY',
		              'LIMIT', 'INSERT', 'INTO', 'VALUES', 'UPDATE', 'OR&nbsp;', 'HAVING', 'OFFSET', 'NOT&nbsp;IN',
		              'IN', 'LIKE', 'NOT&nbsp;LIKE', 'COUNT', 'MAX', 'MIN', 'ON', 'AS', 'AVG', 'SUM', '(', ')'
		];

		$connectionCount = count($this->connections);

		foreach ($this->connections as $alias => $connection)
		{
			if ($connectionCount > 1)
			{
				$output .= '<h3>'.$alias.': <span>'.$connection->getPlatform().' '.$connection->getVersion().
				           '</span></h3>';
			}

			$output .= '<table>';

			$output .= '<thead><tr>';
			$output .= '<th style="width: 6rem;">Time</th>';
			$output .= '<th>Query String</th>';
			$output .= '</tr></thead>';

			$output .= '<body>';

			$queries = $connection->getQueries();

			foreach ($queries as $query)
			{
				$output .= '<tr>';
				$output .='<td class="narrow">'.($query->getDuration(5) * 1000).' ms</td>';

				$sql = $query->getQuery();

				foreach ($highlight as $term)
				{
					$sql = str_replace($term, "<strong>{$term}</strong>", $sql);
				}

				$output .= '<td>'.$sql.'</td>';
				$output .= '</tr>';
			}

			$output .= '</body>';

			$output .= '</table>';
		}

		return $output;
	}

	//--------------------------------------------------------------------

	/**
	 * Information to be displayed next to the title.
	 *
	 * @return string The number of queries (in parentheses) or an empty string.
	 */
	public function getTitleDetails(): string
	{
		$queryCount = 0;

		foreach ($this->connections as $connection)
		{
			$queryCount += $connection->getQueryCount();
		}

		return '('.$queryCount.' Queries across '.count($this->connections).' Connection'.
		       (count($this->connections) > 1 ? 's' : '').')';
	}

	//--------------------------------------------------------------------

}
