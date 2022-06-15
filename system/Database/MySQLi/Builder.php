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
     * Converts call to batchUpsert
     *
     * @throws DatabaseException
     *
     * @return bool|Query
     */
    public function upsert(?array $set = null, ?bool $escape = null)
    {
        if ($set !== null) {
            $this->set([$set], '', $escape);
        }

		return $this->batchExecute([$set], 'setInsertBatch', '_upsertBatch', $escape, 1);
    }

    /**
     * Compiles batch upsert strings and runs the queries
     *
     * @throws DatabaseException
     *
     * @return false|int|string[] Number of rows replaced or FALSE on failure, SQL array when testMode
     */
    public function upsertBatch(?array $set = null, ?bool $escape = null, int $batchSize = 100)
    {
        return $this->batchExecute($set, 'setInsertBatch', '_upsertBatch', $escape, $batchSize);
    }

    /**
     * Generates a platform-specific upsertBatch string from the supplied data
     */
    protected function _upsertBatch(string $table, array $keys, array $values): string
    {
        return 'INSERT INTO ' . $table . ' (' . implode(', ', $keys) . ') VALUES ' . implode(', ', $values) . ' ON DUPLICATE KEY UPDATE ' . implode(', ', array_map(static fn ($columnName) => $columnName . ' = VALUES(' . $columnName . ')', $keys));
	}
}
