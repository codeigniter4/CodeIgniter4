<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
 * Copyright (c) 2019-2020 CodeIgniter Foundation
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
 * @copyright  2019-2020 CodeIgniter Foundation
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

namespace CodeIgniter\Database\SQLite3;

use CodeIgniter\Database\Exceptions\DataException;

/**
 * Class Table
 *
 * Provides missing features for altering tables that are common
 * in other supported databases, but are missing from SQLite.
 * These are needed in order to support migrations during testing
 * when another database is used as the primary engine, but
 * SQLite in memory databases are used for faster test execution.
 *
 * @package CodeIgniter\Database\SQLite3
 */
class Table
{
	/**
	 * All of the fields this table represents.
	 *
	 * @var array
	 */
	protected $fields = [];

	/**
	 * All of the unique/primary keys in the table.
	 *
	 * @var array
	 */
	protected $keys = [];

	/**
	 * All of the foreign keys in the table.
	 *
	 * @var array
	 */
	protected $foreignKeys = [];

	/**
	 * The name of the table we're working with.
	 *
	 * @var string
	 */
	protected $tableName;

	/**
	 * The name of the table, with database prefix
	 *
	 * @var string
	 */
	protected $prefixedTableName;

	/**
	 * Database connection.
	 *
	 * @var Connection
	 */
	protected $db;

	/**
	 * Handle to our forge.
	 *
	 * @var Forge
	 */
	protected $forge;

	/**
	 * Table constructor.
	 *
	 * @param Connection $db
	 * @param Forge      $forge
	 */
	public function __construct(Connection $db, Forge $forge)
	{
		$this->db    = $db;
		$this->forge = $forge;
	}

	/**
	 * Reads an existing database table and
	 * collects all of the information needed to
	 * recreate this table.
	 *
	 * @param string $table
	 *
	 * @return \CodeIgniter\Database\SQLite3\Table
	 */
	public function fromTable(string $table)
	{
		$this->prefixedTableName = $table;

		// Remove the prefix, if any, since it's
		// already been added by the time we get here...
		$prefix = $this->db->DBPrefix; // @phpstan-ignore-line
		if (! empty($prefix))
		{
			if (strpos($table, $prefix) === 0)
			{
				$table = substr($table, strlen($prefix));
			}
		}

		if (! $this->db->tableExists($this->prefixedTableName))
		{
			throw DataException::forTableNotFound($this->prefixedTableName);
		}

		$this->tableName = $table;

		$this->fields = $this->formatFields($this->db->getFieldData($table));

		$this->keys = array_merge($this->keys, $this->formatKeys($this->db->getIndexData($table)));

		$this->foreignKeys = $this->db->getForeignKeyData($table);

		return $this;
	}

	/**
	 * Called after `fromTable` and any actions, like `dropColumn`, etc,
	 * to finalize the action. It creates a temp table, creates the new
	 * table with modifications, and copies the data over to the new table.
	 *
	 * @return boolean
	 */
	public function run(): bool
	{
		$this->db->query('PRAGMA foreign_keys = OFF');

		$this->db->transStart();

		$this->forge->renameTable($this->tableName, "temp_{$this->tableName}");

		$this->forge->reset();

		$this->createTable();

		$this->copyData();

		$this->forge->dropTable("temp_{$this->tableName}");

		$success = $this->db->transComplete();

		$this->db->query('PRAGMA foreign_keys = ON');

		return $success;
	}

	/**
	 * Drops columns from the table.
	 *
	 * @param string|array $columns
	 *
	 * @return \CodeIgniter\Database\SQLite3\Table
	 */
	public function dropColumn($columns)
	{
		//unset($this->fields[$column]);

		if (is_string($columns))
		{
			$columns = explode(',', $columns);
		}

		foreach ($columns as $column)
		{
			$column = trim($column);
			if (isset($this->fields[$column]))
			{
				unset($this->fields[$column]);
			}
		}

		return $this;
	}

	/**
	 * Modifies a field, including changing data type,
	 * renaming, etc.
	 *
	 * @param array $field
	 *
	 * @return \CodeIgniter\Database\SQLite3\Table
	 */
	public function modifyColumn(array $field)
	{
		$field = $field[0];

		$oldName = $field['name'];
		unset($field['name']);

		$this->fields[$oldName] = $field;

		return $this;
	}

