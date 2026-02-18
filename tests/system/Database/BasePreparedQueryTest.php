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

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockConnection;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\Mock\MockPreparedQuery;

/**
 * @internal
 */
#[Group('Others')]
final class BasePreparedQueryTest extends CIUnitTestCase
{
    public function testPrepareConvertsNamedPlaceholdersToPositionalPlaceholders(): void
    {
        $query = $this->createPreparedQuery();

        $query->prepare('SELECT * FROM users WHERE id = :id: AND name = :name');

        $this->assertSame('SELECT * FROM users WHERE id = ? AND name = ?', $query->preparedSql);
    }

    public function testPrepareDoesNotConvertPostgreStyleCastSyntax(): void
    {
        $query = $this->createPreparedQuery();

        $query->prepare('SELECT :name: AS name, created_at::timestamp AS created FROM users WHERE id = :id:');

        $this->assertSame(
            'SELECT ? AS name, created_at::timestamp AS created FROM users WHERE id = ?',
            $query->preparedSql,
        );
    }

    public function testPrepareDoesNotConvertTimeLikeLiterals(): void
    {
        $query = $this->createPreparedQuery();

        $query->prepare("SELECT '12:34' AS time_value, :id: AS id");

        $this->assertSame("SELECT '12:34' AS time_value, ? AS id", $query->preparedSql);
    }

    private function createPreparedQuery(): MockPreparedQuery
    {
        return new MockPreparedQuery(new MockConnection([]));
    }
}
