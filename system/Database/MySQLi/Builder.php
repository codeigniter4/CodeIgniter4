<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Database\MySQLi;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Database\RawSql;

/**
 * Builder for MySQLi
 */
class Builder extends BaseBuilder
{
    /**
     * Identifier escape character
     *
     * @var string
     */
    protected $escapeChar = '`';

    /**
     * Specifies which sql statements
     * support the ignore option.
     *
     * @var array
     */
    protected $supportedIgnoreStatements = [
        'update' => 'IGNORE',
        'insert' => 'IGNORE',
        'delete' => 'IGNORE',
    ];

    /**
     * FROM tables
     *
     * Groups tables in FROM clauses if needed, so there is no confusion
     * about operator precedence.
     *
     * Note: This is only used (and overridden) by MySQL.
     */
    protected function _fromTables(): string
    {
        if (! empty($this->QBJoin) && count($this->QBFrom) > 1) {
            return '(' . implode(', ', $this->QBFrom) . ')';
        }

        return implode(', ', $this->QBFrom);
    }

    /**
     * Generates a platform-specific batch update string from the supplied data
     */
    protected function _updateBatch(string $table, array $values, string $index): string
    {
        // this is a work around until the rest of the platform is refactored
        if ($index !== '') {
            $this->QBOptions['constraints'] = [$index];
        }
        $keys = array_keys(current($values));

        $sql = $this->QBOptions['sql'] ?? '';

        // if this is the first iteration of batch then we need to build skeleton sql
        if ($sql === '') {
            $constraints = $this->QBOptions['constraints'] ?? [];

            if ($constraints === []) {
                if ($this->db->DBDebug) {
                    throw new DatabaseException('You must specify a constraint to match on for batch updates.'); // @codeCoverageIgnore
                }

                return ''; // @codeCoverageIgnore
            }

            $updateFields = $this->QBOptions['updateFields'] ??
                $this->updateFields($keys, false, $constraints)->QBOptions['updateFields'] ??
                [];

            $alias = $this->QBOptions['alias'] ?? '`_u`';

            $sql = 'UPDATE ' . $this->compileIgnore('update') . $table . "\n";

            $sql .= 'INNER JOIN (' . "\n%s";

            $sql .= ') ' . $alias . "\n";

            $sql .= 'ON ' . implode(
                ' AND ',
                array_map(
                    static fn ($key) => ($key instanceof RawSql ?
                    $key :
                    $table . '.' . $key . ' = ' . $alias . '.' . $key),
                    $constraints
                )
            ) . "\n";

            $sql .= 'SET' . "\n";

            $sql .= implode(
                ",\n",
                array_map(
                    static fn ($key, $value) => $table . '.' . $key . ($value instanceof RawSql ?
                        ' = ' . $value :
                        ' = ' . $alias . '.' . $value),
                    array_keys($updateFields),
                    $updateFields
                )
            );

            $this->QBOptions['sql'] = $sql;
        }

        if (isset($this->QBOptions['fromQuery'])) {
            $data = $this->QBOptions['fromQuery'];
        } else {
            $data = implode(
                " UNION ALL\n",
                array_map(
                    static fn ($value) => 'SELECT ' . implode(', ', array_map(
                        static fn ($key, $index) => $index . ' ' . $key,
                        $keys,
                        $value
                    )),
                    $values
                )
            ) . "\n";
        }

        return sprintf($sql, $data);
    }
}
