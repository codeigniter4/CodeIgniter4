<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Database\SQLSRV;

use Closure;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Database\Exceptions\DataException;
use CodeIgniter\Database\ResultInterface;

/**
 * Builder for SQLSRV
 *
 * @todo auto check for TextCastToInt
 * @todo auto check for InsertIndexValue
 * @todo replace: delete index entries before insert
 */
class Builder extends BaseBuilder
{
	/**
	 * ORDER BY random keyword
	 *
	 * @var array
	 */
	protected $randomKeyword = [
		'NEWID()',
		'RAND(%d)',
	];

	/**
	 * Quoted identifier flag
	 *
	 * Whether to use SQL-92 standard quoted identifier
	 * (double quotes) or brackets for identifier escaping.
	 *
	 * @var boolean
	 */
	protected $_quoted_identifier = true;

	/**
	 * Handle increment/decrement on text
	 *
	 * @var boolean
	 */
	public $castTextToInt = true;

	/**
	 * Handle IDENTITY_INSERT property/
	 *
	 * @var boolean
	 */
	public $keyPermission = false;

	//--------------------------------------------------------------------

	/**
	 * FROM tables
	 *
	 * Groups tables in FROM clauses if needed, so there is no confusion
	 * about operator precedence.
	 *
	 * @return string
	 */
	protected function _fromTables(): string
	{
		$from = [];
		foreach ($this->QBFrom as $value)
		{
			$from[] = $this->getFullName($value);
		}

		return implode(', ', $from);
	}

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
		return 'TRUNCATE TABLE ' . $this->getFullName($table);
	}

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
	 * @return $this
	 */
	public function join(string $table, string $cond, string $type = '', bool $escape = null)
	{
		if ($type !== '')
		{
			$type = strtoupper(trim($type));

			if (! in_array($type, $this->joinTypes, true))
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

		if (! is_bool($escape))
		{
			$escape = $this->db->protectIdentifiers;
		}

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
				ksort($conditions);
			}
			else
			{
				$conditions = [$cond];
				$joints     = [''];
			}

			$cond = ' ON ';
			foreach ($conditions as $i => $condition)
			{
				$operator = $this->getOperator($condition);

				$cond .= $joints[$i];
				$cond .= preg_match("/(\(*)?([\[\]\w\.'-]+)" . preg_quote($operator) . '(.*)/i', $condition, $match) ? $match[1] . $this->db->protectIdentifiers($match[2]) . $operator . $this->db->protectIdentifiers($match[3]) : $condition;
			}
		}

		// Do we want to escape the table name?
		if ($escape === true)
		{
			$table = $this->db->protectIdentifiers($table, true, null, false);
		}

		// Assemble the JOIN statement
		$this->QBJoin[] = $join = $type . 'JOIN ' . $this->getFullName($table) . $cond;

		return $this;
	}

	/**
	 * Insert statement
	 *
	 * Generates a platform-specific insert string from the supplied data
	 * auto-enable
	 *
	 * @todo implement check for this instad static $insertKeyPermission
	 *
	 * @param string $table         The table name
	 * @param array  $keys          The insert keys
	 * @param array  $unescapedKeys The insert values
	 *
	 * @return string
	 */
	protected function _insert(string $table, array $keys, array $unescapedKeys): string
	{
		$fullTableName = $this->getFullName($table);

		// insert statement
		$statement = 'INSERT INTO ' . $fullTableName . ' (' . implode(',', $keys) . ') VALUES (' . implode(', ', $unescapedKeys) . ')';

		return $this->keyPermission ? $this->addIdentity($fullTableName, $statement) : $statement;
	}

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

		$fullTableName = $this->getFullName($table);

		$statement = 'UPDATE ' . (empty($this->QBLimit) ? '' : 'TOP(' . $this->QBLimit . ') ') . $fullTableName . ' SET '
				. implode(', ', $valstr) . $this->compileWhereHaving('QBWhere') . $this->compileOrderBy();

		return $this->keyPermission ? $this->addIdentity($fullTableName, $statement) : $statement;
	}

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

		if ($this->castTextToInt)
		{
			$values = [$column => "CONVERT(VARCHAR(MAX),CONVERT(INT,CONVERT(VARCHAR(MAX), {$column})) + {$value})"];
		}
		else
		{
			$values = [$column => "{$column} + {$value}"];
		}
		$sql = $this->_update($this->QBFrom[0], $values);

		return $this->db->query($sql, $this->binds, false);
	}

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

		if ($this->castTextToInt)
		{
			$values = [$column => "CONVERT(VARCHAR(MAX),CONVERT(INT,CONVERT(VARCHAR(MAX), {$column})) - {$value})"];
		}
		else
		{
			$values = [$column => "{$column} + {$value}"];
		}
		$sql = $this->_update($this->QBFrom[0], $values);

		return $this->db->query($sql, $this->binds, false);
	}

	/**
	 * Get full name of the table
	 *
	 * @param string $table
	 *
	 * @return string
	 */
	private function getFullName(string $table): string
	{
		$alias = '';

		if (strpos($table, ' ') !== false)
		{
			$alias = explode(' ', $table);
			$table = array_shift($alias);
			$alias = ' ' . implode(' ', $alias);
		}

		if ($this->db->escapeChar === '"')
		{
			return '"' . $this->db->getDatabase() . '"."' . $this->db->schema . '"."' . str_replace('"', '', $table) . '"' . $alias;
		}

		return '[' . $this->db->getDatabase() . '].[' . $this->db->schema . '].[' . str_replace('"', '', $table) . ']' . str_replace('"', '', $alias);
	}

	/**
	 * Add permision statements for index value inserts
	 *
	 * @param string $fullTable full table name
	 * @param string $insert    statement
	 *
	 * @return string
	 */
	private function addIdentity(string $fullTable, string $insert): string
	{
		return 'SET IDENTITY_INSERT ' . $fullTable . " ON\n" . $insert . "\nSET IDENTITY_INSERT " . $fullTable . ' OFF';
	}

	/**
	 * Local implementation of limit
	 *
	 * @param string  $sql
	 * @param boolean $offsetIgnore
	 *
	 * @return string
	 */
	protected function _limit(string $sql, bool $offsetIgnore = false): string
	{
		if (empty($this->QBOrderBy))
		{
			$sql .= ' ORDER BY (SELECT NULL) ';
		}

		if ($offsetIgnore)
		{
			$sql .= ' OFFSET 0 ';
		}
		else
		{
			$sql .= is_int($this->QBOffset) ? ' OFFSET ' . $this->QBOffset : ' OFFSET 0 ';
		}

		return $sql .= ' ROWS FETCH NEXT ' . $this->QBLimit . ' ROWS ONLY ';
	}

	/**
	 * Replace
	 *
	 * Compiles a replace into string and runs the query
	 *
	 * @param array|null $set An associative array of insert values
	 *
	 * @return mixed
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

			return false; // @codeCoverageIgnore
		}

		$table = $this->QBFrom[0];

		$sql = $this->_replace($table, array_keys($this->QBSet), array_values($this->QBSet));

		$this->resetWrite();

		if ($this->testMode)
		{
			return $sql;
		}

		$this->db->simpleQuery('SET IDENTITY_INSERT ' . $this->db->escapeIdentifiers($table) . ' ON');
		$result = $this->db->query($sql, $this->binds, false);
		$this->db->simpleQuery('SET IDENTITY_INSERT ' . $this->db->escapeIdentifiers($table) . ' OFF');

		return $result;
	}

	/**
	 * Replace statement
	 *
	 * Generates a platform-specific replace string from the supplied data
	 *
	 * on match delete and insert
	 *
	 * @param string $table  The table name
	 * @param array  $keys   The insert keys
	 * @param array  $values The insert values
	 *
	 * @return string
	 */
	protected function _replace(string $table, array $keys, array $values): string
	{
		// check whether the existing keys are part of the primary key.
		// if so then use them for the "ON" part and exclude them from the $values and $keys
		$pKeys     = $this->db->getIndexData($table);
		$keyFields = [];

		foreach ($pKeys as $key)
		{
			if ($key->type === 'PRIMARY')
			{
				$keyFields = array_merge($keyFields, $key->fields);
			}

			if ($key->type === 'UNIQUE')
			{
				$keyFields = array_merge($keyFields, $key->fields);
			}
		}

		// Get the unique field names
		$escKeyFields = array_map(function (string $field): string {
			return $this->db->protectIdentifiers($field);
		}, array_values(array_unique($keyFields)));

		// Get the binds
		$binds = $this->binds;
		array_walk($binds, function (&$item) {
			$item = $item[0];
		});

		// Get the common field and values from the keys data and index fields
		$common = array_intersect($keys, $escKeyFields);
		$bingo  = [];

		foreach ($common as $v)
		{
			$k = array_search($v, $escKeyFields, true);

			$bingo[$keyFields[$k]] = $binds[trim($values[$k], ':')];
		}

		// Querying existing data
		$builder = $this->db->table($table);

		foreach ($bingo as $k => $v)
		{
			$builder->where($k, $v);
		}

		$q = $builder->get()->getResult();

		// Delete entries if we find them
		if ($q !== [])
		{
			$delete = $this->db->table($table);

			foreach ($bingo as $k => $v)
			{
				$delete->where($k, $v);
			}

			$delete->delete();
		}

		return sprintf('INSERT INTO %s (%s) VALUES (%s);', $this->getFullName($table), implode(',', $keys), implode(',', $values));
	}

	/**
	 * SELECT [MAX|MIN|AVG|SUM|COUNT]()
	 *
	 * Handle float return value
	 *
	 * @param string $select Field name
	 * @param string $alias
	 * @param string $type
	 *
	 * @return BaseBuilder
	 */
	protected function maxMinAvgSum(string $select = '', string $alias = '', string $type = 'MAX')
	{
		// int functions can be handled by parent
		if ($type !== 'AVG')
		{
			return parent::maxMinAvgSum($select, $alias, $type);
		}

		if ($select === '')
		{
			throw DataException::forEmptyInputGiven('Select');
		}

		if (strpos($select, ',') !== false)
		{
			throw DataException::forInvalidArgument('Column name not separated by comma');
		}

		if ($alias === '')
		{
			$alias = $this->createAliasFromTable(trim($select));
		}

		$sql = $type . '( CAST( ' . $this->db->protectIdentifiers(trim($select)) . ' AS FLOAT ) ) AS ' . $this->db->escapeIdentifiers(trim($alias));

		$this->QBSelect[]   = $sql;
		$this->QBNoEscape[] = null;

		return $this;
	}

	/**
	 * Delete statement
	 *
	 * @param string $table The table name
	 *
	 * @return string
	 */
	protected function _delete(string $table): string
	{
		return 'DELETE' . (empty($this->QBLimit) ? '' : ' TOP (' . $this->QBLimit . ') ') . ' FROM ' . $this->getFullName($table) . $this->compileWhereHaving('QBWhere');
	}

	/**
	 * Delete
	 *
	 * Compiles a delete string and runs the query
	 *
	 * @param mixed   $where     The where clause
	 * @param integer $limit     The limit clause
	 * @param boolean $resetData
	 *
	 * @return mixed
	 * @throws DatabaseException
	 */
	public function delete($where = '', int $limit = null, bool $resetData = true)
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

			return false; // @codeCoverageIgnore
		}

		if (! empty($limit))
		{
			$this->QBLimit = $limit;
		}

		$sql = $this->_delete($table);

		if ($resetData)
		{
			$this->resetWrite();
		}

		return $this->testMode ? $sql : $this->db->query($sql, $this->binds, false);
	}

	/**
	 * Compile the SELECT statement
	 *
	 * Generates a query string based on which functions were used.
	 *
	 * @param mixed $selectOverride
	 *
	 * @return string
	 */
	protected function compileSelect($selectOverride = false): string
	{
		// Write the "select" portion of the query
		if ($selectOverride !== false)
		{
			$sql = $selectOverride;
		}
		else
		{
			$sql = (! $this->QBDistinct) ? 'SELECT ' : 'SELECT DISTINCT ';

			// SQL Server can't work with select * if group by is specified
			if (empty($this->QBSelect) && ! empty($this->QBGroupBy) && is_array($this->QBGroupBy))
			{
				foreach ($this->QBGroupBy as $field)
				{
					$this->QBSelect[] = is_array($field) ? $field['field'] : $field;
				}
			}

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
					$noEscape             = $this->QBNoEscape[$key] ?? null;
					$this->QBSelect[$key] = $this->db->protectIdentifiers($val, false, $noEscape);
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
			return $sql = $this->_limit($sql . "\n");
		}

		return $sql;
	}

	/**
	 * WHERE, HAVING
	 *
	 * @param string  $qbKey  'QBWhere' or 'QBHaving'
	 * @param mixed   $key
	 * @param mixed   $value
	 * @param string  $type
	 * @param boolean $escape
	 *
	 * @return $this
	 */
	protected function whereHaving(string $qbKey, $key, $value = null, string $type = 'AND ', bool $escape = null)
	{
		if (! is_array($key))
		{
			$key = [$key => $value];
		}

		// If the escape value was not set will base it on the global setting
		if (! is_bool($escape))
		{
			$escape = $this->db->protectIdentifiers;
		}

		foreach ($key as $k => $v)
		{
			$prefix = empty($this->$qbKey) ? $this->groupGetType('') : $this->groupGetType($type);

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
			elseif (! $this->hasOperator($k) && $qbKey !== 'QBHaving')
			{
				// value appears not to have been set, assign the test to IS NULL
				$k .= ' IS NULL';
			}
			elseif (preg_match('/\s*(!?=|<>|IS(?:\s+NOT)?)\s*$/i', $k, $match, PREG_OFFSET_CAPTURE))
			{
				$k = substr($k, 0, $match[0][1]) . ($match[1][0] === '=' ? ' IS NULL' : ' IS NOT NULL');
			}

			$this->{$qbKey}[] = [
				'condition' => $prefix . $k . $v,
				'escape'    => $escape,
			];
		}

		return $this;
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

		$result = $this->testMode ? $this->getCompiledSelect($reset) : $this->db->query($this->compileSelect(), $this->binds, false);

		if ($reset)
		{
			$this->resetSelect();

			// Clear our binds so we don't eat up memory
			$this->binds = [];
		}

		return $result;
	}
}
