<?php namespace CodeIgniter\Database;

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
use \CodeIgniter\Database\Exceptions\DatabaseException;

/**
 * Class Forge
 */
class Forge
{

	/**
	 * The active database connection.
	 *
	 * @var BaseConnection
	 */
	protected $db;

	/**
	 * List of fields.
	 *
	 * @var array
	 */
	protected $fields = [];

	/**
	 * List of keys.
	 *
	 * @var array
	 */
	protected $keys = [];

	/**
	 * List of unique keys.
	 * @var array
	 */
	protected $uniqueKeys = [];

	/**
	 * List of primary keys.
	 *
	 * @var array
	 */
	protected $primaryKeys = [];

	/**
	 * List of foreign keys.
	 *
	 * @var type
	 */
	protected $foreignKeys = [];

	/**
	 * Character set used.
	 *
	 * @var string
	 */
	protected $charset = '';

	//--------------------------------------------------------------------

	/**
	 * CREATE DATABASE statement
	 *
	 * @var    string
	 */
	protected $createDatabaseStr = 'CREATE DATABASE %s';

	/**
	 * DROP DATABASE statement
	 *
	 * @var    string
	 */
	protected $dropDatabaseStr = 'DROP DATABASE %s';

	/**
	 * CREATE TABLE statement
	 *
	 * @var    string
	 */
	protected $createTableStr = "%s %s (%s\n)";

	/**
	 * CREATE TABLE IF statement
	 *
	 * @var    string
	 */
	protected $createTableIfStr = 'CREATE TABLE IF NOT EXISTS';

	/**
	 * CREATE TABLE keys flag
	 *
	 * Whether table keys are created from within the
	 * CREATE TABLE statement.
	 *
	 * @var    bool
	 */
	protected $createTableKeys = false;

	/**
	 * DROP TABLE IF EXISTS statement
	 *
	 * @var    string
	 */
	protected $dropTableIfStr = 'DROP TABLE IF EXISTS';

	/**
	 * RENAME TABLE statement
	 *
	 * @var    string
	 */
	protected $renameTableStr = 'ALTER TABLE %s RENAME TO %s;';

	/**
	 * UNSIGNED support
	 *
	 * @var    bool|array
	 */
	protected $unsigned = true;

	/**
	 * NULL value representation in CREATE/ALTER TABLE statements
	 *
	 * @var    string
	 */
	protected $null = '';

	/**
	 * DEFAULT value representation in CREATE/ALTER TABLE statements
	 *
	 * @var    string
	 */
	protected $default = ' DEFAULT ';

	//--------------------------------------------------------------------

	/**
	 * Constructor.
	 *
	 * @param \CodeIgniter\Database\ConnectionInterface $db
	 */
	public function __construct(ConnectionInterface $db)
	{
		$this->db = &$db;
	}

	//--------------------------------------------------------------------

	/**
	 * Provides access to the forge's current database connection.
	 *
	 * @return ConnectionInterface
	 */
	public function getConnection()
	{
		return $this->db;
	}

	//--------------------------------------------------------------------

