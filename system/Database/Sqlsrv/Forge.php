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

/**
 * Forge for Sqlsrv
 */
class Forge extends \CodeIgniter\Database\Forge {

	/**
	 * DROP CONSTRAINT statement
	 *
	 * @var string
	 */
	protected $dropConstraintStr = 'ALTER TABLE %s DROP CONSTRAINT %s';

	/**
	 * CREATE DATABASE IF statement
	 *
	 * @var string
	 */
	/// TODO: missing charset, collat & check for existant
	protected $createDatabaseIfStr = "DECLARE @DBName VARCHAR(255) = '%s'\nDECLARE @SQL VARCHAR(max) = 'IF DB_ID( ''' + @DBName + ''' ) IS NULL CREATE DATABASE ' + @DBName\nEXEC( @SQL )";
	/// TODO: missing charset & collat
	protected $createDatabaseStr = 'CREATE DATABASE %s ';

	/**
	 * CHECK DATABASE EXIST statement
	 *
	 * @var string
	 */
	protected $checkDatabaseExistStr = 'IF DB_ID( %s ) IS NOT NULL SELECT 1';

	/**
	 * RENAME TABLE statement
	 *
	 * While the below statement would work, it returns an error.
	 * Also MS recommends dropping and dropping and re-creating the table.
	 * https://docs.microsoft.com/en-us/sql/relational-databases/system-stored-procedures/sp-rename-transact-sql?view=sql-server-2017
	 * 'EXEC sp_rename %s , %s ;'
	 *
	 * @var string
	 */
	protected $renameTableStr = 'EXEC sp_rename %s , %s ;';

	/**
	 * UNSIGNED support
	 *
	 * @var array
	 */
	protected $unsigned = [
		'TINYINT'  => 'SMALLINT',
		'SMALLINT' => 'INT',
		'INT'      => 'BIGINT',
		'REAL'     => 'FLOAT',
	];

	/**
	 * CREATE TABLE IF statement
	 *
	 * @var string
	 */
	protected $createTableIfStr = "IF NOT EXISTS (SELECT * FROM sysobjects WHERE ID = object_id(N'%s') AND OBJECTPROPERTY(id, N'IsUserTable') = 1)\nCREATE TABLE";

	/**
	 * DROP TABLE IF statement
	 *
	 * @var string
	 */
	protected $_drop_table_if = "IF EXISTS (SELECT * FROM sysobjects WHERE ID = object_id(N'%s') AND OBJECTPROPERTY(id, N'IsUserTable') = 1)\nDROP TABLE";
	protected $createTableStr = "%s %s (%s\n) ";

	//--------------------------------------------------------------------

	/**
	 * CREATE TABLE attributes
	 *
	 * @param  array $attributes Associative array of table attributes
	 * @return string
	 */
	protected function _createTableAttributes(array $attributes): string
	{
		return '';
	}

	//--------------------------------------------------------------------

	/**
	 * ALTER TABLE
	 *
	 * @param string $alter_type ALTER type
	 * @param string $table      Table name
	 * @param mixed  $field      Column definition
	 *
	 * @return string|array
	 */
	protected function _alterTable(string $alter_type, string $table, $field)
	{
		if (in_array($alter_type, ['ADD'], true))
		{
			return parent::_alterTable($alter_type, $table, $field);
		}

		// Handle DROP here
		if ('DROP' === $alter_type)
		{
			// check if fields are part of any indexes
			$indexData = $this->db->getIndexData($table);

			foreach ($indexData as $index)
			{
				if (is_string($field))
				{
					$field = explode(',', $field);
				}

				$fld = array_intersect($field, $index->fields);

				// Drop index if field is part of an index
				if (! empty($fld))
				{
					$this->_dropIndex($table, $index);
				}
			}

			$sql = 'ALTER TABLE [' . $table . '] DROP ';

			$fields = array_map(function ($item) {
				return 'COLUMN [' . trim($item) . ']';
			}, $field);

			return $sql .= implode(',', $fields);
		}

		$sql = 'ALTER TABLE ' . $this->db->escapeIdentifiers($table);

		$sqls = [];
		foreach ($field as $data)
		{
			if ($data['_literal'] !== false)
			{
				return false;
			}

			if (isset($data['type']))
			{
				$sqls[] = $sql . ' ALTER COLUMN ' . $this->db->escapeIdentifiers($data['name'])
						. " {$data['type']}{$data['length']}";
			}

			if (! empty($data['default']))
			{
				$sqls[] = $sql . ' ALTER COLUMN ADD CONSTRAINT ' . $this->db->escapeIdentifiers($data['name']) . '_def'
						. " DEFAULT {$data['default']} FOR " . $this->db->escapeIdentifiers($data['name']);
			}

			if (isset($data['null']))
			{
				$sqls[] = $sql . ' ALTER COLUMN ' . $this->db->escapeIdentifiers($data['name'])
						. ($data['null'] === true ? ' DROP' : '') . " {$data['type']}{$data['length']} NOT NULL";
			}

			if (! empty($data['comment']))
			{
				$sqls[] = 'EXEC sys.sp_addextendedproperty '
						. "@name=N'Caption', @value=N'" . $data['comment'] . "' , "
						. "@level0type=N'SCHEMA',@level0name=N'" . $this->db->schema . "', "
						. "@level1type=N'TABLE',@level1name=N'" . $this->db->escapeIdentifiers($table) . "', "
						. "@level2type=N'COLUMN',@level2name=N'" . $this->db->escapeIdentifiers($data['name']) . "'";
			}
			if (! empty($data['new_name']))
			{
				// EXEC sp_rename '[dbo].[db_misc].[value]', 'valueasdasd', 'COLUMN';
				$sqls[] = "EXEC sp_rename  '[" . $this->db->schema . '].[' . $table . '].[' . $data['name'] . "]' , '" . $data['new_name'] . "', 'COLUMN';";
			}
		}

		return $sqls;
	}

