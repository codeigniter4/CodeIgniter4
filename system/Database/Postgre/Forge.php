<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Database\Postgre;

use CodeIgniter\Database\Forge as BaseForge;

/**
 * Forge for Postgre
 */
class Forge extends BaseForge
{
    /**
     * CHECK DATABASE EXIST statement
     *
     * @var string
     */
    protected $checkDatabaseExistStr = 'SELECT 1 FROM pg_database WHERE datname = ?';

    /**
     * DROP CONSTRAINT statement
     *
     * @var string
     */
    protected $dropConstraintStr = 'ALTER TABLE %s DROP CONSTRAINT %s';

    /**
     * DROP INDEX statement
     *
     * @var string
     */
    protected $dropIndexStr = 'DROP INDEX %s';

    /**
     * UNSIGNED support
     *
     * @var array
     */
    protected $_unsigned = [
        'INT2'     => 'INTEGER',
        'SMALLINT' => 'INTEGER',
        'INT'      => 'BIGINT',
        'INT4'     => 'BIGINT',
        'INTEGER'  => 'BIGINT',
        'INT8'     => 'NUMERIC',
        'BIGINT'   => 'NUMERIC',
        'REAL'     => 'DOUBLE PRECISION',
        'FLOAT'    => 'DOUBLE PRECISION',
    ];

    /**
     * NULL value representation in CREATE/ALTER TABLE statements
     *
     * @var string
     *
     * @internal
     */
    protected $null = 'NULL';

    /**
     * @var Connection
     */
    protected $db;

    /**
     * CREATE TABLE attributes
     *
     * @param array $attributes Associative array of table attributes
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
        if (in_array($alterType, ['DROP', 'ADD'], true)) {
            return parent::_alterTable($alterType, $table, $processedFields);
        }

        $sql  = 'ALTER TABLE ' . $this->db->escapeIdentifiers($table);
        $sqls = [];

        foreach ($processedFields as $field) {
            if ($field['_literal'] !== false) {
                return false;
            }

            if (version_compare($this->db->getVersion(), '8', '>=') && isset($field['type'])) {
                $sqls[] = $sql . ' ALTER COLUMN ' . $this->db->escapeIdentifiers($field['name'])
                    . " TYPE {$field['type']}{$field['length']}";
            }

            if (! empty($field['default'])) {
                $sqls[] = $sql . ' ALTER COLUMN ' . $this->db->escapeIdentifiers($field['name'])
                    . " SET DEFAULT {$field['default']}";
            }

            $nullable = true; // Nullable by default.
            if (isset($field['null']) && ($field['null'] === false || $field['null'] === ' NOT ' . $this->null)) {
                $nullable = false;
            }
            $sqls[] = $sql . ' ALTER COLUMN ' . $this->db->escapeIdentifiers($field['name'])
                . ($nullable ? ' DROP' : ' SET') . ' NOT NULL';

            if (! empty($field['new_name'])) {
                $sqls[] = $sql . ' RENAME COLUMN ' . $this->db->escapeIdentifiers($field['name'])
                    . ' TO ' . $this->db->escapeIdentifiers($field['new_name']);
            }

            if (! empty($field['comment'])) {
                $sqls[] = 'COMMENT ON COLUMN' . $this->db->escapeIdentifiers($table)
                    . '.' . $this->db->escapeIdentifiers($field['name'])
                    . " IS {$field['comment']}";
            }
        }

        return $sqls;
    }

    /**
     * Process column
     */
    protected function _processColumn(array $processedField): string
    {
        return $this->db->escapeIdentifiers($processedField['name'])
            . ' ' . $processedField['type'] . ($processedField['type'] === 'text' ? '' : $processedField['length'])
            . $processedField['default']
            . $processedField['null']
            . $processedField['auto_increment']
            . $processedField['unique'];
    }

    /**
     * Performs a data type mapping between different databases.
     */
    protected function _attributeType(array &$attributes)
    {
        // Reset field lengths for data types that don't support it
        if (isset($attributes['CONSTRAINT']) && str_contains(strtolower($attributes['TYPE']), 'int')) {
            $attributes['CONSTRAINT'] = null;
        }

        switch (strtoupper($attributes['TYPE'])) {
            case 'TINYINT':
                $attributes['TYPE']     = 'SMALLINT';
                $attributes['UNSIGNED'] = false;
                break;

            case 'MEDIUMINT':
                $attributes['TYPE']     = 'INTEGER';
                $attributes['UNSIGNED'] = false;
                break;

            case 'DATETIME':
                $attributes['TYPE'] = 'TIMESTAMP';
                break;

            case 'BLOB':
                $attributes['TYPE'] = 'BYTEA';
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
        if (! empty($attributes['AUTO_INCREMENT']) && $attributes['AUTO_INCREMENT'] === true) {
            $field['type'] = $field['type'] === 'NUMERIC' || $field['type'] === 'BIGINT' ? 'BIGSERIAL' : 'SERIAL';
        }
    }

    /**
     * Generates a platform-specific DROP TABLE string
     */
    protected function _dropTable(string $table, bool $ifExists, bool $cascade): string
    {
        $sql = parent::_dropTable($table, $ifExists, $cascade);

        if ($cascade) {
            $sql .= ' CASCADE';
        }

        return $sql;
    }

    /**
     * Constructs sql to check if key is a constraint.
     */
    protected function _dropKeyAsConstraint(string $table, string $constraintName): string
    {
        return "SELECT con.conname
               FROM pg_catalog.pg_constraint con
                INNER JOIN pg_catalog.pg_class rel
                           ON rel.oid = con.conrelid
                INNER JOIN pg_catalog.pg_namespace nsp
                           ON nsp.oid = connamespace
               WHERE nsp.nspname = '{$this->db->schema}'
                     AND rel.relname = '" . trim($table, '"') . "'
                     AND con.conname = '" . trim($constraintName, '"') . "'";
    }
}
