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
use CodeIgniter\Database\SQLSRV\Builder as SQLSRVBuilder;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockConnection;
use Config\Feature;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class ExistsTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->db = new MockConnection([]);
    }

    public function testExistsReturnsSqlInTestMode(): void
    {
        $builder = new BaseBuilder('jobs', $this->db);
        $builder->testMode();

        $answer = $builder->where('id >', 3)->exists(false);

        $expectedSQL = 'SELECT 1 FROM "jobs" WHERE "id" > :id:  LIMIT 1';

        $this->assertSameSql($expectedSQL, $answer);
    }

    public function testDoesntExistReturnsSqlInTestMode(): void
    {
        $builder = new BaseBuilder('jobs', $this->db);
        $builder->testMode();

        $answer = $builder->where('id >', 3)->doesntExist(false);

        $expectedSQL = 'SELECT 1 FROM "jobs" WHERE "id" > :id:  LIMIT 1';

        $this->assertSameSql($expectedSQL, $answer);
    }

    public function testExistsDoesNotUseOrderByOrLockForUpdate(): void
    {
        $builder = new BaseBuilder('jobs', $this->db);
        $builder->testMode();

        $answer = $builder->where('id >', 3)
            ->orderBy('id', 'DESC')
            ->lockForUpdate()
            ->exists(false);

        $expectedSQL = 'SELECT 1 FROM "jobs" WHERE "id" > :id:  LIMIT 1';

        $this->assertSameSql($expectedSQL, $answer);
        $this->assertSameSql('SELECT * FROM "jobs" WHERE "id" > 3 ORDER BY "id" DESC FOR UPDATE', $builder->getCompiledSelect(false));
    }

    public function testExistsDoesNotUseOrderByOrSharedLock(): void
    {
        $builder = new BaseBuilder('jobs', $this->db);
        $builder->testMode();

        $answer = $builder->where('id >', 3)
            ->orderBy('id', 'DESC')
            ->sharedLock()
            ->exists(false);

        $expectedSQL = 'SELECT 1 FROM "jobs" WHERE "id" > :id:  LIMIT 1';

        $this->assertSameSql($expectedSQL, $answer);
        $this->assertSameSql('SELECT * FROM "jobs" WHERE "id" > 3 ORDER BY "id" DESC FOR SHARE', $builder->getCompiledSelect(false));
    }

    public function testExistsWithSQLSRVDoesNotUseOrderByOrLockForUpdate(): void
    {
        $this->db = new MockConnection(['DBDriver' => 'SQLSRV', 'database' => 'test', 'schema' => 'dbo']);

        $builder = new SQLSRVBuilder('jobs', $this->db);
        $builder->testMode();

        $answer = $builder->where('id >', 3)
            ->orderBy('id', 'DESC')
            ->lockForUpdate()
            ->exists(false);

        $expectedSQL = 'SELECT 1 FROM "test"."dbo"."jobs" WHERE "id" > :id:  ORDER BY (SELECT NULL)  OFFSET 0  ROWS FETCH NEXT 1 ROWS ONLY ';

        $this->assertSameSql($expectedSQL, $answer);
        $this->assertSameSql('SELECT * FROM "test"."dbo"."jobs" WITH (UPDLOCK, ROWLOCK) WHERE "id" > 3 ORDER BY "id" DESC', $builder->getCompiledSelect(false));
    }

    public function testExistsWithSQLSRVDoesNotUseOrderByOrSharedLock(): void
    {
        $this->db = new MockConnection(['DBDriver' => 'SQLSRV', 'database' => 'test', 'schema' => 'dbo']);

        $builder = new SQLSRVBuilder('jobs', $this->db);
        $builder->testMode();

        $answer = $builder->where('id >', 3)
            ->orderBy('id', 'DESC')
            ->sharedLock()
            ->exists(false);

        $expectedSQL = 'SELECT 1 FROM "test"."dbo"."jobs" WHERE "id" > :id:  ORDER BY (SELECT NULL)  OFFSET 0  ROWS FETCH NEXT 1 ROWS ONLY ';

        $this->assertSameSql($expectedSQL, $answer);
        $this->assertSameSql('SELECT * FROM "test"."dbo"."jobs" WITH (HOLDLOCK, ROWLOCK) WHERE "id" > 3 ORDER BY "id" DESC', $builder->getCompiledSelect(false));
    }

    public function testExistsHonorsExistingLimitAndOffset(): void
    {
        $builder = new BaseBuilder('jobs', $this->db);
        $builder->testMode();

        $answer = $builder->where('id >', 3)
            ->limit(10, 20)
            ->exists(false);

        $expectedSQL = 'SELECT 1 FROM ( SELECT * FROM "jobs" WHERE "id" > :id:  LIMIT 20, 10 ) CI_exists  LIMIT 1';

        $this->assertSameSql($expectedSQL, $answer);
        $this->assertSameSql('SELECT * FROM "jobs" WHERE "id" > 3  LIMIT 20, 10', $builder->getCompiledSelect(false));
    }

    public function testExistsHonorsLimitZero(): void
    {
        $config                 = config(Feature::class);
        $limitZeroAsAll         = $config->limitZeroAsAll;
        $config->limitZeroAsAll = false;

        try {
            $builder = new BaseBuilder('jobs', $this->db);
            $builder->testMode();

            $answer = $builder->where('id >', 3)
                ->limit(0)
                ->exists(false);

            $expectedSQL = 'SELECT 1 FROM "jobs" WHERE "id" > :id:  LIMIT 0';

            $this->assertSameSql($expectedSQL, $answer);
        } finally {
            $config->limitZeroAsAll = $limitZeroAsAll;
        }
    }

    public function testExistsWithGroupByAndHaving(): void
    {
        $builder = new BaseBuilder('jobs', $this->db);
        $builder->testMode();

        $answer = $builder->selectCount('id', 'total')
            ->where('id >', 3)
            ->groupBy('id')
            ->having('total >', 1)
            ->exists(false);

        $expectedSQL = 'SELECT 1 FROM ( SELECT COUNT("id") AS "total" FROM "jobs" WHERE "id" > :id: GROUP BY "id" HAVING "total" > :total: ) CI_exists  LIMIT 1';

        $this->assertSameSql($expectedSQL, $answer);
        $this->assertSameSql('SELECT COUNT("id") AS "total" FROM "jobs" WHERE "id" > 3 GROUP BY "id" HAVING "total" > 1', $builder->getCompiledSelect(false));
    }

    public function testExistsWithAggregateSelection(): void
    {
        $builder = new BaseBuilder('jobs', $this->db);
        $builder->testMode();

        $answer = $builder->selectCount('id', 'total')
            ->where('id >', 3)
            ->exists(false);

        $expectedSQL = 'SELECT 1 FROM ( SELECT COUNT("id") AS "total" FROM "jobs" WHERE "id" > :id: ) CI_exists  LIMIT 1';

        $this->assertSameSql($expectedSQL, $answer);
        $this->assertSameSql('SELECT COUNT("id") AS "total" FROM "jobs" WHERE "id" > 3', $builder->getCompiledSelect(false));
    }

    public function testExistsWithUnion(): void
    {
        $builder = new BaseBuilder('jobs', $this->db);
        $builder->testMode();

        $answer = $builder->union($this->db->table('jobs'))->exists(false);

        $expectedSQL = 'SELECT 1 FROM ( SELECT * FROM (SELECT * FROM "jobs") "uwrp0" UNION SELECT * FROM (SELECT * FROM "jobs") "uwrp1" ) CI_exists  LIMIT 1';

        $this->assertSameSql($expectedSQL, $answer);
        $this->assertSameSql('SELECT * FROM (SELECT * FROM "jobs") "uwrp0" UNION SELECT * FROM (SELECT * FROM "jobs") "uwrp1"', $builder->getCompiledSelect(false));
    }

    public function testExistsResetsByDefault(): void
    {
        $builder = new BaseBuilder('jobs', $this->db);
        $builder->testMode();

        $builder->where('id >', 3)->exists();

        $this->assertSameSql('SELECT * FROM "jobs"', $builder->getCompiledSelect(false));
        $this->assertSame([], $builder->getBinds());
    }

    public function testExistsHonorsResetFalse(): void
    {
        $builder = new BaseBuilder('jobs', $this->db);
        $builder->testMode();

        $builder->where('id >', 3)->exists(false);

        $this->assertSameSql('SELECT * FROM "jobs" WHERE "id" > 3', $builder->getCompiledSelect(false));
        $this->assertSame([
            'id' => [
                3,
                true,
            ],
        ], $builder->getBinds());
    }

    public function testExistsMethodsReturnFalseWhenQueryFails(): void
    {
        $db = new MockConnection([]);
        $db->shouldReturn('execute', false);

        $this->assertFalse((new BaseBuilder('jobs', $db))->exists());
        $this->assertFalse((new BaseBuilder('jobs', $db))->doesntExist());
    }
}
