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
use CodeIgniter\Database\Exceptions\DataException;

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
		// TODO: replace static dbo group with config
		return '[' . $this->db->getDatabase() . '].[dbo].[' . str_replace('"', '', $table) . ']';
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
			$sql .= is_int( $this->QBOffset) ? ' OFFSET ' . $this->QBOffset : ' OFFSET 0 ';
		}

		return $sql .= ' ROWS FETCH NEXT ' . $this->QBLimit . ' ROWS ONLY ';
	}

	public function replace(array $set = null)
	{
		$keyPermission = $this->keyPermission;
		// TODO: delete old entry
		$this->keyPermission = true;
		$this->insert($set);
		$this->keyPermission = $keyPermission;
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
		return 'DELETE' . (empty($this->QBLimit) ? '' : ' TOP ' . $this->QBLimit . '') . ' FROM ' . $table . $this->compileWhereHaving('QBWhere');
	}

	protected function compileSelect($select_override = false): string
	{
		if (empty($this->QBLimit) || $select_override !== false)
		{
			return parent::compileSelect($select_override);
		}

		$sql = (! $this->QBDistinct) ? 'SELECT ' : 'SELECT DISTINCT ';

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

}
