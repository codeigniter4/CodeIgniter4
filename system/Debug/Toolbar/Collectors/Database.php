<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
 * Copyright (c) 2019-2020 CodeIgniter Foundation
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
 * @package    CodeIgniter
 * @author     CodeIgniter Dev Team
 * @copyright  2019-2020 CodeIgniter Foundation
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

namespace CodeIgniter\Debug\Toolbar\Collectors;

use CodeIgniter\Database\Query;

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

	/**
	 * The query instances that have been collected
	 * through the DBQuery Event.
	 *
	 * @var array
	 */
	protected static $queries = [];

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
	 * The static method used during Events to collect
	 * data.
	 *
	 * @param \CodeIgniter\Database\Query $query
	 *
	 * @internal param $ array \CodeIgniter\Database\Query
	 */
	public static function collect(Query $query)
	{
		$config = config('Toolbar');

		// Provide default in case it's not set
		$max = $config->maxQueries ?: 100;

		if (count(static::$queries) < $max)
		{
			static::$queries[] = $query;
		}
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
				'name'      => 'Connecting to Database: "' . $alias . '"',
				'component' => 'Database',
				'start'     => $connection->getConnectStart(),
				'duration'  => $connection->getConnectDuration(),
			];
		}

		foreach (static::$queries as $query)
		{
			$data[] = [
				'name'      => 'Query',
				'component' => 'Database',
				'start'     => $query->getStartTime(true),
				'duration'  => $query->getDuration(),
			];
		}

		return $data;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the data of this collector to be formatted in the toolbar
	 *
	 * @return array
	 */
	public function display(): array
	{
		// Key words we want bolded
		$highlight = [
			'SELECT',
			'DISTINCT',
			'FROM',
			'WHERE',
			'AND',
			'LEFT&nbsp;JOIN',
			'RIGHT&nbsp;JOIN',
			'JOIN',
			'ORDER&nbsp;BY',
			'GROUP&nbsp;BY',
			'LIMIT',
			'INSERT',
			'INTO',
			'VALUES',
			'UPDATE',
			'OR&nbsp;',
			'HAVING',
			'OFFSET',
			'NOT&nbsp;IN',
			'IN',
			'LIKE',
			'NOT&nbsp;LIKE',
			'COUNT',
			'MAX',
			'MIN',
			'ON',
			'AS',
			'AVG',
			'SUM',
			'(',
			')',
		];

		$data = [
			'queries' => [],
		];

		foreach (static::$queries as $query)
		{
			$sql = $query->getQuery();

			foreach ($highlight as $term)
			{
				$sql = str_replace($term, "<strong>{$term}</strong>", $sql);
			}

			$data['queries'][] = [
				'duration' => ($query->getDuration(5) * 1000) . ' ms',
				'sql'      => $sql,
			];
		}

		return $data;
	}

	//--------------------------------------------------------------------

	/**
	 * Gets the "badge" value for the button.
	 *
	 * @return integer
	 */
	public function getBadgeValue(): int
	{
		return count(static::$queries);
	}

	//--------------------------------------------------------------------

	/**
	 * Information to be displayed next to the title.
	 *
	 * @return string The number of queries (in parentheses) or an empty string.
	 */
	public function getTitleDetails(): string
	{
		return '(' . count(static::$queries) . ' Queries across ' . ($countConnection = count($this->connections)) . ' Connection' .
				($countConnection > 1 ? 's' : '') . ')';
	}

	//--------------------------------------------------------------------

	/**
	 * Does this collector have any data collected?
	 *
	 * @return boolean
	 */
	public function isEmpty(): bool
	{
		return empty(static::$queries);
	}

	//--------------------------------------------------------------------

	/**
	 * Display the icon.
	 *
	 * Icon from https://icons8.com - 1em package
	 *
	 * @return string
	 */
	public function icon(): string
	{
		return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAADMSURBVEhLY6A3YExLSwsA4nIycQDIDIhRWEBqamo/UNF/SjDQjF6ocZgAKPkRiFeEhoYyQ4WIBiA9QAuWAPEHqBAmgLqgHcolGQD1V4DMgHIxwbCxYD+QBqcKINseKo6eWrBioPrtQBq/BcgY5ht0cUIYbBg2AJKkRxCNWkDQgtFUNJwtABr+F6igE8olGQD114HMgHIxAVDyAhA/AlpSA8RYUwoeXAPVex5qHCbIyMgwBCkAuQJIY00huDBUz/mUlBQDqHGjgBjAwAAACexpph6oHSQAAAAASUVORK5CYII=';
	}

}
