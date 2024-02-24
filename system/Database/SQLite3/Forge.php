<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
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
     * DROP INDEX statement
     *
     * @var string
     */
    protected $dropIndexStr = 'DROP INDEX %s';

    /**
     * @var Connection
     */
    protected $db;

    /**
     * UNSIGNED support
     *
     * @var array|bool
     */
    protected $_unsigned = false;

    /**
     * NULL value representation in CREATE/ALTER TABLE statements
     *
     * @var string
     *
     * @internal
     */
    protected $null = 'NULL';

    /**
     * Constructor.
     */
    public function __construct(BaseConnection $db)
    {
        parent::__construct($db);

        if (version_compare($this->db->getVersion(), '3.3', '<')) {
            $this->dropTableIfStr = false;
        }
    }

    /**
     * Create database
     *
     * @param bool $ifNotExists Whether to add IF NOT EXISTS condition
     */
    public function createDatabase(string $dbName, bool $ifNotExists = false): bool
    {
        // In SQLite, a database is created when you connect to the database.
        // We'll return TRUE so that an error isn't generated.
        return true;
    }

    /**
     * Drop database
     *
     * @throws DatabaseException
     */
    public function dropDatabase(string $dbName): bool
    {
        // In SQLite, a database is dropped when we delete a file
        if (! is_file($dbName)) {
            if ($this->db->DBDebug) {
                throw new DatabaseException('Unable to drop the specified database.');
            }

            return false;
        }

        // We need to close the pseudo-connection first
        $this->db->close();
        if (! @unlink($dbName)) {
            if ($this->db->DBDebug) {
                throw new DatabaseException('Unable to drop the specified database.');
            }

            return false;
        }

        if (! empty($this->db->dataCache['db_names'])) {
            $key = array_search(strtolower($dbName), array_map('strtolower', $this->db->dataCache['db_names']), true);
            if ($key !== false) {
                unset($this->db->dataCache['db_names'][$key]);
            }
        }

        return true;
    }

    /**
     * @param array|string $processedFields Processed column definitions
     *                                      or column names to DROP
     *
     * @return         array|string|null
     * @return         list<string>|string|null                            SQL string or null
     * @phpstan-return ($alterType is 'DROP' ? string : list<string>|null)
     */
    protected function _alterTable(string $alterType, string $table, $processedFields)
    {
        switch ($alterType) {
            case 'DROP':
                $columnNamesToDrop = $processedFields;

                $sqlTable = new Table($this->db, $this);

                $sqlTable->fromTable($table)
                    ->dropColumn($columnNamesToDrop)
                    ->run();

                return ''; // Why empty string?

            case 'CHANGE':
                $fieldsToModify = [];

                foreach ($processedFields as $processedField) {
                    $name    = $processedField['name'];
                    $newName = $processedField['new_name'];

                    $field             = $this->fields[$name];
                    $field['name']     = $name;
                    $field['new_name'] = $newName;

                    // Unlike when creating a table, if `null` is not specified,
                    // the column will be `NULL`, not `NOT NULL`.
                    if ($processedField['null'] === '') {
                        $field['null'] = true;
                    }

                    $fieldsToModify[] = $field;
                }

                (new Table($this->db, $this))
                    ->fromTable($table)
                    ->modifyColumn($fieldsToModify)
                    ->run();

                return null; // Why null?

            default:
                return parent::_alterTable($alterType, $table, $processedFields);
        }
    }

    /**
     * Process column
     */
    protected function _processColumn(array $processedField): string
    {
        if ($processedField['type'] === 'TEXT' && strpos($processedField['length'], "('") === 0) {
            $processedField['type'] .= ' CHECK(' . $this->db->escapeIdentifiers($processedField['name'])
                . ' IN ' . $processedField['length'] . ')';
        }

        return $this->db->escapeIdentifiers($processedField['name'])
            . ' ' . $processedField['type']
            . $processedField['auto_increment']
            . $processedField['null']
            . $processedField['unique']
            . $processedField['default'];
    }

    /**
     * Field attribute TYPE
     *
     * Performs a data type mapping between different databases.
     */
    protected function _attributeType(array &$attributes)
    {
        switch (strtoupper($attributes['TYPE'])) {
            case 'ENUM':
            case 'SET':
                $attributes['TYPE'] = 'TEXT';
                break;

            case 'BOOLEAN':
                $attributes['TYPE'] = 'INT';
                break;

            default:
                break;
        }
    }

    /**
     * Field attribute AUTO_INCREMENT
     */
    protected function _attributeAutoIncrement(array &$attributes, array &$field)
    {
        if (
            ! empty($attributes['AUTO_INCREMENT'])
            && $attributes['AUTO_INCREMENT'] === true
            && stripos($field['type'], 'int') !== false
        ) {
            $field['type']           = 'INTEGER PRIMARY KEY';
            $field['default']        = '';
            $field['null']           = '';
            $field['unique']         = '';
            $field['auto_increment'] = ' AUTOINCREMENT';

            $this->primaryKeys = [];
        }
    }

    /**
     * Foreign Key Drop
     *
     * @throws DatabaseException
     */
    public function dropForeignKey(string $table, string $foreignName): bool
    {
        // If this version of SQLite doesn't support it, we're done here
        if ($this->db->supportsForeignKeys() !== true) {
            return true;
        }

        // Otherwise we have to copy the table and recreate
        // without the foreign key being involved now
        $sqlTable = new Table($this->db, $this);

        return $sqlTable->fromTable($this->db->DBPrefix . $table)
            ->dropForeignKey($foreignName)
            ->run();
    }

    /**
     * Drop Primary Key
     */
    public function dropPrimaryKey(string $table, string $keyName = ''): bool
    {
        $sqlTable = new Table($this->db, $this);

        return $sqlTable->fromTable($this->db->DBPrefix . $table)
            ->dropPrimaryKey()
            ->run();
    }

    public function addForeignKey($fieldName = '', string $tableName = '', $tableField = '', string $onUpdate = '', string $onDelete = '', string $fkName = ''): BaseForge
    {
        if ($fkName === '') {
            return parent::addForeignKey($fieldName, $tableName, $tableField, $onUpdate, $onDelete, $fkName);
        }

        throw new DatabaseException('SQLite does not support foreign key names. CodeIgniter will refer to them in the format: prefix_table_column_referencecolumn_foreign');
    }

    /**
     * Generates SQL to add primary key
     *
     * @param bool $asQuery When true recreates table with key, else partial SQL used with CREATE TABLE
     */
    protected function _processPrimaryKeys(string $table, bool $asQuery = false): string
    {
        if ($asQuery === false) {
            return parent::_processPrimaryKeys($table, $asQuery);
        }

        $sqlTable = new Table($this->db, $this);

        $sqlTable->fromTable($this->db->DBPrefix . $table)
            ->addPrimaryKey($this->primaryKeys)
            ->run();

        return '';
    }

    /**
     * Generates SQL to add foreign keys
     *
     * @param bool $asQuery When true recreates table with key, else partial SQL used with CREATE TABLE
     */
    protected function _processForeignKeys(string $table, bool $asQuery = false): array
    {
        if ($asQuery === false) {
            return parent::_processForeignKeys($table, $asQuery);
        }

        $errorNames = [];

        foreach ($this->foreignKeys as $name) {
            foreach ($name['field'] as $f) {
                if (! isset($this->fields[$f])) {
                    $errorNames[] = $f;
                }
            }
        }

        if ($errorNames !== []) {
            $errorNames = [implode(', ', $errorNames)];

            throw new DatabaseException(lang('Database.fieldNotExists', $errorNames));
        }

        $sqlTable = new Table($this->db, $this);

        $sqlTable->fromTable($this->db->DBPrefix . $table)
            ->addForeignKey($this->foreignKeys)
            ->run();

        return [];
    }
}
