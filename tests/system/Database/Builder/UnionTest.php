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
use CodeIgniter\Database\SQLSRV\Connection as SQLSRVConnection;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockConnection;

/**
 * @internal
 *
 * @group Others
 */
final class UnionTest extends CIUnitTestCase
{
    /**
     * @var MockConnection
     */
    protected $db;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = new MockConnection([]);
    }

    public function testUnion(): void
    {
        $expected = 'SELECT * FROM (SELECT * FROM "test") "uwrp0" UNION SELECT * FROM (SELECT * FROM "test") "uwrp1"';
        $builder  = $this->db->table('test');

        $builder->union($this->db->table('test'));
        $this->assertSame($expected, $this->buildSelect($builder));

        $builder = $this->db->table('test');

        $builder->union(static fn ($builder) => $builder->from('test'));
        $this->assertSame($expected, $this->buildSelect($builder));
    }

    public function testUnionAll(): void
    {
        $expected = 'SELECT * FROM (SELECT * FROM "test") "uwrp0"'
            . ' UNION ALL SELECT * FROM (SELECT * FROM "test") "uwrp1"';
        $builder = $this->db->table('test');

        $builder->unionAll($this->db->table('test'));
        $this->assertSame($expected, $this->buildSelect($builder));
    }

    public function testOrderLimit(): void
    {
        $expected = 'SELECT * FROM (SELECT * FROM "test" ORDER BY "id" DESC  LIMIT 10) "uwrp0"'
            . ' UNION SELECT * FROM (SELECT * FROM "test") "uwrp1"';
        $builder = $this->db->table('test');

        $builder->union($this->db->table('test'))->limit(10)->orderBy('id', 'DESC');
        $this->assertSame($expected, $this->buildSelect($builder));
    }

    public function testUnionSQLSRV(): void
    {
        $expected = 'SELECT * FROM (SELECT * FROM "test"."dbo"."users") "uwrp0"'
            . ' UNION SELECT * FROM (SELECT * FROM "test"."dbo"."users") "uwrp1"';

        $db = new SQLSRVConnection(['DBDriver' => 'SQLSRV', 'database' => 'test', 'schema' => 'dbo']);

        $builder = $db->table('users');

        $builder->union($db->table('users'));
        $this->assertSame($expected, $this->buildSelect($builder));

        $builder = $db->table('users');

        $builder->union(static fn ($builder) => $builder->from('users'));
        $this->assertSame($expected, $this->buildSelect($builder));
    }

    protected function buildSelect(BaseBuilder $builder): string
    {
        return str_replace("\n", ' ', $builder->getCompiledSelect());
    }
}
