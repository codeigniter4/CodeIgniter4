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
    private function _savepointQueryDefault(bool $create, bool $commit): string
    {
        return ($create ? '' : ($commit ? 'RELEASE' : 'ROLLBACK TO') . ' ') . 'SAVEPOINT';
    }

    abstract private function _savepointQuery(bool $begin, bool $commit): string;

    /**
     * Generates the SQL for managing savepoints, which
     * are used to support nested transactions with SQLite3
     */
    private function _savepoint(int $savepoint, bool $create, bool $commit): string
    {
        $savepointIdentifier = $this->escapeIdentifier('__ci4_savepoint_' . $savepoint . '__');

        return $this->_savepointQuery($create, $commit);
    }

    /**
     * Begin a Nested Transaction
     */
    protected function _transBeginNested(): bool
    {
        return false !== $this->execute($this->_savepoint($this->transDepth + 1, true));
    }

    /**
     * Commit a Nested Transaction
     */
    protected function _transCommitNested(): bool
    {
        return false !== $this->execute($this->_savepoint($this->transDepth, false, true));
    }

    /**
     * Rollback a Nested Transaction
     */
    protected function _transRollbackNested(): bool
    {
        return false !== $this->execute($this->_savepoint($this->transDepth, false, true));
    }
}
