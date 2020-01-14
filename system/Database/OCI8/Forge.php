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
 * @since      Version 4.0.0
 * @filesource
 */

namespace CodeIgniter\Database\OCI8;

/**
 * Forge for MySQLi
 */
class Forge extends \CodeIgniter\Database\Forge
{

	/**
	 * CREATE DATABASE statement
	 *
	 * @var string
	 */
	protected $createDatabaseStr = false;

	/**
	 * CREATE TABLE IF statement
	 *
	 * @var string
	 */
	protected $createTableIfStr = false;

	/**
	 * DROP TABLE IF EXISTS statement
	 *
	 * @var string
	 */
	protected $dropTableIfStr = false;

	/**
	 * DROP DATABASE statement
	 *
	 * @var string
	 */
	protected $dropDatabaseStr = false;

	/**
	 * UNSIGNED support
	 *
	 * @var boolean|array
	 */
	protected $unsigned = false;

	/**
	 * NULL value representation in CREATE/ALTER TABLE statements
	 *
	 * @var string
	 */
	protected $null = 'NULL';

	/**
	 * RENAME TABLE statement
	 *
	 * @var string
	 */
	protected $renameTableStr = 'ALTER TABLE %s RENAME TO %s';

	/**
	 * DROP CONSTRAINT statement
	 *
	 * @var string
	 */
	protected $dropConstraintStr = 'ALTER TABLE %s DROP CONSTRAINT %s';

	//--------------------------------------------------------------------

	/**
	 * ALTER TABLE
	 *
	 * @param string $alter_type ALTER type
	 * @param string $table      Table name
	 * @param mixed  $field      Column definition
	 *
	 * @return string|string[]
	 */
	protected function _alterTable(string $alter_type, string $table, $field)
	{
		if ($alter_type === 'DROP')
		{
			return parent::_alterTable($alter_type, $table, $field);
		}
		elseif ($alter_type === 'CHANGE')
		{
			$alter_type = 'MODIFY';
		}

		$sql          = 'ALTER TABLE ' . $this->db->escapeIdentifiers($table);
		$nullable_map = array_column($this->db->getFieldData($table), 'nullable', 'name');
		$sqls         = [];
		for ($i = 0, $c = count($field); $i < $c; $i++)
		{
			if ($alter_type === 'MODIFY')
			{
				// If a null constraint is added to a column with a null constraint,
				// ORA-01451 will occur,
				// so add null constraint is used only when it is different from the current null constraint.
				$is_want_to_add_null  = (strpos($field[$i]['null'], ' NOT') === false);
				$current_null_addable = $nullable_map[$field[$i]['name']];

				if ($is_want_to_add_null === $current_null_addable)
				{
					$field[$i]['null'] = '';
				}
			}

			if ($field[$i]['_literal'] !== false)
			{
				$field[$i] = "\n\t" . $field[$i]['_literal'];
			}
			else
			{
				$field[$i]['_literal'] = "\n\t" . $this->_processColumn($field[$i]);

				if (! empty($field[$i]['comment']))
				{
					$sqls[] = 'COMMENT ON COLUMN '
						. $this->db->escapeIdentifiers($table) . '.' . $this->db->escapeIdentifiers($field[$i]['name'])
						. ' IS ' . $field[$i]['comment'];
				}

				if ($alter_type === 'MODIFY' && ! empty($field[$i]['new_name']))
				{
					$sqls[] = $sql . ' RENAME COLUMN ' . $this->db->escapeIdentifiers($field[$i]['name'])
						. ' TO ' . $this->db->escapeIdentifiers($field[$i]['new_name']);
				}

				$field[$i] = "\n\t" . $field[$i]['_literal'];
			}
		}

		$sql .= ' ' . $alter_type . ' ';
		$sql .= (count($field) === 1)
				? $field[0]
				: '(' . implode(',', $field) . ')';

		// RENAME COLUMN must be executed after MODIFY
		array_unshift($sqls, $sql);
		return $sqls;
	}

	//--------------------------------------------------------------------

