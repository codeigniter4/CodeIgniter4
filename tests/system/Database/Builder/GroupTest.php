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
final class GroupTest extends CIUnitTestCase
{
    protected $db;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = new MockConnection([]);
    }

    public function testGroupBy(): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->select('name')->groupBy('name');

        $expectedSQL = 'SELECT "name" FROM "user" GROUP BY "name"';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testHavingBy(): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->select('name')
            ->groupBy('name')
            ->having('SUM(id) > 2');

        $expectedSQL = 'SELECT "name" FROM "user" GROUP BY "name" HAVING SUM(id) > 2';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testOrHavingBy(): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->select('name')
            ->groupBy('name')
            ->having('id >', 3)
            ->orHaving('SUM(id) > 2');

        $expectedSQL = 'SELECT "name" FROM "user" GROUP BY "name" HAVING "id" > 3 OR SUM(id) > 2';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testHavingIn(): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->select('name')
            ->groupBy('name')
            ->havingIn('id', [1, 2]);

        $expectedSQL = 'SELECT "name" FROM "user" GROUP BY "name" HAVING "id" IN (1,2)';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testHavingInClosure(): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->select('name')->groupBy('name');

        $builder->havingIn('id', static fn (BaseBuilder $builder) => $builder->select('user_id')->from('users_jobs')->where('group_id', 3));

        $expectedSQL = 'SELECT "name" FROM "user" GROUP BY "name" HAVING "id" IN (SELECT "user_id" FROM "users_jobs" WHERE "group_id" = 3)';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testOrHavingIn(): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->select('name')
            ->groupBy('name')
            ->havingIn('id', [1, 2])
            ->orHavingIn('group_id', [5, 6]);

        $expectedSQL = 'SELECT "name" FROM "user" GROUP BY "name" HAVING "id" IN (1,2) OR "group_id" IN (5,6)';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testOrHavingInClosure(): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->select('name')->groupBy('name');

        $builder->havingIn('id', static fn (BaseBuilder $builder) => $builder->select('user_id')->from('users_jobs')->where('group_id', 3));
        $builder->orHavingIn('group_id', static fn (BaseBuilder $builder) => $builder->select('group_id')->from('groups')->where('group_id', 6));

        $expectedSQL = 'SELECT "name" FROM "user" GROUP BY "name" HAVING "id" IN (SELECT "user_id" FROM "users_jobs" WHERE "group_id" = 3) OR "group_id" IN (SELECT "group_id" FROM "groups" WHERE "group_id" = 6)';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testHavingNotIn(): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->select('name')
            ->groupBy('name')
            ->havingNotIn('id', [1, 2]);

        $expectedSQL = 'SELECT "name" FROM "user" GROUP BY "name" HAVING "id" NOT IN (1,2)';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testHavingNotInClosure(): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->select('name')->groupBy('name');

        $builder->havingNotIn('id', static fn (BaseBuilder $builder) => $builder->select('user_id')->from('users_jobs')->where('group_id', 3));

        $expectedSQL = 'SELECT "name" FROM "user" GROUP BY "name" HAVING "id" NOT IN (SELECT "user_id" FROM "users_jobs" WHERE "group_id" = 3)';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testOrHavingNotIn(): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->select('name')
            ->groupBy('name')
            ->havingNotIn('id', [1, 2])
            ->orHavingNotIn('group_id', [5, 6]);

        $expectedSQL = 'SELECT "name" FROM "user" GROUP BY "name" HAVING "id" NOT IN (1,2) OR "group_id" NOT IN (5,6)';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testOrHavingNotInClosure(): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->select('name')->groupBy('name');

        $builder->havingNotIn('id', static fn (BaseBuilder $builder) => $builder->select('user_id')->from('users_jobs')->where('group_id', 3));
        $builder->orHavingNotIn('group_id', static fn (BaseBuilder $builder) => $builder->select('group_id')->from('groups')->where('group_id', 6));

        $expectedSQL = 'SELECT "name" FROM "user" GROUP BY "name" HAVING "id" NOT IN (SELECT "user_id" FROM "users_jobs" WHERE "group_id" = 3) OR "group_id" NOT IN (SELECT "group_id" FROM "groups" WHERE "group_id" = 6)';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testHavingLike(): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->select('name')
            ->groupBy('name')
            ->havingLike('pet_name', 'a');

        $expectedSQL = 'SELECT "name" FROM "user" GROUP BY "name" HAVING "pet_name" LIKE \'%a%\' ESCAPE \'!\'';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testHavingLikeBefore(): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->select('name')
            ->groupBy('name')
            ->havingLike('pet_name', 'a', 'before');

        $expectedSQL = 'SELECT "name" FROM "user" GROUP BY "name" HAVING "pet_name" LIKE \'%a\' ESCAPE \'!\'';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testHavingLikeAfter(): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->select('name')
            ->groupBy('name')
            ->havingLike('pet_name', 'a', 'after');

        $expectedSQL = 'SELECT "name" FROM "user" GROUP BY "name" HAVING "pet_name" LIKE \'a%\' ESCAPE \'!\'';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testNotHavingLike(): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->select('name')
            ->groupBy('name')
            ->notHavingLike('pet_name', 'a');

        $expectedSQL = 'SELECT "name" FROM "user" GROUP BY "name" HAVING "pet_name" NOT LIKE \'%a%\' ESCAPE \'!\'';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testNotHavingLikeBefore(): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->select('name')
            ->groupBy('name')
            ->notHavingLike('pet_name', 'a', 'before');

        $expectedSQL = 'SELECT "name" FROM "user" GROUP BY "name" HAVING "pet_name" NOT LIKE \'%a\' ESCAPE \'!\'';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testNotHavingLikeAfter(): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->select('name')
            ->groupBy('name')
            ->notHavingLike('pet_name', 'a', 'after');

        $expectedSQL = 'SELECT "name" FROM "user" GROUP BY "name" HAVING "pet_name" NOT LIKE \'a%\' ESCAPE \'!\'';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testOrHavingLike(): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->select('name')
            ->groupBy('name')
            ->havingLike('pet_name', 'a')
            ->orHavingLike('pet_color', 'b');

        $expectedSQL = 'SELECT "name" FROM "user" GROUP BY "name" HAVING "pet_name" LIKE \'%a%\' ESCAPE \'!\' OR  "pet_color" LIKE \'%b%\' ESCAPE \'!\'';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testOrHavingLikeBefore(): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->select('name')
            ->groupBy('name')
            ->havingLike('pet_name', 'a', 'before')
            ->orHavingLike('pet_color', 'b', 'before');

        $expectedSQL = 'SELECT "name" FROM "user" GROUP BY "name" HAVING "pet_name" LIKE \'%a\' ESCAPE \'!\' OR  "pet_color" LIKE \'%b\' ESCAPE \'!\'';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testOrHavingLikeAfter(): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->select('name')
            ->groupBy('name')
            ->havingLike('pet_name', 'a', 'after')
            ->orHavingLike('pet_color', 'b', 'after');

        $expectedSQL = 'SELECT "name" FROM "user" GROUP BY "name" HAVING "pet_name" LIKE \'a%\' ESCAPE \'!\' OR  "pet_color" LIKE \'b%\' ESCAPE \'!\'';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testOrNotHavingLike(): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->select('name')
            ->groupBy('name')
            ->havingLike('pet_name', 'a')
            ->orNotHavingLike('pet_color', 'b');

        $expectedSQL = 'SELECT "name" FROM "user" GROUP BY "name" HAVING "pet_name" LIKE \'%a%\' ESCAPE \'!\' OR  "pet_color" NOT LIKE \'%b%\' ESCAPE \'!\'';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testOrNotHavingLikeBefore(): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->select('name')
            ->groupBy('name')
            ->havingLike('pet_name', 'a', 'before')
            ->orNotHavingLike('pet_color', 'b', 'before');

        $expectedSQL = 'SELECT "name" FROM "user" GROUP BY "name" HAVING "pet_name" LIKE \'%a\' ESCAPE \'!\' OR  "pet_color" NOT LIKE \'%b\' ESCAPE \'!\'';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testOrNotHavingLikeAfter(): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->select('name')
            ->groupBy('name')
            ->havingLike('pet_name', 'a', 'after')
            ->orNotHavingLike('pet_color', 'b', 'after');

        $expectedSQL = 'SELECT "name" FROM "user" GROUP BY "name" HAVING "pet_name" LIKE \'a%\' ESCAPE \'!\' OR  "pet_color" NOT LIKE \'b%\' ESCAPE \'!\'';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testHavingAndGroup(): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->select('name')
            ->groupBy('name')
            ->having('SUM(id) <', 3)
            ->havingGroupStart()
            ->having('SUM(id)', 2)
            ->having('name', 'adam')
            ->havingGroupEnd();

        $expectedSQL = 'SELECT "name" FROM "user" GROUP BY "name" HAVING SUM(id) < 3 AND   ( SUM(id) = 2 AND "name" = \'adam\'  )';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testHavingOrGroup(): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->select('name')
            ->groupBy('name')
            ->having('SUM(id) >', 3)
            ->orHavingGroupStart()
            ->having('SUM(id)', 2)
            ->having('name', 'adam')
            ->havingGroupEnd();

        $expectedSQL = 'SELECT "name" FROM "user" GROUP BY "name" HAVING SUM(id) > 3 OR   ( SUM(id) = 2 AND "name" = \'adam\'  )';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testNotHavingAndGroup(): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->select('name')
            ->groupBy('name')
            ->having('SUM(id) <', 3)
            ->notHavingGroupStart()
            ->having('SUM(id)', 2)
            ->having('name', 'adam')
            ->havingGroupEnd();

        $expectedSQL = 'SELECT "name" FROM "user" GROUP BY "name" HAVING SUM(id) < 3 AND NOT   ( SUM(id) = 2 AND "name" = \'adam\'  )';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testNotHavingOrGroup(): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->select('name')
            ->groupBy('name')
            ->having('SUM(id) <', 3)
            ->orNotHavingGroupStart()
            ->having('SUM(id)', 2)
            ->having('name', 'adam')
            ->havingGroupEnd();

        $expectedSQL = 'SELECT "name" FROM "user" GROUP BY "name" HAVING SUM(id) < 3 OR NOT   ( SUM(id) = 2 AND "name" = \'adam\'  )';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testAndGroups(): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->groupStart()
            ->where('id >', 3)
            ->where('name !=', 'Luke')
            ->groupEnd()
            ->where('name', 'Darth');

        $expectedSQL = 'SELECT * FROM "user" WHERE   ( "id" > 3 AND "name" != \'Luke\'  ) AND "name" = \'Darth\'';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testOrGroups(): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->where('name', 'Darth')
            ->orGroupStart()
            ->where('id >', 3)
            ->where('name !=', 'Luke')
            ->groupEnd();

        $expectedSQL = 'SELECT * FROM "user" WHERE "name" = \'Darth\' OR   ( "id" > 3 AND "name" != \'Luke\'  )';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testNotGroups(): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->where('name', 'Darth')
            ->notGroupStart()
            ->where('id >', 3)
            ->where('name !=', 'Luke')
            ->groupEnd();

        $expectedSQL = 'SELECT * FROM "user" WHERE "name" = \'Darth\' AND NOT   ( "id" > 3 AND "name" != \'Luke\'  )';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testOrNotGroups(): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->where('name', 'Darth')
            ->orNotGroupStart()
            ->where('id >', 3)
            ->where('name !=', 'Luke')
            ->groupEnd();

        $expectedSQL = 'SELECT * FROM "user" WHERE "name" = \'Darth\' OR NOT   ( "id" > 3 AND "name" != \'Luke\'  )';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }
}
