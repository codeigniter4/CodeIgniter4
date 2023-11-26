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
use CodeIgniter\Database\SQLSRV\Builder as SQLSRVBuilder;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockConnection;

/**
 * @internal
 *
 * @group Others
 */
final class FromTest extends CIUnitTestCase
{
    protected $db;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = new MockConnection([]);
    }

    public function testSimpleFrom(): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->from('jobs');

        $expectedSQL = 'SELECT * FROM "user", "jobs"';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testFromThatOverwrites(): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->from('jobs', true);

        $expectedSQL = 'SELECT * FROM "jobs"';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testFromWithMultipleTables(): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->from(['jobs', 'roles']);

        $expectedSQL = 'SELECT * FROM "user", "jobs", "roles"';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testFromWithMultipleTablesAsString(): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->from(['jobs, roles']);

        $expectedSQL = 'SELECT * FROM "user", "jobs", "roles"';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testFromReset(): void
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

    public function testFromSubquery(): void
    {
        $expectedSQL = 'SELECT * FROM (SELECT * FROM "users") "alias"';
        $subquery    = new BaseBuilder('users', $this->db);
        $builder     = $this->db->newQuery()->fromSubquery($subquery, 'alias');

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));

        $expectedSQL = 'SELECT * FROM (SELECT "id", "name" FROM "users") "users_1"';
        $subquery    = (new BaseBuilder('users', $this->db))->select('id, name');
        $builder     = $this->db->newQuery()->fromSubquery($subquery, 'users_1');

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));

        $expectedSQL = 'SELECT * FROM (SELECT * FROM "users") "alias", "some_table"';
        $subquery    = new BaseBuilder('users', $this->db);
        $builder     = $this->db->newQuery()->fromSubquery($subquery, 'alias')->from('some_table');

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testFromWithMultipleTablesAsStringWithSQLSRV(): void
    {
        $this->db = new MockConnection(['DBDriver' => 'SQLSRV', 'database' => 'test', 'schema' => 'dbo']);

        $builder = new SQLSRVBuilder('user', $this->db);

        $builder->from(['jobs, roles']);

        $expectedSQL = 'SELECT * FROM "test"."dbo"."user", "test"."dbo"."jobs", "test"."dbo"."roles"';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testFromSubqueryWithSQLSRV(): void
    {
        $this->db = new MockConnection(['DBDriver' => 'SQLSRV', 'database' => 'test', 'schema' => 'dbo']);

        $subquery = new SQLSRVBuilder('users', $this->db);

        $builder = new SQLSRVBuilder('jobs', $this->db);

        $builder->fromSubquery($subquery, 'users_1');

        $expectedSQL = 'SELECT * FROM "test"."dbo"."jobs", (SELECT * FROM "test"."dbo"."users") "users_1"';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }
}
