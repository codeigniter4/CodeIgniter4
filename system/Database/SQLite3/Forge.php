<?php namespace CodeIgniter\Database\SQLite3;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2017 British Columbia Institute of Technology
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
 * @package      CodeIgniter
 * @author       CodeIgniter Dev Team
 * @copyright    2014-2017 British Columbia Institute of Technology (https://bcit.ca/)
 * @license      https://opensource.org/licenses/MIT	MIT License
 * @link         https://codeigniter.com
 * @since        Version 3.0.0
 * @filesource
 */

use CodeIgniter\Database\Exceptions\DatabaseException;

/**
 * Forge for Postgre
 */
class Forge extends \CodeIgniter\Database\Forge
{

	/**
	 * UNSIGNED support
	 *
	 * @var    bool|array
	 */
	protected $_unsigned = false;

	/**
	 * NULL value representation in CREATE/ALTER TABLE statements
	 *
	 * @var    string
	 */
	protected $_null = 'NULL';

	//--------------------------------------------------------------------

	/**
	 * Constructor.
	 *
	 */
	public function __construct($db)
	{
		parent::__construct($db);

		if (version_compare($this->db->getVersion(), '3.3', '<'))
		{
			$this->createTableIfStr = false;
			$this->dropTableIfStr   = false;
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Create database
	 *
	 * @param    string $db_name
	 *
	 * @return    bool
	 */
	public function createDatabase($db_name): bool
	{
		// In SQLite, a database is created when you connect to the database.
		// We'll return TRUE so that an error isn't generated.
		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Drop database
	 *
	 * @param    string $db_name
	 *
	 * @return    bool
	 * @throws \CodeIgniter\DatabaseException
	 */
	public function dropDatabase($db_name): bool
	{
		// In SQLite, a database is dropped when we delete a file
		if (! file_exists($db_name))
		{
			if ($this->db->DBDebug)
			{
				throw new DatabaseException('Unable to drop the specified database.');
			}

			return false;
		}

		// We need to close the pseudo-connection first
		$this->db->close();
		if (! @unlink($db_name))
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
	 * ALTER TABLE
	 *
	 * @todo    implement drop_column(), modify_column()
	 *
	 * @param    string $alter_type ALTER type
	 * @param    string $table      Table name
	 * @param    mixed  $field      Column definition
	 *
	 * @return    string|array
	 */
	protected function _alterTable($alter_type, $table, $field)
	{
		if (in_array($alter_type, ['DROP', 'CHANGE'], true))
		{
			return false;
		}

		return parent::_alterTable($alter_type, $table, $field);
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
		       .' '.$field['type']
		       .$field['auto_increment']
		       .$field['null']
		       .$field['unique']
		       .$field['default'];
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
				$sqls[] = 'CREATE UNIQUE INDEX '.$this->db->escapeIdentifiers($table.'_'.implode('_', $this->keys[$i]))
				          .' ON '.$this->db->escapeIdentifiers($table)
				          .' ('.implode(', ', $this->db->escapeIdentifiers($this->keys[$i])).');';
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
		switch (strtoupper($attributes['TYPE']))
		{
			case 'ENUM':
			case 'SET':
				$attributes['TYPE'] = 'TEXT';

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
		if (! empty($attributes['AUTO_INCREMENT']) && $attributes['AUTO_INCREMENT'] === true
		    && stripos($field['type'], 'int') !== false)
		{
			$field['type']           = 'INTEGER PRIMARY KEY';
			$field['default']        = '';
			$field['null']           = '';
			$field['unique']         = '';
			$field['auto_increment'] = ' AUTOINCREMENT';

			$this->primaryKeys = [];
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Foreign Key Drop
	 *
	 * @param    string $table        Table name
	 * @param    string $foreign_name Foreign name
	 *
	 * @return bool
	 * @throws \CodeIgniter\Database\Exceptions\DatabaseException
	 */
	public function dropForeignKey($table, $foreign_name)
	{
		throw new DatabaseException(lang('Database.dropForeignKeyUnsupported'));
	}

	//--------------------------------------------------------------------

}
