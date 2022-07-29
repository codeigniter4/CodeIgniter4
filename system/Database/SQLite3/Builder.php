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

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\Exceptions\DatabaseException;

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
     * Generates a platform-specific upsertBatch string from the supplied data
     *
     * @throws DatabaseException
     */
    protected function _upsertBatch(string $table, array $keys, array $values): string
    {
        $fieldNames = array_map(static fn ($columnName) => trim($columnName, '`'), $keys);

        $constraints = $this->QBOptions['constraints'] ?? [];

        $updateFields = $this->QBOptions['updateFields'] ?? $fieldNames;

        if (empty($constraints)) {
            $allIndexes = array_filter($this->db->getIndexData($table), static function ($index) use ($fieldNames) {
                $hasAllFields = count(array_intersect($index->fields, $fieldNames)) === count($index->fields);

                return ($index->type === 'PRIMARY' || $index->type === 'UNIQUE') && $hasAllFields;
            });

            foreach (array_map(static fn ($index) => $index->fields, $allIndexes) as $index) {
                foreach ($index as $constraint) {
                    $constraints[] = $constraint;
                }

                break;
            }

            $this->QBOptions['constraints'] = $constraints;
        }

        if (empty($constraints)) {
            throw new DatabaseException('No constraint found for upsert.');
        }

        $sql = 'INSERT INTO ' . $table . ' (';

        $sql .= implode(', ', array_map(static fn ($columnName) => $columnName, $keys));

        $sql .= ")\n";

        $sql .= 'VALUES ' . implode(', ', $this->getValues($values)) . "\n";

        $sql .= 'ON CONFLICT(`' . implode('`,`', $constraints) . "`)\n";

        $sql .= "DO UPDATE SET\n";

        return $sql . implode(
            ",\n",
            array_map(
                static fn ($updateField) => '`' . $updateField . '` = `excluded`.`' . $updateField . '`',
                $updateFields
            )
        );
    }
}
