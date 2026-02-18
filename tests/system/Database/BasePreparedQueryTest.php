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

    private function createPreparedQuery(): BasePreparedQuery
    {
        return new class (new MockConnection([])) extends BasePreparedQuery {
            public string $preparedSql = '';

            public function _prepare(string $sql, array $options = [])
            {
                $this->preparedSql = $sql;

                return $this;
            }

            public function _execute(array $data): bool
            {
                return true;
            }

            public function _getResult()
            {
                return null;
            }

            protected function _close(): bool
            {
                return true;
            }
        };
    }
}
