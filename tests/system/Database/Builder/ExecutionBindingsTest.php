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
use CodeIgniter\Database\Query;
use CodeIgniter\Database\SQLSRV\Builder as SQLSRVBuilder;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockConnection;
use CodeIgniter\Test\Mock\MockResult;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 */
#[Group('Others')]
final class ExecutionBindingsTest extends CIUnitTestCase
{
    #[DataProvider('provideExecution')]
    public function testExecutionClearsBindingsOnReset(string $method): void
    {
        $db      = $this->connection();
        $builder = new BaseBuilder('jobs', $db);

        for ($id = 1; $id <= 3; $id++) {
            $result = $builder->where('id >', $id)->where('id <', $id + 10)->{$method}();

            $this->assertSame($method === 'delete' ? true : 0, $result);
            $this->assertStringContainsString('"id" > ' . $id, $db->lastQuery->getQuery());
            $this->assertSame([], $builder->getBinds());
            $this->assertSame([], $this->getPrivateProperty($builder, 'bindsKeyCount'));
        }
    }

    #[DataProvider('provideExecution')]
    public function testExecutionPreservesBindingsWithoutReset(string $method): void
    {
        $db      = $this->connection();
        $builder = new BaseBuilder('jobs', $db);
        $builder->where('id', 3);

        for ($i = 0; $i < 2; $i++) {
            if ($method === 'delete') {
                $builder->delete('', null, false);
            } else {
                $builder->countAllResults(false);
            }

            $this->assertSame(['id' => [3, true]], $builder->getBinds());
            $this->assertStringContainsString('"id" = 3', $db->lastQuery->getQuery());
        }

        $builder->{$method}();
        $this->assertSame([], $builder->getBinds());
    }

    #[DataProvider('provideExecution')]
    public function testFailedExecutionClearsBindingsOnReset(string $method): void
    {
        $db      = $this->connection(false);
        $builder = new BaseBuilder('jobs', $db);
        $builder->where('id', 3);

        $this->assertSame($method === 'delete' ? false : 0, $builder->{$method}());
        $this->assertSame([], $builder->getBinds());
        $this->assertSameSql('SELECT * FROM "jobs"', $builder->getCompiledSelect(false));
    }

    #[DataProvider('provideExecution')]
    public function testTestModeRetainsBindingsForReturnedPlaceholders(string $method): void
    {
        $builder = new BaseBuilder('jobs', $this->connection());
        $builder->where('id', 3)->testMode();

        $sql = $builder->{$method}();

        $this->assertIsString($sql);
        $this->assertStringContainsString(':id:', $sql);
        $this->assertSame(['id' => [3, true]], $builder->getBinds());
    }

    #[DataProvider('provideExecution')]
    public function testExecutionWithSubqueryPreservesInnerBindings(string $method): void
    {
        $db       = $this->connection();
        $subquery = (new BaseBuilder('user_jobs', $db))->select('job_id')->where('id', 7);
        $builder  = (new BaseBuilder('jobs', $db))->whereIn('id', $subquery);

        if ($method === 'countAllResults') {
            $builder->groupBy('id');
        }

        $builder->{$method}();

        $this->assertStringContainsString('IN (SELECT "job_id" FROM "user_jobs" WHERE "id" = 7)', str_replace("\n", ' ', $db->lastQuery->getQuery()));
        $this->assertSame([], $builder->getBinds());
        $this->assertSame(['id' => [7, true]], $subquery->getBinds());

        if ($method === 'countAllResults') {
            $this->assertStringContainsString('CI_count_all_results', $db->lastQuery->getQuery());
        }
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideExecution(): iterable
    {
        yield 'delete' => ['delete'];

        yield 'count' => ['countAllResults'];
    }

    public function testCompiledDeleteKeepsBindingsUntilFinalCompilation(): void
    {
        $builder = new BaseBuilder('jobs', $this->connection());
        $builder->where('id', 3);

        $this->assertSameSql('DELETE FROM "jobs" WHERE "id" = 3', $builder->getCompiledDelete(false));
        $this->assertSame(['id' => [3, true]], $builder->getBinds());
        $this->assertSameSql('DELETE FROM "jobs" WHERE "id" = 3', $builder->getCompiledDelete());
        $this->assertSame([], $builder->getBinds());
    }

    public function testSQLSRVDeleteClearsBindingsAfterExecution(): void
    {
        foreach ([true, false] as $success) {
            $db      = $this->connection($success);
            $builder = new SQLSRVBuilder('jobs', $db);

            for ($id = 1; $id <= 3; $id++) {
                $this->assertSame($success, $builder->where('id', $id)->delete());
                $this->assertSame([], $builder->getBinds());
                $this->assertStringContainsString('"id" = ' . $id, $db->lastQuery->getQuery());
            }
        }
    }

    public function testSQLSRVDeletePreservesBindingsWhenNeeded(): void
    {
        $builder = new SQLSRVBuilder('jobs', $this->connection());
        $builder->where('id', 3)->delete('', null, false);

        $this->assertSame(['id' => [3, true]], $builder->getBinds());
        $sql = $builder->getCompiledDelete(false);
        $this->assertStringContainsString('"id" = 3', $sql);
        $this->assertSame(['id' => [3, true]], $builder->getBinds());
        $this->assertSame($sql, $builder->getCompiledDelete());
        $this->assertSame([], $builder->getBinds());
    }

    private function connection(bool $success = true): MockConnection
    {
        $result = $this->createMock(MockResult::class);
        $result->method('getRow')->willReturn((object) ['numrows' => 0]);

        $db = $this->getMockBuilder(MockConnection::class)
            ->setConstructorArgs([[]])
            ->onlyMethods(['query'])
            ->getMock();

        $db->method('query')->willReturnCallback(static function (string $sql, array $binds, bool $setEscapeFlags) use ($db, $result, $success): bool|MockObject {
            $query = new Query($db);
            $query->setQuery($sql, $binds, $setEscapeFlags);
            $query->getQuery();
            $db->lastQuery = $query;

            return $success ? ($query->isWriteType() ? true : $result) : false;
        });

        return $db;
    }
}
