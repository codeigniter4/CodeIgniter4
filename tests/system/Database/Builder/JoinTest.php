<?php

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
use CodeIgniter\Database\Postgre\Builder as PostgreBuilder;
use CodeIgniter\Database\RawSql;
use CodeIgniter\Database\SQLSRV\Builder as SQLSRVBuilder;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockConnection;

/**
 * @internal
 *
 * @group Others
 */
final class JoinTest extends CIUnitTestCase
{
    protected $db;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = new MockConnection([]);
    }

    public function testJoinSimple(): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->join('job', 'user.id = job.id');

        $expectedSQL = 'SELECT * FROM "user" JOIN "job" ON "user"."id" = "job"."id"';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testJoinIsNull(): void
    {
        $builder = new BaseBuilder('table1', $this->db);

        $builder->join('table2', 'field IS NULL');

        $expectedSQL = 'SELECT * FROM "table1" JOIN "table2" ON "field" IS NULL';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testJoinIsNotNull(): void
    {
        $builder = new BaseBuilder('table1', $this->db);

        $builder->join('table2', 'field IS NOT NULL');

        $expectedSQL = 'SELECT * FROM "table1" JOIN "table2" ON "field" IS NOT NULL';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testJoinMultipleConditions(): void
    {
        $builder = new BaseBuilder('table1', $this->db);

        $builder->join('table2', "table1.field1 = table2.field2 AND table1.field1 = 'foo' AND table2.field2 = 0", 'LEFT');

        $expectedSQL = "SELECT * FROM \"table1\" LEFT JOIN \"table2\" ON \"table1\".\"field1\" = \"table2\".\"field2\" AND \"table1\".\"field1\" = 'foo' AND \"table2\".\"field2\" = 0";

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/3832
     */
    public function testJoinRawSql(): void
    {
        $builder = new BaseBuilder('device', $this->db);

        $sql = 'user.id = device.user_id
            AND (
                (1=1 OR 1=1)
                OR
                (1=1 OR 1=1)
            )';
        $builder->join('user', new RawSql($sql), 'LEFT');

        $expectedSQL = 'SELECT * FROM "device" LEFT JOIN "user" ON user.id = device.user_id AND ( (1=1 OR 1=1) OR (1=1 OR 1=1) )';

        $output = str_replace("\n", ' ', $builder->getCompiledSelect());
        $output = preg_replace('/\s+/', ' ', $output);
        $this->assertSame($expectedSQL, $output);
    }

    public function testFullOuterJoin(): void
    {
        $builder = new PostgreBuilder('jobs', $this->db);
        $builder->testMode();
        $builder->join('users as u', 'users.id = jobs.id', 'full outer');

        $expectedSQL = 'SELECT * FROM "jobs" FULL OUTER JOIN "users" as "u" ON "users"."id" = "jobs"."id"';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testJoinWithAlias(): void
    {
        $this->db = new MockConnection(['DBDriver' => 'SQLSRV', 'database' => 'test', 'schema' => 'dbo']);

        $builder = new SQLSRVBuilder('jobs', $this->db);
        $builder->testMode();
        $builder->join('users u', 'u.id = jobs.id', 'LEFT');

        $expectedSQL = 'SELECT * FROM "test"."dbo"."jobs" LEFT JOIN "test"."dbo"."users" "u" ON "u"."id" = "jobs"."id"';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }
}
