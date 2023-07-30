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
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockConnection;

/**
 * @internal
 *
 * @group Others
 */
final class PrefixTest extends CIUnitTestCase
{
    protected $db;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = new MockConnection(['DBPrefix' => 'ci_']);
    }

    public function testPrefixesSetOnTableNames(): void
    {
        $expected = 'ci_users';

        $this->assertSame($expected, $this->db->prefixTable('users'));
    }

    public function testPrefixesSetOnTableNamesWithWhereClause(): void
    {
        $builder = $this->db->table('users');

        $where = 'users.created_at < users.updated_at';

        $expectedSQL   = 'SELECT * FROM "ci_users" WHERE "ci_users"."created_at" < "ci_users"."updated_at"';
        $expectedBinds = [];

        $builder->where($where);

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
        $this->assertSame($expectedBinds, $builder->getBinds());
    }

    public function testPrefixWithSubquery(): void
    {
        $expected = <<<'NOWDOC'
            SELECT "u"."id", "u"."name", (SELECT 1 FROM "ci_users" "sub" WHERE "sub"."id" = "u"."id") "one"
            FROM "ci_users" "u"
            WHERE "u"."id" = 1
            NOWDOC;

        $subquery = $this->db->table('users sub')
            ->select('1', false)
            ->where('sub.id = u.id');

        $builder = $this->db->table('users u')
            ->select('u.id, u.name')
            ->selectSubquery($subquery, 'one')
            ->where('u.id', 1);

        $this->assertSame($expected, $builder->getCompiledSelect());
    }

    public function testPrefixWithNewQuery(): void
    {
        $expectedSQL = <<<'NOWDOC'
            SELECT "users_1"."id", "name"
            FROM (SELECT "u"."id", "u"."name" FROM "ci_users" "u") "users_1"
            WHERE "users_1"."id" > 10
            NOWDOC;

        $subquery = (new BaseBuilder('users u', $this->db))->select('u.id, u.name');
        $builder  = $this->db->newQuery()->fromSubquery($subquery, 'users_1')
            ->select('users_1.id, name')
            ->where('users_1.id > ', 10);

        $this->assertSame($expectedSQL, $builder->getCompiledSelect());
    }
}
