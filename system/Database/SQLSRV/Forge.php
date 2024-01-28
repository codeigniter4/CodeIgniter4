<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Database\SQLSRV;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Forge as BaseForge;

/**
 * Forge for SQLSRV
 */
class Forge extends BaseForge
{
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
    protected $dropIndexStr;

    /**
     * CREATE DATABASE IF statement
     *
     * @todo missing charset, collat & check for existent
     *
     * @var string
     */
    protected $createDatabaseIfStr = "DECLARE @DBName VARCHAR(255) = '%s'\nDECLARE @SQL VARCHAR(max) = 'IF DB_ID( ''' + @DBName + ''' ) IS NULL CREATE DATABASE ' + @DBName\nEXEC( @SQL )";

    /**
     * CREATE DATABASE IF statement
     *
     * @todo missing charset & collat
     *
     * @var string
     */
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
     *
     * @see https://docs.microsoft.com/en-us/sql/relational-databases/system-stored-procedures/sp-rename-transact-sql?view=sql-server-2017
     * 'EXEC sp_rename %s , %s ;'
     *
     * @var string
     */
    protected $renameTableStr;

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
     * Foreign Key Allowed Actions
     *
     * @var array
     */
    protected $fkAllowActions = ['CASCADE', 'SET NULL', 'NO ACTION', 'RESTRICT', 'SET DEFAULT'];

    /**
     * CREATE TABLE IF statement
     *
     * @var string
     *
     * @deprecated This is no longer used.
     */
    protected $createTableIfStr;

    /**
     * CREATE TABLE statement
     *
     * @var string
     */
    protected $createTableStr;

    public function __construct(BaseConnection $db)
    {
        parent::__construct($db);

        $this->createTableStr = '%s ' . $this->db->escapeIdentifiers($this->db->schema) . ".%s (%s\n) ";
        $this->renameTableStr = 'EXEC sp_rename [' . $this->db->escapeIdentifiers($this->db->schema) . '.%s] , %s ;';

        $this->dropConstraintStr = 'ALTER TABLE ' . $this->db->escapeIdentifiers($this->db->schema) . '.%s DROP CONSTRAINT %s';
        $this->dropIndexStr      = 'DROP INDEX %s ON ' . $this->db->escapeIdentifiers($this->db->schema) . '.%s';
    }

    /**
     * CREATE TABLE attributes
     */
    protected function _createTableAttributes(array $attributes): string
    {
        return '';
    }

    /**
     * @param array|string $processedFields Processed column definitions
     *                                      or column names to DROP
     *
     * @return         false|list<string>|string                            SQL string or false
     * @phpstan-return ($alterType is 'DROP' ? string : list<string>|false)
     */
    protected function _alterTable(string $alterType, string $table, $processedFields)
    {
        // Handle DROP here
        if ($alterType === 'DROP') {
            $columnNamesToDrop = $processedFields;

            // check if fields are part of any indexes
            $indexData = $this->db->getIndexData($table);

            foreach ($indexData as $index) {
                if (is_string($columnNamesToDrop)) {
                    $columnNamesToDrop = explode(',', $columnNamesToDrop);
                }

                $fld = array_intersect($columnNamesToDrop, $index->fields);

                // Drop index if field is part of an index
                if ($fld !== []) {
                    $this->_dropIndex($table, $index);
                }
            }

            $fullTable = $this->db->escapeIdentifiers($this->db->schema) . '.' . $this->db->escapeIdentifiers($table);

            // Drop default constraints
            $fields = implode(',', $this->db->escape((array) $columnNamesToDrop));

            $sql = <<<SQL
                SELECT name
                FROM SYS.DEFAULT_CONSTRAINTS
                WHERE PARENT_OBJECT_ID = OBJECT_ID('{$fullTable}')
                AND PARENT_COLUMN_ID IN (
                SELECT column_id FROM sys.columns WHERE NAME IN ({$fields}) AND object_id = OBJECT_ID(N'{$fullTable}')
                )
                SQL;

            foreach ($this->db->query($sql)->getResultArray() as $index) {
                $this->db->query('ALTER TABLE ' . $fullTable . ' DROP CONSTRAINT ' . $index['name'] . '');
            }

            $sql = 'ALTER TABLE ' . $fullTable . ' DROP ';

            $fields = array_map(static fn ($item) => 'COLUMN [' . trim($item) . ']', (array) $columnNamesToDrop);

            return $sql . implode(',', $fields);
        }

        $sql = 'ALTER TABLE ' . $this->db->escapeIdentifiers($this->db->schema) . '.' . $this->db->escapeIdentifiers($table);
        $sql .= ($alterType === 'ADD') ? 'ADD ' : ' ';

        $sqls = [];

        if ($alterType === 'ADD') {
            foreach ($processedFields as $field) {
                $sqls[] = $sql . ($field['_literal'] !== false ? $field['_literal'] : $this->_processColumn($field));
            }

            return $sqls;
        }

        foreach ($processedFields as $field) {
            if ($field['_literal'] !== false) {
                return false;
            }

            if (isset($field['type'])) {
                $sqls[] = $sql . ' ALTER COLUMN ' . $this->db->escapeIdentifiers($field['name'])
                    . " {$field['type']}{$field['length']}";
            }

            if (! empty($field['default'])) {
                $sqls[] = $sql . ' ALTER COLUMN ADD CONSTRAINT ' . $this->db->escapeIdentifiers($field['name']) . '_def'
                    . " DEFAULT {$field['default']} FOR " . $this->db->escapeIdentifiers($field['name']);
            }

            $nullable = true; // Nullable by default.
            if (isset($field['null']) && ($field['null'] === false || $field['null'] === ' NOT ' . $this->null)) {
                $nullable = false;
            }
            $sqls[] = $sql . ' ALTER COLUMN ' . $this->db->escapeIdentifiers($field['name'])
                . " {$field['type']}{$field['length']} " . ($nullable === true ? '' : 'NOT') . ' NULL';

            if (! empty($field['comment'])) {
                $sqls[] = 'EXEC sys.sp_addextendedproperty '
                    . "@name=N'Caption', @value=N'" . $field['comment'] . "' , "
                    . "@level0type=N'SCHEMA',@level0name=N'" . $this->db->schema . "', "
                    . "@level1type=N'TABLE',@level1name=N'" . $this->db->escapeIdentifiers($table) . "', "
                    . "@level2type=N'COLUMN',@level2name=N'" . $this->db->escapeIdentifiers($field['name']) . "'";
            }

            if (! empty($field['new_name'])) {
                $sqls[] = "EXEC sp_rename  '[" . $this->db->schema . '].[' . $table . '].[' . $field['name'] . "]' , '" . $field['new_name'] . "', 'COLUMN';";
            }
        }

        return $sqls;
    }

