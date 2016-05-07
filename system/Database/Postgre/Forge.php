<?php namespace CodeIgniter\Database\Postgre;

class Forge extends \CodeIgniter\Database\Forge
{
	/**
	 * UNSIGNED support
	 *
	 * @var    array
	 */
	protected $_unsigned = [
		'INT2'		=> 'INTEGER',
		'SMALLINT'	=> 'INTEGER',
		'INT'		=> 'BIGINT',
		'INT4'		=> 'BIGINT',
		'INTEGER'	=> 'BIGINT',
		'INT8'		=> 'NUMERIC',
		'BIGINT'	=> 'NUMERIC',
		'REAL'		=> 'DOUBLE PRECISION',
		'FLOAT'		=> 'DOUBLE PRECISION'
	];

	/**
	 * NULL value representation in CREATE/ALTER TABLE statements
	 *
	 * @var    string
	 */
	protected $_null = 'NULL';

	//--------------------------------------------------------------------

	/**
	 * ALTER TABLE
	 *
	 * @param    string $alter_type ALTER type
	 * @param    string $table      Table name
	 * @param    mixed  $field      Column definition
	 *
	 * @return    string|string[]
	 */
	protected function _alterTable($alter_type, $table, $field)
	{
		if (in_array($alter_type, ['DROP', 'ADD'], true))
		{
			return parent::_alterTable($alter_type, $table, $field);
		}

		$sql = 'ALTER TABLE '.$this->db->escapeIdentifiers($table);
		$sqls = [];
		for ($i = 0, $c = count($field); $i < $c; $i++)
		{
			if ($field[$i]['_literal'] !== false)
			{
				return false;
			}

			if (version_compare($this->db->getVersion(), '8', '>=') && isset($field[$i]['type']))
			{
				$sqls[] = $sql.' ALTER COLUMN '.$this->db->escapeIdentifiers($field[$i]['name'])
					." TYPE {$field[$i]['type']}{$field[$i]['length']}";
			}

			if ( ! empty($field[$i]['default']))
			{
				$sqls[] = $sql.' ALTER COLUMN '.$this->db->escapeIdentifiers($field[$i]['name'])
					." SET DEFAULT {$field[$i]['default']}";
			}

			if (isset($field[$i]['null']))
			{
				$sqls[] = $sql.' ALTER COLUMN '.$this->db->escapeIdentifiers($field[$i]['name'])
					.($field[$i]['null'] === true ? ' DROP' : ' SET'). ' NOT NULL';
			}

			if ( ! empty($field[$i]['new_name']))
			{
				$sqls[] = $sql.' RENAME COLUMN '.$this->db->escapeIdentifiers($field[$i]['name'])
					.' TO '.$this->db->escapeIdentifiers($field[$i]['new_name']);
			}

			if ( ! empty($field[$i]['comment']))
			{
				$sqls[] = 'COMMENT ON COLUMN'.$this->db->escapeIdentifiers($table)
					.'.'.$this->db->escapeIdentifiers($field[$i]['name'])
					." IS {$field[$i]['comment']}";
			}
		}

		return $sqls;
	}

	//--------------------------------------------------------------------

	/**
	 * Field attribute TYPE
	 *
	 * Performs a data type mapping between different databases.
	 *
	 * @param    array &$attributes
	 *
	 * @return    void
	 */
	protected function _attributeType(&$attributes)
	{
		// Reset field lengths for data types that don't support it
		if (isset($attributes['CONSTRAINT']) && stripos($attributes['TYPE'], 'int') !== false)
		{
			$attributes['CONSTRAINT'] = null;
		}

		switch (strtoupper($attributes['TYPE']))
		{
			case 'TINYINT':
				$attributes['TYPE'] = 'SMALLINT';
				$attributes['UNSIGNED'] = false;
				return;
			case 'MEDIUMINT':
				$attributes['TYPE'] = 'INTEGER';
				$attributes['UNSIGNED'] = false;
				return;
			default:
				return;
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Field attribute AUTO_INCREMENT
	 *
	 * @param    array &$attributes
	 * @param    array &$field
	 *
	 * @return    void
	 */
	protected function _attributeAutoIncrement(&$attributes, &$field)
	{
		if ( ! empty($attributes['AUTO_INCREMENT']) && $attributes['AUTO_INCREMENT'] === true)
		{
			$field['type'] = $field['type'] === 'NUMERIC' ? 'BIGSERIAL' : 'SERIAL';
		}
	}

	//--------------------------------------------------------------------
}
