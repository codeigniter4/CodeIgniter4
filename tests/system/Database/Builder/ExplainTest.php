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

namespace CodeIgniter\Database\Builder;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Database\OCI8\Builder as OCI8Builder;
use CodeIgniter\Database\SQLite3\Builder as SQLite3Builder;
use CodeIgniter\Database\SQLSRV\Builder as SQLSRVBuilder;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockConnection;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class ExplainTest extends CIUnitTestCase
{
    protected $db;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = new MockConnection([]);
    }

    public function testExplainReturnsSqlInTestMode(): void
    {
        $builder = new BaseBuilder('jobs', $this->db);
        $builder->testMode();

        $answer = $builder->where('id >', 3)->explain(false);

        $expectedSQL = 'EXPLAIN SELECT * FROM "jobs" WHERE "id" > 3';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $answer));
    }

    public function testSQLiteExplainUsesQueryPlanInTestMode(): void
    {
        $builder = new SQLite3Builder('jobs', $this->db);
        $builder->testMode();

        $answer = $builder->where('id >', 3)->explain(false);

        $expectedSQL = 'EXPLAIN QUERY PLAN SELECT * FROM "jobs" WHERE "id" > 3';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $answer));
    }

    public function testExplainResetsByDefault(): void
    {
        $builder = new BaseBuilder('jobs', $this->db);
        $builder->testMode();

        $builder->where('id >', 3)->explain();

        $this->assertSame('SELECT * FROM "jobs"', str_replace("\n", ' ', $builder->getCompiledSelect(false)));
        $this->assertSame([], $builder->getBinds());
    }

    public function testExplainHonorsResetFalse(): void
    {
        $builder = new BaseBuilder('jobs', $this->db);
        $builder->testMode();

        $builder->where('id >', 3)->explain(false);

        $this->assertSame('SELECT * FROM "jobs" WHERE "id" > 3', str_replace("\n", ' ', $builder->getCompiledSelect(false)));
        $this->assertSame([
            'id' => [
                3,
                true,
            ],
        ], $builder->getBinds());
    }

    public function testExplainReturnsFalseWhenQueryFails(): void
    {
        $db = new MockConnection([]);
        $db->shouldReturn('execute', false);

        $builder = new BaseBuilder('jobs', $db);

        $this->assertFalse($builder->where('id >', 3)->explain());
        $this->assertSame('SELECT * FROM "jobs"', str_replace("\n", ' ', $builder->getCompiledSelect(false)));
        $this->assertSame([], $builder->getBinds());
    }

    public function testSQLSRVExplainIsNotSupported(): void
    {
        $builder = new SQLSRVBuilder('jobs', new MockConnection([
            'DBDriver' => 'SQLSRV',
            'database' => 'test',
            'schema'   => 'dbo',
        ]));

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('SQLSRV does not support explain().');

        $builder->explain();
    }

    public function testSQLSRVExplainChecksSupportBeforeCompilingSelect(): void
    {
        $db = new MockConnection([
            'DBDriver' => 'SQLSRV',
            'database' => 'test',
            'schema'   => 'dbo',
        ]);

        $builder = new SQLSRVBuilder('jobs', $db);
        $builder->union(new SQLSRVBuilder('jobs', $db))->lockForUpdate();

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('SQLSRV does not support explain().');

        $builder->explain();
    }

    public function testOCI8ExplainIsNotSupported(): void
    {
        $builder = new OCI8Builder('jobs', new MockConnection(['DBDriver' => 'OCI8']));

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('OCI8 does not support explain().');

        $builder->explain();
    }
}
