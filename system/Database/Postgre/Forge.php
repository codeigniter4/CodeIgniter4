<?php

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
     * CREATE TABLE attributes
     *
     * @param array $attributes Associative array of table attributes
     */
    protected function _createTableAttributes(array $attributes): string
    {
        return '';
    }

    /**
     * @param mixed $field
     *
     * @return array|bool|string
     */
    protected function _alterTable(string $alterType, string $table, $field)
    {
        if (in_array($alterType, ['DROP', 'ADD'], true)) {
            return parent::_alterTable($alterType, $table, $field);
        }

        $sql  = 'ALTER TABLE ' . $this->db->escapeIdentifiers($table);
        $sqls = [];

        foreach ($field as $data) {
            if ($data['_literal'] !== false) {
                return false;
            }

            if (version_compare($this->db->getVersion(), '8', '>=') && isset($data['type'])) {
                $sqls[] = $sql . ' ALTER COLUMN ' . $this->db->escapeIdentifiers($data['name'])
                    . " TYPE {$data['type']}{$data['length']}";
            }

            if (! empty($data['default'])) {
                $sqls[] = $sql . ' ALTER COLUMN ' . $this->db->escapeIdentifiers($data['name'])
                    . " SET DEFAULT {$data['default']}";
            }

            if (isset($data['null'])) {
                $sqls[] = $sql . ' ALTER COLUMN ' . $this->db->escapeIdentifiers($data['name'])
                    . ($data['null'] === true ? ' DROP' : ' SET') . ' NOT NULL';
            }

            if (! empty($data['new_name'])) {
                $sqls[] = $sql . ' RENAME COLUMN ' . $this->db->escapeIdentifiers($data['name'])
                    . ' TO ' . $this->db->escapeIdentifiers($data['new_name']);
            }

            if (! empty($data['comment'])) {
                $sqls[] = 'COMMENT ON COLUMN' . $this->db->escapeIdentifiers($table)
                    . '.' . $this->db->escapeIdentifiers($data['name'])
                    . " IS {$data['comment']}";
            }
        }

        return $sqls;
    }

    /**
     * Process column
     */
    protected function _processColumn(array $field): string
    {
        return $this->db->escapeIdentifiers($field['name'])
            . ' ' . $field['type'] . $field['length']
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
        // Reset field lengths for data types that don't support it
        if (isset($attributes['CONSTRAINT']) && stripos($attributes['TYPE'], 'int') !== false) {
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

        if ($cascade === true) {
            $sql .= ' CASCADE';
        }

        return $sql;
    }
}
