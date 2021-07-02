<?php

namespace CodeIgniter\Database\Builder;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockConnection;

/**
 * @internal
 */
final class AliasTest extends CIUnitTestCase
{
    protected $db;

    //--------------------------------------------------------------------

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = new MockConnection([]);
    }

    //--------------------------------------------------------------------

    public function testAlias()
    {
        $builder = $this->db->table('jobs j');

        $expectedSQL = 'SELECT * FROM "jobs" "j"';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    //--------------------------------------------------------------------

    public function testAliasSupportsArrayOfNames()
    {
        $builder = $this->db->table(['jobs j', 'users u']);

        $expectedSQL = 'SELECT * FROM "jobs" "j", "users" "u"';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    //--------------------------------------------------------------------

    public function testAliasSupportsStringOfNames()
    {
        $builder = $this->db->table('jobs j, users u');

        $expectedSQL = 'SELECT * FROM "jobs" "j", "users" "u"';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    //--------------------------------------------------------------------

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/1599
     */
    public function testAliasLeftJoinWithShortTableName()
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
    public function testAliasLeftJoinWithLongTableName()
    {
        $this->setPrivateProperty($this->db, 'DBPrefix', 'db_');
        $builder = $this->db->table('jobs');

        $builder->join('users as u', 'users.id = jobs.id', 'left');

        $expectedSQL = 'SELECT * FROM "db_jobs" LEFT JOIN "db_users" as "u" ON "db_users"."id" = "db_jobs"."id"';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }
}
