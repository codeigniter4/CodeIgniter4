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

namespace CodeIgniter\Database;

trait SavepointsForNestedTransactions
{
    /**
     * Generates the SQL for managing savepoints, which
     * are used to support nested transactions with SQLite3
     */
    private function _savepoint(int $savepoint, string $action = ''): string
    {
        $savepointIdentifier = $this->escapeIdentifier('__ci4_savepoint_' . $savepoint . '__');

        return ($action === '' ? '' : $action . ' ') . 'SAVEPOINT ' . $savepointIdentifier;
    }

    /**
     * Begin a Nested Transaction
     */
    protected function _transBeginNested(): bool
    {
        return false !== $this->execute($this->_savepoint($this->transDepth + 1));
    }

    /**
     * Commit a Nested Transaction
     */
    protected function _transCommitNested(): bool
    {
        return false !== $this->execute($this->_savepoint($this->transDepth, 'RELEASE'));
    }

    /**
     * Rollback a Nested Transaction
     */
    protected function _transRollbackNested(): bool
    {
        return false !== $this->execute($this->_savepoint($this->transDepth, 'ROLLBACK TO'));
    }
}
