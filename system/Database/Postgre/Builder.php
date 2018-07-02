<?php namespace CodeIgniter\Database\Postgre;

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
 * @package	CodeIgniter
 * @author	CodeIgniter Dev Team
 * @copyright	2014-2018 British Columbia Institute of Technology (https://bcit.ca/)
 * @license	https://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */
use CodeIgniter\Database\BaseBuilder;
use \CodeIgniter\Database\Exceptions\DatabaseException;

/**
 * Builder for Postgre
 */
class Builder extends BaseBuilder
{

	/**
	 * ORDER BY random keyword
	 *
	 * @var array
	 */
	protected $randomKeyword = ['RANDOM()', 'RANDOM()'];

	//--------------------------------------------------------------------

	/**
	 * ORDER BY
	 *
	 * @param    string $orderby
	 * @param    string $direction ASC, DESC or RANDOM
	 * @param    bool   $escape
	 *
	 * @return    BaseBuilder
	 */
	public function orderBy($orderby, $direction = '', $escape = null)
	{
		$direction = strtoupper(trim($direction));
		if ($direction === 'RANDOM')
		{
			if ( ! is_float($orderby) && ctype_digit((string) $orderby))
			{
				$orderby = (float) ($orderby > 1 ? "0.{$orderby}" : $orderby);
			}

			if (is_float($orderby))
			{
				$this->db->simpleQuery("SET SEED {$orderby}");
			}

			$orderby = $this->randomKeyword[0];
			$direction = '';
			$escape = false;
		}

		return parent::orderBy($orderby, $direction, $escape);
	}

	//--------------------------------------------------------------------

	/**
	 * Increments a numeric column by the specified value.
	 *
	 * @param string $column
	 * @param int    $value
	 *
	 * @return bool
	 */
	public function increment(string $column, int $value = 1)
	{
		$column = $this->db->protectIdentifiers($column);

		$sql = $this->_update($this->QBFrom[0], [$column => "to_number({$column}, '9999999') + {$value}"]);

		return $this->db->query($sql, $this->binds);
	}

	//--------------------------------------------------------------------

	/**
	 * Decrements a numeric column by the specified value.
	 *
	 * @param string $column
	 * @param int    $value
	 *
	 * @return bool
	 */
	public function decrement(string $column, int $value = 1)
	{
		$column = $this->db->protectIdentifiers($column);

		$sql = $this->_update($this->QBFrom[0], [$column => "to_number({$column}, '9999999') - {$value}"]);

		return $this->db->query($sql, $this->binds);
	}

	//--------------------------------------------------------------------

	/**
	 * Replace
	 *
	 * Compiles an replace into string and runs the query.
	 * Because PostgreSQL doesn't support the replace into command,
	 * we simply do a DELETE and an INSERT on the first key/value
	 * combo, assuming that it's either the primary key or a unique key.
	 *
	 * @param      array  $set   An associative array of insert values
	 * @param bool $returnSQL
	 *
	 * @return bool TRUE on success, FALSE on failure
	 * @throws DatabaseException
	 * @internal param true $bool returns the generated SQL, false executes the query.
	 *
	 */
	public function replace($set = null, $returnSQL = false)
	{
		if ($set !== null)
		{
			$this->set($set);
		}

		if (! $this->QBSet)
		{
			if (CI_DEBUG)
			{
				throw new DatabaseException('You must use the "set" method to update an entry.');
			}
			return false;
		}

		$table = $this->QBFrom[0];

		$set = $this->binds;
		$keys = array_keys($set);
		$values = array_values($set);

		$builder = $this->db->table($table);
		$exists = $builder->where("$keys[0] = $values[0]", null, false)->get()->getFirstRow();

		if (empty($exists))
		{
			$result = $builder->insert($set);
		}
		else
		{
			array_pop($set);
			$result = $builder->update($set, "$keys[0] = $values[0]");
		}

		unset($builder);
		$this->resetWrite();

		return $result;
	}

	//--------------------------------------------------------------------

	/**
	 * Delete
	 *
	 * Compiles a delete string and runs the query
	 *
	 * @param string $where
	 * @param null   $limit
	 * @param bool   $reset_data
	 * @param bool   $returnSQL
	 *
	 * @return mixed
	 * @throws DatabaseException
	 * @internal param the $mixed where clause
	 * @internal param the $mixed limit clause
	 * @internal param $bool
	 *
	 */
	public function delete($where = '', $limit = null, $reset_data = true, $returnSQL = false)
	{
		if ( ! empty($limit) || ! empty($this->QBLimit))
		{
			throw new DatabaseException('PostgreSQL does not allow LIMITs on DELETE queries.');
		}

		return parent::delete($where, $limit, $reset_data, $returnSQL);
	}

	//--------------------------------------------------------------------

	/**
	 * LIMIT string
	 *
	 * Generates a platform-specific LIMIT clause.
	 *
	 * @param    string $sql SQL Query
	 *
	 * @return    string
	 */
	protected function _limit($sql)
	{
		return $sql . ' LIMIT ' . $this->QBLimit . ($this->QBOffset ? " OFFSET {$this->QBOffset}" : '');
	}

	//--------------------------------------------------------------------

	/**
	 * Update statement
	 *
	 * Generates a platform-specific update string from the supplied data
	 *
	 * @param string $table
	 * @param array  $values
	 *
	 * @return string
	 * @throws DatabaseException
	 * @internal param the $string table name
	 * @internal param the $array update data
	 *
	 */
	protected function _update($table, $values)
	{
		if ( ! empty($this->QBLimit))
		{
			throw new DatabaseException('Postgres does not support LIMITs with UPDATE queries.');
		}

		$this->QBOrderBy = [];
		return parent::_update($table, $values);
	}

	//--------------------------------------------------------------------

	/**
	 * Update_Batch statement
	 *
	 * Generates a platform-specific batch update string from the supplied data
	 *
	 * @param    string $table  Table name
	 * @param    array  $values Update data
	 * @param    string $index  WHERE key
	 *
	 * @return    string
	 */
	protected function _updateBatch($table, $values, $index)
	{
		$ids = [];
		foreach ($values as $key => $val)
		{
			$ids[] = $val[$index];

			foreach (array_keys($val) as $field)
			{
				if ($field !== $index)
				{
					$final[$field][] = "WHEN {$val[$index]} THEN {$val[$field]}";
				}
			}
		}

		$cases = '';
		foreach ($final as $k => $v)
		{
			$cases .= "{$k} = (CASE {$index}\n"
					. implode("\n", $v)
					. "\nELSE {$k} END), ";
		}

		$this->where("{$index} IN(" . implode(',', $ids) . ')', null, false);

		return "UPDATE {$table} SET " . substr($cases, 0, -2) . $this->compileWhereHaving('QBWhere');
	}

	//--------------------------------------------------------------------

	/**
	 * Delete statement
	 *
	 * Generates a platform-specific delete string from the supplied data
	 *
	 * @param    string $table The table name
	 *
	 * @return    string
	 */
	protected function _delete($table)
	{
		$this->QBLimit = false;
		return parent::_delete($table);
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
	 * @param    string $table The table name
	 *
	 * @return    string
	 */
	protected function _truncate($table)
	{
		return 'TRUNCATE ' . $table . ' RESTART IDENTITY';
	}

	//--------------------------------------------------------------------

	/**
	 * Platform independent LIKE statement builder.
	 *
	 * In PostgreSQL, the ILIKE operator will perform case insensitive
	 * searches according to the current locale.
	 *
	 * @see https://www.postgresql.org/docs/9.2/static/functions-matching.html
	 *
	 * @param string|null $prefix
	 * @param string      $column
	 * @param string|null $not
	 * @param string      $bind
	 * @param bool        $insensitiveSearch
	 *
	 * @return string     $like_statement
	 */
	public function _like_statement(string $prefix = null, string $column, string $not = null, string $bind, bool $insensitiveSearch = false): string
	{
		$op = $insensitiveSearch === true ? 'ILIKE' : 'LIKE';

		return "{$prefix} {$column} {$not} {$op} :{$bind}:";
	}

	//--------------------------------------------------------------------
}