	/**
	 * Drops a foreign key from this table so that
	 * it won't be recreated in the future.
	 *
	 * @param string $column
	 *
	 * @return \CodeIgniter\Database\SQLite3\Table
	 */
	public function dropForeignKey(string $column)
	{
		if (empty($this->foreignKeys))
		{
			return $this;
		}

		for ($i = 0; $i < count($this->foreignKeys); $i++)
		{
			if ($this->foreignKeys[$i]->table_name !== $this->tableName)
			{
				continue;
			}

			// The column name should be the first thing in the constraint name
			if (strpos($this->foreignKeys[$i]->constraint_name, $column) !== 0)
			{
				continue;
			}

			unset($this->foreignKeys[$i]);
		}

		return $this;
	}

	/**
	 * Creates the new table based on our current fields.
	 *
	 * @return mixed
	 */
	protected function createTable()
	{
		$this->dropIndexes();
		$this->db->resetDataCache();

		// Handle any modified columns.
		$fields = [];
		foreach ($this->fields as $name => $field)
		{
			if (isset($field['new_name']))
			{
				$fields[$field['new_name']] = $field;
				continue;
			}

			$fields[$name] = $field;
		}

		$this->forge->addField($fields);

		// Unique/Index keys
		if (is_array($this->keys))
		{
			foreach ($this->keys as $key)
			{
				switch ($key['type'])
				{
					case 'primary':
						$this->forge->addPrimaryKey($key['fields']);
						break;
					case 'unique':
						$this->forge->addUniqueKey($key['fields']);
						break;
					case 'index':
						$this->forge->addKey($key['fields']);
						break;
				}
			}
		}

		// Foreign Keys

		return $this->forge->createTable($this->tableName);
	}

	/**
	 * Copies data from our old table to the new one,
	 * taking care map data correctly based on any columns
	 * that have been renamed.
	 *
	 * @return void
	 */
	protected function copyData()
	{
		$exFields  = [];
		$newFields = [];

		foreach ($this->fields as $name => $details)
		{
			// Are we modifying the column?
			if (isset($details['new_name']))
			{
				$newFields[] = $details['new_name'];
			}
			else
			{
				$newFields[] = $name;
			}

			$exFields[] = $name;
		}

		$exFields  = implode(', ', $exFields);
		$newFields = implode(', ', $newFields);

		// @phpstan-ignore-next-line
		$this->db->query("INSERT INTO {$this->prefixedTableName}({$newFields}) SELECT {$exFields} FROM {$this->db->DBPrefix}temp_{$this->tableName}");
	}

	/**
	 * Converts fields retrieved from the database to
	 * the format needed for creating fields with Forge.
	 *
	 * @param array|boolean $fields
	 *
	 * @return mixed
	 */
	protected function formatFields($fields)
	{
		if (! is_array($fields))
		{
			return $fields;
		}

		$return = [];

		foreach ($fields as $field)
		{
			$return[$field->name] = [
				'type'     => $field->type,
				'default'  => $field->default,
				'nullable' => $field->nullable,
			];

			if ($field->primary_key)
			{
				$this->keys[$field->name] = [
					'fields' => [$field->name],
					'type'   => 'primary',
				];
			}
		}

		return $return;
	}

	/**
	 * Converts keys retrieved from the database to
	 * the format needed to create later.
	 *
	 * @param mixed $keys
	 *
	 * @return mixed
	 */
	protected function formatKeys($keys)
	{
		if (! is_array($keys))
		{
			return $keys;
		}

		$return = [];

		foreach ($keys as $name => $key)
		{
			$return[$name] = [
				'fields' => $key->fields,
				'type'   => 'index',
			];
		}

		return $return;
	}

	/**
	 * Attempts to drop all indexes and constraints
	 * from the database for this table.
	 *
	 * @return null|void
	 */
	protected function dropIndexes()
	{
		if (! is_array($this->keys) || ! count($this->keys))
		{
			return;
		}

		foreach ($this->keys as $name => $key)
		{
			if ($key['type'] === 'primary' || $key['type'] === 'unique')
			{
				continue;
			}

			$this->db->query("DROP INDEX IF EXISTS '{$name}'");
		}
	}
}
