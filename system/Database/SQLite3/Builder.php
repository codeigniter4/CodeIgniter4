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

namespace CodeIgniter\Database\SQLite3;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Database\RawSql;
use InvalidArgumentException;

/**
 * Builder for SQLite3
 */
class Builder extends BaseBuilder
{
    /**
     * Default installs of SQLite typically do not
     * support limiting delete clauses.
     *
     * @var bool
     */
    protected $canLimitDeletes = false;

    /**
     * Default installs of SQLite do no support
     * limiting update queries in combo with WHERE.
     *
     * @var bool
     */
    protected $canLimitWhereUpdates = false;

    /**
     * ORDER BY random keyword
     *
     * @var array
     */
    protected $randomKeyword = [
        'RANDOM()',
    ];

    /**
     * @var array
     */
    protected $supportedIgnoreStatements = [
        'insert' => 'OR IGNORE',
    ];

    /**
     * Replace statement
     *
     * Generates a platform-specific replace string from the supplied data
     */
    protected function _replace(string $table, array $keys, array $values): string
    {
        return 'INSERT OR ' . parent::_replace($table, $keys, $values);
    }

    /**
     * Generates a platform-specific truncate string from the supplied data
     *
     * If the database does not support the TRUNCATE statement,
     * then this method maps to 'DELETE FROM table'
     */
    protected function _truncate(string $table): string
    {
        return 'DELETE FROM ' . $table;
    }

    /**
     * Generates a platform-specific batch update string from the supplied data
     */
    protected function _updateBatch(string $table, array $keys, array $values): string
    {
        if (version_compare($this->db->getVersion(), '3.33.0') >= 0) {
            return parent::_updateBatch($table, $keys, $values);
        }

        $constraints = $this->QBOptions['constraints'] ?? [];

        if ($constraints === []) {
            if ($this->db->DBDebug) {
                throw new DatabaseException('You must specify a constraint to match on for batch updates.');
            }

            return ''; // @codeCoverageIgnore
        }

        if (count($constraints) > 1 || isset($this->QBOptions['setQueryAsData']) || (current($constraints) instanceof RawSql)) {
            throw new DatabaseException('You are trying to use a feature which requires SQLite version 3.33 or higher.');
        }

        $index = current($constraints);

        $ids   = [];
        $final = [];

        foreach ($values as $val) {
            $val = array_combine($keys, $val);

            $ids[] = $val[$index];

            foreach (array_keys($val) as $field) {
                if ($field !== $index) {
                    $final[$field][] = 'WHEN ' . $index . ' = ' . $val[$index] . ' THEN ' . $val[$field];
                }
            }
        }

        $cases = '';

        foreach ($final as $k => $v) {
            $cases .= $k . " = CASE \n"
                . implode("\n", $v) . "\n"
                . 'ELSE ' . $k . ' END, ';
        }

        $this->where($index . ' IN(' . implode(',', $ids) . ')', null, false);

        return 'UPDATE ' . $this->compileIgnore('update') . $table . ' SET ' . substr($cases, 0, -2) . $this->compileWhereHaving('QBWhere');
    }

