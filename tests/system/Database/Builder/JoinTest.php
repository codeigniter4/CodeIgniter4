<?php

declare(strict_types=1);

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
use CodeIgniter\Database\JoinClause;
use CodeIgniter\Database\Postgre\Builder as PostgreBuilder;
use CodeIgniter\Database\RawSql;
use CodeIgniter\Database\SQLSRV\Builder as SQLSRVBuilder;
use CodeIgniter\Exceptions\InvalidArgumentException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockConnection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class JoinTest extends CIUnitTestCase
{
    protected $db;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = new MockConnection([]);
    }

    public function testJoinSimple(): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->join('job', 'user.id = job.id');

        $expectedSQL = 'SELECT * FROM "user" JOIN "job" ON "user"."id" = "job"."id"';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testJoinIsNull(): void
    {
        $builder = new BaseBuilder('table1', $this->db);

        $builder->join('table2', 'field IS NULL');

        $expectedSQL = 'SELECT * FROM "table1" JOIN "table2" ON "field" IS NULL';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testJoinIsNotNull(): void
    {
        $builder = new BaseBuilder('table1', $this->db);

        $builder->join('table2', 'field IS NOT NULL');

        $expectedSQL = 'SELECT * FROM "table1" JOIN "table2" ON "field" IS NOT NULL';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testJoinMultipleConditions(): void
    {
        $builder = new BaseBuilder('table1', $this->db);

        $builder->join('table2', "table1.field1 = table2.field2 AND table1.field1 = 'foo' AND table2.field2 = 0", 'LEFT');

        $expectedSQL = "SELECT * FROM \"table1\" LEFT JOIN \"table2\" ON \"table1\".\"field1\" = \"table2\".\"field2\" AND \"table1\".\"field1\" = 'foo' AND \"table2\".\"field2\" = 0";

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/8791
     */
    public function testJoinMultipleConditionsBetween(): void
    {
        $builder = new BaseBuilder('table1', $this->db);

        $builder->join(
            'leases',
            'units.unit_id = leases.unit_id AND CURDATE() BETWEEN lease_start_date AND lease_exp_date',
            'LEFT',
        );

        // @TODO Should be `... CURDATE() BETWEEN "lease_start_date" AND "lease_exp_date"`
        $expectedSQL = 'SELECT * FROM "table1" LEFT JOIN "leases" ON "units"."unit_id" = "leases"."unit_id" AND CURDATE() BETWEEN lease_start_date AND lease_exp_date';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testJoinClosureWithColumnComparison(): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->join('job', static function (JoinClause $join): void {
            $join->on('user.id', 'job.id');
        });

        $expectedSQL = 'SELECT * FROM "user" JOIN "job" ON "user"."id" = "job"."id"';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testJoinClosureWithColumnOperator(): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->join('job', static function (JoinClause $join): void {
            $join->on('user.id >=', 'job.id');
        });

        $expectedSQL = 'SELECT * FROM "user" JOIN "job" ON "user"."id" >= "job"."id"';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testJoinClosureWithValueCondition(): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->join('job', static function (JoinClause $join): void {
            $join->on('user.id', 'job.id')
                ->where('job.name', 'Developer');
        });

        $expectedSQL   = 'SELECT * FROM "user" JOIN "job" ON "user"."id" = "job"."id" AND "job"."name" = \'Developer\'';
        $expectedBinds = [
            'job.name' => [
                'Developer',
                true,
            ],
        ];

        $this->assertSame($expectedBinds, $builder->getBinds());
        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testJoinClosureWithOuterBindCollision(): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->where('job.name', 'Accountant')
            ->join('job', static function (JoinClause $join): void {
                $join->on('user.id', 'job.id')
                    ->where('job.name', 'Developer');
            });

        $expectedSQL   = 'SELECT * FROM "user" JOIN "job" ON "user"."id" = "job"."id" AND "job"."name" = \'Developer\' WHERE "job"."name" = \'Accountant\'';
        $expectedBinds = [
            'job.name' => [
                'Accountant',
                true,
            ],
            'job.name.1' => [
                'Developer',
                true,
            ],
        ];

        $this->assertSame($expectedBinds, $builder->getBinds());
        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testJoinClosureWithNullConditions(): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->join('job', static function (JoinClause $join): void {
            $join->on('user.id', 'job.id')
                ->where('job.deleted_at')
                ->orWhere('job.archived_at !=');
        });

        $expectedSQL = 'SELECT * FROM "user" JOIN "job" ON "user"."id" = "job"."id" AND "job"."deleted_at" IS NULL OR "job"."archived_at" IS NOT NULL';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testJoinClosureWithOrConditions(): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->join('job', static function (JoinClause $join): void {
            $join->on('user.id', 'job.id')
                ->orOn('user.email', 'job.email')
                ->orWhere('job.name', 'Developer');
        });

        $expectedSQL = 'SELECT * FROM "user" JOIN "job" ON "user"."id" = "job"."id" OR "user"."email" = "job"."email" OR "job"."name" = \'Developer\'';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    #[DataProvider('provideJoinClosureWithGroupedValueConditions')]
    public function testJoinClosureWithGroupedValueConditions(string $groupStartMethod, string $expectedSQL): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->join('orders', static function (JoinClause $join) use ($groupStartMethod): void {
            $join->on('orders.user_id', 'user.id')
                ->{$groupStartMethod}()
                ->where('orders.status', 'paid')
                ->orWhere('orders.status', 'pending')
                ->groupEnd();
        });

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideJoinClosureWithGroupedValueConditions(): iterable
    {
        return [
            'and group'     => ['groupStart', 'SELECT * FROM "user" JOIN "orders" ON "orders"."user_id" = "user"."id" AND ("orders"."status" = \'paid\' OR "orders"."status" = \'pending\')'],
            'or group'      => ['orGroupStart', 'SELECT * FROM "user" JOIN "orders" ON "orders"."user_id" = "user"."id" OR ("orders"."status" = \'paid\' OR "orders"."status" = \'pending\')'],
            'and not group' => ['notGroupStart', 'SELECT * FROM "user" JOIN "orders" ON "orders"."user_id" = "user"."id" AND NOT ("orders"."status" = \'paid\' OR "orders"."status" = \'pending\')'],
            'or not group'  => ['orNotGroupStart', 'SELECT * FROM "user" JOIN "orders" ON "orders"."user_id" = "user"."id" OR NOT ("orders"."status" = \'paid\' OR "orders"."status" = \'pending\')'],
        ];
    }

    public function testJoinClosureWithGroupedColumnComparisons(): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->join('contacts', static function (JoinClause $join): void {
            $join->groupStart()
                ->on('contacts.user_id', 'user.id')
                ->orOn('contacts.email', 'user.email')
                ->groupEnd();
        });

        $expectedSQL = 'SELECT * FROM "user" JOIN "contacts" ON ("contacts"."user_id" = "user"."id" OR "contacts"."email" = "user"."email")';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testJoinClosureWithNestedGroups(): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->join('orders', static function (JoinClause $join): void {
            $join->on('orders.user_id', 'user.id')
                ->groupStart()
                ->where('orders.status', 'paid')
                ->orGroupStart()
                ->where('orders.status', 'pending')
                ->where('orders.approved_at !=')
                ->groupEnd()
                ->groupEnd();
        });

        $expectedSQL = 'SELECT * FROM "user" JOIN "orders" ON "orders"."user_id" = "user"."id" AND ("orders"."status" = \'paid\' OR ("orders"."status" = \'pending\' AND "orders"."approved_at" IS NOT NULL))';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testJoinClosureWithAlias(): void
    {
        $builder = new BaseBuilder('jobs j', $this->db);

        $builder->join('users u', static function (JoinClause $join): void {
            $join->on('u.id', 'j.id')
                ->where('u.name', 'Derek Jones');
        }, 'LEFT');

        $expectedSQL = 'SELECT * FROM "jobs" "j" LEFT JOIN "users" "u" ON "u"."id" = "j"."id" AND "u"."name" = \'Derek Jones\'';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testJoinClosureNoEscape(): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->join('job', static function (JoinClause $join): void {
            $join->on('LOWER(user.email)', 'job.email', false)
                ->where('job.name', 'Developer');
        });

        $expectedSQL = 'SELECT * FROM "user" JOIN "job" ON LOWER(user.email) = job.email AND "job"."name" = \'Developer\'';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testJoinClosureInheritsNoEscape(): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->join('job', static function (JoinClause $join): void {
            $join->on('user.id', 'job.id')
                ->where('job.name', 'Developer');
        }, escape: false);

        $expectedSQL   = 'SELECT * FROM "user" JOIN job ON user.id = job.id AND job.name = Developer';
        $expectedBinds = [
            'job.name' => [
                'Developer',
                false,
            ],
        ];

        $this->assertSame($expectedBinds, $builder->getBinds());
        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testJoinClosureGroupInheritsNoEscape(): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->join('job', static function (JoinClause $join): void {
            $join->on('user.id', 'job.id')
                ->groupStart()
                ->where('job.name', 'Developer')
                ->orWhere('job.name', 'Designer')
                ->groupEnd();
        }, escape: false);

        $expectedSQL   = 'SELECT * FROM "user" JOIN job ON user.id = job.id AND (job.name = Developer OR job.name = Designer)';
        $expectedBinds = [
            'job.name' => [
                'Developer',
                false,
            ],
            'job.name.1' => [
                'Designer',
                false,
            ],
        ];

        $this->assertSame($expectedBinds, $builder->getBinds());
        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testJoinClosureGroupMaintainsBindCollisions(): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->where('job.name', 'Accountant')
            ->join('job', static function (JoinClause $join): void {
                $join->on('user.id', 'job.id')
                    ->groupStart()
                    ->where('job.name', 'Developer')
                    ->orWhere('job.name', 'Designer')
                    ->groupEnd();
            });

        $expectedBinds = [
            'job.name' => [
                'Accountant',
                true,
            ],
            'job.name.1' => [
                'Developer',
                true,
            ],
            'job.name.2' => [
                'Designer',
                true,
            ],
        ];

        $this->assertSame($expectedBinds, $builder->getBinds());
    }

    public function testJoinClosureRequiresCondition(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('JoinClause must contain at least one condition.');

        $builder = new BaseBuilder('user', $this->db);

        $builder->join('job', static function (JoinClause $join): void {
        });
    }

    public function testJoinClosureGroupEndRequiresMatchingGroupStart(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('JoinClause groupEnd() called without a matching groupStart().');

        $builder = new BaseBuilder('user', $this->db);

        $builder->join('job', static function (JoinClause $join): void {
            $join->groupEnd();
        });
    }

    public function testJoinClosureGroupRequiresCondition(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('JoinClause groups must contain at least one condition.');

        $builder = new BaseBuilder('user', $this->db);

        $builder->join('job', static function (JoinClause $join): void {
            $join->groupStart()
                ->groupEnd();
        });
    }

    public function testJoinClosureRequiresBalancedGroups(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('JoinClause groups must be balanced.');

        $builder = new BaseBuilder('user', $this->db);

        $builder->join('job', static function (JoinClause $join): void {
            $join->groupStart()
                ->on('user.id', 'job.id');
        });
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/3832
     */
    public function testJoinRawSql(): void
    {
        $builder = new BaseBuilder('device', $this->db);

        $sql = 'user.id = device.user_id
            AND (
                (1=1 OR 1=1)
                OR
                (1=1 OR 1=1)
            )';
        $builder->join('user', new RawSql($sql), 'LEFT');

        $expectedSQL = 'SELECT * FROM "device" LEFT JOIN "user" ON user.id = device.user_id AND ( (1=1 OR 1=1) OR (1=1 OR 1=1) )';

        $output = str_replace("\n", ' ', $builder->getCompiledSelect());
        $output = preg_replace('/\s+/', ' ', $output);
        $this->assertSame($expectedSQL, $output);
    }

    public function testFullOuterJoin(): void
    {
        $builder = new PostgreBuilder('jobs', $this->db);
        $builder->testMode();
        $builder->join('users as u', 'users.id = jobs.id', 'full outer');

        $expectedSQL = 'SELECT * FROM "jobs" FULL OUTER JOIN "users" as "u" ON "users"."id" = "jobs"."id"';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testJoinWithAlias(): void
    {
        $this->db = new MockConnection(['DBDriver' => 'SQLSRV', 'database' => 'test', 'schema' => 'dbo']);

        $builder = new SQLSRVBuilder('jobs', $this->db);
        $builder->testMode();
        $builder->join('users u', 'u.id = jobs.id', 'LEFT');

        $expectedSQL = 'SELECT * FROM "test"."dbo"."jobs" LEFT JOIN "test"."dbo"."users" "u" ON "u"."id" = "jobs"."id"';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testJoinClosureWithSqlsrvFullTableName(): void
    {
        $this->db = new MockConnection(['DBDriver' => 'SQLSRV', 'database' => 'test', 'schema' => 'dbo']);

        $builder = new SQLSRVBuilder('jobs', $this->db);
        $builder->testMode();
        $builder->join('users u', static function (JoinClause $join): void {
            $join->on('u.id', 'jobs.id')
                ->where('u.name', 'Derek Jones');
        }, 'LEFT');

        $expectedSQL = 'SELECT * FROM "test"."dbo"."jobs" LEFT JOIN "test"."dbo"."users" "u" ON "u"."id" = "jobs"."id" AND "u"."name" = \'Derek Jones\'';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }
}
