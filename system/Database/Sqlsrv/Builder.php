<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
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
 * @copyright  2014-2019 British Columbia Institute of Technology (https://bcit.ca/)
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 3.0.0
 * @filesource
 */

namespace CodeIgniter\Database\Sqlsrv;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Database\Exceptions\DataException;
use CodeIgniter\Database\ResultInterface;
use const CI_DEBUG;

/**
 * Builder for Sqlsrv
 */
class Builder extends BaseBuilder {
	/// TODO: auto check for TextCastToInt
	/// TODO: auto check for InsertIndexValue
	/// TODO: replace: delete index entries before insert

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
	// handle increment/decrement on text
	public $castTextToInt = true;
	public $keyPermission = false;

	protected function _truncate(string $table): string
	{
		return 'TRUNCATE TABLE ' . $table;
	}

	/**
	 * Insert statement
	 *
	 * Generates a platform-specific insert string from the supplied data
	 * auto-enable
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

		// check for index key
		// TODO: implement check for this instad static $insertKeyPermission
		// insert statement
		$statement = 'INSERT INTO ' . $fullTableName . ' (' . implode(',', $keys) . ') VALUES (' . implode(', ', $unescapedKeys) . ')';

		return $this->keyPermission ? $this->addIdentity($fullTableName, $statement) : $statement;
	}

	protected function _update(string $table, array $values): string
	{
		$valstr = [];

		foreach ($values as $key => $val)
		{
			$valstr[] = $key . ' = ' . $val;
		}

		$statement = 'UPDATE ' . ( empty($this->QBLimit) ? '' : 'TOP(' . $this->QBLimit . ') ' ) . $table . ' SET '
				. implode(', ', $valstr) . $this->compileWhereHaving('QBWhere') . $this->compileOrderBy();

		return $this->keyPermission ? $this->addIdentity($this->getFullName($table), $statement) : $statement;
	}

	/**
	 * increment
	 *
	 * @param  string  $column
	 * @param  integer $value
	 * @return type
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

	private function getFullName(string $table): string
	{
		if ('"' === $this->db->escapeChar)
		{
			return '"' . $this->db->getDatabase() . '"."' . $this->db->schema . '"."' . str_replace('"', '', $table) . '"';
		}
		else
		{
			return '[' . $this->db->getDatabase() . '].[' . $this->db->schema . '].[' . str_replace('"', '', $table) . ']';
		}
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
	 * @param  string  $sql
	 * @param  boolean $offsetIgnore
	 * @return string
	 */
	protected function _limit(string $sql, bool $offsetIgnore = false): string
	{
		// Ref ->  return $sql . ' LIMIT ' . (false === $offsetIgnore && $this->QBOffset ? $this->QBOffset . ', ' : '') . $this->QBLimit;

		if (empty($this->QBOrderBy))
		{
			$sql .= ' ORDER BY (SELECT NULL) ';
		}

		if (true === $offsetIgnore)
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
			// @codeCoverageIgnoreStart
			return false;
			// @codeCoverageIgnoreEnd
		}

		$table = $this->QBFrom[0];

		$sql = $this->_replace($table, array_keys($this->QBSet), array_values($this->QBSet));

		$this->resetWrite();

		if ($this->testMode)
		{
			return $sql;
		}
		else
		{
			$this->db->simpleQuery('SET IDENTITY_INSERT ' . $this->db->escapeIdentifiers($table) . ' ON');
			$returnValue = $this->db->query($sql, $this->binds, false);
			$this->db->simpleQuery('SET IDENTITY_INSERT ' . $this->db->escapeIdentifiers($table) . ' OFF');

			return $returnValue;
		}
	}

	//--------------------------------------------------------------------

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
			if ('PRIMARY' === $key->type)
			{
				$keyFields = array_merge($keyFields, $key->fields);
			}

			if ('UNIQUE' === $key->type)
			{
				$keyFields = array_merge($keyFields, $key->fields);
			}
		}

		// Get the unique field names
		$keyFields = array_values(array_flip(array_flip($keyFields)));

		// Get the fields out of binds
		$set = $this->binds;
		array_walk($set, function (&$item, $key) {
			$item = $item[0];
		});

		// Get the common field and values from the bind data and index fields
		$setKeys = array_keys($set);
		$common  = array_intersect($setKeys, $keyFields);

		$bingo = [];
		foreach ($common as $k => $v)
		{
			$bingo[$v] = $set[$v];
		}

		// Querying existing data
		$builder = $this->db->table($table);
		foreach ($bingo as $k => $v)
		{
			$builder->where($k, $v);
		}
		$q = $builder->get()->getResult();

		// Delete entries if we find them
		if (count($q) > 0)
		{
			$delete = $this->db->table($table);
			foreach ($bingo as $k => $v)
			{
				$delete->where($k, $v);
			}
			$delete->delete();
		}

		// Key field names are not escaped, so escape them
		$escapedKeyFields = array_map(function ($item) {
			return $this->db->escapeIdentifiers($item);
		}, $keyFields);

		return'INSERT INTO ' . $table . ' (' . implode(',', $keys) . ') VALUES (' . implode(',', $values) . ');';
	}

	// handle float return value
	protected function maxMinAvgSum(string $select = '', string $alias = '', string $type = 'MAX')
	{
		// int functions can be handled by parent
		if (! in_array($type, ['AVG']))
		{
			return parent::maxMinAvgSum($select, $alias, $type);
		}

		if ($select === '')
		{
			throw DataException::forEmptyInputGiven('Select');
		}

		if (strpos($select, ',') !== false)
		{
			throw DataException::forInvalidArgument('column name not separated by comma');
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

	protected function _delete(string $table): string
	{
		return 'DELETE' . (empty($this->QBLimit) ? '' : ' TOP (' . $this->QBLimit . ') ') . ' FROM ' . $table . $this->compileWhereHaving('QBWhere');
	}

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
	 * @throws DatabaseException
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
			// @codeCoverageIgnoreStart
			return false;
			// @codeCoverageIgnoreEnd
		}

		if (! empty($limit))
		{
			$this->QBLimit = $limit;
		}

		$sql = $this->_delete($table);

		if ($reset_data)
		{
			$this->resetWrite();
		}

		return $this->testMode ? $sql : $this->db->query($sql, $this->binds, false);
	}

	//--------------------------------------------------------------------
	protected function compileSelect($select_override = false): string
	{
		if (empty($this->QBLimit) || $select_override !== false)
		{
			return parent::compileSelect($select_override);
		}

		$sql = (! $this->QBDistinct) ? 'SELECT ' : 'SELECT DISTINCT ';

		// SQL Server can't work with select * if group by is specified
		if (empty($this->QBSelect) && ! empty($this->QBGroupBy))
		{
			if (is_array($this->QBGroupBy))
			{
				foreach ($this->QBGroupBy as $field)
				{
					$this->QBSelect[] = $field['field'];
				}
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
				$no_escape            = $this->QBNoEscape[$key] ?? null;
				$this->QBSelect[$key] = $this->db->protectIdentifiers($val, false, $no_escape);
			}

			$sql .= implode(', ', $this->QBSelect);
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
	 * whereHaving
	 *
	 * @param  string  $qb_key
	 * @param  type    $key
	 * @param  type    $value
	 * @param  string  $type
	 * @param  boolean $escape
	 * @return $this
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
					$k .= ' LIKE';
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

		if ($reset === true)
		{
			$this->resetSelect();

			// Clear our binds so we don't eat up memory
			$this->binds = [];
		}

		return $result;
	}

	//--------------------------------------------------------------------
}
