<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Database\MySQLi;

use CodeIgniter\Database\Forge as BaseForge;

/**
 * Forge for MySQLi
 */
class Forge extends BaseForge
{
	/**
	 * CREATE DATABASE statement
	 *
	 * @var string
	 */
	protected $createDatabaseStr = 'CREATE DATABASE %s CHARACTER SET %s COLLATE %s';

	/**
	 * CREATE DATABASE IF statement
	 *
	 * @var string
	 */
	protected $createDatabaseIfStr = 'CREATE DATABASE IF NOT EXISTS %s CHARACTER SET %s COLLATE %s';

	/**
	 * DROP CONSTRAINT statement
	 *
	 * @var string
	 */
	protected $dropConstraintStr = 'ALTER TABLE %s DROP FOREIGN KEY %s';

	/**
	 * CREATE TABLE keys flag
	 *
	 * Whether table keys are created from within the
	 * CREATE TABLE statement.
	 *
	 * @var boolean
	 */
	protected $createTableKeys = true;

	/**
	 * UNSIGNED support
	 *
	 * @var array
	 */
	protected $_unsigned = [
		'TINYINT',
		'SMALLINT',
		'MEDIUMINT',
		'INT',
		'INTEGER',
		'BIGINT',
		'REAL',
		'DOUBLE',
		'DOUBLE PRECISION',
		'FLOAT',
		'DECIMAL',
		'NUMERIC',
	];

	/**
	 * Table Options list which required to be quoted
	 *
	 * @var array
	 */
	protected $_quoted_table_options = [
		'COMMENT',
		'COMPRESSION',
		'CONNECTION',
		'DATA DIRECTORY',
		'INDEX DIRECTORY',
		'ENCRYPTION',
		'PASSWORD',
	];

	/**
	 * NULL value representation in CREATE/ALTER TABLE statements
	 *
	 * @var string
	 */
	protected $_null = 'NULL';

	//--------------------------------------------------------------------

	/**
	 * CREATE TABLE attributes
	 *
	 * @param  array $attributes Associative array of table attributes
	 * @return string
	 */
	protected function _createTableAttributes(array $attributes): string
	{
		$sql = '';

		foreach (array_keys($attributes) as $key)
		{
			if (is_string($key))
			{
				$sql .= ' ' . strtoupper($key) . ' = ';

				if (in_array(strtoupper($key), $this->_quoted_table_options, true))
				{
					$sql .= $this->db->escape($attributes[$key]);
				}
				else
				{
					$sql .= $this->db->escapeString($attributes[$key]);
				}
			}
		}

		if (! empty($this->db->charset) && ! strpos($sql, 'CHARACTER SET') && ! strpos($sql, 'CHARSET'))
		{
			$sql .= ' DEFAULT CHARACTER SET = ' . $this->db->escapeString($this->db->charset);
		}

		if (! empty($this->db->DBCollat) && ! strpos($sql, 'COLLATE'))
		{
			$sql .= ' COLLATE = ' . $this->db->escapeString($this->db->DBCollat);
		}

		return $sql;
	}

	//--------------------------------------------------------------------

	/**
	 * ALTER TABLE
	 *
	 * @param  string $alterType ALTER type
	 * @param  string $table     Table name
	 * @param  mixed  $field     Column definition
	 * @return string|string[]
	 */
	protected function _alterTable(string $alterType, string $table, $field)
	{
		if ($alterType === 'DROP')
		{
			return parent::_alterTable($alterType, $table, $field);
		}

		$sql = 'ALTER TABLE ' . $this->db->escapeIdentifiers($table);
		foreach ($field as $i => $data)
		{
			if ($data['_literal'] !== false)
			{
				$field[$i] = ($alterType === 'ADD') ? "\n\tADD " . $data['_literal'] : "\n\tMODIFY " . $data['_literal'];
			}
			else
			{
				if ($alterType === 'ADD')
				{
					$field[$i]['_literal'] = "\n\tADD ";
				}
				else
				{
					$field[$i]['_literal'] = empty($data['new_name']) ? "\n\tMODIFY " : "\n\tCHANGE ";
				}

				$field[$i] = $field[$i]['_literal'] . $this->_processColumn($field[$i]);
			}
		}

		return [$sql . implode(',', $field)];
	}

	//--------------------------------------------------------------------

	/**
	 * Process column
	 *
	 * @param  array $field
	 * @return string
	 */
	protected function _processColumn(array $field): string
	{
		$extraClause = isset($field['after']) ? ' AFTER ' . $this->db->escapeIdentifiers($field['after']) : '';

		if (empty($extraClause) && isset($field['first']) && $field['first'] === true)
		{
			$extraClause = ' FIRST';
		}

		return $this->db->escapeIdentifiers($field['name'])
				. (empty($field['new_name']) ? '' : ' ' . $this->db->escapeIdentifiers($field['new_name']))
				. ' ' . $field['type'] . $field['length']
				. $field['unsigned']
				. $field['null']
				. $field['default']
				. $field['auto_increment']
				. $field['unique']
				. (empty($field['comment']) ? '' : ' COMMENT ' . $field['comment'])
				. $extraClause;
	}

	//--------------------------------------------------------------------

	/**
	 * Process indexes
	 *
	 * @param  string $table (ignored)
	 * @return string
	 */
	protected function _processIndexes(string $table): string
	{
		$sql = '';

		for ($i = 0, $c = count($this->keys); $i < $c; $i ++)
		{
			if (is_array($this->keys[$i]))
			{
				for ($i2 = 0, $c2 = count($this->keys[$i]); $i2 < $c2; $i2 ++)
				{
					if (! isset($this->fields[$this->keys[$i][$i2]]))
					{
						unset($this->keys[$i][$i2]);
						continue;
					}
				}
			}
			elseif (! isset($this->fields[$this->keys[$i]]))
			{
				unset($this->keys[$i]);
				continue;
			}

			// @phpstan-ignore-next-line
			is_array($this->keys[$i]) || $this->keys[$i] = [$this->keys[$i]];

			$unique = in_array($i, $this->uniqueKeys, true) ? 'UNIQUE ' : '';

			$sql .= ",\n\t{$unique}KEY " . $this->db->escapeIdentifiers(implode('_', $this->keys[$i]))
				. ' (' . implode(', ', $this->db->escapeIdentifiers($this->keys[$i])) . ')';
		}

		$this->keys = [];

		return $sql;
	}
}
