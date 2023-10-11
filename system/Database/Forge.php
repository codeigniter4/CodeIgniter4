<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Database;

use CodeIgniter\Database\Exceptions\DatabaseException;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

/**
 * The Forge class transforms migrations to executable
 * SQL statements.
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
     * @phpstan-var array{}|list<array{fields: string[], keyName: string}>
     */
    protected $keys = [];

    /**
     * List of unique keys.
     *
     * @var array
     */
    protected $uniqueKeys = [];

    /**
     * Primary keys.
     *
     * @phpstan-var array{}|array{fields: string[], keyName: string}
     */
    protected $primaryKeys = [];

    /**
     * List of foreign keys.
     *
     * @var array
     */
    protected $foreignKeys = [];

    /**
     * Character set used.
     *
     * @var string
     */
    protected $charset = '';

    /**
     * CREATE DATABASE statement
     *
     * @var false|string
     */
    protected $createDatabaseStr = 'CREATE DATABASE %s';

    /**
     * CREATE DATABASE IF statement
     *
     * @var string
     */
    protected $createDatabaseIfStr;

    /**
     * CHECK DATABASE EXIST statement
     *
     * @var string
     */
    protected $checkDatabaseExistStr;

    /**
     * DROP DATABASE statement
     *
     * @var false|string
     */
    protected $dropDatabaseStr = 'DROP DATABASE %s';

    /**
     * CREATE TABLE statement
     *
     * @var string
     */
    protected $createTableStr = "%s %s (%s\n)";

    /**
     * CREATE TABLE IF statement
     *
     * @var bool|string
     *
     * @deprecated This is no longer used.
     */
    protected $createTableIfStr = 'CREATE TABLE IF NOT EXISTS';

    /**
     * CREATE TABLE keys flag
     *
     * Whether table keys are created from within the
     * CREATE TABLE statement.
     *
     * @var bool
     */
    protected $createTableKeys = false;

    /**
     * DROP TABLE IF EXISTS statement
     *
     * @var bool|string
     */
    protected $dropTableIfStr = 'DROP TABLE IF EXISTS';

    /**
     * RENAME TABLE statement
     *
     * @var false|string
     */
    protected $renameTableStr = 'ALTER TABLE %s RENAME TO %s';

    /**
     * UNSIGNED support
     *
     * @var array|bool
     */
    protected $unsigned = true;

    /**
     * NULL value representation in CREATE/ALTER TABLE statements
     *
     * @var string
     *
     * @internal Used for marking nullable fields. Not covered by BC promise.
     */
    protected $null = 'NULL';

    /**
     * DEFAULT value representation in CREATE/ALTER TABLE statements
     *
     * @var false|string
     */
    protected $default = ' DEFAULT ';

    /**
     * DROP CONSTRAINT statement
     *
     * @var string
     */
    protected $dropConstraintStr;

    /**
     * DROP INDEX statement
     *
     * @var string
     */
    protected $dropIndexStr = 'DROP INDEX %s ON %s';

    /**
     * Foreign Key Allowed Actions
     *
     * @var array
     */
    protected $fkAllowActions = ['CASCADE', 'SET NULL', 'NO ACTION', 'RESTRICT', 'SET DEFAULT'];

    /**
     * Constructor.
     */
    public function __construct(BaseConnection $db)
    {
        $this->db = $db;
    }

    /**
     * Provides access to the forge's current database connection.
     *
     * @return ConnectionInterface
     */
    public function getConnection()
    {
        return $this->db;
    }

    /**
     * Create database
     *
     * @param bool $ifNotExists Whether to add IF NOT EXISTS condition
     *
     * @throws DatabaseException
     */
    public function createDatabase(string $dbName, bool $ifNotExists = false): bool
    {
        if ($ifNotExists && $this->createDatabaseIfStr === null) {
            if ($this->databaseExists($dbName)) {
                return true;
            }

            $ifNotExists = false;
        }

        if ($this->createDatabaseStr === false) {
            if ($this->db->DBDebug) {
                throw new DatabaseException('This feature is not available for the database you are using.');
            }

            return false; // @codeCoverageIgnore
        }

        try {
            if (! $this->db->query(sprintf($ifNotExists ? $this->createDatabaseIfStr : $this->createDatabaseStr, $dbName, $this->db->charset, $this->db->DBCollat))) {
                // @codeCoverageIgnoreStart
                if ($this->db->DBDebug) {
                    throw new DatabaseException('Unable to create the specified database.');
                }

                return false;
                // @codeCoverageIgnoreEnd
            }

            if (! empty($this->db->dataCache['db_names'])) {
                $this->db->dataCache['db_names'][] = $dbName;
            }

            return true;
        } catch (Throwable $e) {
            if ($this->db->DBDebug) {
                throw new DatabaseException('Unable to create the specified database.', 0, $e);
            }

            return false; // @codeCoverageIgnore
        }
    }

    /**
     * Determine if a database exists
     *
     * @throws DatabaseException
     */
    private function databaseExists(string $dbName): bool
    {
        if ($this->checkDatabaseExistStr === null) {
            if ($this->db->DBDebug) {
                throw new DatabaseException('This feature is not available for the database you are using.');
            }

            return false;
        }

        return $this->db->query($this->checkDatabaseExistStr, $dbName)->getRow() !== null;
    }

    /**
     * Drop database
     *
     * @throws DatabaseException
     */
    public function dropDatabase(string $dbName): bool
    {
        if ($this->dropDatabaseStr === false) {
            if ($this->db->DBDebug) {
                throw new DatabaseException('This feature is not available for the database you are using.');
            }

            return false;
        }

        if (! $this->db->query(sprintf($this->dropDatabaseStr, $dbName))) {
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
     * Add Key
     *
     * @param array|string $key
     *
     * @return Forge
     */
    public function addKey($key, bool $primary = false, bool $unique = false, string $keyName = '')
    {
        if ($primary) {
            $this->primaryKeys = ['fields' => (array) $key, 'keyName' => $keyName];
        } else {
            $this->keys[] = ['fields' => (array) $key, 'keyName' => $keyName];

            if ($unique) {
                $this->uniqueKeys[] = count($this->keys) - 1;
            }
        }

        return $this;
    }

    /**
     * Add Primary Key
     *
     * @param array|string $key
     *
     * @return Forge
     */
    public function addPrimaryKey($key, string $keyName = '')
    {
        return $this->addKey($key, true, false, $keyName);
    }

    /**
     * Add Unique Key
     *
     * @param array|string $key
     *
     * @return Forge
     */
    public function addUniqueKey($key, string $keyName = '')
    {
        return $this->addKey($key, false, true, $keyName);
    }

    /**
     * Add Field
     *
     * @param array|string $field
     *
     * @return Forge
     */
    public function addField($field)
    {
        if (is_string($field)) {
            if ($field === 'id') {
                $this->addField([
                    'id' => [
                        'type'           => 'INT',
                        'constraint'     => 9,
                        'auto_increment' => true,
                    ],
                ]);
                $this->addKey('id', true);
            } else {
                if (strpos($field, ' ') === false) {
                    throw new InvalidArgumentException('Field information is required for that operation.');
                }

                $fieldName = explode(' ', $field, 2)[0];
                $fieldName = trim($fieldName, '`\'"');

                $this->fields[$fieldName] = $field;
            }
        }

        if (is_array($field)) {
            foreach ($field as $idx => $f) {
                if (is_string($f)) {
                    $this->addField($f);

                    continue;
                }

                if (is_array($f)) {
                    $this->fields = array_merge($this->fields, [$idx => $f]);
                }
            }
        }

        return $this;
    }

    /**
     * Add Foreign Key
     *
     * @param string|string[] $fieldName
     * @param string|string[] $tableField
     *
     * @throws DatabaseException
     */
    public function addForeignKey($fieldName = '', string $tableName = '', $tableField = '', string $onUpdate = '', string $onDelete = '', string $fkName = ''): Forge
    {
        $fieldName  = (array) $fieldName;
        $tableField = (array) $tableField;

        $this->foreignKeys[] = [
            'field'          => $fieldName,
            'referenceTable' => $tableName,
            'referenceField' => $tableField,
            'onDelete'       => strtoupper($onDelete),
            'onUpdate'       => strtoupper($onUpdate),
            'fkName'         => $fkName,
        ];

        return $this;
    }

    /**
     * Drop Key
     *
     * @throws DatabaseException
     */
    public function dropKey(string $table, string $keyName, bool $prefixKeyName = true): bool
    {
        $keyName             = $this->db->escapeIdentifiers(($prefixKeyName === true ? $this->db->DBPrefix : '') . $keyName);
        $table               = $this->db->escapeIdentifiers($this->db->DBPrefix . $table);
        $dropKeyAsConstraint = $this->dropKeyAsConstraint($table, $keyName);

        if ($dropKeyAsConstraint === true) {
            $sql = sprintf(
                $this->dropConstraintStr,
                $table,
                $keyName,
            );
        } else {
            $sql = sprintf(
                $this->dropIndexStr,
                $keyName,
                $table,
            );
        }

        if ($sql === '') {
            if ($this->db->DBDebug) {
                throw new DatabaseException('This feature is not available for the database you are using.');
            }

            return false;
        }

        return $this->db->query($sql);
    }

    /**
     * Checks if if key needs to be dropped as a constraint.
     */
    protected function dropKeyAsConstraint(string $table, string $constraintName): bool
    {
        $sql = $this->_dropKeyAsConstraint($table, $constraintName);

        if ($sql === '') {
            return false;
        }

        return $this->db->query($sql)->getResultArray() !== [];
    }

    /**
     * Constructs sql to check if key is a constraint.
     */
    protected function _dropKeyAsConstraint(string $table, string $constraintName): string
    {
        return '';
    }

    /**
     * Drop Primary Key
     */
    public function dropPrimaryKey(string $table, string $keyName = ''): bool
    {
        $sql = sprintf(
            'ALTER TABLE %s DROP CONSTRAINT %s',
            $this->db->escapeIdentifiers($this->db->DBPrefix . $table),
            ($keyName === '') ? $this->db->escapeIdentifiers('pk_' . $this->db->DBPrefix . $table) : $this->db->escapeIdentifiers($keyName),
        );

        return $this->db->query($sql);
    }

    /**
     * @return BaseResult|bool|false|mixed|Query
     *
     * @throws DatabaseException
     */
    public function dropForeignKey(string $table, string $foreignName)
    {
        $sql = sprintf(
            (string) $this->dropConstraintStr,
            $this->db->escapeIdentifiers($this->db->DBPrefix . $table),
            $this->db->escapeIdentifiers($foreignName)
        );

        if ($sql === '') {
            if ($this->db->DBDebug) {
                throw new DatabaseException('This feature is not available for the database you are using.');
            }

            return false;
        }

        return $this->db->query($sql);
    }

    /**
     * @return mixed
     *
     * @throws DatabaseException
     */
    public function createTable(string $table, bool $ifNotExists = false, array $attributes = [])
    {
        if ($table === '') {
            throw new InvalidArgumentException('A table name is required for that operation.');
        }

        $table = $this->db->DBPrefix . $table;

        if ($this->fields === []) {
            throw new RuntimeException('Field information is required.');
        }

        // If table exists lets stop here
        if ($ifNotExists === true && $this->db->tableExists($table, false)) {
            $this->reset();

            return true;
        }

        $sql = $this->_createTable($table, false, $attributes);

        if (($result = $this->db->query($sql)) !== false) {
            if (isset($this->db->dataCache['table_names']) && ! in_array($table, $this->db->dataCache['table_names'], true)) {
                $this->db->dataCache['table_names'][] = $table;
            }

            // Most databases don't support creating indexes from within the CREATE TABLE statement
            if (! empty($this->keys)) {
                for ($i = 0, $sqls = $this->_processIndexes($table), $c = count($sqls); $i < $c; $i++) {
                    $this->db->query($sqls[$i]);
                }
            }
        }

        $this->reset();

        return $result;
    }

    /**
     * @return string SQL string
     *
     * @deprecated $ifNotExists is no longer used, and will be removed.
     */
    protected function _createTable(string $table, bool $ifNotExists, array $attributes)
    {
        $columns = $this->_processFields(true);

        for ($i = 0, $c = count($columns); $i < $c; $i++) {
            $columns[$i] = ($columns[$i]['_literal'] !== false) ? "\n\t" . $columns[$i]['_literal']
                : "\n\t" . $this->_processColumn($columns[$i]);
        }

        $columns = implode(',', $columns);

        $columns .= $this->_processPrimaryKeys($table);
        $columns .= current($this->_processForeignKeys($table));

        if ($this->createTableKeys === true) {
            $indexes = current($this->_processIndexes($table));
            if (is_string($indexes)) {
                $columns .= $indexes;
            }
        }

        return sprintf(
            $this->createTableStr . '%s',
            'CREATE TABLE',
            $this->db->escapeIdentifiers($table),
            $columns,
            $this->_createTableAttributes($attributes)
        );
    }

    protected function _createTableAttributes(array $attributes): string
    {
        $sql = '';

        foreach (array_keys($attributes) as $key) {
            if (is_string($key)) {
                $sql .= ' ' . strtoupper($key) . ' ' . $this->db->escape($attributes[$key]);
            }
        }

        return $sql;
    }

    /**
     * @return mixed
     *
     * @throws DatabaseException
     */
    public function dropTable(string $tableName, bool $ifExists = false, bool $cascade = false)
    {
        if ($tableName === '') {
            if ($this->db->DBDebug) {
                throw new DatabaseException('A table name is required for that operation.');
            }

            return false;
        }

        if ($this->db->DBPrefix && strpos($tableName, $this->db->DBPrefix) === 0) {
            $tableName = substr($tableName, strlen($this->db->DBPrefix));
        }

        if (($query = $this->_dropTable($this->db->DBPrefix . $tableName, $ifExists, $cascade)) === true) {
            return true;
        }

        $this->db->disableForeignKeyChecks();

        $query = $this->db->query($query);

        $this->db->enableForeignKeyChecks();

        if ($query && ! empty($this->db->dataCache['table_names'])) {
            $key = array_search(
                strtolower($this->db->DBPrefix . $tableName),
                array_map('strtolower', $this->db->dataCache['table_names']),
                true
            );

            if ($key !== false) {
                unset($this->db->dataCache['table_names'][$key]);
            }
        }

        return $query;
    }

    /**
     * Generates a platform-specific DROP TABLE string
     *
     * @return bool|string
     */
    protected function _dropTable(string $table, bool $ifExists, bool $cascade)
    {
        $sql = 'DROP TABLE';

        if ($ifExists) {
            if ($this->dropTableIfStr === false) {
                if (! $this->db->tableExists($table)) {
                    return true;
                }
            } else {
                $sql = sprintf($this->dropTableIfStr, $this->db->escapeIdentifiers($table));
            }
        }

        return $sql . ' ' . $this->db->escapeIdentifiers($table);
    }

    /**
     * @return mixed
     *
     * @throws DatabaseException
     */
    public function renameTable(string $tableName, string $newTableName)
    {
        if ($tableName === '' || $newTableName === '') {
            throw new InvalidArgumentException('A table name is required for that operation.');
        }

        if ($this->renameTableStr === false) {
            if ($this->db->DBDebug) {
                throw new DatabaseException('This feature is not available for the database you are using.');
            }

            return false;
        }

        $result = $this->db->query(sprintf(
            $this->renameTableStr,
            $this->db->escapeIdentifiers($this->db->DBPrefix . $tableName),
            $this->db->escapeIdentifiers($this->db->DBPrefix . $newTableName)
        ));

        if ($result && ! empty($this->db->dataCache['table_names'])) {
            $key = array_search(
                strtolower($this->db->DBPrefix . $tableName),
                array_map('strtolower', $this->db->dataCache['table_names']),
                true
            );

            if ($key !== false) {
                $this->db->dataCache['table_names'][$key] = $this->db->DBPrefix . $newTableName;
            }
        }

        return $result;
    }

    /**
     * @param array|string $field
     *
     * @throws DatabaseException
     */
    public function addColumn(string $table, $field): bool
    {
        // Work-around for literal column definitions
        if (! is_array($field)) {
            $field = [$field];
        }

        foreach (array_keys($field) as $k) {
            $this->addField([$k => $field[$k]]);
        }

        $sqls = $this->_alterTable('ADD', $this->db->DBPrefix . $table, $this->_processFields());
        $this->reset();
        if ($sqls === false) {
            if ($this->db->DBDebug) {
                throw new DatabaseException('This feature is not available for the database you are using.');
            }

            return false;
        }

        foreach ($sqls as $sql) {
            if ($this->db->query($sql) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array|string $columnName
     *
     * @return mixed
     *
     * @throws DatabaseException
     */
    public function dropColumn(string $table, $columnName)
    {
        $sql = $this->_alterTable('DROP', $this->db->DBPrefix . $table, $columnName);
        if ($sql === false) {
            if ($this->db->DBDebug) {
                throw new DatabaseException('This feature is not available for the database you are using.');
            }

            return false;
        }

        return $this->db->query($sql);
    }

    /**
     * @param array|string $field
     *
     * @throws DatabaseException
     */
    public function modifyColumn(string $table, $field): bool
    {
        // Work-around for literal column definitions
        if (! is_array($field)) {
            $field = [$field];
        }

        foreach (array_keys($field) as $k) {
            $this->addField([$k => $field[$k]]);
        }

        if ($this->fields === []) {
            throw new RuntimeException('Field information is required');
        }

        $sqls = $this->_alterTable('CHANGE', $this->db->DBPrefix . $table, $this->_processFields());
        $this->reset();
        if ($sqls === false) {
            if ($this->db->DBDebug) {
                throw new DatabaseException('This feature is not available for the database you are using.');
            }

            return false;
        }

        if (is_array($sqls)) {
            foreach ($sqls as $sql) {
                if ($this->db->query($sql) === false) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param array|string $fields
     *
     * @return false|string|string[]
     */
    protected function _alterTable(string $alterType, string $table, $fields)
    {
        $sql = 'ALTER TABLE ' . $this->db->escapeIdentifiers($table) . ' ';

        // DROP has everything it needs now.
        if ($alterType === 'DROP') {
            if (is_string($fields)) {
                $fields = explode(',', $fields);
            }

            $fields = array_map(fn ($field) => 'DROP COLUMN ' . $this->db->escapeIdentifiers(trim($field)), $fields);

            return $sql . implode(', ', $fields);
        }

        $sql .= ($alterType === 'ADD') ? 'ADD ' : $alterType . ' COLUMN ';

        $sqls = [];

        foreach ($fields as $data) {
            $sqls[] = $sql . ($data['_literal'] !== false
                ? $data['_literal']
                : $this->_processColumn($data));
        }

        return $sqls;
    }

    /**
     * Process fields
     */
    protected function _processFields(bool $createTable = false): array
    {
        $fields = [];

        foreach ($this->fields as $key => $attributes) {
            if (! is_array($attributes)) {
                $fields[] = ['_literal' => $attributes];

                continue;
            }

            $attributes = array_change_key_case($attributes, CASE_UPPER);

            if ($createTable === true && empty($attributes['TYPE'])) {
                continue;
            }

            if (isset($attributes['TYPE'])) {
                $this->_attributeType($attributes);
            }

            $field = [
                'name'           => $key,
                'new_name'       => $attributes['NAME'] ?? null,
                'type'           => $attributes['TYPE'] ?? null,
                'length'         => '',
                'unsigned'       => '',
                'null'           => '',
                'unique'         => '',
                'default'        => '',
                'auto_increment' => '',
                '_literal'       => false,
            ];

            if (isset($attributes['TYPE'])) {
                $this->_attributeUnsigned($attributes, $field);
            }

            if ($createTable === false) {
                if (isset($attributes['AFTER'])) {
                    $field['after'] = $attributes['AFTER'];
                } elseif (isset($attributes['FIRST'])) {
                    $field['first'] = (bool) $attributes['FIRST'];
                }
            }

            $this->_attributeDefault($attributes, $field);

            if (isset($attributes['NULL'])) {
                $nullString = ' ' . $this->null;

                if ($attributes['NULL'] === true) {
                    $field['null'] = empty($this->null) ? '' : $nullString;
                } elseif ($attributes['NULL'] === $nullString) {
                    $field['null'] = $nullString;
                } elseif ($attributes['NULL'] === '') {
                    $field['null'] = '';
                } else {
                    $field['null'] = ' NOT ' . $this->null;
                }
            } elseif ($createTable === true) {
                $field['null'] = ' NOT ' . $this->null;
            }

            $this->_attributeAutoIncrement($attributes, $field);
            $this->_attributeUnique($attributes, $field);

            if (isset($attributes['COMMENT'])) {
                $field['comment'] = $this->db->escape($attributes['COMMENT']);
            }

            if (isset($attributes['TYPE']) && ! empty($attributes['CONSTRAINT'])) {
                if (is_array($attributes['CONSTRAINT'])) {
                    $attributes['CONSTRAINT'] = $this->db->escape($attributes['CONSTRAINT']);
                    $attributes['CONSTRAINT'] = implode(',', $attributes['CONSTRAINT']);
                }

                $field['length'] = '(' . $attributes['CONSTRAINT'] . ')';
            }

            $fields[] = $field;
        }

        return $fields;
    }

    /**
     * Process column
     */
    protected function _processColumn(array $field): string
    {
        return $this->db->escapeIdentifiers($field['name'])
            . ' ' . $field['type'] . $field['length']
            . $field['unsigned']
            . $field['default']
            . $field['null']
            . $field['auto_increment']
            . $field['unique'];
    }

    /**
     * Performs a data type mapping between different databases.
     */
    protected function _attributeType(array &$attributes)
    {
        // Usually overridden by drivers
    }

    /**
     * Depending on the unsigned property value:
     *
     *    - TRUE will always set $field['unsigned'] to 'UNSIGNED'
     *    - FALSE will always set $field['unsigned'] to ''
     *    - array(TYPE) will set $field['unsigned'] to 'UNSIGNED',
     *        if $attributes['TYPE'] is found in the array
     *    - array(TYPE => UTYPE) will change $field['type'],
     *        from TYPE to UTYPE in case of a match
     */
    protected function _attributeUnsigned(array &$attributes, array &$field)
    {
        if (empty($attributes['UNSIGNED']) || $attributes['UNSIGNED'] !== true) {
            return;
        }

        // Reset the attribute in order to avoid issues if we do type conversion
        $attributes['UNSIGNED'] = false;

        if (is_array($this->unsigned)) {
            foreach (array_keys($this->unsigned) as $key) {
                if (is_int($key) && strcasecmp($attributes['TYPE'], $this->unsigned[$key]) === 0) {
                    $field['unsigned'] = ' UNSIGNED';

                    return;
                }

                if (is_string($key) && strcasecmp($attributes['TYPE'], $key) === 0) {
                    $field['type'] = $key;

                    return;
                }
            }

            return;
        }

        $field['unsigned'] = ($this->unsigned === true) ? ' UNSIGNED' : '';
    }

    protected function _attributeDefault(array &$attributes, array &$field)
    {
        if ($this->default === false) {
            return;
        }

        if (array_key_exists('DEFAULT', $attributes)) {
            if ($attributes['DEFAULT'] === null) {
                $field['default'] = empty($this->null) ? '' : $this->default . $this->null;

                // Override the NULL attribute if that's our default
                $attributes['NULL'] = true;
                $field['null']      = empty($this->null) ? '' : ' ' . $this->null;
            } elseif ($attributes['DEFAULT'] instanceof RawSql) {
                $field['default'] = $this->default . $attributes['DEFAULT'];
            } else {
                $field['default'] = $this->default . $this->db->escape($attributes['DEFAULT']);
            }
        }
    }

    protected function _attributeUnique(array &$attributes, array &$field)
    {
        if (! empty($attributes['UNIQUE']) && $attributes['UNIQUE'] === true) {
            $field['unique'] = ' UNIQUE';
        }
    }

    protected function _attributeAutoIncrement(array &$attributes, array &$field)
    {
        if (! empty($attributes['AUTO_INCREMENT']) && $attributes['AUTO_INCREMENT'] === true
            && stripos($field['type'], 'int') !== false
        ) {
            $field['auto_increment'] = ' AUTO_INCREMENT';
        }
    }

    /**
     * Generates SQL to add primary key
     *
     * @param bool $asQuery When true returns stand alone SQL, else partial SQL used with CREATE TABLE
     */
    protected function _processPrimaryKeys(string $table, bool $asQuery = false): string
    {
        $sql = '';

        if (isset($this->primaryKeys['fields'])) {
            for ($i = 0, $c = count($this->primaryKeys['fields']); $i < $c; $i++) {
                if (! isset($this->fields[$this->primaryKeys['fields'][$i]])) {
                    unset($this->primaryKeys['fields'][$i]);
                }
            }
        }

        if (isset($this->primaryKeys['fields']) && $this->primaryKeys['fields'] !== []) {
            if ($asQuery === true) {
                $sql .= 'ALTER TABLE ' . $this->db->escapeIdentifiers($this->db->DBPrefix . $table) . ' ADD ';
            } else {
                $sql .= ",\n\t";
            }
            $sql .= 'CONSTRAINT ' . $this->db->escapeIdentifiers(($this->primaryKeys['keyName'] === '' ?
                'pk_' . $table :
                $this->primaryKeys['keyName']))
                    . ' PRIMARY KEY(' . implode(', ', $this->db->escapeIdentifiers($this->primaryKeys['fields'])) . ')';
        }

        return $sql;
    }

    /**
     * Executes Sql to add indexes without createTable
     */
    public function processIndexes(string $table): bool
    {
        $sqls = [];
        $fk   = $this->foreignKeys;

        if ($this->fields === []) {
            $this->fields = array_flip(array_map(
                static fn ($columnName) => $columnName->name,
                $this->db->getFieldData($this->db->DBPrefix . $table)
            ));
        }

        $fields = $this->fields;

        if ($this->keys !== []) {
            $sqls = $this->_processIndexes($this->db->DBPrefix . $table, true);
        }

        if ($this->primaryKeys !== []) {
            $sqls[] = $this->_processPrimaryKeys($table, true);
        }

        $this->foreignKeys = $fk;
        $this->fields      = $fields;

        if ($this->foreignKeys !== []) {
            $sqls = array_merge($sqls, $this->_processForeignKeys($table, true));
        }

        foreach ($sqls as $sql) {
            if ($this->db->query($sql) === false) {
                return false;
            }
        }

        $this->reset();

        return true;
    }

    /**
     * Generates SQL to add indexes
     *
     * @param bool $asQuery When true returns stand alone SQL, else partial SQL used with CREATE TABLE
     */
    protected function _processIndexes(string $table, bool $asQuery = false): array
    {
        $sqls = [];

        for ($i = 0, $c = count($this->keys); $i < $c; $i++) {
            for ($i2 = 0, $c2 = count($this->keys[$i]['fields']); $i2 < $c2; $i2++) {
                if (! isset($this->fields[$this->keys[$i]['fields'][$i2]])) {
                    unset($this->keys[$i]['fields'][$i2]);
                }
            }

            if (count($this->keys[$i]['fields']) <= 0) {
                continue;
            }

            $keyName = $this->db->escapeIdentifiers(($this->keys[$i]['keyName'] === '') ?
                $table . '_' . implode('_', $this->keys[$i]['fields']) :
                $this->keys[$i]['keyName']);

            if (in_array($i, $this->uniqueKeys, true)) {
                if ($this->db->DBDriver === 'SQLite3') {
                    $sqls[] = 'CREATE UNIQUE INDEX ' . $keyName
                        . ' ON ' . $this->db->escapeIdentifiers($table)
                        . ' (' . implode(', ', $this->db->escapeIdentifiers($this->keys[$i]['fields'])) . ')';
                } else {
                    $sqls[] = 'ALTER TABLE ' . $this->db->escapeIdentifiers($table)
                        . ' ADD CONSTRAINT ' . $keyName
                        . ' UNIQUE (' . implode(', ', $this->db->escapeIdentifiers($this->keys[$i]['fields'])) . ')';
                }

                continue;
            }

            $sqls[] = 'CREATE INDEX ' . $keyName
                . ' ON ' . $this->db->escapeIdentifiers($table)
                . ' (' . implode(', ', $this->db->escapeIdentifiers($this->keys[$i]['fields'])) . ')';
        }

        return $sqls;
    }

    /**
     * Generates SQL to add foreign keys
     *
     * @param bool $asQuery When true returns stand alone SQL, else partial SQL used with CREATE TABLE
     */
    protected function _processForeignKeys(string $table, bool $asQuery = false): array
    {
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

        $sqls = [''];

        foreach ($this->foreignKeys as $index => $fkey) {
            if ($asQuery === false) {
                $index = 0;
            } else {
                $sqls[$index] = '';
            }

            $nameIndex = $fkey['fkName'] !== '' ?
            $fkey['fkName'] :
            $table . '_' . implode('_', $fkey['field']) . ($this->db->DBDriver === 'OCI8' ? '_fk' : '_foreign');

            $nameIndexFilled      = $this->db->escapeIdentifiers($nameIndex);
            $foreignKeyFilled     = implode(', ', $this->db->escapeIdentifiers($fkey['field']));
            $referenceTableFilled = $this->db->escapeIdentifiers($this->db->DBPrefix . $fkey['referenceTable']);
            $referenceFieldFilled = implode(', ', $this->db->escapeIdentifiers($fkey['referenceField']));

            if ($asQuery === true) {
                $sqls[$index] .= 'ALTER TABLE ' . $this->db->escapeIdentifiers($this->db->DBPrefix . $table) . ' ADD ';
            } else {
                $sqls[$index] .= ",\n\t";
            }

            $formatSql = 'CONSTRAINT %s FOREIGN KEY (%s) REFERENCES %s(%s)';
            $sqls[$index] .= sprintf($formatSql, $nameIndexFilled, $foreignKeyFilled, $referenceTableFilled, $referenceFieldFilled);

            if ($fkey['onDelete'] !== false && in_array($fkey['onDelete'], $this->fkAllowActions, true)) {
                $sqls[$index] .= ' ON DELETE ' . $fkey['onDelete'];
            }

            if ($this->db->DBDriver !== 'OCI8' && $fkey['onUpdate'] !== false && in_array($fkey['onUpdate'], $this->fkAllowActions, true)) {
                $sqls[$index] .= ' ON UPDATE ' . $fkey['onUpdate'];
            }
        }

        return $sqls;
    }

    /**
     * Resets table creation vars
     */
    public function reset()
    {
        $this->fields = $this->keys = $this->uniqueKeys = $this->primaryKeys = $this->foreignKeys = [];
    }
}
