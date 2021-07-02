<?php

namespace CodeIgniter\Database\Builder;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\Postgre\Builder as PostgreBuilder;
use CodeIgniter\Database\SQLSRV\Builder as SQLSRVBuilder;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockConnection;

/**
 * @internal
 */
final class JoinTest extends CIUnitTestCase
{
    protected $db;

    //--------------------------------------------------------------------

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = new MockConnection([]);
    }

    //--------------------------------------------------------------------

    public function testJoinSimple()
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->join('job', 'user.id = job.id');

        $expectedSQL = 'SELECT * FROM "user" JOIN "job" ON "user"."id" = "job"."id"';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    //--------------------------------------------------------------------

    public function testJoinIsNull()
    {
        $builder = new BaseBuilder('table1', $this->db);

        $builder->join('table2', 'field IS NULL');

        $expectedSQL = 'SELECT * FROM "table1" JOIN "table2" ON "field" IS NULL';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    //--------------------------------------------------------------------

    public function testJoinIsNotNull()
    {
        $builder = new BaseBuilder('table1', $this->db);

        $builder->join('table2', 'field IS NOT NULL');

        $expectedSQL = 'SELECT * FROM "table1" JOIN "table2" ON "field" IS NOT NULL';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    //--------------------------------------------------------------------

    public function testJoinMultipleConditions()
    {
        $builder = new BaseBuilder('table1', $this->db);

        $builder->join('table2', "table1.field1 = table2.field2 AND table1.field1 = 'foo' AND table2.field2 = 0", 'LEFT');

        $expectedSQL = "SELECT * FROM \"table1\" LEFT JOIN \"table2\" ON \"table1\".\"field1\" = \"table2\".\"field2\" AND \"table1\".\"field1\" = 'foo' AND \"table2\".\"field2\" = 0";

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    //--------------------------------------------------------------------

    public function testFullOuterJoin()
    {
        $builder = new PostgreBuilder('jobs', $this->db);
        $builder->testMode();
        $builder->join('users as u', 'users.id = jobs.id', 'full outer');

        $expectedSQL = 'SELECT * FROM "jobs" FULL OUTER JOIN "users" as "u" ON "users"."id" = "jobs"."id"';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    //--------------------------------------------------------------------

    public function testJoinWithAlias()
    {
        $this->db = new MockConnection(['DBDriver' => 'SQLSRV', 'database' => 'test', 'schema' => 'dbo']);

        $builder = new SQLSRVBuilder('jobs', $this->db);
        $builder->testMode();
        $builder->join('users u', 'u.id = jobs.id', 'LEFT');

        $expectedSQL = 'SELECT * FROM "test"."dbo"."jobs" LEFT JOIN "test"."dbo"."users" "u" ON "u"."id" = "jobs"."id"';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    //--------------------------------------------------------------------
}
