<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Database\SQLite3;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Database\Forge as BaseForge;

/**
 * Forge for SQLite3
 */
class Forge extends BaseForge
{
	/**
	 * UNSIGNED support
	 *
	 * @var boolean|array
	 */
	protected $_unsigned = false;

	/**
	 * NULL value representation in CREATE/ALTER TABLE statements
	 *
	 * @var string
	 */
	protected $_null = 'NULL';

	//--------------------------------------------------------------------

	/**
	 * Constructor.
	 *
	 * @param BaseConnection $db
	 */
	public function __construct(BaseConnection $db)
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
	 * @param string  $dbName
	 * @param boolean $ifNotExists Whether to add IF NOT EXISTS condition
	 *
	 * @return boolean
	 */
	public function createDatabase(string $dbName, bool $ifNotExists = false): bool
	{
		// In SQLite, a database is created when you connect to the database.
		// We'll return TRUE so that an error isn't generated.
		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Drop database
	 *
	 * @param string $dbName
	 *
	 * @return boolean
	 * @throws DatabaseException
	 */
	public function dropDatabase(string $dbName): bool
	{
		// In SQLite, a database is dropped when we delete a file
		if (! is_file($dbName))
		{
			if ($this->db->DBDebug)
			{
				throw new DatabaseException('Unable to drop the specified database.');
			}

			return false;
		}

		// We need to close the pseudo-connection first
		$this->db->close();
		if (! @unlink($dbName))
		{
			if ($this->db->DBDebug)
			{
				throw new DatabaseException('Unable to drop the specified database.');
			}

			return false;
		}

		if (! empty($this->db->dataCache['db_names']))
		{
			$key = array_search(strtolower($dbName), array_map('strtolower', $this->db->dataCache['db_names']), true);
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
	 * @param string $alterType ALTER type
	 * @param string $table     Table name
	 * @param mixed  $field     Column definition
	 *
	 * @return string|array|null
	 */
	protected function _alterTable(string $alterType, string $table, $field)
	{
		switch ($alterType)
		{
			case 'DROP':
				$sqlTable = new Table($this->db, $this);

				$sqlTable->fromTable($table)
					->dropColumn($field)
					->run();

				return '';
			case 'CHANGE':
				$sqlTable = new Table($this->db, $this);

				$sqlTable->fromTable($table)
						 ->modifyColumn($field)
						 ->run();

				return null;
			default:
				return parent::_alterTable($alterType, $table, $field);
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
		if ($field['type'] === 'TEXT' && strpos($field['length'], "('") === 0)
		{
			$field['type'] .= ' CHECK(' . $this->db->escapeIdentifiers($field['name'])
				. ' IN ' . $field['length'] . ')';
		}

		return $this->db->escapeIdentifiers($field['name'])
			   . ' ' . $field['type']
			   . $field['auto_increment']
			   . $field['null']
			   . $field['unique']
			   . $field['default'];
	}

	//--------------------------------------------------------------------

	/**
	 * Process indexes
	 *
	 * @param string $table
	 *
	 * @return array
	 */
	protected function _processIndexes(string $table): array
	{
		$sqls = [];

		for ($i = 0, $c = count($this->keys); $i < $c; $i++)
		{
			$this->keys[$i] = (array) $this->keys[$i];

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

			if (in_array($i, $this->uniqueKeys, true))
			{
				$sqls[] = 'CREATE UNIQUE INDEX ' . $this->db->escapeIdentifiers($table . '_' . implode('_', $this->keys[$i]))
						  . ' ON ' . $this->db->escapeIdentifiers($table)
						  . ' (' . implode(', ', $this->db->escapeIdentifiers($this->keys[$i])) . ');';
				continue;
			}

			$sqls[] = 'CREATE INDEX ' . $this->db->escapeIdentifiers($table . '_' . implode('_', $this->keys[$i]))
					  . ' ON ' . $this->db->escapeIdentifiers($table)
					  . ' (' . implode(', ', $this->db->escapeIdentifiers($this->keys[$i])) . ');';
		}

		return $sqls;
	}

	//--------------------------------------------------------------------

	/**
	 * Field attribute TYPE
	 *
	 * Performs a data type mapping between different databases.
	 *
	 * @param array $attributes
	 *
	 * @return void
	 */
	protected function _attributeType(array &$attributes)
	{
		switch (strtoupper($attributes['TYPE']))
		{
			case 'ENUM':
			case 'SET':
				$attributes['TYPE'] = 'TEXT';
				break;
			default:
				break;
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Field attribute AUTO_INCREMENT
	 *
	 * @param array $attributes
	 * @param array $field
	 *
	 * @return void
	 */
	protected function _attributeAutoIncrement(array &$attributes, array &$field)
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
	 * @param string $table       Table name
	 * @param string $foreignName Foreign name
	 *
	 * @return boolean
	 * @throws DatabaseException
	 */
	public function dropForeignKey(string $table, string $foreignName): bool
	{
		// If this version of SQLite doesn't support it, we're done here
		if ($this->db->supportsForeignKeys() !== true)
		{
			return true;
		}

		// Otherwise we have to copy the table and recreate
		// without the foreign key being involved now
		$sqlTable = new Table($this->db, $this);

		return $sqlTable->fromTable($this->db->DBPrefix . $table)
			->dropForeignKey($foreignName)
			->run();
	}
}
