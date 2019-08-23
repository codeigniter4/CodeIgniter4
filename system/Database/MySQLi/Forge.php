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

namespace CodeIgniter\Database\MySQLi;

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
	protected $createDatabaseStr = 'CREATE DATABASE %s CHARACTER SET %s COLLATE %s';

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

				if (in_array(strtoupper($key), $this->_quoted_table_options))
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
	 * @param  string $alter_type ALTER type
	 * @param  string $table      Table name
	 * @param  mixed  $field      Column definition
	 * @return string|string[]
	 */
	protected function _alterTable(string $alter_type, string $table, $field)
	{
		if ($alter_type === 'DROP')
		{
			return parent::_alterTable($alter_type, $table, $field);
		}

		$sql = 'ALTER TABLE ' . $this->db->escapeIdentifiers($table);
		foreach ($field as $i => $data)
		{
			if ($data['_literal'] !== false)
			{
				$field[$i] = ($alter_type === 'ADD') ? "\n\tADD " . $data['_literal'] : "\n\tMODIFY " . $data['_literal'];
			}
			else
			{
				if ($alter_type === 'ADD')
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
		$extra_clause = isset($field['after']) ? ' AFTER ' . $this->db->escapeIdentifiers($field['after']) : '';

		if (empty($extra_clause) && isset($field['first']) && $field['first'] === true)
		{
			$extra_clause = ' FIRST';
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
				. $extra_clause;
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

			is_array($this->keys[$i]) || $this->keys[$i] = [$this->keys[$i]];

			$unique = in_array($i, $this->uniqueKeys) ? 'UNIQUE ' : '';

			$sql .= ",\n\t{$unique}KEY " . $this->db->escapeIdentifiers(implode('_', $this->keys[$i]))
				. ' (' . implode(', ', $this->db->escapeIdentifiers($this->keys[$i])) . ')';
		}

		$this->keys = [];

		return $sql;
	}

	//--------------------------------------------------------------------
}
