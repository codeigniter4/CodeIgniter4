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
 */
final class PrefixTest extends CIUnitTestCase
{
    protected $db;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = new MockConnection(['DBPrefix' => 'ci_']);
    }

    public function testPrefixesSetOnTableNames()
    {
        $expected = 'ci_users';

        $this->assertSame($expected, $this->db->prefixTable('users'));
    }

    public function testPrefixesSetOnTableNamesWithWhereClause()
    {
        $builder = $this->db->table('users');

        $where = 'users.created_at < users.updated_at';

        $expectedSQL   = 'SELECT * FROM "ci_users" WHERE "ci_users"."created_at" < "ci_users"."updated_at"';
        $expectedBinds = [];

        $builder->where($where);

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
        $this->assertSame($expectedBinds, $builder->getBinds());
    }
}
