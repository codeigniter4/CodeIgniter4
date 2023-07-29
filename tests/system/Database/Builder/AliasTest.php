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

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockConnection;

/**
 * @internal
 *
 * @group Others
 */
final class AliasTest extends CIUnitTestCase
{
    protected $db;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = new MockConnection([]);
    }

    public function testAlias(): void
    {
        $builder = $this->db->table('jobs j');

        $expectedSQL = 'SELECT * FROM "jobs" "j"';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testAliasSupportsArrayOfNames(): void
    {
        $builder = $this->db->table(['jobs j', 'users u']);

        $expectedSQL = 'SELECT * FROM "jobs" "j", "users" "u"';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testAliasSupportsStringOfNames(): void
    {
        $builder = $this->db->table('jobs j, users u');

        $expectedSQL = 'SELECT * FROM "jobs" "j", "users" "u"';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/1599
     */
    public function testAliasLeftJoinWithShortTableName(): void
    {
        $this->setPrivateProperty($this->db, 'DBPrefix', 'db_');
        $builder = $this->db->table('jobs');

        $builder->join('users as u', 'u.id = jobs.id', 'left');

        $expectedSQL = 'SELECT * FROM "db_jobs" LEFT JOIN "db_users" as "u" ON "u"."id" = "db_jobs"."id"';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/1599
     */
    public function testAliasLeftJoinWithLongTableName(): void
    {
        $this->setPrivateProperty($this->db, 'DBPrefix', 'db_');
        $builder = $this->db->table('jobs');

        $builder->join('users as u', 'users.id = jobs.id', 'left');

        $expectedSQL = 'SELECT * FROM "db_jobs" LEFT JOIN "db_users" as "u" ON "db_users"."id" = "db_jobs"."id"';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/5360
     */
    public function testAliasSimpleLikeWithDBPrefix(): void
    {
        $this->setPrivateProperty($this->db, 'DBPrefix', 'db_');
        $builder = $this->db->table('jobs j');

        $builder->like('j.name', 'veloper');

        $expectedSQL = <<<'SQL'
            SELECT * FROM "db_jobs" "j" WHERE "j"."name" LIKE '%veloper%' ESCAPE '!'
            SQL;
        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }
}
