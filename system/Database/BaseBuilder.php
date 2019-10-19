<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
 * Copyright (c) 2019 CodeIgniter Foundation
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
 * @copyright  2019 CodeIgniter Foundation
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

namespace CodeIgniter\Database;

use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Database\Exceptions\DataException;
use Closure;

/**
 * Class BaseBuilder
 *
 * Provides the core Query Builder methods.
 * Database-specific Builders might need to override
 * certain methods to make them work.
 *
 * @package CodeIgniter\Database
 * @mixin   \CodeIgniter\Model
 */
class BaseBuilder
{

	/**
	 * Reset DELETE data flag
	 *
	 * @var boolean
	 */
	protected $resetDeleteData = false;

	/**
	 * QB SELECT data
	 *
	 * @var array
	 */
	protected $QBSelect = [];

	/**
	 * QB DISTINCT flag
	 *
	 * @var boolean
	 */
	protected $QBDistinct = false;

	/**
	 * QB FROM data
	 *
	 * @var array
	 */
	protected $QBFrom = [];

	/**
	 * QB JOIN data
	 *
	 * @var array
	 */
	protected $QBJoin = [];

	/**
	 * QB WHERE data
	 *
	 * @var array
	 */
	protected $QBWhere = [];

	/**
	 * QB GROUP BY data
	 *
	 * @var array
	 */
	protected $QBGroupBy = [];

	/**
	 * QB HAVING data
	 *
	 * @var array
	 */
	protected $QBHaving = [];

	/**
	 * QB keys
	 *
	 * @var array
	 */
	protected $QBKeys = [];

	/**
	 * QB LIMIT data
	 *
	 * @var integer
	 */
	protected $QBLimit = false;

	/**
	 * QB OFFSET data
	 *
	 * @var integer
	 */
	protected $QBOffset = false;

	/**
	 * QB ORDER BY data
	 *
	 * @var array
	 */
	public $QBOrderBy = [];

	/**
	 * QB NO ESCAPE data
	 *
	 * @var array
	 */
	public $QBNoEscape = [];

	/**
	 * QB data sets
	 *
	 * @var array
	 */
	protected $QBSet = [];

	/**
	 * QB WHERE group started flag
	 *
	 * @var boolean
	 */
	protected $QBWhereGroupStarted = false;

	/**
	 * QB WHERE group count
	 *
	 * @var integer
	 */
	protected $QBWhereGroupCount = 0;

	/**
	 * A reference to the database connection.
	 *
	 * @var BaseConnection
	 */
	protected $db;

	/**
	 * ORDER BY random keyword
	 *
	 * @var array
	 */
	protected $randomKeyword = [
		'RAND()',
		'RAND(%d)',
	];

	/**
	 * COUNT string
	 *
	 * @used-by CI_DB_driver::count_all()
	 * @used-by BaseBuilder::count_all_results()
	 *
	 * @var string
	 */
	protected $countString = 'SELECT COUNT(*) AS ';

	/**
	 * Collects the named parameters and
	 * their values for later binding
	 * in the Query object.
	 *
	 * @var array
	 */
	protected $binds = [];

	/**
	 * Some databases, like SQLite, do not by default
	 * allow limiting of delete clauses.
	 *
	 * @var boolean
	 */
	protected $canLimitDeletes = true;

	/**
	 * Some databases do not by default
	 * allow limit update queries with WHERE.
	 *
	 * @var boolean
	 */
	protected $canLimitWhereUpdates = true;

	/**
	 * Builder testing mode status.
	 *
	 * @var boolean
	 */
	protected $testMode = false;