	/**
	 * Create database
	 *
	 * @param    string $db_name
	 *
	 * @return    bool
	 * @throws \CodeIgniter\Database\Exceptions\DatabaseException
	 */
	public function createDatabase($db_name)
	{
		if ($this->createDatabaseStr === false)
		{
			if ($this->db->DBDebug)
			{
				throw new DatabaseException('This feature is not available for the database you are using.');
			}

			return false;
		}
		elseif (! $this->db->query(sprintf($this->createDatabaseStr, $db_name, $this->db->charset, $this->db->DBCollat))
		)
		{
			if ($this->db->DBDebug)
			{
				throw new DatabaseException('Unable to drop the specified database.');
			}

			return false;
		}

		if (! empty($this->db->dataCache['db_names']))
		{
			$this->db->dataCache['db_names'][] = $db_name;
		}

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Drop database
	 *
	 * @param    string $db_name
	 *
	 * @return    bool
	 * @throws \CodeIgniter\Database\Exceptions\DatabaseException
	 */
	public function dropDatabase($db_name)
	{
		if ($this->dropDatabaseStr === false)
		{
			if ($this->db->DBDebug)
			{
				throw new DatabaseException('This feature is not available for the database you are using.');
			}

			return false;
		}
		elseif (! $this->db->query(sprintf($this->dropDatabaseStr, $db_name)))
		{
			if ($this->db->DBDebug)
			{
				throw new DatabaseException('Unable to drop the specified database.');
			}

			return false;
		}

		if (! empty($this->db->dataCache['db_names']))
		{
			$key = array_search(strtolower($db_name), array_map('strtolower', $this->db->dataCache['db_names']), true);
			if ($key !== false)
			{
				unset($this->db->dataCache['db_names'][$key]);
			}
		}

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Add Key
	 *
	 * @param    string|array $key
	 * @param    bool         $primary
	 * @param    bool         $unique
	 *
	 * @return    Forge
	 */
	public function addKey($key, bool $primary = false, bool $unique = false)
	{
		if ($primary === true)
		{
			foreach ((array)$key as $one)
			{
				$this->primaryKeys[] = $one;
			}
		}
		else
		{
			$this->keys[] = $key;
			if ($unique === true)
			{
				$this->uniqueKeys[] = ($c = count($this->keys)) ? $c - 1 : 0;
			}
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Add Primary Key
	 *
	 * @param string|array $key
	 *
	 * @return Forge
	 */
	public function addPrimaryKey($key)
	{
		return $this->addKey($key, true);
	}

	//--------------------------------------------------------------------


	/**
	 * Add Unique Key
	 *
	 * @param string|array $key
	 *
	 * @return Forge
	 */
	public function addUniqueKey($key)
	{
		return $this->addKey($key, false, true);
	}

	//--------------------------------------------------------------------

	/**
	 * Add Field
	 *
	 * @param    array $field
	 *
	 * @return    Forge
	 */
	public function addField($field)
	{
		if (is_string($field))
		{
			if ($field === 'id')
			{
				$this->addField([
					'id' => [
						'type'           => 'INT',
						'constraint'     => 9,
						'auto_increment' => true,
					],
				]);
				$this->addKey('id', true);
			}
			else
			{
				if (strpos($field, ' ') === false)
				{
					throw new \InvalidArgumentException('Field information is required for that operation.');
				}

				$this->fields[] = $field;
			}
		}

		if (is_array($field))
		{
			$this->fields = array_merge($this->fields, $field);
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Add Foreign Key
	 *
	 * @param string $fieldName
	 * @param string $tableName
	 * @param string $tableField
	 * @param bool   $onUpdate
	 * @param bool   $onDelete
	 *
	 * @return \CodeIgniter\Database\Forge
	 * @throws \CodeIgniter\Database\Exceptions\DatabaseException
	 */
	public function addForeignKey($fieldName = '', $tableName = '', $tableField = '', $onUpdate = false, $onDelete = false)
	{
		if (! isset($this->fields[$fieldName]))
		{
			throw new DatabaseException(lang('Database.fieldNotExists', [$fieldName]));
		}

		$this->foreignKeys[$fieldName] = [
			'table'    => $tableName,
			'field'    => $tableField,
			'onDelete' => strtoupper($onDelete),
			'onUpdate' => strtoupper($onUpdate),
		];


		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Foreign Key Drop
	 *
	 * @param    string $table        Table name
	 * @param    string $foreign_name Foreign name
	 *
	 * @return bool|\CodeIgniter\Database\BaseResult|\CodeIgniter\Database\Query|false|mixed
	 * @throws \CodeIgniter\Database\Exceptions\DatabaseException
	 */
	public function dropForeignKey($table, $foreign_name)
	{
		$sql = sprintf($this->dropConstraintStr, $this->db->escapeIdentifiers($this->db->DBPrefix.$table),
			$this->db->escapeIdentifiers($this->db->DBPrefix.$foreign_name));

		if ($sql === false)
		{
			if ($this->db->DBDebug)
			{
				throw new DatabaseException('This feature is not available for the database you are using.');
			}

			return false;
		}

		return $this->db->query($sql);
	}

	//--------------------------------------------------------------------

	/**
	 * Create Table
	 *
	 * @param    string $table         Table name
	 * @param    bool   $if_not_exists Whether to add IF NOT EXISTS condition
	 * @param    array  $attributes    Associative array of table attributes
	 *
	 * @return bool
	 * @throws \CodeIgniter\Database\Exceptions\DatabaseException
	 */
	public function createTable($table, $if_not_exists = false, array $attributes = [])
	{
		if ($table === '')
		{
			throw new \InvalidArgumentException('A table name is required for that operation.');
		}

		$table = $this->db->DBPrefix . $table;

		if (count($this->fields) === 0)
		{
			throw new \RuntimeException('Field information is required.');
		}

		$sql = $this->_createTable($table, $if_not_exists, $attributes);

		if (is_bool($sql))
		{
			$this->_reset();
			if ($sql === false)
			{
				if ($this->db->DBDebug)
				{
					throw new DatabaseException('This feature is not available for the database you are using.');
				}

				return false;
			}
		}

		if (($result = $this->db->query($sql)) !== false)
		{
			empty($this->db->dataCache['table_names']) || $this->db->dataCache['table_names'][] = $table;

			// Most databases don't support creating indexes from within the CREATE TABLE statement
			if (! empty($this->keys))
			{
				for ($i = 0, $sqls = $this->_processIndexes($table), $c = count($sqls); $i < $c; $i++)
				{
					$this->db->query($sqls[$i]);
				}
			}
		}

		$this->_reset();

		return $result;
	}

	//--------------------------------------------------------------------

	/**
	 * Create Table
	 *
	 * @param    string $table         Table name
	 * @param    bool   $if_not_exists Whether to add 'IF NOT EXISTS' condition
	 * @param    array  $attributes    Associative array of table attributes
	 *
	 * @return    mixed
	 */
	protected function _createTable($table, $if_not_exists, $attributes)
	{
		// For any platforms that don't support Create If Not Exists...
		if ($if_not_exists === true && $this->createTableIfStr === false)
		{
			if ($this->db->tableExists($table))
			{
				return true;
			}

			$if_not_exists = false;
		}

		$sql = ($if_not_exists) ? sprintf($this->createTableIfStr, $this->db->escapeIdentifiers($table))
			: 'CREATE TABLE';

		$columns = $this->_processFields(true);
		for ($i = 0, $c = count($columns); $i < $c; $i++)
		{
			$columns[$i] = ($columns[$i]['_literal'] !== false) ? "\n\t".$columns[$i]['_literal']
				: "\n\t".$this->_processColumn($columns[$i]);
		}

		$columns = implode(',', $columns);

		$columns .= $this->_processPrimaryKeys($table);
		$columns .= $this->_processForeignKeys($table);

		// Are indexes created from within the CREATE TABLE statement? (e.g. in MySQL)
		if ($this->createTableKeys === true)
		{
			$columns .= $this->_processIndexes($table);
		}

		// createTableStr will usually have the following format: "%s %s (%s\n)"
		$sql = sprintf($this->createTableStr.'%s', $sql, $this->db->escapeIdentifiers($table), $columns,
			$this->_createTableAttributes($attributes));

		return $sql;
	}

	//--------------------------------------------------------------------

	/**
	 * CREATE TABLE attributes
	 *
	 * @param    array $attributes Associative array of table attributes
	 *
	 * @return    string
	 */
	protected function _createTableAttributes($attributes)
	{
		$sql = '';

		foreach (array_keys($attributes) as $key)
		{
			if (is_string($key))
			{
				$sql .= ' ' . strtoupper($key) . ' ' . $this->db->escape($attributes[$key]);
			}
		}

		return $sql;
	}

	//--------------------------------------------------------------------

	/**
	 * Drop Table
	 *
	 * @param    string $table_name Table name
	 * @param    bool   $if_exists  Whether to add an IF EXISTS condition
	 * @param    bool   $cascade    Whether to add an CASCADE condition
	 *
	 * @return mixed
	 * @throws \CodeIgniter\Database\Exceptions\DatabaseException
	 */
	public function dropTable($table_name, $if_exists = false, $cascade = false)
	{
		if ($table_name === '')
		{
			if ($this->db->DBDebug)
			{
				throw new DatabaseException('A table name is required for that operation.');
			}

			return false;
		}


		// If the prefix is already starting the table name, remove it...
		if (! empty($this->db->DBPrefix) && strpos($table_name, $this->db->DBPrefix) === 0)
		{
			$table_name = substr($table_name, strlen($this->db->DBPrefix));
		}

		if (($query = $this->_dropTable($this->db->DBPrefix.$table_name, $if_exists, $cascade)) === true)
		{
			return true;
		}

		$query = $this->db->query($query);

		// Update table list cache
		if ($query && ! empty($this->db->dataCache['table_names']))
		{
			$key = array_search(strtolower($this->db->DBPrefix.$table_name),
				array_map('strtolower', $this->db->dataCache['table_names']), true);
			if ($key !== false)
			{
				unset($this->db->dataCache['table_names'][$key]);
			}
		}

		return $query;
	}

	//--------------------------------------------------------------------

	/**
	 * Drop Table
	 *
	 * Generates a platform-specific DROP TABLE string
	 *
	 * @param    string $table     Table name
	 * @param    bool   $if_exists Whether to add an IF EXISTS condition
	 * @param    bool   $cascade   Whether to add an CASCADE condition
	 *
	 * @return    string
	 */
	protected function _dropTable($table, $if_exists, $cascade)
	{
		$sql = 'DROP TABLE';

		if ($if_exists)
		{
			if ($this->dropTableIfStr === false)
			{
				if (! $this->db->tableExists($table))
				{
					return true;
				}
			}
			else
			{
				$sql = sprintf($this->dropTableIfStr, $this->db->escapeIdentifiers($table));
			}
		}

		$sql = $sql.' '.$this->db->escapeIdentifiers($table);

		return $sql;
	}

	//--------------------------------------------------------------------

	/**
	 * Rename Table
	 *
	 * @param    string $table_name     Old table name
	 * @param    string $new_table_name New table name
	 *
	 * @return    mixed
	 * @throws \CodeIgniter\Database\Exceptions\DatabaseException
	 */
	public function renameTable($table_name, $new_table_name)
	{
		if ($table_name === '' || $new_table_name === '')
		{
			throw new \InvalidArgumentException('A table name is required for that operation.');
		}
		elseif ($this->renameTableStr === false)
		{
			if ($this->db->DBDebug)
			{
				throw new DatabaseException('This feature is not available for the database you are using.');
			}

			return false;
		}

		$result = $this->db->query(sprintf($this->renameTableStr,
				$this->db->escapeIdentifiers($this->db->DBPrefix.$table_name),
				$this->db->escapeIdentifiers($this->db->DBPrefix.$new_table_name))
		);

		if ($result && ! empty($this->db->dataCache['table_names']))
		{
			$key = array_search(strtolower($this->db->DBPrefix.$table_name),
				array_map('strtolower', $this->db->dataCache['table_names']), true);
			if ($key !== false)
			{
				$this->db->dataCache['table_names'][$key] = $this->db->DBPrefix.$new_table_name;
			}
		}

		return $result;
	}

	//--------------------------------------------------------------------

	/**
	 * Column Add
	 *
	 * @param    string $table Table name
	 * @param    array  $field Column definition
	 *
	 * @return    bool
	 * @throws \CodeIgniter\Database\Exceptions\DatabaseException
	 */
	public function addColumn($table, $field)
	{
		// Work-around for literal column definitions
		is_array($field) || $field = [$field];

		foreach (array_keys($field) as $k)
		{
			$this->addField([$k => $field[$k]]);
		}

		$sqls = $this->_alterTable('ADD', $this->db->DBPrefix.$table, $this->_processFields());
		$this->_reset();
		if ($sqls === false)
		{
			if ($this->db->DBDebug)
			{
				throw new DatabaseException('This feature is not available for the database you are using.');
			}

			return false;
		}

		for ($i = 0, $c = count($sqls); $i < $c; $i++)
		{
			if ($this->db->query($sqls[$i]) === false)
			{
				return false;
			}
		}

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Column Drop
	 *
	 * @param    string $table       Table name
	 * @param    string $column_name Column name
	 *
	 * @return    bool
	 * @throws \CodeIgniter\Database\Exceptions\DatabaseException
	 */
	public function dropColumn($table, $column_name)
	{
		$sql = $this->_alterTable('DROP', $this->db->DBPrefix.$table, $column_name);
		if ($sql === false)
		{
			if ($this->db->DBDebug)
			{
				throw new DatabaseException('This feature is not available for the database you are using.');
			}

			return false;
		}

		return $this->db->query($sql);
	}

	//--------------------------------------------------------------------

	/**
	 * Column Modify
	 *
	 * @param    string $table Table name
	 * @param    string $field Column definition
	 *
	 * @return    bool
	 * @throws \CodeIgniter\Database\Exceptions\DatabaseException
	 */
	public function modifyColumn($table, $field)
	{
		// Work-around for literal column definitions
		is_array($field) || $field = [$field];

		foreach (array_keys($field) as $k)
		{
			$this->addField([$k => $field[$k]]);
		}

		if (count($this->fields) === 0)
		{
			throw new \RuntimeException('Field information is required');
		}

		$sqls = $this->_alterTable('CHANGE', $this->db->DBPrefix.$table, $this->_processFields());
		$this->_reset();
		if ($sqls === false)
		{
			if ($this->db->DBDebug)
			{
				throw new DatabaseException('This feature is not available for the database you are using.');
			}

			return false;
		}

		for ($i = 0, $c = count($sqls); $i < $c; $i++)
		{
			if ($this->db->query($sqls[$i]) === false)
			{
				return false;
			}
		}

		return true;
	}

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
		$sql = 'ALTER TABLE '.$this->db->escapeIdentifiers($table).' ';

		// DROP has everything it needs now.
		if ($alter_type === 'DROP')
		{
			return $sql.'DROP COLUMN '.$this->db->escapeIdentifiers($field);
		}

		$sql .= ($alter_type === 'ADD') ? 'ADD ' : $alter_type.' COLUMN ';

		$sqls = [];
		for ($i = 0, $c = count($field); $i < $c; $i++)
		{
			$sqls[] = $sql
			          .($field[$i]['_literal'] !== false ? $field[$i]['_literal'] : $this->_processColumn($field[$i]));
		}

		return $sqls;
	}

	//--------------------------------------------------------------------

	/**
	 * Process fields
	 *
	 * @param    bool $create_table
	 *
	 * @return    array
	 */
	protected function _processFields($create_table = false)
	{
		$fields = [];

		foreach ($this->fields as $key => $attributes)
		{
			if (is_int($key) && ! is_array($attributes))
			{
				$fields[] = ['_literal' => $attributes];
				continue;
			}

			$attributes = array_change_key_case($attributes, CASE_UPPER);

			if ($create_table === true && empty($attributes['TYPE']))
			{
				continue;
			}

			isset($attributes['TYPE']) && $this->_attributeType($attributes);

			$field = [
				'name'           => $key,
				'new_name'       => isset($attributes['NAME']) ? $attributes['NAME'] : null,
				'type'           => isset($attributes['TYPE']) ? $attributes['TYPE'] : null,
				'length'         => '',
				'unsigned'       => '',
				'null'           => '',
				'unique'         => '',
				'default'        => '',
				'auto_increment' => '',
				'_literal'       => false,
			];

			isset($attributes['TYPE']) && $this->_attributeUnsigned($attributes, $field);

			if ($create_table === false)
			{
				if (isset($attributes['AFTER']))
				{
					$field['after'] = $attributes['AFTER'];
				}
				elseif (isset($attributes['FIRST']))
				{
					$field['first'] = (bool)$attributes['FIRST'];
				}
			}

			$this->_attributeDefault($attributes, $field);

			if (isset($attributes['NULL']))
			{
				if ($attributes['NULL'] === true)
				{
					$field['null'] = empty($this->null) ? '' : ' '.$this->null;
				}
				else
				{
					$field['null'] = ' NOT NULL';
				}
			}
			elseif ($create_table === true)
			{
				$field['null'] = ' NOT NULL';
			}

			$this->_attributeAutoIncrement($attributes, $field);
			$this->_attributeUnique($attributes, $field);

			if (isset($attributes['COMMENT']))
			{
				$field['comment'] = $this->db->escape($attributes['COMMENT']);
			}

			if (isset($attributes['TYPE']) && ! empty($attributes['CONSTRAINT']))
			{
				switch (strtoupper($attributes['TYPE']))
				{
					case 'ENUM':
					case 'SET':
						$attributes['CONSTRAINT'] = $this->db->escape($attributes['CONSTRAINT']);
						$field['length']          = is_array($attributes['CONSTRAINT']) ? "('".implode("','",
								$attributes['CONSTRAINT'])."')" : '('.$attributes['CONSTRAINT'].')';
						break;
					default:
						$field['length'] = is_array($attributes['CONSTRAINT']) ? '('.implode(',',
								$attributes['CONSTRAINT']).')' : '('.$attributes['CONSTRAINT'].')';
						break;
				}
			}

			$fields[] = $field;
		}

		return $fields;
	}

	//--------------------------------------------------------------------

	/**
	 * Process column
	 *
	 * @param    array $field
	 *
	 * @return    string
	 */
	protected function _processColumn($field)
	{
		return $this->db->escapeIdentifiers($field['name'])
		       .' '.$field['type'].$field['length']
		       .$field['unsigned']
		       .$field['default']
		       .$field['null']
		       .$field['auto_increment']
		       .$field['unique'];
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
		// Usually overridden by drivers
	}

	//--------------------------------------------------------------------

	/**
	 * Field attribute UNSIGNED
	 *
	 * Depending on the unsigned property value:
	 *
	 *    - TRUE will always set $field['unsigned'] to 'UNSIGNED'
	 *    - FALSE will always set $field['unsigned'] to ''
	 *    - array(TYPE) will set $field['unsigned'] to 'UNSIGNED',
	 *        if $attributes['TYPE'] is found in the array
	 *    - array(TYPE => UTYPE) will change $field['type'],
	 *        from TYPE to UTYPE in case of a match
	 *
	 * @param    array &$attributes
	 * @param    array &$field
	 *
	 * @return    void
	 */
	protected function _attributeUnsigned(&$attributes, &$field)
	{
		if (empty($attributes['UNSIGNED']) || $attributes['UNSIGNED'] !== true)
		{
			return;
		}

		// Reset the attribute in order to avoid issues if we do type conversion
		$attributes['UNSIGNED'] = false;

		if (is_array($this->unsigned))
		{
			foreach (array_keys($this->unsigned) as $key)
			{
				if (is_int($key) && strcasecmp($attributes['TYPE'], $this->unsigned[$key]) === 0)
				{
					$field['unsigned'] = ' UNSIGNED';

					return;
				}
				elseif (is_string($key) && strcasecmp($attributes['TYPE'], $key) === 0)
				{
					$field['type'] = $key;

					return;
				}
			}

			return;
		}

		$field['unsigned'] = ($this->unsigned === true) ? ' UNSIGNED' : '';
	}

	//--------------------------------------------------------------------

	/**
	 * Field attribute DEFAULT
	 *
	 * @param    array &$attributes
	 * @param    array &$field
	 *
	 * @return    void
	 */
	protected function _attributeDefault(&$attributes, &$field)
	{
		if ($this->default === false)
		{
			return;
		}

		if (array_key_exists('DEFAULT', $attributes))
		{
			if ($attributes['DEFAULT'] === null)
			{
				$field['default'] = empty($this->null) ? '' : $this->default.$this->null;

				// Override the NULL attribute if that's our default
				$attributes['NULL'] = true;
				$field['null']      = empty($this->null) ? '' : ' '.$this->null;
			}
			else
			{
				$field['default'] = $this->default.$this->db->escape($attributes['DEFAULT']);
			}
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Field attribute UNIQUE
	 *
	 * @param    array &$attributes
	 * @param    array &$field
	 *
	 * @return    void
	 */
	protected function _attributeUnique(&$attributes, &$field)
	{
		if (! empty($attributes['UNIQUE']) && $attributes['UNIQUE'] === true)
		{
			$field['unique'] = ' UNIQUE';
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
		if (! empty($attributes['AUTO_INCREMENT']) && $attributes['AUTO_INCREMENT'] === true
		    && stripos($field['type'], 'int') !== false
		)
		{
			$field['auto_increment'] = ' AUTO_INCREMENT';
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Process primary keys
	 *
	 * @param    string $table Table name
	 *
	 * @return    string
	 */
	protected function _processPrimaryKeys($table)
	{
		$sql = '';

		for ($i = 0, $c = count($this->primaryKeys); $i < $c; $i++)
		{
			if (! isset($this->fields[$this->primaryKeys[$i]]))
			{
				unset($this->primaryKeys[$i]);
			}
		}

		if (count($this->primaryKeys) > 0)
		{
			$sql .= ",\n\tCONSTRAINT ".$this->db->escapeIdentifiers('pk_'.$table)
			        .' PRIMARY KEY('.implode(', ', $this->db->escapeIdentifiers($this->primaryKeys)).')';
		}

		return $sql;
	}

	//--------------------------------------------------------------------

	/**
	 * Process indexes
	 *
	 * @param    string $table
	 *
	 * @return    array
	 */
	protected function _processIndexes($table)
	{
		$sqls = [];

		for ($i = 0, $c = count($this->keys); $i < $c; $i++)
		{
			$this->keys[$i] = (array)$this->keys[$i];

			for ($i2 = 0, $c2 = count($this->keys[$i]); $i2 < $c2; $i2++)
			{
				if (! isset($this->fields[$this->keys[$i][$i2]]))
				{
					unset($this->keys[$i][$i2]);
				}
			}
			if (count($this->keys[$i]) <= 0)
			{
				continue;
			}

			if (in_array($i, $this->uniqueKeys))
			{
				$sqls[] = 'ALTER TABLE '.$this->db->escapeIdentifiers($table)
				          .' ADD CONSTRAINT '.$this->db->escapeIdentifiers($table.'_'.implode('_', $this->keys[$i]))
				          .' UNIQUE ('.implode(', ', $this->db->escapeIdentifiers($this->keys[$i])).');';
				continue;
			}

			$sqls[] = 'CREATE INDEX '.$this->db->escapeIdentifiers($table.'_'.implode('_', $this->keys[$i]))
			          .' ON '.$this->db->escapeIdentifiers($table)
			          .' ('.implode(', ', $this->db->escapeIdentifiers($this->keys[$i])).');';
		}

		return $sqls;
	}

	//--------------------------------------------------------------------

	/**
	 * Process foreign keys
	 *
	 * @param    string $table Table name
	 *
	 * @return    string
	 */
	protected function _processForeignKeys($table) {
        $sql = '';

        $allowActions = ['CASCADE','SET NULL','NO ACTION','RESTRICT','SET DEFAULT'];

        if (count($this->foreignKeys) > 0){
            foreach ($this->foreignKeys as $field => $fkey) {
                $name_index = $table.'_'.$field.'_foreign';

                $sql .= ",\n\tCONSTRAINT " . $this->db->escapeIdentifiers($name_index)
                    . ' FOREIGN KEY(' . $this->db->escapeIdentifiers($field) . ') REFERENCES '.$this->db->escapeIdentifiers($this->db->DBPrefix.$fkey['table']).' ('.$this->db->escapeIdentifiers($fkey['field']).')';

                if($fkey['onDelete'] !== false && in_array($fkey['onDelete'], $allowActions)){
                    $sql .= " ON DELETE ".$fkey['onDelete'];
                }

                if($fkey['onUpdate'] !== false && in_array($fkey['onUpdate'], $allowActions)){
                    $sql .= " ON UPDATE ".$fkey['onUpdate'];
                }

            }
        }

        return $sql;
    }

	//--------------------------------------------------------------------

	/**
	 * Reset
	 *
	 * Resets table creation vars
	 *
	 * @return    void
	 */
	protected function _reset()
	{
		$this->fields = $this->keys = $this->uniqueKeys = $this->primaryKeys = $this->foreignKeys = [];
	}

}
