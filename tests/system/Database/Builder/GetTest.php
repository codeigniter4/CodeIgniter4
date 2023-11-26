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
final class GetTest extends CIUnitTestCase
{
    protected $db;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = new MockConnection([]);
    }

    public function testGet(): void
    {
        $builder = $this->db->table('users');

        $expectedSQL = 'SELECT * FROM "users"';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/2141
     */
    public function testGetWithReset(): void
    {
        $builder = $this->db->table('users');
        $builder->testMode()->where('username', 'bogus');

        $expectedSQL           = 'SELECT * FROM "users" WHERE "username" = \'bogus\'';
        $expectedSQLafterreset = 'SELECT * FROM "users"';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->get(0, 50, false)));
        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->get(0, 50, true)));
        $this->assertSame($expectedSQLafterreset, str_replace("\n", ' ', $builder->get(0, 50, true)));
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/2143
     */
    public function testGetWhereWithLimit(): void
    {
        $builder = $this->db->table('users');
        $builder->testMode();

        $expectedSQL             = 'SELECT * FROM "users" WHERE "username" = \'bogus\'  LIMIT 5';
        $expectedSQLWithoutReset = 'SELECT * FROM "users" WHERE "username" = \'bogus\' AND "username" = \'bogus\'  LIMIT 5';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getWhere(['username' => 'bogus'], 5, null, false)));
        $this->assertSame($expectedSQLWithoutReset, str_replace("\n", ' ', $builder->getWhere(['username' => 'bogus'], 5, 0, true)));
        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getWhere(['username' => 'bogus'], 5, null, true)));
    }

    public function testGetWhereWithLimitAndOffset(): void
    {
        $builder = $this->db->table('users');
        $builder->testMode();

        $expectedSQL             = 'SELECT * FROM "users" WHERE "username" = \'bogus\'  LIMIT 10, 5';
        $expectedSQLWithoutReset = 'SELECT * FROM "users" WHERE "username" = \'bogus\' AND "username" = \'bogus\'  LIMIT 10, 5';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getWhere(['username' => 'bogus'], 5, 10, false)));
        $this->assertSame($expectedSQLWithoutReset, str_replace("\n", ' ', $builder->getWhere(['username' => 'bogus'], 5, 10, true)));
        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getWhere(['username' => 'bogus'], 5, 10, true)));
    }

    public function testGetWhereWithWhereConditionOnly(): void
    {
        $builder = $this->db->table('users');
        $builder->testMode();

        $expectedSQL             = 'SELECT * FROM "users" WHERE "username" = \'bogus\'';
        $expectedSQLWithoutReset = 'SELECT * FROM "users" WHERE "username" = \'bogus\' AND "username" = \'bogus\'';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getWhere(['username' => 'bogus'], null, null, false)));
        $this->assertSame($expectedSQLWithoutReset, str_replace("\n", ' ', $builder->getWhere(['username' => 'bogus'], null, null, true)));
        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getWhere(['username' => 'bogus'], null, null, true)));
    }

    public function testGetWhereWithoutArgs(): void
    {
        $builder = $this->db->table('users');
        $builder->testMode();

        $expectedSQL = 'SELECT * FROM "users"';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getWhere(null, null, null, true)));
    }
}