    /**
     * Drop index for table
     *
     * @return mixed
     */
    protected function _dropIndex(string $table, object $indexData)
    {
        if ($indexData->type === 'PRIMARY') {
            $sql = 'ALTER TABLE [' . $this->db->schema . '].[' . $table . '] DROP [' . $indexData->name . ']';
        } else {
            $sql = 'DROP INDEX [' . $indexData->name . '] ON [' . $this->db->schema . '].[' . $table . ']';
        }

        return $this->db->simpleQuery($sql);
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
                $sqls[] = 'ALTER TABLE '
                    . $this->db->escapeIdentifiers($this->db->schema) . '.' . $this->db->escapeIdentifiers($table)
                    . ' ADD CONSTRAINT ' . $keyName
                    . ' UNIQUE (' . implode(', ', $this->db->escapeIdentifiers($this->keys[$i]['fields'])) . ');';

                continue;
            }

            $sqls[] = 'CREATE INDEX '
                . $keyName
                . ' ON ' . $this->db->escapeIdentifiers($this->db->schema) . '.' . $this->db->escapeIdentifiers($table)
                . ' (' . implode(', ', $this->db->escapeIdentifiers($this->keys[$i]['fields'])) . ');';
        }

        return $sqls;
    }

    /**
     * Process column
     */
    protected function _processColumn(array $processedField): string
    {
        return $this->db->escapeIdentifiers($processedField['name'])
            . (empty($processedField['new_name']) ? '' : ' ' . $this->db->escapeIdentifiers($processedField['new_name']))
            . ' ' . $processedField['type'] . ($processedField['type'] === 'text' ? '' : $processedField['length'])
            . $processedField['default']
            . $processedField['null']
            . $processedField['auto_increment']
            . ''
            . $processedField['unique'];
    }

    /**
     * Performs a data type mapping between different databases.
     */
    protected function _attributeType(array &$attributes)
    {
        // Reset field lengths for data types that don't support it
        if (isset($attributes['CONSTRAINT']) && stripos($attributes['TYPE'], 'int') !== false) {
            $attributes['CONSTRAINT'] = null;
        }

        switch (strtoupper($attributes['TYPE'])) {
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

            case 'TIMESTAMP':
                $attributes['TYPE'] = 'DATETIME';
                break;

            case 'BOOLEAN':
                $attributes['TYPE'] = 'BIT';
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
        if (! empty($attributes['AUTO_INCREMENT']) && $attributes['AUTO_INCREMENT'] === true && stripos($field['type'], 'INT') !== false) {
            $field['auto_increment'] = ' IDENTITY(1,1)';
        }
    }

    /**
     * Generates a platform-specific DROP TABLE string
     *
     * @todo Support for cascade
     */
    protected function _dropTable(string $table, bool $ifExists, bool $cascade): string
    {
        $sql = 'DROP TABLE';

        if ($ifExists) {
            $sql .= ' IF EXISTS ';
        }

        $table = ' [' . $this->db->database . '].[' . $this->db->schema . '].[' . $table . '] ';

        $sql .= $table;

        if ($cascade) {
            $sql .= '';
        }

        return $sql;
    }

    /**
     * Constructs sql to check if key is a constraint.
     */
    protected function _dropKeyAsConstraint(string $table, string $constraintName): string
    {
        return "SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
                WHERE TABLE_NAME= '" . trim($table, '"') . "'
                AND CONSTRAINT_NAME = '" . trim($constraintName, '"') . "'";
    }
}