	/**
	 * Field attribute AUTO_INCREMENT
	 *
	 * @param array &$attributes
	 * @param array &$field
	 *
	 * @return void
	 */
	protected function _attributeAutoIncrement(array &$attributes, array &$field)
	{
		if (! empty($attributes['AUTO_INCREMENT']) && $attributes['AUTO_INCREMENT'] === true
			&& stripos($field['type'], 'NUMBER') !== false
			&& version_compare($this->db->getVersion(), '12.1', '>=')
		)
		{
			$field['auto_increment'] = ' GENERATED BY DEFAULT AS IDENTITY';
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Process column
	 *
	 * @param array $field
	 *
	 * @return string
	 */
	protected function _processColumn(array $field): string
	{
		return $this->db->escapeIdentifiers($field['name'])
			   . ' ' . $field['type'] . $field['length']
			   . $field['unsigned']
			   . $field['default']
			   . $field['auto_increment']
			   . $field['null']
			   . $field['unique'];
	}

	//--------------------------------------------------------------------

	/**
	 * Field attribute TYPE
	 *
	 * Performs a data type mapping between different databases.
	 *
	 * @param array &$attributes
	 *
	 * @return void
	 */
	protected function _attributeType(array &$attributes)
	{
		// Reset field lengths for data types that don't support it
		// Usually overridden by drivers
		switch (strtoupper($attributes['TYPE']))
		{
			case 'TINYINT':
				$attributes['CONSTRAINT'] = $attributes['CONSTRAINT'] ?? 3;
			case 'SMALLINT':
				$attributes['CONSTRAINT'] = $attributes['CONSTRAINT'] ?? 5;
			case 'MEDIUMINT':
				$attributes['CONSTRAINT'] = $attributes['CONSTRAINT'] ?? 7;
			case 'INT':
			case 'INTEGER':
				$attributes['CONSTRAINT'] = $attributes['CONSTRAINT'] ?? 11;
			case 'BIGINT':
				$attributes['CONSTRAINT'] = $attributes['CONSTRAINT'] ?? 19;
			case 'NUMERIC':
				$attributes['TYPE'] = 'NUMBER';
				return;
			case 'DATETIME':
				$attributes['TYPE'] = 'DATE';
				return;
			case 'TEXT':
			case 'VARCHAR':
				$attributes['TYPE']       = 'VARCHAR2';
				$attributes['CONSTRAINT'] = $attributes['CONSTRAINT'] ?? 255;
				return;
			default: return;
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Drop Table
	 *
	 * Generates a platform-specific DROP TABLE string
	 *
	 * @param string  $table     Table name
	 * @param boolean $if_exists Whether to add an IF EXISTS condition
	 * @param boolean $cascade
	 *
	 * @return string
	 */
	protected function _dropTable(string $table, bool $if_exists, bool $cascade): string
	{
		$sql = parent::_dropTable($table, $if_exists, $cascade);

		if ($sql !== '' && $cascade === true)
		{
			$sql .= ' CASCADE CONSTRAINTS PURGE';
		}
		elseif ($sql !== '')
		{
			$sql .= ' PURGE';
		}

		return $sql;
	}

	//--------------------------------------------------------------------

	/**
	 * Process foreign keys
	 *
	 * @param string $table Table name
	 *
	 * @return string
	 */
	protected function _processForeignKeys(string $table): string
	{
		$sql = '';

		$allowActions = [
			'CASCADE',
			'SET NULL',
			'NO ACTION',
		];

		if (count($this->foreignKeys) > 0)
		{
			foreach ($this->foreignKeys as $field => $fkey)
			{
				$name_index = $table . '_' . $field . '_fk';

				$sql .= ",\n\tCONSTRAINT " . $this->db->escapeIdentifiers($name_index)
					. ' FOREIGN KEY(' . $this->db->escapeIdentifiers($field) . ') REFERENCES ' . $this->db->escapeIdentifiers($this->db->DBPrefix . $fkey['table']) . ' (' . $this->db->escapeIdentifiers($fkey['field']) . ')';

				if ($fkey['onDelete'] !== false && in_array($fkey['onDelete'], $allowActions))
				{
					$sql .= ' ON DELETE ' . $fkey['onDelete'];
				}
			}
		}

		return $sql;
	}
}
