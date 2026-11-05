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

namespace CodeIgniter\Session\Handlers\Database;

use CodeIgniter\Test\Mock\MockConnection;
use CodeIgniter\Test\Mock\MockResult;

/**
 * Mock connection that records the raw SQL passed to simpleQuery().
 *
 * PostgreHandler uses simpleQuery() when no bindings are needed, so the
 * queries are not bound and must be captured as they are sent to the database.
 *
 * @internal
 */
final class PostgreHandlerMockConnection extends MockConnection
{
    /**
     * @var list<string>
     */
    public array $executed = [];

    public function simpleQuery(string $sql): MockResult
    {
        $this->executed[] = $sql;

        return new MockResult($this->connID, $this->resultID);
    }
}