	//--------------------------------------------------------------------

	protected function _dropIndex(string $table, object $indexData)
	{
		if ('PRIMARY' === $indexData->type)
		{
			$sql = 'ALTER TABLE [' . $this->db->schema . '].[' . $table . '] DROP [' . $indexData->name . ']';
		}
		else
		{
			$sql = 'DROP INDEX [' . $indexData->name . '] ON [' . $this->db->schema . '].[' . $table . ']';
		}

		return $this->db->simpleQuery($sql);
	}

	/**
	 * Process column
	 *
	 * @param  array $field
	 * @return string
	 */
	protected function _processColumn(array $field): string
	{
		return $this->db->escapeIdentifiers($field['name'])
				. (empty($field['new_name']) ? '' : ' ' . $this->db->escapeIdentifiers($field['new_name']))
				. ' ' . $field['type'] . $field['length']
				. $field['default']
				. $field['null']
				. $field['auto_increment']
				. '' // (empty($field['comment']) ? '' : ' COMMENT ' . $field['comment'])
				. $field['unique'];
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
			'RESTRICT',
			'SET DEFAULT',
		];

		if (count($this->foreignKeys) > 0)
		{
			foreach ($this->foreignKeys as $field => $fkey)
			{
				// TODO: Review and fix this.

				/*
				 *  ALTER TABLE [dbo].[NewTable]  ADD FOREIGN KEY ([system]) REFERENCES [dbo].[lofasz] ([asdasdasd])
				 * CONSTRAINT FK_TempSales_SalesReason FOREIGN KEY (TempID)  REFERENCES Sales.SalesReason (SalesReasonID) ON DELETE CASCADE  ON UPDATE CASCADE
				 */
				$name_index = $table . '_' . $field . '_foreign';

				$sql .= ",\n\t CONSTRAINT " . $this->db->escapeIdentifiers($name_index)
						. ' FOREIGN KEY (' . $this->db->escapeIdentifiers($field) . ') '
						. ' REFERENCES ' . $this->db->escapeIdentifiers($this->db->getPrefix() . $fkey['table']) . ' (' . $this->db->escapeIdentifiers($fkey['field']) . ')';

				if ($fkey['onDelete'] !== false && in_array($fkey['onDelete'], $allowActions))
				{
					$sql .= ' ON DELETE ' . $fkey['onDelete'];
				}

				if ($fkey['onUpdate'] !== false && in_array($fkey['onUpdate'], $allowActions))
				{
					$sql .= ' ON UPDATE ' . $fkey['onUpdate'];
				}
			}
		}

		return $sql;
	}

	//--------------------------------------------------------------------

	/**
	 * Process primary keys
	 *
	 * @param string $table Table name
	 *
	 * @return string
	 */
	protected function _processPrimaryKeys(string $table): string
	{
		for ($i = 0, $c = count($this->primaryKeys); $i < $c; $i++)
		{
			if (! isset($this->fields[$this->primaryKeys[$i]]))
			{
				unset($this->primaryKeys[$i]);
			}
		}

		if (count($this->primaryKeys) > 0)
		{
			$sql = ",\n\tCONSTRAINT " . $this->db->escapeIdentifiers('pk_' . $table)
					. ' PRIMARY KEY(' . implode(', ', $this->db->escapeIdentifiers($this->primaryKeys)) . ')';
		}

		return $sql ?? '';
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
		if (isset($attributes['CONSTRAINT']) && stripos($attributes['TYPE'], 'int') !== false)
		{
			$attributes['CONSTRAINT'] = null;
		}

		switch (strtoupper($attributes['TYPE']))
		{
			case 'MEDIUMINT':
				$attributes['TYPE']     = 'INTEGER';
				$attributes['UNSIGNED'] = false;
				break;
			case 'INTEGER':
				$attributes['TYPE'] = 'INT';

				break;
			case 'ENUM':
				$attributes['TYPE']       = 'TEXT';
				$attributes['CONSTRAINT'] = null;

				break;
			/* case 'DATETIME':
			  $attributes['TYPE'] = 'TIMESTAMP';
			  break; */
			case 'TIMESTAMP':
				$attributes['TYPE'] = 'DATETIME';
				break;
			default:
				break;
		}
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
		if (! empty($attributes['AUTO_INCREMENT']) && $attributes['AUTO_INCREMENT'] === true && stripos($field['type'], 'INT') !== false)
		{
			$field['auto_increment'] = ' IDENTITY(1,1)';
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
		$sql = 'DROP TABLE';

		if ($if_exists)
		{
			$sql .= ' IF EXISTS ';
		}

		$table = ' [' . $this->db->database . '].[' . $this->db->schema . '][' . $table . '] ';

		$sql .= $table;

		if ($cascade === true)
		{
			$sql .= ' CASCADE';
		}

		return $sql;
	}

	//--------------------------------------------------------------------
}
