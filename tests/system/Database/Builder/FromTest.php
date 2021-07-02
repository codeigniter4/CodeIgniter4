<?php

namespace CodeIgniter\Database\Builder;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\SQLSRV\Builder as SQLSRVBuilder;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockConnection;

/**
 * @internal
 */
final class FromTest extends CIUnitTestCase
{
    protected $db;

    //--------------------------------------------------------------------

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = new MockConnection([]);
    }

    //--------------------------------------------------------------------

    public function testSimpleFrom()
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->from('jobs');

        $expectedSQL = 'SELECT * FROM "user", "jobs"';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    //--------------------------------------------------------------------

    public function testFromThatOverwrites()
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->from('jobs', true);

        $expectedSQL = 'SELECT * FROM "jobs"';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    //--------------------------------------------------------------------

    public function testFromWithMultipleTables()
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->from(['jobs', 'roles']);

        $expectedSQL = 'SELECT * FROM "user", "jobs", "roles"';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    //--------------------------------------------------------------------

    public function testFromWithMultipleTablesAsString()
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->from(['jobs, roles']);

        $expectedSQL = 'SELECT * FROM "user", "jobs", "roles"';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    //--------------------------------------------------------------------

    public function testFromReset()
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->from(['jobs', 'roles']);

        $expectedSQL = 'SELECT * FROM "user", "jobs", "roles"';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));

        $expectedSQL = 'SELECT * FROM "user"';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));

        $expectedSQL = 'SELECT *';

        $builder->from(null, true);

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));

        $expectedSQL = 'SELECT * FROM "jobs"';

        $builder->from('jobs');

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    //--------------------------------------------------------------------

    public function testFromWithMultipleTablesAsStringWithSQLSRV()
    {
        $this->db = new MockConnection(['DBDriver' => 'SQLSRV', 'database' => 'test', 'schema' => 'dbo']);

        $builder = new SQLSRVBuilder('user', $this->db);

        $builder->from(['jobs, roles']);

        $expectedSQL = 'SELECT * FROM "test"."dbo"."user", "test"."dbo"."jobs", "test"."dbo"."roles"';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    //--------------------------------------------------------------------
}
