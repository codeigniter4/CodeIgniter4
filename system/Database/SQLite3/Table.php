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

use CodeIgniter\Database\Exceptions\DataException;

/**
 * Class Table
 *
 * Provides missing features for altering tables that are common
 * in other supported databases, but are missing from SQLite.
 * These are needed in order to support migrations during testing
 * when another database is used as the primary engine, but
 * SQLite in memory databases are used for faster test execution.
 */
class Table
{
    /**
     * All of the fields this table represents.
     *
     * @var array
     * @phpstan-var array<string, array<string, bool|int|string|null>>
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
     * @return Table
     */
    public function fromTable(string $table)
    {
        $this->prefixedTableName = $table;

        $prefix = $this->db->DBPrefix;

        if (! empty($prefix) && strpos($table, $prefix) === 0) {
            $table = substr($table, strlen($prefix));
        }

        if (! $this->db->tableExists($this->prefixedTableName)) {
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
     * Resets the connection dataCache to be sure changes are collected.
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

        $this->db->resetDataCache();

        return $success;
    }

    /**
     * Drops columns from the table.
     *
     * @param array|string $columns
     *
     * @return Table
     */
    public function dropColumn($columns)
    {
        if (is_string($columns)) {
            $columns = explode(',', $columns);
        }

        foreach ($columns as $column) {
            $column = trim($column);
            if (isset($this->fields[$column])) {
                unset($this->fields[$column]);
            }
        }

        return $this;
    }

    /**
     * Modifies a field, including changing data type,
     * renaming, etc.
     *
     * @return Table
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
     * @return Table
     */
    public function dropForeignKey(string $column)
    {
        if (empty($this->foreignKeys)) {
            return $this;
        }

        for ($i = 0; $i < count($this->foreignKeys); $i++) {
            if ($this->foreignKeys[$i]->table_name !== $this->tableName) {
                continue;
            }

            // The column name should be the first thing in the constraint name
            if (strpos($this->foreignKeys[$i]->constraint_name, $column) !== 0) {
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

        foreach ($this->fields as $name => $field) {
            if (isset($field['new_name'])) {
                $fields[$field['new_name']] = $field;

                continue;
            }

            $fields[$name] = $field;
        }

        $this->forge->addField($fields);

        // Unique/Index keys
        if (is_array($this->keys)) {
            foreach ($this->keys as $key) {
                switch ($key['type']) {
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

        return $this->forge->createTable($this->tableName);
    }

    /**
     * Copies data from our old table to the new one,
     * taking care map data correctly based on any columns
     * that have been renamed.
     */
    protected function copyData()
    {
        $exFields  = [];
        $newFields = [];

        foreach ($this->fields as $name => $details) {
            $newFields[] = $details['new_name'] ?? $name;
            $exFields[]  = $name;
        }

        $exFields = implode(
            ', ',
            array_map(fn ($item) => $this->db->protectIdentifiers($item), $exFields)
        );
        $newFields = implode(
            ', ',
            array_map(fn ($item) => $this->db->protectIdentifiers($item), $newFields)
        );

        $this->db->query(
            "INSERT INTO {$this->prefixedTableName}({$newFields}) SELECT {$exFields} FROM {$this->db->DBPrefix}temp_{$this->tableName}"
        );
    }

    /**
     * Converts fields retrieved from the database to
     * the format needed for creating fields with Forge.
     *
     * @param array|bool $fields
     *
     * @return mixed
     * @phpstan-return ($fields is array ? array : mixed)
     */
    protected function formatFields($fields)
    {
        if (! is_array($fields)) {
            return $fields;
        }

        $return = [];

        foreach ($fields as $field) {
            $return[$field->name] = [
                'type'    => $field->type,
                'default' => $field->default,
                'null'    => $field->nullable,
            ];

            if ($field->primary_key) {
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
        if (! is_array($keys)) {
            return $keys;
        }

        $return = [];

        foreach ($keys as $name => $key) {
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
     */
    protected function dropIndexes()
    {
        if (! is_array($this->keys) || $this->keys === []) {
            return;
        }

        foreach ($this->keys as $name => $key) {
            if ($key['type'] === 'primary' || $key['type'] === 'unique') {
                continue;
            }

            $this->db->query("DROP INDEX IF EXISTS '{$name}'");
        }
    }
}
