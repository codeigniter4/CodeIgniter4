<?php namespace CodeIgniter\Database\Postgre;

use CodeIgniter\Database\BaseBuilder;

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
	 * @param      array     an associative array of insert values
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

		if (count($this->QBSet) === 0)
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
			$result = $builder->insert($set, false);
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
		return $sql.' LIMIT '.$this->QBLimit.($this->QBOffset ? " OFFSET {$this->QBOffset}" : '');
	}

	//--------------------------------------------------------------------

	/**
	 * Update statement
	 *
	 * Generates a platform-specific update string from the supplied data
	 *
	 * @param    string    the table name
	 * @param    array     the update data
	 *
	 * @return    string
	 */
	protected function _update($table, $values)
	{
		$this->QBLimit = false;
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
				.implode("\n", $v)
				."\nELSE {$k} END), ";
		}

		$this->where("{$index} IN(".implode(',', $ids).')', null, false);

		return "UPDATE {$table} SET ".substr($cases, 0, -2).$this->compileWhereHaving('QBWhere');
	}

	//--------------------------------------------------------------------

	/**
	 * Delete statement
	 *
	 * Generates a platform-specific delete string from the supplied data
	 *
	 * @param    string    the table name
	 *
	 * @return    string
	 */
	protected function _delete($table)
	{
		$this->QBLimit = false;
		return parent::_delete($table);
	}

	//--------------------------------------------------------------------

}