    /**
     * Generates a platform-specific upsertBatch string from the supplied data
     *
     * @throws DatabaseException
     */
    protected function _upsertBatch(string $table, array $keys, array $values): string
    {
        $sql = $this->QBOptions['sql'] ?? '';

        // if this is the first iteration of batch then we need to build skeleton sql
        if ($sql === '') {
            $constraints = $this->QBOptions['constraints'] ?? [];

            if (empty($constraints)) {
                $fieldNames = array_map(static fn ($columnName): string => trim($columnName, '`'), $keys);

                $allIndexes = array_filter($this->db->getIndexData($table), static function ($index) use ($fieldNames): bool {
                    $hasAllFields = count(array_intersect($index->fields, $fieldNames)) === count($index->fields);

                    return ($index->type === 'PRIMARY' || $index->type === 'UNIQUE') && $hasAllFields;
                });

                foreach (array_map(static fn ($index) => $index->fields, $allIndexes) as $index) {
                    $constraints[] = current($index);
                    break;
                }

                $constraints = $this->onConstraint($constraints)->QBOptions['constraints'] ?? [];
            }

            if (empty($constraints)) {
                if ($this->db->DBDebug) {
                    throw new DatabaseException('No constraint found for upsert.');
                }

                return ''; // @codeCoverageIgnore
            }

            $alias = $this->QBOptions['alias'] ?? '`excluded`';

            if (strtolower($alias) !== '`excluded`') {
                throw new InvalidArgumentException('SQLite alias is always named "excluded". A custom alias cannot be used.');
            }

            $updateFields = $this->QBOptions['updateFields'] ??
                $this->updateFields($keys, false, $constraints)->QBOptions['updateFields'] ??
                [];

            $sql = 'INSERT INTO ' . $table . ' (';

            $sql .= implode(', ', array_map(static fn ($columnName): string => $columnName, $keys));

            $sql .= ")\n";

            $sql .= '{:_table_:}';

            $sql .= 'ON CONFLICT(' . implode(',', $constraints) . ")\n";

            $sql .= "DO UPDATE SET\n";

            $sql .= implode(
                ",\n",
                array_map(
                    static fn ($key, $value): string => $key . ($value instanceof RawSql ?
                        " = {$value}" :
                        " = {$alias}.{$value}"),
                    array_keys($updateFields),
                    $updateFields,
                ),
            );

            $this->QBOptions['sql'] = $sql;
        }

        if (isset($this->QBOptions['setQueryAsData'])) {
            $hasWhere = stripos($this->QBOptions['setQueryAsData'], 'WHERE') > 0;

            $data = $this->QBOptions['setQueryAsData'] . ($hasWhere ? '' : "\nWHERE 1 = 1\n");
        } else {
            $data = 'VALUES ' . implode(', ', $this->formatValues($values)) . "\n";
        }

        return str_replace('{:_table_:}', $data, $sql);
    }

    /**
     * Generates a platform-specific batch update string from the supplied data
     */
    protected function _deleteBatch(string $table, array $keys, array $values): string
    {
        $sql = $this->QBOptions['sql'] ?? '';

        // if this is the first iteration of batch then we need to build skeleton sql
        if ($sql === '') {
            $constraints = $this->QBOptions['constraints'] ?? [];

            if ($constraints === []) {
                if ($this->db->DBDebug) {
                    throw new DatabaseException('You must specify a constraint to match on for batch deletes.'); // @codeCoverageIgnore
                }

                return ''; // @codeCoverageIgnore
            }

            $sql = 'DELETE FROM ' . $table . "\n";

            if (current($constraints) instanceof RawSql && $this->db->DBDebug) {
                throw new DatabaseException('You cannot use RawSql for constraint in SQLite.');
                // @codeCoverageIgnore
            }

            if (is_string(current(array_keys($constraints)))) {
                $concat1 = implode(' || ', array_keys($constraints));
                $concat2 = implode(' || ', array_values($constraints));
            } else {
                $concat1 = implode(' || ', $constraints);
                $concat2 = $concat1;
            }

            $sql .= "WHERE {$concat1} IN (SELECT {$concat2} FROM (\n{:_table_:}))";

            // where is not supported
            if ($this->QBWhere !== [] && $this->db->DBDebug) {
                throw new DatabaseException('You cannot use WHERE with SQLite.');
                // @codeCoverageIgnore
            }

            $this->QBOptions['sql'] = $sql;
        }

        if (isset($this->QBOptions['setQueryAsData'])) {
            $data = $this->QBOptions['setQueryAsData'];
        } else {
            $data = implode(
                " UNION ALL\n",
                array_map(
                    static fn ($value): string => 'SELECT ' . implode(', ', array_map(
                        static fn ($key, $index): string => $index . ' ' . $key,
                        $keys,
                        $value,
                    )),
                    $values,
                ),
            ) . "\n";
        }

        return str_replace('{:_table_:}', $data, $sql);
    }
}