	//--------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @param  string|array                              $tableName
	 * @param  \CodeIgniter\Database\ConnectionInterface $db
	 * @param  array                                     $options
	 * @throws DatabaseException
	 */
	public function __construct($tableName, ConnectionInterface &$db, array $options = null)
	{
		if (empty($tableName))
		{
			throw new DatabaseException('A table must be specified when creating a new Query Builder.');
		}

		$this->db = $db;

		$this->from($tableName);

		if (! empty($options))
		{
			foreach ($options as $key => $value)
			{
				if (property_exists($this, $key))
				{
					$this->$key = $value;
				}
			}
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Sets a test mode status.
	 *
	 * @param boolean $mode Mode to set
	 *
	 * @return BaseBuilder
	 */
	public function testMode(bool $mode = true)
	{
		$this->testMode = $mode;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns an array of bind values and their
	 * named parameters for binding in the Query object later.
	 *
	 * @return array
	 */
	public function getBinds(): array
	{
		return $this->binds;
	}

	//--------------------------------------------------------------------

	/**
	 * Select
	 *
	 * Generates the SELECT portion of the query
	 *
	 * @param string|array $select
	 * @param boolean      $escape
	 *
	 * @return BaseBuilder
	 */
	public function select($select = '*', bool $escape = null)
	{
		if (is_string($select))
		{
			$select = explode(',', $select);
		}

		// If the escape value was not set, we will base it on the global setting
		is_bool($escape) || $escape = $this->db->protectIdentifiers;

		foreach ($select as $val)
		{
			$val = trim($val);

			if ($val !== '')
			{
				$this->QBSelect[] = $val;

				/*
				 * When doing 'SELECT NULL as field_alias FROM table'
				 * null gets taken as a field, and therefore escaped
				 * with backticks.
				 * This prevents NULL being escaped
				 * @see https://github.com/codeigniter4/CodeIgniter4/issues/1169
				 */
				if (strtoupper(mb_substr(trim($val), 0, 4)) === 'NULL')
				{
					$escape = false;
				}

				$this->QBNoEscape[] = $escape;
			}
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Select Max
	 *
	 * Generates a SELECT MAX(field) portion of a query
	 *
	 * @param string $select The field
	 * @param string $alias  An alias
	 *
	 * @return BaseBuilder
	 */
	public function selectMax(string $select = '', string $alias = '')
	{
		return $this->maxMinAvgSum($select, $alias, 'MAX');
	}

	//--------------------------------------------------------------------

	/**
	 * Select Min
	 *
	 * Generates a SELECT MIN(field) portion of a query
	 *
	 * @param string $select The field
	 * @param string $alias  An alias
	 *
	 * @return BaseBuilder
	 */
	public function selectMin(string $select = '', string $alias = '')
	{
		return $this->maxMinAvgSum($select, $alias, 'MIN');
	}

	//--------------------------------------------------------------------

	/**
	 * Select Average
	 *
	 * Generates a SELECT AVG(field) portion of a query
	 *
	 * @param string $select The field
	 * @param string $alias  An alias
	 *
	 * @return BaseBuilder
	 */
	public function selectAvg(string $select = '', string $alias = '')
	{
		return $this->maxMinAvgSum($select, $alias, 'AVG');
	}

	//--------------------------------------------------------------------

	/**
	 * Select Sum
	 *
	 * Generates a SELECT SUM(field) portion of a query
	 *
	 * @param string $select The field
	 * @param string $alias  An alias
	 *
	 * @return BaseBuilder
	 */
	public function selectSum(string $select = '', string $alias = '')
	{
		return $this->maxMinAvgSum($select, $alias, 'SUM');
	}

	//--------------------------------------------------------------------

	/**
	 * Select Count
	 *
	 * Generates a SELECT COUNT(field) portion of a query
	 *
	 * @param string $select The field
	 * @param string $alias  An alias
	 *
	 * @return BaseBuilder
	 */
	public function selectCount(string $select = '', string $alias = '')
	{
		return $this->maxMinAvgSum($select, $alias, 'COUNT');
	}

	//--------------------------------------------------------------------

	/**
	 * SELECT [MAX|MIN|AVG|SUM|COUNT]()
	 *
	 * @used-by selectMax()
	 * @used-by selectMin()
	 * @used-by selectAvg()
	 * @used-by selectSum()
	 *
	 * @param string $select Field name
	 * @param string $alias
	 * @param string $type
	 *
	 * @return BaseBuilder
	 * @throws \CodeIgniter\Database\Exceptions\DataException
	 * @throws \CodeIgniter\Database\Exceptions\DatabaseException
	 */
	protected function maxMinAvgSum(string $select = '', string $alias = '', string $type = 'MAX')
	{
		if ($select === '')
		{
			throw DataException::forEmptyInputGiven('Select');
		}

		if (strpos($select, ',') !== false)
		{
			throw DataException::forInvalidArgument('column name not separated by comma');
		}

		$type = strtoupper($type);

		if (! in_array($type, ['MAX', 'MIN', 'AVG', 'SUM', 'COUNT']))
		{
			throw new DatabaseException('Invalid function type: ' . $type);
		}

		if ($alias === '')
		{
			$alias = $this->createAliasFromTable(trim($select));
		}

		$sql = $type . '(' . $this->db->protectIdentifiers(trim($select)) . ') AS ' . $this->db->escapeIdentifiers(trim($alias));

		$this->QBSelect[]   = $sql;
		$this->QBNoEscape[] = null;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Determines the alias name based on the table
	 *
	 * @param string $item
	 *
	 * @return string
	 */
	protected function createAliasFromTable(string $item): string
	{
		if (strpos($item, '.') !== false)
		{
			$item = explode('.', $item);

			return end($item);
		}

		return $item;
	}

	//--------------------------------------------------------------------

	/**
	 * DISTINCT
	 *
	 * Sets a flag which tells the query string compiler to add DISTINCT
	 *
	 * @param boolean $val
	 *
	 * @return BaseBuilder
	 */
	public function distinct(bool $val = true)
	{
		$this->QBDistinct = $val;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * From
	 *
	 * Generates the FROM portion of the query
	 *
	 * @param mixed   $from      can be a string or array
	 * @param boolean $overwrite Should we remove the first table existing?
	 *
	 * @return BaseBuilder
	 */
	public function from($from, bool $overwrite = false)
	{
		if ($overwrite === true)
		{
			$this->QBFrom = [];
			$this->db->setAliasedTables([]);
		}

		foreach ((array) $from as $val)
		{
			if (strpos($val, ',') !== false)
			{
				foreach (explode(',', $val) as $v)
				{
					$v = trim($v);
					$this->trackAliases($v);

					$this->QBFrom[] = $v = $this->db->protectIdentifiers($v, true, null, false);
				}
			}
			else
			{
				$val = trim($val);

				// Extract any aliases that might exist. We use this information
				// in the protectIdentifiers to know whether to add a table prefix
				$this->trackAliases($val);

				$this->QBFrom[] = $this->db->protectIdentifiers($val, true, null, false);
			}
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * JOIN
	 *
	 * Generates the JOIN portion of the query
	 *
	 * @param string  $table
	 * @param string  $cond   The join condition
	 * @param string  $type   The type of join
	 * @param boolean $escape Whether not to try to escape identifiers
	 *
	 * @return BaseBuilder
	 */
	public function join(string $table, string $cond, string $type = '', bool $escape = null)
	{
		if ($type !== '')
		{
			$type = strtoupper(trim($type));

			if (! in_array($type, ['LEFT', 'RIGHT', 'OUTER', 'INNER', 'LEFT OUTER', 'RIGHT OUTER'], true))
			{
				$type = '';
			}
			else
			{
				$type .= ' ';
			}
		}

		// Extract any aliases that might exist. We use this information
		// in the protectIdentifiers to know whether to add a table prefix
		$this->trackAliases($table);

		is_bool($escape) || $escape = $this->db->protectIdentifiers;

		if (! $this->hasOperator($cond))
		{
			$cond = ' USING (' . ($escape ? $this->db->escapeIdentifiers($cond) : $cond) . ')';
		}
		elseif ($escape === false)
		{
			$cond = ' ON ' . $cond;
		}
		else
		{
			// Split multiple conditions
			if (preg_match_all('/\sAND\s|\sOR\s/i', $cond, $joints, PREG_OFFSET_CAPTURE))
			{
				$conditions = [];
				$joints     = $joints[0];
				array_unshift($joints, ['', 0]);

				for ($i = count($joints) - 1, $pos = strlen($cond); $i >= 0; $i --)
				{
					$joints[$i][1] += strlen($joints[$i][0]); // offset
					$conditions[$i] = substr($cond, $joints[$i][1], $pos - $joints[$i][1]);
					$pos            = $joints[$i][1] - strlen($joints[$i][0]);
					$joints[$i]     = $joints[$i][0];
				}
			}
			else
			{
				$conditions = [$cond];
				$joints     = [''];
			}

			$cond = ' ON ';
			for ($i = 0, $c = count($conditions); $i < $c; $i ++)
			{
				$operator = $this->getOperator($conditions[$i]);
				$cond    .= $joints[$i];
				$cond    .= preg_match("/(\(*)?([\[\]\w\.'-]+)" . preg_quote($operator) . '(.*)/i', $conditions[$i], $match) ? $match[1] . $this->db->protectIdentifiers($match[2]) . $operator . $this->db->protectIdentifiers($match[3]) : $conditions[$i];
			}
		}

		// Do we want to escape the table name?
		if ($escape === true)
		{
			$table = $this->db->protectIdentifiers($table, true, null, false);
		}

		// Assemble the JOIN statement
		$this->QBJoin[] = $join = $type . 'JOIN ' . $table . $cond;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * WHERE
	 *
	 * Generates the WHERE portion of the query.
	 * Separates multiple calls with 'AND'.
	 *
	 * @param mixed   $key
	 * @param mixed   $value
	 * @param boolean $escape
	 *
	 * @return BaseBuilder
	 */
	public function where($key, $value = null, bool $escape = null)
	{
		return $this->whereHaving('QBWhere', $key, $value, 'AND ', $escape);
	}

	//--------------------------------------------------------------------

	/**
	 * OR WHERE
	 *
	 * Generates the WHERE portion of the query.
	 * Separates multiple calls with 'OR'.
	 *
	 * @param mixed   $key
	 * @param mixed   $value
	 * @param boolean $escape
	 *
	 * @return BaseBuilder
	 */
	public function orWhere($key, $value = null, bool $escape = null)
	{
		return $this->whereHaving('QBWhere', $key, $value, 'OR ', $escape);
	}

	//--------------------------------------------------------------------

	/**
	 * WHERE, HAVING
	 *
	 * @used-by where()
	 * @used-by orWhere()
	 * @used-by having()
	 * @used-by orHaving()
	 *
	 * @param string  $qb_key 'QBWhere' or 'QBHaving'
	 * @param mixed   $key
	 * @param mixed   $value
	 * @param string  $type
	 * @param boolean $escape
	 *
	 * @return BaseBuilder
	 */
	protected function whereHaving(string $qb_key, $key, $value = null, string $type = 'AND ', bool $escape = null)
	{
		if (! is_array($key))
		{
			$key = [$key => $value];
		}

		// If the escape value was not set will base it on the global setting
		is_bool($escape) || $escape = $this->db->protectIdentifiers;

		foreach ($key as $k => $v)
		{
			$prefix = empty($this->$qb_key) ? $this->groupGetType('') : $this->groupGetType($type);

			if ($v !== null)
			{
				$op = $this->getOperator($k, true);

				if (! empty($op))
				{
					$k = trim($k);

					end($op);

					$op = trim(current($op));

					if (substr($k, -1 * strlen($op)) === $op)
					{
						$k = rtrim(strrev(preg_replace(strrev('/' . $op . '/'), strrev(''), strrev($k), 1)));
					}
				}

				$bind = $this->setBind($k, $v, $escape);

				if (empty($op))
				{
					$k .= ' =';
				}
				else
				{
					$k .= " $op";
				}

				if ($v instanceof Closure)
				{
					$builder = $this->cleanClone();
					$v       = '(' . str_replace("\n", ' ', $v($builder)->getCompiledSelect()) . ')';
				}
				else
				{
					$v = " :$bind:";
				}
			}
			elseif (! $this->hasOperator($k) && $qb_key !== 'QBHaving')
			{
				// value appears not to have been set, assign the test to IS NULL
				$k .= ' IS NULL';
			}
			elseif (preg_match('/\s*(!?=|<>|IS(?:\s+NOT)?)\s*$/i', $k, $match, PREG_OFFSET_CAPTURE))
			{
				$k = substr($k, 0, $match[0][1]) . ($match[1][0] === '=' ? ' IS NULL' : ' IS NOT NULL');
			}

			$this->{$qb_key}[] = [
				'condition' => $prefix . $k . $v,
				'escape'    => $escape,
			];
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * WHERE IN
	 *
	 * Generates a WHERE field IN('item', 'item') SQL query,
	 * joined with 'AND' if appropriate.
	 *
	 * @param string               $key    The field to search
	 * @param array|string|Closure $values The values searched on, or anonymous function with subquery
	 * @param boolean              $escape
	 *
	 * @return BaseBuilder
	 */
	public function whereIn(string $key = null, $values = null, bool $escape = null)
	{
		return $this->_whereIn($key, $values, false, 'AND ', $escape);
	}

	//--------------------------------------------------------------------

	/**
	 * OR WHERE IN
	 *
	 * Generates a WHERE field IN('item', 'item') SQL query,
	 * joined with 'OR' if appropriate.
	 *
	 * @param string               $key    The field to search
	 * @param array|string|Closure $values The values searched on, or anonymous function with subquery
	 * @param boolean              $escape
	 *
	 * @return BaseBuilder
	 */
	public function orWhereIn(string $key = null, $values = null, bool $escape = null)
	{
		return $this->_whereIn($key, $values, false, 'OR ', $escape);
	}

	//--------------------------------------------------------------------

	/**
	 * WHERE NOT IN
	 *
	 * Generates a WHERE field NOT IN('item', 'item') SQL query,
	 * joined with 'AND' if appropriate.
	 *
	 * @param string               $key    The field to search
	 * @param array|string|Closure $values The values searched on, or anonymous function with subquery
	 * @param boolean              $escape
	 *
	 * @return BaseBuilder
	 */
	public function whereNotIn(string $key = null, $values = null, bool $escape = null)
	{
		return $this->_whereIn($key, $values, true, 'AND ', $escape);
	}

	//--------------------------------------------------------------------

	/**
	 * OR WHERE NOT IN
	 *
	 * Generates a WHERE field NOT IN('item', 'item') SQL query,
	 * joined with 'OR' if appropriate.
	 *
	 * @param string               $key    The field to search
	 * @param array|string|Closure $values The values searched on, or anonymous function with subquery
	 * @param boolean              $escape
	 *
	 * @return BaseBuilder
	 */
	public function orWhereNotIn(string $key = null, $values = null, bool $escape = null)
	{
		return $this->_whereIn($key, $values, true, 'OR ', $escape);
	}

	//--------------------------------------------------------------------

	/**
	 * HAVING IN
	 *
	 * Generates a HAVING field IN('item', 'item') SQL query,
	 * joined with 'AND' if appropriate.
	 *
	 * @param string               $key    The field to search
	 * @param array|string|Closure $values The values searched on, or anonymous function with subquery
	 * @param boolean              $escape
	 *
	 * @return BaseBuilder
	 */
	public function havingIn(string $key = null, $values = null, bool $escape = null)
	{
		return $this->_whereIn($key, $values, false, 'AND ', $escape, 'QBHaving');
	}

	//--------------------------------------------------------------------

	/**
	 * OR HAVING IN
	 *
	 * Generates a HAVING field IN('item', 'item') SQL query,
	 * joined with 'OR' if appropriate.
	 *
	 * @param string               $key    The field to search
	 * @param array|string|Closure $values The values searched on, or anonymous function with subquery
	 * @param boolean              $escape
	 *
	 * @return BaseBuilder
	 */
	public function orHavingIn(string $key = null, $values = null, bool $escape = null)
	{
		return $this->_whereIn($key, $values, false, 'OR ', $escape, 'QBHaving');
	}

	//--------------------------------------------------------------------

	/**
	 * HAVING NOT IN
	 *
	 * Generates a HAVING field NOT IN('item', 'item') SQL query,
	 * joined with 'AND' if appropriate.
	 *
	 * @param string               $key    The field to search
	 * @param array|string|Closure $values The values searched on, or anonymous function with subquery
	 * @param boolean              $escape
	 *
	 * @return BaseBuilder
	 */
	public function havingNotIn(string $key = null, $values = null, bool $escape = null)
	{
		return $this->_whereIn($key, $values, true, 'AND ', $escape, 'QBHaving');
	}

	//--------------------------------------------------------------------

	/**
	 * OR HAVING NOT IN
	 *
	 * Generates a HAVING field NOT IN('item', 'item') SQL query,
	 * joined with 'OR' if appropriate.
	 *
	 * @param string               $key    The field to search
	 * @param array|string|Closure $values The values searched on, or anonymous function with subquery
	 * @param boolean              $escape
	 *
	 * @return BaseBuilder
	 */
	public function orHavingNotIn(string $key = null, $values = null, bool $escape = null)
	{
		return $this->_whereIn($key, $values, true, 'OR ', $escape, 'QBHaving');
	}

	//--------------------------------------------------------------------

	/**
	 * Internal WHERE IN
	 *
	 * @used-by WhereIn()
	 * @used-by orWhereIn()
	 * @used-by whereNotIn()
	 * @used-by orWhereNotIn()
	 *
	 * @param string        $key    The field to search
	 * @param array|Closure $values The values searched on, or anonymous function with subquery
	 * @param boolean       $not    If the statement would be IN or NOT IN
	 * @param string        $type
	 * @param boolean       $escape
	 * @param string        $clause (Internal use only)
	 *
	 * @return BaseBuilder
	 */
	protected function _whereIn(string $key = null, $values = null, bool $not = false, string $type = 'AND ', bool $escape = null, string $clause = 'QBWhere')
	{
		if ($key === null || $values === null || (! is_array($values) && ! ($values instanceof Closure)))
		{
			return $this;
		}

		is_bool($escape) || $escape = $this->db->protectIdentifiers;

		$ok = $key;

		if ($escape === true)
		{
			$key = $this->db->protectIdentifiers($key);
		}

		$not = ($not) ? ' NOT' : '';

		if ($values instanceof Closure)
		{
			$builder = $this->cleanClone();
			$ok      = str_replace("\n", ' ', $values($builder)->getCompiledSelect());
		}
		else
		{
			$whereIn = is_array($values) ? array_values($values) : $values;
			$ok      = $this->setBind($ok, $whereIn, $escape);
		}

		$prefix = empty($this->$clause) ? $this->groupGetType('') : $this->groupGetType($type);

		$whereIn = [
			'condition' => $prefix . $key . $not . ($values instanceof Closure ? " IN ($ok)" : " IN :{$ok}:"),
			'escape'    => false,
		];

		$this->{$clause}[] = $whereIn;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * LIKE
	 *
	 * Generates a %LIKE% portion of the query.
	 * Separates multiple calls with 'AND'.
	 *
	 * @param mixed   $field
	 * @param string  $match
	 * @param string  $side
	 * @param boolean $escape
	 * @param boolean $insensitiveSearch IF true, will force a case-insensitive search
	 *
	 * @return BaseBuilder
	 */
	public function like($field, string $match = '', string $side = 'both', bool $escape = null, bool $insensitiveSearch = false)
	{
		return $this->_like($field, $match, 'AND ', $side, '', $escape, $insensitiveSearch);
	}

	//--------------------------------------------------------------------

	/**
	 * NOT LIKE
	 *
	 * Generates a NOT LIKE portion of the query.
	 * Separates multiple calls with 'AND'.
	 *
	 * @param mixed   $field
	 * @param string  $match
	 * @param string  $side
	 * @param boolean $escape
	 * @param boolean $insensitiveSearch IF true, will force a case-insensitive search
	 *
	 * @return BaseBuilder
	 */
	public function notLike($field, string $match = '', string $side = 'both', bool $escape = null, bool $insensitiveSearch = false)
	{
		return $this->_like($field, $match, 'AND ', $side, 'NOT', $escape, $insensitiveSearch);
	}

	//--------------------------------------------------------------------

	/**
	 * OR LIKE
	 *
	 * Generates a %LIKE% portion of the query.
	 * Separates multiple calls with 'OR'.
	 *
	 * @param mixed   $field
	 * @param string  $match
	 * @param string  $side
	 * @param boolean $escape
	 * @param boolean $insensitiveSearch IF true, will force a case-insensitive search
	 *
	 * @return BaseBuilder
	 */
	public function orLike($field, string $match = '', string $side = 'both', bool $escape = null, bool $insensitiveSearch = false)
	{
		return $this->_like($field, $match, 'OR ', $side, '', $escape, $insensitiveSearch);
	}

	//--------------------------------------------------------------------

	/**
	 * OR NOT LIKE
	 *
	 * Generates a NOT LIKE portion of the query.
	 * Separates multiple calls with 'OR'.
	 *
	 * @param mixed   $field
	 * @param string  $match
	 * @param string  $side
	 * @param boolean $escape
	 * @param boolean $insensitiveSearch IF true, will force a case-insensitive search
	 *
	 * @return BaseBuilder
	 */
	public function orNotLike($field, string $match = '', string $side = 'both', bool $escape = null, bool $insensitiveSearch = false)
	{
		return $this->_like($field, $match, 'OR ', $side, 'NOT', $escape, $insensitiveSearch);
	}

	// --------------------------------------------------------------------

	/**
	 * LIKE with HAVING clause
	 *
	 * Generates a %LIKE% portion of the query.
	 * Separates multiple calls with 'AND'.
	 *
	 * @param mixed   $field
	 * @param string  $match
	 * @param string  $side
	 * @param boolean $escape
	 *
	 * @return BaseBuilder
	 */
	public function havingLike($field, string $match = '', string $side = 'both', bool $escape = null, bool $insensitiveSearch = false)
	{
		return $this->_like($field, $match, 'AND ', $side, '', $escape, $insensitiveSearch, 'QBHaving');
	}

	// --------------------------------------------------------------------

	/**
	 * NOT LIKE with HAVING clause
	 *
	 * Generates a NOT LIKE portion of the query.
	 * Separates multiple calls with 'AND'.
	 *
	 * @param mixed   $field
	 * @param string  $match
	 * @param string  $side
	 * @param boolean $escape
	 *
	 * @return BaseBuilder
	 */
	public function notHavingLike($field, string $match = '', string $side = 'both', bool $escape = null, bool $insensitiveSearch = false)
	{
		return $this->_like($field, $match, 'AND ', $side, 'NOT', $escape, $insensitiveSearch, 'QBHaving');
	}

	// --------------------------------------------------------------------

	/**
	 * OR LIKE with HAVING clause
	 *
	 * Generates a %LIKE% portion of the query.
	 * Separates multiple calls with 'OR'.
	 *
	 * @param mixed   $field
	 * @param string  $match
	 * @param string  $side
	 * @param boolean $escape
	 *
	 * @return BaseBuilder
	 */
	public function orHavingLike($field, string $match = '', string $side = 'both', bool $escape = null, bool $insensitiveSearch = false)
	{
		return $this->_like($field, $match, 'OR ', $side, '', $escape, $insensitiveSearch, 'QBHaving');
	}

	// --------------------------------------------------------------------

	/**
	 * OR NOT LIKE with HAVING clause
	 *
	 * Generates a NOT LIKE portion of the query.
	 * Separates multiple calls with 'OR'.
	 *
	 * @param mixed   $field
	 * @param string  $match
	 * @param string  $side
	 * @param boolean $escape
	 *
	 * @return BaseBuilder
	 */
	public function orNotHavingLike($field, string $match = '', string $side = 'both', bool $escape = null, bool $insensitiveSearch = false)
	{
		return $this->_like($field, $match, 'OR ', $side, 'NOT', $escape, $insensitiveSearch, 'QBHaving');
	}

	//--------------------------------------------------------------------

	/**
	 * Internal LIKE
	 *
	 * @used-by like()
	 * @used-by orLike()
	 * @used-by notLike()
	 * @used-by orNotLike()
	 * @used-by havingLike()
	 * @used-by orHavingLike()
	 * @used-by notHavingLike()
	 * @used-by orNotHavingLike()
	 *
	 * @param mixed   $field
	 * @param string  $match
	 * @param string  $type
	 * @param string  $side
	 * @param string  $not
	 * @param boolean $escape
	 * @param boolean $insensitiveSearch IF true, will force a case-insensitive search
	 * @param string  $clause            (Internal use only)
	 *
	 * @return BaseBuilder
	 */
	protected function _like($field, string $match = '', string $type = 'AND ', string $side = 'both', string $not = '', bool $escape = null, bool $insensitiveSearch = false, string $clause = 'QBWhere')
	{
		if (! is_array($field))
		{
			$field = [$field => $match];
		}

		$escape = is_bool($escape) ? $escape : $this->db->protectIdentifiers;

		// lowercase $side in case somebody writes e.g. 'BEFORE' instead of 'before' (doh)
		$side = strtolower($side);

		foreach ($field as $k => $v)
		{
			if ($insensitiveSearch === true)
			{
				$v = strtolower($v);
			}

			$prefix = empty($this->$clause) ? $this->groupGetType('') : $this->groupGetType($type);

			if ($side === 'none')
			{
				$bind = $this->setBind($k, $v, $escape);
			}
			elseif ($side === 'before')
			{
				$bind = $this->setBind($k, "%$v", $escape);
			}
			elseif ($side === 'after')
			{
				$bind = $this->setBind($k, "$v%", $escape);
			}
			else
			{
				$bind = $this->setBind($k, "%$v%", $escape);
			}

			$like_statement = $this->_like_statement($prefix, $k, $not, $bind, $insensitiveSearch);

			// some platforms require an escape sequence definition for LIKE wildcards
			if ($escape === true && $this->db->likeEscapeStr !== '')
			{
				$like_statement .= sprintf($this->db->likeEscapeStr, $this->db->likeEscapeChar);
			}

			$this->{$clause}[] = [
				'condition' => $like_statement,
				'escape'    => $escape,
			];
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Platform independent LIKE statement builder.
	 *
	 * @param string  $prefix
	 * @param string  $column
	 * @param string  $not
	 * @param string  $bind
	 * @param boolean $insensitiveSearch
	 *
	 * @return string     $like_statement
	 */
	protected function _like_statement(string $prefix = null, string $column, string $not = null, string $bind, bool $insensitiveSearch = false): string
	{
		$like_statement = "{$prefix} {$column} {$not} LIKE :{$bind}:";

		if ($insensitiveSearch === true)
		{
			$like_statement = "{$prefix} LOWER({$column}) {$not} LIKE :{$bind}:";
		}

		return $like_statement;
	}

	//--------------------------------------------------------------------

	/**
	 * Starts a query group.
	 *
	 * @return BaseBuilder
	 */
	public function groupStart()
	{
		return $this->groupStartPrepare('', 'AND ', 'QBWhere');
	}

	//--------------------------------------------------------------------

	/**
	 * Starts a query group, but ORs the group
	 *
	 * @return BaseBuilder
	 */
	public function orGroupStart()
	{
		return $this->groupStartPrepare('', 'OR ', 'QBWhere');
	}

	//--------------------------------------------------------------------

	/**
	 * Starts a query group, but NOTs the group
	 *
	 * @return BaseBuilder
	 */
	public function notGroupStart()
	{
		return $this->groupStartPrepare('NOT ', 'AND ', 'QBWhere');
	}

	//--------------------------------------------------------------------

	/**
	 * Starts a query group, but OR NOTs the group
	 *
	 * @return BaseBuilder
	 */
	public function orNotGroupStart()
	{
		return $this->groupStartPrepare('NOT ', 'OR ', 'QBWhere');
	}

	//--------------------------------------------------------------------

	/**
	 * Ends a query group
	 *
	 * @return BaseBuilder
	 */
	public function groupEnd()
	{
		return $this->groupEndPrepare('QBWhere');
	}

	// --------------------------------------------------------------------

	/**
	 * Starts a query group for HAVING clause.
	 *
	 * @return BaseBuilder
	 */
	public function havingGroupStart()
	{
		return $this->groupStartPrepare('', 'AND ', 'QBHaving');
	}

	// --------------------------------------------------------------------

	/**
	 * Starts a query group for HAVING clause, but ORs the group.
	 *
	 * @return BaseBuilder
	 */
	public function orHavingGroupStart()
	{
		return $this->groupStartPrepare('', 'OR ', 'QBHaving');
	}

	// --------------------------------------------------------------------

	/**
	 * Starts a query group for HAVING clause, but NOTs the group.
	 *
	 * @return BaseBuilder
	 */
	public function notHavingGroupStart()
	{
		return $this->groupStartPrepare('NOT ', 'AND ', 'QBHaving');
	}

	// --------------------------------------------------------------------

	/**
	 * Starts a query group for HAVING clause, but OR NOTs the group.
	 *
	 * @return BaseBuilder
	 */
	public function orNotHavingGroupStart()
	{
		return $this->groupStartPrepare('NOT ', 'OR ', 'QBHaving');
	}

	// --------------------------------------------------------------------

	/**
	 * Ends a query group for HAVING clause.
	 *
	 * @return BaseBuilder
	 */
	public function havingGroupEnd()
	{
		return $this->groupEndPrepare('QBHaving');
	}

	//--------------------------------------------------------------------

	/**
	 * Prepate a query group start.
	 *
	 * @param string $not
	 * @param string $type
	 * @param string $clause
	 *
	 * @return BaseBuilder
	 */
	protected function groupStartPrepare(string $not = '', string $type = 'AND ', string $clause = 'QBWhere')
	{
		$type = $this->groupGetType($type);

		$this->QBWhereGroupStarted = true;
		$prefix                    = empty($this->$clause) ? '' : $type;
		$where                     = [
			'condition' => $prefix . $not . str_repeat(' ', ++ $this->QBWhereGroupCount) . ' (',
			'escape'    => false,
		];

		$this->{$clause}[] = $where;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Prepate a query group end.
	 *
	 * @param string $clause
	 *
	 * @return BaseBuilder
	 */
	protected function groupEndPrepare(string $clause = 'QBWhere')
	{
		$this->QBWhereGroupStarted = false;
		$where                     = [
			'condition' => str_repeat(' ', $this->QBWhereGroupCount -- ) . ')',
			'escape'    => false,
		];

		$this->{$clause}[] = $where;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Group_get_type
	 *
	 * @used-by groupStart()
	 * @used-by _like()
	 * @used-by whereHaving()
	 * @used-by _whereIn()
	 * @used-by havingGroupStart()
	 *
	 * @param string $type
	 *
	 * @return string
	 */
	protected function groupGetType(string $type): string
	{
		if ($this->QBWhereGroupStarted)
		{
			$type                      = '';
			$this->QBWhereGroupStarted = false;
		}

		return $type;
	}

	//--------------------------------------------------------------------

	/**
	 * GROUP BY
	 *
	 * @param string|array $by
	 * @param boolean      $escape
	 *
	 * @return BaseBuilder
	 */
	public function groupBy($by, bool $escape = null)
	{
		is_bool($escape) || $escape = $this->db->protectIdentifiers;

		if (is_string($by))
		{
			$by = ($escape === true) ? explode(',', $by) : [$by];
		}

		foreach ($by as $val)
		{
			$val = trim($val);

			if ($val !== '')
			{
				$val = [
					'field'  => $val,
					'escape' => $escape,
				];

				$this->QBGroupBy[] = $val;
			}
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * HAVING
	 *
	 * Separates multiple calls with 'AND'.
	 *
	 * @param string|array $key
	 * @param mixed        $value
	 * @param boolean      $escape
	 *
	 * @return BaseBuilder
	 */
	public function having($key, $value = null, bool $escape = null)
	{
		return $this->whereHaving('QBHaving', $key, $value, 'AND ', $escape);
	}

	//--------------------------------------------------------------------

	/**
	 * OR HAVING
	 *
	 * Separates multiple calls with 'OR'.
	 *
	 * @param string|array $key
	 * @param mixed        $value
	 * @param boolean      $escape
	 *
	 * @return BaseBuilder
	 */
	public function orHaving($key, $value = null, bool $escape = null)
	{
		return $this->whereHaving('QBHaving', $key, $value, 'OR ', $escape);
	}

	//--------------------------------------------------------------------

	/**
	 * ORDER BY
	 *
	 * @param string  $orderBy
	 * @param string  $direction ASC, DESC or RANDOM
	 * @param boolean $escape
	 *
	 * @return BaseBuilder
	 */
	public function orderBy(string $orderBy, string $direction = '', bool $escape = null)
	{
		$direction = strtoupper(trim($direction));

		if ($direction === 'RANDOM')
		{
			$direction = '';

			// Do we have a seed value?
			$orderBy = ctype_digit((string) $orderBy) ? sprintf($this->randomKeyword[1], $orderBy) : $this->randomKeyword[0];
		}
		elseif (empty($orderBy))
		{
			return $this;
		}
		elseif ($direction !== '')
		{
			$direction = in_array($direction, ['ASC', 'DESC'], true) ? ' ' . $direction : '';
		}

		is_bool($escape) || $escape = $this->db->protectIdentifiers;

		if ($escape === false)
		{
			$qb_orderBy[] = [
				'field'     => $orderBy,
				'direction' => $direction,
				'escape'    => false,
			];
		}
		else
		{
			$qb_orderBy = [];
			foreach (explode(',', $orderBy) as $field)
			{
				$qb_orderBy[] = ($direction === '' && preg_match('/\s+(ASC|DESC)$/i', rtrim($field), $match, PREG_OFFSET_CAPTURE))
					?
					[
						'field'     => ltrim(substr($field, 0, $match[0][1])),
						'direction' => ' ' . $match[1][0],
						'escape'    => true,
					]
					:
					[
						'field'     => trim($field),
						'direction' => $direction,
						'escape'    => true,
					];
			}
		}

		$this->QBOrderBy = array_merge($this->QBOrderBy, $qb_orderBy);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * LIMIT
	 *
	 * @param integer $value  LIMIT value
	 * @param integer $offset OFFSET value
	 *
	 * @return BaseBuilder
	 */
	public function limit(int $value = null, ?int $offset = 0)
	{
		if (! is_null($value))
		{
			$this->QBLimit = $value;
		}

		if (! empty($offset))
		{
			$this->QBOffset = $offset;
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the OFFSET value
	 *
	 * @param integer $offset OFFSET value
	 *
	 * @return BaseBuilder
	 */
	public function offset(int $offset)
	{
		if (! empty($offset))
		{
			$this->QBOffset = (int) $offset;
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * LIMIT string
	 *
	 * Generates a platform-specific LIMIT clause.
	 *
	 * @param string $sql SQL Query
	 *
	 * @return string
	 */
	protected function _limit(string $sql): string
	{
		return $sql . ' LIMIT ' . ($this->QBOffset ? $this->QBOffset . ', ' : '') . $this->QBLimit;
	}

	//--------------------------------------------------------------------

	/**
	 * The "set" function.
	 *
	 * Allows key/value pairs to be set for insert(), update() or replace().
	 *
	 * @param string|array|object $key    Field name, or an array of field/value pairs
	 * @param string              $value  Field value, if $key is a single field
	 * @param boolean             $escape Whether to escape values and identifiers
	 *
	 * @return BaseBuilder
	 */
	public function set($key, ?string $value = '', bool $escape = null)
	{
		$key = $this->objectToArray($key);

		if (! is_array($key))
		{
			$key = [$key => $value];
		}

		$escape = is_bool($escape) ? $escape : $this->db->protectIdentifiers;

		foreach ($key as $k => $v)
		{
			if ($escape)
			{
				$bind                                                           = $this->setBind($k, $v, $escape);
				$this->QBSet[$this->db->protectIdentifiers($k, false, $escape)] = ":$bind:";
			}
			else
			{
				$this->QBSet[$this->db->protectIdentifiers($k, false, $escape)] = $v;
			}
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the previously set() data, alternatively resetting it
	 * if needed.
	 *
	 * @param boolean $clean
	 *
	 * @return array
	 */
	public function getSetData(bool $clean = false): array
	{
		$data = $this->QBSet;

		if ($clean)
		{
			$this->QBSet = [];
		}

		return $data;
	}

	//--------------------------------------------------------------------

	/**
	 * Get SELECT query string
	 *
	 * Compiles a SELECT query string and returns the sql.
	 *
	 * @param boolean $reset TRUE: resets QB values; FALSE: leave QB values alone
	 *
	 * @return string
	 */
	public function getCompiledSelect(bool $reset = true): string
	{
		$select = $this->compileSelect();

		if ($reset === true)
		{
			$this->resetSelect();
		}

		return $this->compileFinalQuery($select);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a finalized, compiled query string with the bindings
	 * inserted and prefixes swapped out.
	 *
	 * @param string $sql
	 *
	 * @return string
	 */
	protected function compileFinalQuery(string $sql): string
	{
		$query = new Query($this->db);
		$query->setQuery($sql, $this->binds, false);

		if (! empty($this->db->swapPre) && ! empty($this->db->DBPrefix))
		{
			$query->swapPrefix($this->db->DBPrefix, $this->db->swapPre);
		}

		return $query->getQuery();
	}

	/**
	 * Get
	 *
	 * Compiles the select statement based on the other functions called
	 * and runs the query
	 *
	 * @param integer $limit  The limit clause
	 * @param integer $offset The offset clause
	 * @param boolean $reset  Are we want to clear query builder values?
	 *
	 * @return ResultInterface
	 */
	public function get(int $limit = null, int $offset = 0, bool $reset = true)
	{
		if (! is_null($limit))
		{
			$this->limit($limit, $offset);
		}

		$result = $this->testMode
			? $this->getCompiledSelect($reset)
			: $this->db->query($this->compileSelect(), $this->binds, false);

		if ($reset === true)
		{
			$this->resetSelect();

			// Clear our binds so we don't eat up memory
			$this->binds = [];
		}

		return $result;
	}

	//--------------------------------------------------------------------

	/**
	 * "Count All" query
	 *
	 * Generates a platform-specific query string that counts all records in
	 * the specified database
	 *
	 * @param boolean $reset Are we want to clear query builder values?
	 *
	 * @return integer|string when $test = true
	 */
	public function countAll(bool $reset = true)
	{
		$table = $this->QBFrom[0];

		$sql = $this->countString . $this->db->escapeIdentifiers('numrows') . ' FROM ' .
				$this->db->protectIdentifiers($table, true, null, false);

		if ($this->testMode)
		{
			return $sql;
		}

		$query = $this->db->query($sql, null, false);
		if (empty($query->getResult()))
		{
			return 0;
		}

		$query = $query->getRow();

		if ($reset === true)
		{
			$this->resetSelect();
		}

		return (int) $query->numrows;
	}

	//--------------------------------------------------------------------

	/**
	 * "Count All Results" query
	 *
	 * Generates a platform-specific query string that counts all records
	 * returned by an Query Builder query.
	 *
	 * @param boolean $reset
	 *
	 * @return integer|string when $test = true
	 */
	public function countAllResults(bool $reset = true)
	{
		// ORDER BY usage is often problematic here (most notably
		// on Microsoft SQL Server) and ultimately unnecessary
		// for selecting COUNT(*) ...
		$orderBy = [];
		if (! empty($this->QBOrderBy))
		{
			$orderBy         = $this->QBOrderBy;
			$this->QBOrderBy = null;
		}

		// We cannot use a LIMIT when getting the single row COUNT(*) result
		$limit         = $this->QBLimit;
		$this->QBLimit = false;

		$sql = ($this->QBDistinct === true)
			?
			$this->countString . $this->db->protectIdentifiers('numrows') . "\nFROM (\n" . $this->compileSelect() . "\n) CI_count_all_results"
			:
			$this->compileSelect($this->countString . $this->db->protectIdentifiers('numrows'));

		if ($this->testMode)
		{
			return $sql;
		}

		$result = $this->db->query($sql, $this->binds, false);

		if ($reset === true)
		{
			$this->resetSelect();
		}
		// If we've previously reset the QBOrderBy values, get them back
		elseif (! isset($this->QBOrderBy))
		{
			$this->QBOrderBy = $orderBy ?? [];
		}

		// Restore the LIMIT setting
		$this->QBLimit = $limit;

		$row = (! $result instanceof ResultInterface)
			? null
			: $result->getRow();

		if (empty($row))
		{
			return 0;
		}

		return (int) $row->numrows;
	}

	//--------------------------------------------------------------------
	/**
	 * Get compiled 'where' condition string
	 *
	 * Compiles the set conditions and returns the sql statement
	 *
	 * @return string
	 */
	public function getCompiledQBWhere()
	{
		return $this->QBWhere;
	}
	//--------------------------------------------------------------------

	/**
	 * Get_Where
	 *
	 * Allows the where clause, limit and offset to be added directly
	 *
	 * @param string|array $where  Where condition
	 * @param integer      $limit  Limit value
	 * @param integer      $offset Offset value
	 * @param boolean      $reset  Are we want to clear query builder values?
	 *
	 * @return ResultInterface
	 */
	public function getWhere($where = null, int $limit = null, ?int $offset = 0, bool $reset = true)
	{
		if ($where !== null)
		{
			$this->where($where);
		}

		if (! empty($limit))
		{
			$this->limit($limit, $offset);
		}

		$result = $this->testMode
			? $this->getCompiledSelect($reset)
			: $this->db->query($this->compileSelect(), $this->binds, false);

		if ($reset === true)
		{
			$this->resetSelect();

			// Clear our binds so we don't eat up memory
			$this->binds = [];
		}

		return $result;
	}

	//--------------------------------------------------------------------

	/**
	 * Insert_Batch
	 *
	 * Compiles batch insert strings and runs the queries
	 *
	 * @param array   $set       An associative array of insert values
	 * @param boolean $escape    Whether to escape values and identifiers
	 * @param integer $batchSize Batch size
	 *
	 * @return integer Number of rows inserted or FALSE on failure
	 * @throws DatabaseException
	 */
	public function insertBatch(array $set = null, bool $escape = null, int $batchSize = 100)
	{
		if ($set === null)
		{
			if (empty($this->QBSet))
			{
				if (CI_DEBUG)
				{
					throw new DatabaseException('You must use the "set" method to update an entry.');
				}

				return false;
			}
		}
		else
		{
			if (empty($set))
			{
				if (CI_DEBUG)
				{
					throw new DatabaseException('insertBatch() called with no data');
				}

				return false;
			}

			$this->setInsertBatch($set, '', $escape);
		}

		$table = $this->QBFrom[0];

		// Batch this baby
		$affected_rows = 0;
		for ($i = 0, $total = count($this->QBSet); $i < $total; $i += $batchSize)
		{
			$sql = $this->_insertBatch($this->db->protectIdentifiers($table, true, $escape, false), $this->QBKeys, array_slice($this->QBSet, $i, $batchSize));

			if ($this->testMode)
			{
				++ $affected_rows;
			}
			else
			{
				$this->db->query($sql, $this->binds, false);
				$affected_rows += $this->db->affectedRows();
			}
		}

		if (! $this->testMode)
		{
			$this->resetWrite();
		}

		return $affected_rows;
	}

	//--------------------------------------------------------------------

	/**
	 * Insert batch statement
	 *
	 * Generates a platform-specific insert string from the supplied data.
	 *
	 * @param string $table  Table name
	 * @param array  $keys   INSERT keys
	 * @param array  $values INSERT values
	 *
	 * @return string
	 */
	protected function _insertBatch(string $table, array $keys, array $values): string
	{
		return 'INSERT INTO ' . $table . ' (' . implode(', ', $keys) . ') VALUES ' . implode(', ', $values);
	}

	//--------------------------------------------------------------------

	/**
	 * The "setInsertBatch" function.  Allows key/value pairs to be set for batch inserts
	 *
	 * @param mixed   $key
	 * @param string  $value
	 * @param boolean $escape
	 *
	 * @return BaseBuilder|null
	 */
	public function setInsertBatch($key, string $value = '', bool $escape = null)
	{
		$key = $this->batchObjectToArray($key);

		if (! is_array($key))
		{
			$key = [$key => $value];
		}

		$escape = is_bool($escape) ? $escape : $this->db->protectIdentifiers;

		$keys = array_keys($this->objectToArray(current($key)));
		sort($keys);

		foreach ($key as $row)
		{
			$row = $this->objectToArray($row);
			if (count(array_diff($keys, array_keys($row))) > 0 || count(array_diff(array_keys($row), $keys)) > 0)
			{
				// batch function above returns an error on an empty array
				$this->QBSet[] = [];

				return null;
			}

			ksort($row); // puts $row in the same order as our keys

			$clean = [];
			foreach ($row as $k => $value)
			{
				$clean[] = ':' . $this->setBind($k, $value, $escape) . ':';
			}

			$row = $clean;

			$this->QBSet[] = '(' . implode(',', $row) . ')';
		}

		foreach ($keys as $k)
		{
			$this->QBKeys[] = $this->db->protectIdentifiers($k, false, $escape);
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Get INSERT query string
	 *
	 * Compiles an insert query and returns the sql
	 *
	 * @param boolean $reset TRUE: reset QB values; FALSE: leave QB values alone
	 *
	 * @return string
	 */
	public function getCompiledInsert(bool $reset = true): string
	{
		if ($this->validateInsert() === false)
		{
			return false;
		}

		$sql = $this->_insert(
				$this->db->protectIdentifiers(
						$this->QBFrom[0], true, null, false
				), array_keys($this->QBSet), array_values($this->QBSet)
		);

		if ($reset === true)
		{
			$this->resetWrite();
		}

		return $this->compileFinalQuery($sql);
	}

	//--------------------------------------------------------------------

	/**
	 * Insert
	 *
	 * Compiles an insert string and runs the query
	 *
	 * @param array   $set    An associative array of insert values
	 * @param boolean $escape Whether to escape values and identifiers
	 *
	 * @return BaseResult|Query|false
	 */
	public function insert(array $set = null, bool $escape = null)
	{
		if ($set !== null)
		{
			$this->set($set, '', $escape);
		}

		if ($this->validateInsert() === false)
		{
			return false;
		}

		$sql = $this->_insert(
				$this->db->protectIdentifiers(
						$this->QBFrom[0], true, $escape, false
				), array_keys($this->QBSet), array_values($this->QBSet)
		);

		if (! $this->testMode)
		{
			$this->resetWrite();

			$result = $this->db->query($sql, $this->binds, false);

			// Clear our binds so we don't eat up memory
			$this->binds = [];

			return $result;
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Validate Insert
	 *
	 * This method is used by both insert() and getCompiledInsert() to
	 * validate that the there data is actually being set and that table
	 * has been chosen to be inserted into.
	 *
	 * @return boolean
	 * @throws DatabaseException
	 */
	protected function validateInsert(): bool
	{
		if (empty($this->QBSet))
		{
			if (CI_DEBUG)
			{
				throw new DatabaseException('You must use the "set" method to update an entry.');
			}

			return false;
		}

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Insert statement
	 *
	 * Generates a platform-specific insert string from the supplied data
	 *
	 * @param string $table         The table name
	 * @param array  $keys          The insert keys
	 * @param array  $unescapedKeys The insert values
	 *
	 * @return string
	 */
	protected function _insert(string $table, array $keys, array $unescapedKeys): string
	{
		return 'INSERT INTO ' . $table . ' (' . implode(', ', $keys) . ') VALUES (' . implode(', ', $unescapedKeys) . ')';
	}

	//--------------------------------------------------------------------

	/**
	 * Replace
	 *
	 * Compiles an replace into string and runs the query
	 *
	 * @param array $set An associative array of insert values
	 *
	 * @return BaseResult|Query|string|false
	 * @throws DatabaseException
	 */
	public function replace(array $set = null)
	{
		if ($set !== null)
		{
			$this->set($set);
		}

		if (empty($this->QBSet))
		{
			if (CI_DEBUG)
			{
				throw new DatabaseException('You must use the "set" method to update an entry.');
			}
			return false;
		}

		$table = $this->QBFrom[0];

		$sql = $this->_replace($table, array_keys($this->QBSet), array_values($this->QBSet));

		$this->resetWrite();

		return $this->testMode ? $sql : $this->db->query($sql, $this->binds, false);
	}

	//--------------------------------------------------------------------

	/**
	 * Replace statement
	 *
	 * Generates a platform-specific replace string from the supplied data
	 *
	 * @param string $table  The table name
	 * @param array  $keys   The insert keys
	 * @param array  $values The insert values
	 *
	 * @return string
	 */
	protected function _replace(string $table, array $keys, array $values): string
	{
		return 'REPLACE INTO ' . $table . ' (' . implode(', ', $keys) . ') VALUES (' . implode(', ', $values) . ')';
	}

	//--------------------------------------------------------------------

	/**
	 * FROM tables
	 *
	 * Groups tables in FROM clauses if needed, so there is no confusion
	 * about operator precedence.
	 *
	 * Note: This is only used (and overridden) by MySQL and CUBRID.
	 *
	 * @return string
	 */
	protected function _fromTables(): string
	{
		return implode(', ', $this->QBFrom);
	}

	//--------------------------------------------------------------------

	/**
	 * Get UPDATE query string
	 *
	 * Compiles an update query and returns the sql
	 *
	 * @param boolean $reset TRUE: reset QB values; FALSE: leave QB values alone
	 *
	 * @return string
	 */
	public function getCompiledUpdate(bool $reset = true): string
	{
		if ($this->validateUpdate() === false)
		{
			return false;
		}

		$sql = $this->_update($this->QBFrom[0], $this->QBSet);

		if ($reset === true)
		{
			$this->resetWrite();
		}

		return $this->compileFinalQuery($sql);
	}

	//--------------------------------------------------------------------

	/**
	 * UPDATE
	 *
	 * Compiles an update string and runs the query.
	 *
	 * @param array   $set   An associative array of update values
	 * @param mixed   $where
	 * @param integer $limit
	 *
	 * @return boolean    TRUE on success, FALSE on failure
	 */
	public function update(array $set = null, $where = null, int $limit = null): bool
	{
		if ($set !== null)
		{
			$this->set($set);
		}

		if ($this->validateUpdate() === false)
		{
			return false;
		}

		if ($where !== null)
		{
			$this->where($where);
		}

		if (! empty($limit))
		{
			if (! $this->canLimitWhereUpdates)
			{
				throw new DatabaseException('This driver does not allow LIMITs on UPDATE queries using WHERE.');
			}

			$this->limit($limit);
		}

		$sql = $this->_update($this->QBFrom[0], $this->QBSet);

		if (! $this->testMode)
		{
			$this->resetWrite();

			if ($this->db->query($sql, $this->binds, false))
			{
				// Clear our binds so we don't eat up memory
				$this->binds = [];

				return true;
			}

			return false;
		}

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Update statement
	 *
	 * Generates a platform-specific update string from the supplied data
	 *
	 * @param string $table  the Table name
	 * @param array  $values the Update data
	 *
	 * @return string
	 */
	protected function _update(string $table, array $values): string
	{
		$valstr = [];

		foreach ($values as $key => $val)
		{
			$valstr[] = $key . ' = ' . $val;
		}

		return 'UPDATE ' . $table . ' SET ' . implode(', ', $valstr)
				. $this->compileWhereHaving('QBWhere')
				. $this->compileOrderBy()
				. ($this->QBLimit ? $this->_limit(' ') : '');
	}

	//--------------------------------------------------------------------

	/**
	 * Validate Update
	 *
	 * This method is used by both update() and getCompiledUpdate() to
	 * validate that data is actually being set and that a table has been
	 * chosen to be update.
	 *
	 * @return boolean
	 * @throws \CodeIgniter\Database\Exceptions\DatabaseException
	 */
	protected function validateUpdate(): bool
	{
		if (empty($this->QBSet))
		{
			if (CI_DEBUG)
			{
				throw new DatabaseException('You must use the "set" method to update an entry.');
			}

			return false;
		}

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Update_Batch
	 *
	 * Compiles an update string and runs the query
	 *
	 * @param array   $set       An associative array of update values
	 * @param string  $index     The where key
	 * @param integer $batchSize The size of the batch to run
	 *
	 * @return mixed    Number of rows affected, SQL string, or FALSE on failure
	 * @throws \CodeIgniter\Database\Exceptions\DatabaseException
	 */
	public function updateBatch(array $set = null, string $index = null, int $batchSize = 100)
	{
		if ($index === null)
		{
			if (CI_DEBUG)
			{
				throw new DatabaseException('You must specify an index to match on for batch updates.');
			}

			return false;
		}

		if ($set === null)
		{
			if (empty($this->QBSet))
			{
				if (CI_DEBUG)
				{
					throw new DatabaseException('You must use the "set" method to update an entry.');
				}
				return false;
			}
		}
		else
		{
			if (empty($set))
			{
				if (CI_DEBUG)
				{
					throw new DatabaseException('updateBatch() called with no data');
				}
				return false;
			}

			$this->setUpdateBatch($set, $index);
		}

		$table = $this->QBFrom[0];

		// Batch this baby
		$affected_rows = 0;
		$savedSQL      = [];
		$savedQBWhere  = $this->QBWhere;
		for ($i = 0, $total = count($this->QBSet); $i < $total; $i += $batchSize)
		{
			$sql = $this->_updateBatch($table, array_slice($this->QBSet, $i, $batchSize), $this->db->protectIdentifiers($index)
			);

			if ($this->testMode)
			{
				$savedSQL[] = $sql;
			}
			else
			{
				$this->db->query($sql, $this->binds, false);
				$affected_rows += $this->db->affectedRows();
			}

			$this->QBWhere = $savedQBWhere;
		}

		$this->resetWrite();

		return $this->testMode ? $savedSQL : $affected_rows;
	}

	//--------------------------------------------------------------------

	/**
	 * Update_Batch statement
	 *
	 * Generates a platform-specific batch update string from the supplied data
	 *
	 * @param string $table  Table name
	 * @param array  $values Update data
	 * @param string $index  WHERE key
	 *
	 * @return string
	 */
	protected function _updateBatch(string $table, array $values, string $index): string
	{
		$ids   = [];
		$final = [];
		foreach ($values as $key => $val)
		{
			$ids[] = $val[$index];

			foreach (array_keys($val) as $field)
			{
				if ($field !== $index)
				{
					$final[$field][] = 'WHEN ' . $index . ' = ' . $val[$index] . ' THEN ' . $val[$field];
				}
			}
		}

		$cases = '';
		foreach ($final as $k => $v)
		{
			$cases .= $k . " = CASE \n"
					. implode("\n", $v) . "\n"
					. 'ELSE ' . $k . ' END, ';
		}

		$this->where($index . ' IN(' . implode(',', $ids) . ')', null, false);

		return 'UPDATE ' . $table . ' SET ' . substr($cases, 0, -2) . $this->compileWhereHaving('QBWhere');
	}

	//--------------------------------------------------------------------

	/**
	 * The "setUpdateBatch" function.  Allows key/value pairs to be set for batch updating
	 *
	 * @param array|object $key
	 * @param string       $index
	 * @param boolean      $escape
	 *
	 * @return BaseBuilder|null
	 * @throws \CodeIgniter\Database\Exceptions\DatabaseException
	 */
	public function setUpdateBatch($key, string $index = '', bool $escape = null)
	{
		$key = $this->batchObjectToArray($key);

		if (! is_array($key))
		{
			return null;
		}

		is_bool($escape) || $escape = $this->db->protectIdentifiers;

		foreach ($key as $k => $v)
		{
			$index_set = false;
			$clean     = [];
			foreach ($v as $k2 => $v2)
			{
				if ($k2 === $index)
				{
					$index_set = true;
				}

				$bind = $this->setBind($k2, $v2, $escape);

				$clean[$this->db->protectIdentifiers($k2, false, $escape)] = ":$bind:";
			}

			if ($index_set === false)
			{
				throw new DatabaseException('One or more rows submitted for batch updating is missing the specified index.');
			}

			$this->QBSet[] = $clean;
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Empty Table
	 *
	 * Compiles a delete string and runs "DELETE FROM table"
	 *
	 * @return boolean    TRUE on success, FALSE on failure
	 */
	public function emptyTable()
	{
		$table = $this->QBFrom[0];

		$sql = $this->_delete($table);

		if ($this->testMode)
		{
			return $sql;
		}

		$this->resetWrite();

		return $this->db->query($sql, null, false);
	}

	//--------------------------------------------------------------------

	/**
	 * Truncate
	 *
	 * Compiles a truncate string and runs the query
	 * If the database does not support the truncate() command
	 * This function maps to "DELETE FROM table"
	 *
	 * @return boolean    TRUE on success, FALSE on failure
	 */
	public function truncate()
	{
		$table = $this->QBFrom[0];

		$sql = $this->_truncate($table);

		if ($this->testMode)
		{
			return $sql;
		}

		$this->resetWrite();

		return $this->db->query($sql, null, false);
	}

	//--------------------------------------------------------------------

	/**
	 * Truncate statement
	 *
	 * Generates a platform-specific truncate string from the supplied data
	 *
	 * If the database does not support the truncate() command,
	 * then this method maps to 'DELETE FROM table'
	 *
	 * @param string $table The table name
	 *
	 * @return string
	 */
	protected function _truncate(string $table): string
	{
		return 'TRUNCATE ' . $table;
	}

	//--------------------------------------------------------------------

	/**
	 * Get DELETE query string
	 *
	 * Compiles a delete query string and returns the sql
	 *
	 * @param boolean $reset TRUE: reset QB values; FALSE: leave QB values alone
	 *
	 * @return string
	 */
	public function getCompiledDelete(bool $reset = true): string
	{
		$table = $this->QBFrom[0];

		$sql = $this->delete($table, null, $reset, true);

		return $this->compileFinalQuery($sql);
	}

	//--------------------------------------------------------------------

	/**
	 * Delete
	 *
	 * Compiles a delete string and runs the query
	 *
	 * @param mixed   $where      The where clause
	 * @param integer $limit      The limit clause
	 * @param boolean $reset_data
	 *
	 * @return mixed
	 * @throws \CodeIgniter\Database\Exceptions\DatabaseException
	 */
	public function delete($where = '', int $limit = null, bool $reset_data = true)
	{
		$table = $this->db->protectIdentifiers($this->QBFrom[0], true, null, false);

		if ($where !== '')
		{
			$this->where($where);
		}

		if (empty($this->QBWhere))
		{
			if (CI_DEBUG)
			{
				throw new DatabaseException('Deletes are not allowed unless they contain a "where" or "like" clause.');
			}

			return false;
		}

		$sql = $this->_delete($table);

		if (! empty($limit))
		{
			$this->QBLimit = $limit;
		}

		if (! empty($this->QBLimit))
		{
			if (! $this->canLimitDeletes)
			{
				throw new DatabaseException('SQLite3 does not allow LIMITs on DELETE queries.');
			}

			$sql = $this->_limit($sql);
		}

		if ($reset_data)
		{
			$this->resetWrite();
		}

		return $this->testMode ? $sql : $this->db->query($sql, $this->binds, false);
	}

	//--------------------------------------------------------------------

	/**
	 * Increments a numeric column by the specified value.
	 *
	 * @param string  $column
	 * @param integer $value
	 *
	 * @return boolean
	 */
	public function increment(string $column, int $value = 1)
	{
		$column = $this->db->protectIdentifiers($column);

		$sql = $this->_update($this->QBFrom[0], [$column => "{$column} + {$value}"]);

		return $this->db->query($sql, $this->binds, false);
	}

	//--------------------------------------------------------------------

	/**
	 * Decrements a numeric column by the specified value.
	 *
	 * @param string  $column
	 * @param integer $value
	 *
	 * @return boolean
	 */
	public function decrement(string $column, int $value = 1)
	{
		$column = $this->db->protectIdentifiers($column);

		$sql = $this->_update($this->QBFrom[0], [$column => "{$column}-{$value}"]);

		return $this->db->query($sql, $this->binds, false);
	}

	//--------------------------------------------------------------------

	/**
	 * Delete statement
	 *
	 * Generates a platform-specific delete string from the supplied data
	 *
	 * @param string $table The table name
	 *
	 * @return string
	 */
	protected function _delete(string $table): string
	{
		return 'DELETE FROM ' . $table . $this->compileWhereHaving('QBWhere')
				. ($this->QBLimit ? ' LIMIT ' . $this->QBLimit : '');
	}

	//--------------------------------------------------------------------

	/**
	 * Track Aliases
	 *
	 * Used to track SQL statements written with aliased tables.
	 *
	 * @param string|array $table The table to inspect
	 *
	 * @return string|void
	 */
	protected function trackAliases($table)
	{
		if (is_array($table))
		{
			foreach ($table as $t)
			{
				$this->trackAliases($t);
			}
			return;
		}

		// Does the string contain a comma?  If so, we need to separate
		// the string into discreet statements
		if (strpos($table, ',') !== false)
		{
			return $this->trackAliases(explode(',', $table));
		}

		// if a table alias is used we can recognize it by a space
		if (strpos($table, ' ') !== false)
		{
			// if the alias is written with the AS keyword, remove it
			$table = preg_replace('/\s+AS\s+/i', ' ', $table);

			// Grab the alias
			$table = trim(strrchr($table, ' '));

			// Store the alias, if it doesn't already exist
			$this->db->addTableAlias($table);
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Compile the SELECT statement
	 *
	 * Generates a query string based on which functions were used.
	 * Should not be called directly.
	 *
	 * @param mixed $select_override
	 *
	 * @return string
	 */
	protected function compileSelect($select_override = false): string
	{
		// Write the "select" portion of the query
		if ($select_override !== false)
		{
			$sql = $select_override;
		}
		else
		{
			$sql = ( ! $this->QBDistinct) ? 'SELECT ' : 'SELECT DISTINCT ';

			if (empty($this->QBSelect))
			{
				$sql .= '*';
			}
			else
			{
				// Cycle through the "select" portion of the query and prep each column name.
				// The reason we protect identifiers here rather than in the select() function
				// is because until the user calls the from() function we don't know if there are aliases
				foreach ($this->QBSelect as $key => $val)
				{
					$no_escape            = $this->QBNoEscape[$key] ?? null;
					$this->QBSelect[$key] = $this->db->protectIdentifiers($val, false, $no_escape);
				}

				$sql .= implode(', ', $this->QBSelect);
			}
		}

		// Write the "FROM" portion of the query
		if (! empty($this->QBFrom))
		{
			$sql .= "\nFROM " . $this->_fromTables();
		}

		// Write the "JOIN" portion of the query
		if (! empty($this->QBJoin))
		{
			$sql .= "\n" . implode("\n", $this->QBJoin);
		}

		$sql .= $this->compileWhereHaving('QBWhere')
				. $this->compileGroupBy()
				. $this->compileWhereHaving('QBHaving')
				. $this->compileOrderBy(); // ORDER BY
		// LIMIT
		if ($this->QBLimit)
		{
			return $this->_limit($sql . "\n");
		}

		return $sql;
	}

	//--------------------------------------------------------------------

	/**
	 * Compile WHERE, HAVING statements
	 *
	 * Escapes identifiers in WHERE and HAVING statements at execution time.
	 *
	 * Required so that aliases are tracked properly, regardless of whether
	 * where(), orWhere(), having(), orHaving are called prior to from(),
	 * join() and prefixTable is added only if needed.
	 *
	 * @param string $qb_key 'QBWhere' or 'QBHaving'
	 *
	 * @return string    SQL statement
	 */
	protected function compileWhereHaving(string $qb_key): string
	{
		if (! empty($this->$qb_key))
		{
			for ($i = 0, $c = count($this->$qb_key); $i < $c; $i ++)
			{
				// Is this condition already compiled?
				if (is_string($this->{$qb_key}[$i]))
				{
					continue;
				}
				elseif ($this->{$qb_key}[$i]['escape'] === false)
				{
					$this->{$qb_key}[$i] = $this->{$qb_key}[$i]['condition'];
					continue;
				}

				// Split multiple conditions
				$conditions = preg_split(
						'/((?:^|\s+)AND\s+|(?:^|\s+)OR\s+)/i', $this->{$qb_key}[$i]['condition'], -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
				);

				for ($ci = 0, $cc = count($conditions); $ci < $cc; $ci ++)
				{
					if (($op = $this->getOperator($conditions[$ci])) === false
							|| ! preg_match('/^(\(?)(.*)(' . preg_quote($op, '/') . ')\s*(.*(?<!\)))?(\)?)$/i', $conditions[$ci], $matches)
					)
					{
						continue;
					}
					// $matches = array(
					//	0 => '(test <= foo)',	/* the whole thing */
					//	1 => '(',		/* optional */
					//	2 => 'test',		/* the field name */
					//	3 => ' <= ',		/* $op */
					//	4 => 'foo',		/* optional, if $op is e.g. 'IS NULL' */
					//	5 => ')'		/* optional */
					// );

					if (! empty($matches[4]))
					{
						$matches[4] = ' ' . $matches[4];
					}

					$conditions[$ci] = $matches[1] . $this->db->protectIdentifiers(trim($matches[2]))
							. ' ' . trim($matches[3]) . $matches[4] . $matches[5];
				}

				$this->{$qb_key}[$i] = implode('', $conditions);
			}

			return ($qb_key === 'QBHaving' ? "\nHAVING " : "\nWHERE ")
					. implode("\n", $this->$qb_key);
		}

		return '';
	}

	//--------------------------------------------------------------------

	/**
	 * Compile GROUP BY
	 *
	 * Escapes identifiers in GROUP BY statements at execution time.
	 *
	 * Required so that aliases are tracked properly, regardless of whether
	 * groupBy() is called prior to from(), join() and prefixTable is added
	 * only if needed.
	 *
	 * @return string    SQL statement
	 */
	protected function compileGroupBy(): string
	{
		if (! empty($this->QBGroupBy))
		{
			for ($i = 0, $c = count($this->QBGroupBy); $i < $c; $i ++)
			{
				// Is it already compiled?
				if (is_string($this->QBGroupBy[$i]))
				{
					continue;
				}

				$this->QBGroupBy[$i] = ($this->QBGroupBy[$i]['escape'] === false ||
						$this->isLiteral($this->QBGroupBy[$i]['field'])) ? $this->QBGroupBy[$i]['field'] : $this->db->protectIdentifiers($this->QBGroupBy[$i]['field']);
			}

			return "\nGROUP BY " . implode(', ', $this->QBGroupBy);
		}

		return '';
	}

	//--------------------------------------------------------------------

	/**
	 * Compile ORDER BY
	 *
	 * Escapes identifiers in ORDER BY statements at execution time.
	 *
	 * Required so that aliases are tracked properly, regardless of whether
	 * orderBy() is called prior to from(), join() and prefixTable is added
	 * only if needed.
	 *
	 * @return string    SQL statement
	 */
	protected function compileOrderBy(): string
	{
		if (is_array($this->QBOrderBy) && ! empty($this->QBOrderBy))
		{
			for ($i = 0, $c = count($this->QBOrderBy); $i < $c; $i ++)
			{
				if ($this->QBOrderBy[$i]['escape'] !== false && ! $this->isLiteral($this->QBOrderBy[$i]['field']))
				{
					$this->QBOrderBy[$i]['field'] = $this->db->protectIdentifiers($this->QBOrderBy[$i]['field']);
				}

				$this->QBOrderBy[$i] = $this->QBOrderBy[$i]['field'] . $this->QBOrderBy[$i]['direction'];
			}

			return $this->QBOrderBy = "\nORDER BY " . implode(', ', $this->QBOrderBy);
		}
		elseif (is_string($this->QBOrderBy))
		{
			return $this->QBOrderBy;
		}

		return '';
	}

	//--------------------------------------------------------------------

	/**
	 * Object to Array
	 *
	 * Takes an object as input and converts the class variables to array key/vals
	 *
	 * @param mixed $object
	 *
	 * @return mixed
	 */
	protected function objectToArray($object)
	{
		if (! is_object($object))
		{
			return $object;
		}

		$array = [];
		foreach (get_object_vars($object) as $key => $val)
		{
			// There are some built in keys we need to ignore for this conversion
			if (! is_object($val) && ! is_array($val) && $key !== '_parent_name')
			{
				$array[$key] = $val;
			}
		}

		return $array;
	}

	//--------------------------------------------------------------------

	/**
	 * Object to Array
	 *
	 * Takes an object as input and converts the class variables to array key/vals
	 *
	 * @param mixed $object
	 *
	 * @return mixed
	 */
	protected function batchObjectToArray($object)
	{
		if (! is_object($object))
		{
			return $object;
		}

		$array  = [];
		$out    = get_object_vars($object);
		$fields = array_keys($out);

		foreach ($fields as $val)
		{
			// There are some built in keys we need to ignore for this conversion
			if ($val !== '_parent_name')
			{
				$i = 0;
				foreach ($out[$val] as $data)
				{
					$array[$i ++][$val] = $data;
				}
			}
		}

		return $array;
	}

	//--------------------------------------------------------------------

	/**
	 * Is literal
	 *
	 * Determines if a string represents a literal value or a field name
	 *
	 * @param string $str
	 *
	 * @return boolean
	 */
	protected function isLiteral(string $str): bool
	{
		$str = trim($str);

		if (empty($str) || ctype_digit($str) || (string) (float) $str === $str ||
				in_array(strtoupper($str), ['TRUE', 'FALSE'], true)
		)
		{
			return true;
		}

		static $_str;

		if (empty($_str))
		{
			$_str = ($this->db->escapeChar !== '"') ? ['"', "'"] : ["'"];
		}

		return in_array($str[0], $_str, true);
	}

	//--------------------------------------------------------------------

	/**
	 * Reset Query Builder values.
	 *
	 * Publicly-visible method to reset the QB values.
	 *
	 * @return BaseBuilder
	 */
	public function resetQuery()
	{
		$this->resetSelect();
		$this->resetWrite();

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Resets the query builder values.  Called by the get() function
	 *
	 * @param array $qb_reset_items An array of fields to reset
	 *
	 * @return void
	 */
	protected function resetRun(array $qb_reset_items)
	{
		foreach ($qb_reset_items as $item => $default_value)
		{
			$this->$item = $default_value;
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Resets the query builder values.  Called by the get() function
	 */
	protected function resetSelect()
	{
		$this->resetRun([
			'QBSelect'   => [],
			'QBJoin'     => [],
			'QBWhere'    => [],
			'QBGroupBy'  => [],
			'QBHaving'   => [],
			'QBOrderBy'  => [],
			'QBNoEscape' => [],
			'QBDistinct' => false,
			'QBLimit'    => false,
			'QBOffset'   => false,
		]);

		if (! empty($this->db))
		{
			$this->db->setAliasedTables([]);
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Resets the query builder "write" values.
	 *
	 * Called by the insert() update() insertBatch() updateBatch() and delete() functions
	 */
	protected function resetWrite()
	{
		$this->resetRun([
			'QBSet'     => [],
			'QBJoin'    => [],
			'QBWhere'   => [],
			'QBOrderBy' => [],
			'QBKeys'    => [],
			'QBLimit'   => false,
		]);
	}

	//--------------------------------------------------------------------

	/**
	 * Tests whether the string has an SQL operator
	 *
	 * @param string $str
	 *
	 * @return boolean
	 */
	protected function hasOperator(string $str): bool
	{
		return (bool) preg_match('/(<|>|!|=|\sIS NULL|\sIS NOT NULL|\sEXISTS|\sBETWEEN|\sLIKE|\sIN\s*\(|\s)/i', trim($str));
	}

	// --------------------------------------------------------------------

	/**
	 * Returns the SQL string operator
	 *
	 * @param string  $str
	 * @param boolean $list
	 *
	 * @return mixed
	 */
	protected function getOperator(string $str, bool $list = false)
	{
		static $_operators;

		if (empty($_operators))
		{
			$_les       = ($this->db->likeEscapeStr !== '') ? '\s+' . preg_quote(trim(sprintf($this->db->likeEscapeStr, $this->db->likeEscapeChar)), '/') : '';
			$_operators = [
				'\s*(?:<|>|!)?=\s*', // =, <=, >=, !=
				'\s*<>?\s*', // <, <>
				'\s*>\s*', // >
				'\s+IS NULL', // IS NULL
				'\s+IS NOT NULL', // IS NOT NULL
				'\s+EXISTS\s*\(.*\)', // EXISTS(sql)
				'\s+NOT EXISTS\s*\(.*\)', // NOT EXISTS(sql)
				'\s+BETWEEN\s+', // BETWEEN value AND value
				'\s+IN\s*\(.*\)', // IN(list)
				'\s+NOT IN\s*\(.*\)', // NOT IN (list)
				'\s+LIKE\s+\S.*(' . $_les . ')?', // LIKE 'expr'[ ESCAPE '%s']
				'\s+NOT LIKE\s+\S.*(' . $_les . ')?', // NOT LIKE 'expr'[ ESCAPE '%s']
			];
		}

		return preg_match_all('/' . implode('|', $_operators) . '/i', $str, $match) ? ($list ? $match[0] : $match[0][0]) : false;
	}

	// --------------------------------------------------------------------

	/**
	 * Stores a bind value after ensuring that it's unique.
	 * While it might be nicer to have named keys for our binds array
	 * with PHP 7+ we get a huge memory/performance gain with indexed
	 * arrays instead, so lets take advantage of that here.
	 *
	 * @param string  $key
	 * @param mixed   $value
	 * @param boolean $escape
	 *
	 * @return string
	 */
	protected function setBind(string $key, $value = null, bool $escape = true): string
	{
		if (! array_key_exists($key, $this->binds))
		{
			$this->binds[$key] = [
				$value,
				$escape,
			];

			return $key;
		}

		$count = 0;

		while (array_key_exists($key . $count, $this->binds))
		{
			++$count;
		}

		$this->binds[$key . $count] = [
			$value,
			$escape,
		];

		return $key . $count;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a clone of a Base Builder with reset query builder values.
	 *
	 * @return BaseBuilder
	 */
	protected function cleanClone()
	{
		return (clone $this)->from([], true)->resetQuery();
	}

	//--------------------------------------------------------------------
}
