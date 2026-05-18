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
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Database\RawSql;
use CodeIgniter\Exceptions\InvalidArgumentException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockConnection;
use DateTime;
use Error;
use ErrorException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use stdClass;

/**
 * @internal
 */
#[Group('Others')]
final class WhereTest extends CIUnitTestCase
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

    public function testSimpleWhere(): void
    {
        $builder = $this->db->table('users');

        $expectedSQL   = 'SELECT * FROM "users" WHERE "id" = 3';
        $expectedBinds = [
            'id' => [
                3,
                true,
            ],
        ];

        $builder->where('id', 3);
        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
        $this->assertSame($expectedBinds, $builder->getBinds());
    }

    public function testWhereNoEscape(): void
    {
        $builder = $this->db->table('users');

        $expectedSQL   = 'SELECT * FROM "users" WHERE id = 3';
        $expectedBinds = [
            'id' => [
                3,
                false,
            ],
        ];

        $builder->where('id', 3, false);
        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
        $this->assertSame($expectedBinds, $builder->getBinds());
    }

    public function testWhereCustomKeyOperator(): void
    {
        $builder = $this->db->table('users');

        $expectedSQL   = 'SELECT * FROM "users" WHERE "id" != 3';
        $expectedBinds = [
            'id' => [
                3,
                true,
            ],
        ];

        $builder->where('id !=', 3);
        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
        $this->assertSame($expectedBinds, $builder->getBinds());
    }

    public function testWhereAssociateArray(): void
    {
        $builder = $this->db->table('jobs');

        $where = [
            'id'      => 2,
            'name !=' => 'Accountant',
        ];

        $expectedSQL   = 'SELECT * FROM "jobs" WHERE "id" = 2 AND "name" != \'Accountant\'';
        $expectedBinds = [
            'id' => [
                2,
                true,
            ],
            'name' => [
                'Accountant',
                true,
            ],
        ];

        $builder->where($where);
        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
        $this->assertSame($expectedBinds, $builder->getBinds());
    }

    public function testWhereAssociateArrayKeyHasEqualValueIsNull(): void
    {
        $builder = $this->db->table('users');

        $where = [
            'deleted_at =' => null,
        ];

        $expectedSQL   = 'SELECT * FROM "users" WHERE "deleted_at" IS NULL';
        $expectedBinds = [];

        $builder->where($where);
        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
        $this->assertSame($expectedBinds, $builder->getBinds());
    }

    public function testWhereLikeInAssociateArray(): void
    {
        $builder = $this->db->table('user');

        $where = [
            'id <'      => 100,
            'col1 LIKE' => '%gmail%',
        ];
        $builder->where($where);

        $expectedSQL = 'SELECT * FROM "user" WHERE "id" < 100 AND "col1" LIKE \'%gmail%\'';
        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    /**
     * @param mixed $value
     */
    #[DataProvider('provideWhereOperatorRegressionCases')]
    public function testWhereOperatorRegressionCases(string $key, $value, string $expectedSQL): void
    {
        $builder = $this->db->table('jobs job');

        $builder->where($key, $value);

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    /**
     * @return iterable<string, array{string, mixed, string}>
     */
    public static function provideWhereOperatorRegressionCases(): iterable
    {
        return [
            'like operator with value' => [
                'job.status LIKE',
                'p%',
                'SELECT * FROM "jobs" "job" WHERE "job"."status" LIKE \'p%\'',
            ],
            'equals operator with null' => [
                'job.deleted_at =',
                null,
                'SELECT * FROM "jobs" "job" WHERE "job"."deleted_at" IS NULL',
            ],
            'not equals operator with null' => [
                'job.deleted_at !=',
                null,
                'SELECT * FROM "jobs" "job" WHERE "job"."deleted_at" IS NOT NULL',
            ],
        ];
    }

    public function testWhereCustomString(): void
    {
        $builder = $this->db->table('jobs');

        $where = "id > 2 AND name != 'Accountant'";

        $expectedSQL   = "SELECT * FROM \"jobs\" WHERE \"id\" > 2 AND \"name\" != 'Accountant'";
        $expectedBinds = [];

        $builder->where($where);
        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
        $this->assertSame($expectedBinds, $builder->getBinds());
    }

    public function testWhereCustomStringWithOperatorEscapeFalse(): void
    {
        $builder = $this->db->table('jobs');

        $where = 'CURRENT_TIMESTAMP() = DATE_ADD(column, INTERVAL 2 HOUR)';
        $builder->where($where, null, false);

        $expectedSQL = 'SELECT * FROM "jobs" WHERE CURRENT_TIMESTAMP() = DATE_ADD(column, INTERVAL 2 HOUR)';
        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));

        $expectedBinds = [];
        $this->assertSame($expectedBinds, $builder->getBinds());
    }

    public function testWhereCustomStringWithoutOperatorEscapeFalse(): void
    {
        $builder = $this->db->table('jobs');

        $where = "REPLACE(column, 'somestring', '')";
        $builder->where($where, "''", false);

        $expectedSQL = "SELECT * FROM \"jobs\" WHERE REPLACE(column, 'somestring', '') = ''";
        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));

        $expectedBinds = [
            "REPLACE(column, 'somestring', '')" => [
                0 => "''",
                1 => false,
            ],
        ];
        $this->assertSame($expectedBinds, $builder->getBinds());
    }

    public function testWhereCustomStringWithBetweenEscapeFalse(): void
    {
        $builder = $this->db->table('jobs');

        $where = "created_on BETWEEN '2022-07-01 00:00:00' AND '2022-12-31 23:59:59'";
        $builder->where($where, null, false);

        $expectedSQL = "SELECT * FROM \"jobs\" WHERE created_on BETWEEN '2022-07-01 00:00:00' AND '2022-12-31 23:59:59'";
        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));

        $expectedBinds = [];
        $this->assertSame($expectedBinds, $builder->getBinds());
    }

    public function testWhereRawSql(): void
    {
        $builder = $this->db->table('jobs');

        $sql = "id > 2 AND name != 'Accountant'";
        $builder->where(new RawSql($sql));

        $expectedSQL   = "SELECT * FROM \"jobs\" WHERE id > 2 AND name != 'Accountant'";
        $expectedBinds = [];

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
        $this->assertSame($expectedBinds, $builder->getBinds());
    }

    public function testWhereValueRawSql(): void
    {
        $sql = $this->db->table('auth_bearer')
            ->select('*')
            ->where('expires', new RawSql('DATE_ADD(NOW(), INTERVAL 2 HOUR)'))
            ->getCompiledSelect(true);

        $expected = <<<'SQL'
            SELECT *
            FROM "auth_bearer"
            WHERE "expires" = DATE_ADD(NOW(), INTERVAL 2 HOUR)
            SQL;
        $this->assertSame($expected, $sql);
    }

    public function testWhereKeyOnlyRawSql(): void
    {
        $sql = $this->db->table('auth_bearer')
            ->select('*')
            ->where(new RawSql('DATE_ADD(NOW(), INTERVAL 2 HOUR)'), '2023-01-01')
            ->getCompiledSelect(true);

        $expected = <<<'SQL'
            SELECT *
            FROM "auth_bearer"
            WHERE DATE_ADD(NOW(), INTERVAL 2 HOUR) = '2023-01-01'
            SQL;
        $this->assertSame($expected, $sql);
    }

    public function testWhereKeyAndValueRawSql(): void
    {
        $sql = $this->db->table('auth_bearer')
            ->select('*')
            ->where(new RawSql('CURRENT_TIMESTAMP()'), new RawSql('DATE_ADD(column, INTERVAL 2 HOUR)'))
            ->getCompiledSelect(true);

        $expected = <<<'SQL'
            SELECT *
            FROM "auth_bearer"
            WHERE CURRENT_TIMESTAMP() = DATE_ADD(column, INTERVAL 2 HOUR)
            SQL;
        $this->assertSame($expected, $sql);
    }

    public function testWhereKeyAndValueRawSqlWithOperator(): void
    {
        $sql = $this->db->table('auth_bearer')
            ->select('*')
            ->where(new RawSql('CURRENT_TIMESTAMP() >='), new RawSql('DATE_ADD(column, INTERVAL 2 HOUR)'))
            ->getCompiledSelect(true);

        $expected = <<<'SQL'
            SELECT *
            FROM "auth_bearer"
            WHERE CURRENT_TIMESTAMP() >= DATE_ADD(column, INTERVAL 2 HOUR)
            SQL;
        $this->assertSame($expected, $sql);
    }

    public function testWhereValueSubQuery(): void
    {
        $expectedSQL = 'SELECT * FROM "neworder" WHERE "advance_amount" < (SELECT MAX(advance_amount) FROM "orders" WHERE "id" > 2)';

        // Closure
        $builder = $this->db->table('neworder');

        $builder->where('advance_amount <', static fn (BaseBuilder $builder) => $builder->select('MAX(advance_amount)', false)->from('orders')->where('id >', 2));

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));

        // Builder
        $builder = $this->db->table('neworder');

        $subQuery = $this->db->table('orders')
            ->select('MAX(advance_amount)', false)
            ->where('id >', 2);

        $builder->where('advance_amount <', $subQuery);

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testOrWhere(): void
    {
        $builder = $this->db->table('jobs');

        $builder->where('name !=', 'Accountant')->orWhere('id >', 3);

        $expectedSQL   = 'SELECT * FROM "jobs" WHERE "name" != \'Accountant\' OR "id" > 3';
        $expectedBinds = [
            'name' => [
                'Accountant',
                true,
            ],
            'id' => [
                3,
                true,
            ],
        ];

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
        $this->assertSame($expectedBinds, $builder->getBinds());
    }

    public function testOrWhereSameColumn(): void
    {
        $builder = $this->db->table('jobs');

        $builder->where('name', 'Accountant')->orWhere('name', 'foobar');

        $expectedSQL   = 'SELECT * FROM "jobs" WHERE "name" = \'Accountant\' OR "name" = \'foobar\'';
        $expectedBinds = [
            'name' => [
                'Accountant',
                true,
            ],
            'name.1' => [
                'foobar',
                true,
            ],
        ];

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
        $this->assertSame($expectedBinds, $builder->getBinds());
    }

    #[DataProvider('provideWhereColumnWithOperators')]
    public function testWhereColumnWithOperators(string $first, string $operator): void
    {
        $builder = $this->db->table('users');

        $builder->whereColumn($first, 'updated_at');

        $expectedSQL   = sprintf('SELECT * FROM "users" WHERE "created_at" %s "updated_at"', $operator);
        $expectedBinds = [];

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
        $this->assertSame($expectedBinds, $builder->getBinds());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideWhereColumnWithOperators(): iterable
    {
        return [
            'default' => ['created_at', '='],
            '='       => ['created_at =', '='],
            '!='      => ['created_at !=', '!='],
            '<>'      => ['created_at <>', '<>'],
            '<'       => ['created_at <', '<'],
            '>'       => ['created_at >', '>'],
            '<='      => ['created_at <=', '<='],
            '>='      => ['created_at >=', '>='],
        ];
    }

    public function testWhereColumnWithAlias(): void
    {
        $builder = $this->db->table('users u');

        $builder->whereColumn('u.updated_at >', 'u.created_at');

        $expectedSQL   = 'SELECT * FROM "users" "u" WHERE "u"."updated_at" > "u"."created_at"';
        $expectedBinds = [];

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
        $this->assertSame($expectedBinds, $builder->getBinds());
    }

    public function testOrWhereColumn(): void
    {
        $builder = $this->db->table('users');

        $builder->where('active', 1)
            ->orWhereColumn('updated_at >', 'created_at');

        $expectedSQL   = 'SELECT * FROM "users" WHERE "active" = 1 OR "updated_at" > "created_at"';
        $expectedBinds = [
            'active' => [
                1,
                true,
            ],
        ];

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
        $this->assertSame($expectedBinds, $builder->getBinds());
    }

    public function testWhereColumnWithGroupedConditions(): void
    {
        $builder = $this->db->table('users');

        $builder->groupStart()
            ->whereColumn('created_at', 'updated_at')
            ->orWhereColumn('updated_at >', 'created_at')
            ->groupEnd()
            ->where('active', 1);

        $expectedSQL = 'SELECT * FROM "users" WHERE   ( "created_at" = "updated_at" OR "updated_at" > "created_at"  ) AND "active" = 1';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testWhereColumnNoEscape(): void
    {
        $builder = $this->db->table('users');

        $builder->whereColumn('LOWER(users.email)', 'normalized_email', escape: false);

        $expectedSQL   = 'SELECT * FROM "users" WHERE LOWER(users.email) = normalized_email';
        $expectedBinds = [];

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
        $this->assertSame($expectedBinds, $builder->getBinds());
    }

    public function testWhereColumnTreatsSecondArgumentAsColumnName(): void
    {
        $builder = $this->db->table('users');

        $builder->whereColumn('created_at', 'like');

        $expectedSQL   = 'SELECT * FROM "users" WHERE "created_at" = "like"';
        $expectedBinds = [];

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
        $this->assertSame($expectedBinds, $builder->getBinds());
    }

    public function testWhereColumnIgnoresOperatorsInsideFirstArgument(): void
    {
        $builder = $this->db->table('users');

        $builder->whereColumn("JSON_EXTRACT(data, '$.a>b')", 'updated_at', escape: false);

        $expectedSQL   = 'SELECT * FROM "users" WHERE JSON_EXTRACT(data, \'$.a>b\') = updated_at';
        $expectedBinds = [];

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
        $this->assertSame($expectedBinds, $builder->getBinds());
    }

    #[DataProvider('provideWhereColumnInvalidColumnThrowInvalidArgumentException')]
    public function testWhereColumnInvalidColumnThrowInvalidArgumentException(string $first, string $second): void
    {
        $this->expectException(InvalidArgumentException::class);

        $builder = $this->db->table('users');
        $builder->whereColumn($first, $second);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideWhereColumnInvalidColumnThrowInvalidArgumentException(): iterable
    {
        return [
            'empty first column'  => ['', 'updated_at'],
            'empty second column' => ['created_at =', ''],
        ];
    }

    public function testWhereExistsSubQuery(): void
    {
        $expectedSQL = 'SELECT * FROM "users" WHERE EXISTS (SELECT 1 FROM "orders" WHERE "orders"."user_id" = "users"."id")';

        // Closure
        $builder = $this->db->table('users');

        $builder->whereExists(static fn (BaseBuilder $builder) => $builder
            ->select('1', false)
            ->from('orders')
            ->whereColumn('orders.user_id', 'users.id'));

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));

        // Builder
        $builder = $this->db->table('users');

        $subQuery = $this->db->table('orders')
            ->select('1', false)
            ->whereColumn('orders.user_id', 'users.id');

        $builder->whereExists($subQuery);

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    #[DataProvider('provideWhereExistsVariants')]
    public function testWhereExistsVariants(string $method, string $expectedSQL): void
    {
        $builder = $this->db->table('users');

        $builder->where('active', 1);

        $builder->{$method}(static fn (BaseBuilder $builder) => $builder
            ->select('1', false)
            ->from('orders')
            ->whereColumn('orders.user_id', 'users.id'));

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideWhereExistsVariants(): iterable
    {
        $exists    = '(SELECT 1 FROM "orders" WHERE "orders"."user_id" = "users"."id")';
        $baseQuery = 'SELECT * FROM "users" WHERE "active" = 1';

        return [
            'whereExists'      => ['whereExists', "{$baseQuery} AND EXISTS {$exists}"],
            'orWhereExists'    => ['orWhereExists', "{$baseQuery} OR EXISTS {$exists}"],
            'whereNotExists'   => ['whereNotExists', "{$baseQuery} AND NOT EXISTS {$exists}"],
            'orWhereNotExists' => ['orWhereNotExists', "{$baseQuery} OR NOT EXISTS {$exists}"],
        ];
    }

    public function testWhereExistsWithGroupedConditions(): void
    {
        $builder = $this->db->table('users');

        $builder->groupStart()
            ->whereExists(static fn (BaseBuilder $builder) => $builder
                ->select('1', false)
                ->from('orders')
                ->whereColumn('orders.user_id', 'users.id'))
            ->orWhereNotExists(static fn (BaseBuilder $builder) => $builder
                ->select('1', false)
                ->from('jobs')
                ->whereColumn('jobs.user_id', 'users.id'))
            ->groupEnd()
            ->where('active', 1);

        $expectedSQL = 'SELECT * FROM "users" WHERE   ( EXISTS (SELECT 1 FROM "orders" WHERE "orders"."user_id" = "users"."id") OR NOT EXISTS (SELECT 1 FROM "jobs" WHERE "jobs"."user_id" = "users"."id")  ) AND "active" = 1';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testWhereExistsWithOuterAndInnerBinds(): void
    {
        $builder = $this->db->table('users');

        $builder->where('active', 1)
            ->whereExists(static fn (BaseBuilder $builder) => $builder
                ->select('1', false)
                ->from('orders')
                ->where('orders.status', 'paid')
                ->whereColumn('orders.user_id', 'users.id'));

        $expectedSQL   = 'SELECT * FROM "users" WHERE "active" = 1 AND EXISTS (SELECT 1 FROM "orders" WHERE "orders"."status" = \'paid\' AND "orders"."user_id" = "users"."id")';
        $expectedBinds = [
            'active' => [
                1,
                true,
            ],
        ];

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
        $this->assertSame($expectedBinds, $builder->getBinds());
    }

    /**
     * @param mixed $subquery
     */
    #[DataProvider('provideWhereExistsInvalidSubqueryThrowInvalidArgumentException')]
    public function testWhereExistsInvalidSubqueryThrowInvalidArgumentException($subquery): void
    {
        $this->expectException(InvalidArgumentException::class);

        $builder = $this->db->table('users');
        $builder->whereExists($subquery);
    }

    /**
     * @return iterable<string, array{mixed}>
     */
    public static function provideWhereExistsInvalidSubqueryThrowInvalidArgumentException(): iterable
    {
        return [
            'null'       => [null],
            'array'      => [[]],
            'stdClass'   => [new stdClass()],
            'raw string' => ['SELECT 1'],
        ];
    }

    public function testWhereExistsSameBaseBuilderObject(): void
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('The subquery cannot be the same object as the main query object.');

        $builder = $this->db->table('users');
        $builder->whereExists($builder);
    }

    #[DataProvider('provideWhereBetweenMethods')]
    public function testWhereBetweenMethods(string $method, string $sql): void
    {
        $builder = $this->db->table('jobs');

        $builder->{$method}('created_at', ['2026-01-01', '2026-01-31']);

        $expectedSQL   = 'SELECT * FROM "jobs" WHERE "created_at" ' . $sql . " '2026-01-01' AND '2026-01-31'";
        $expectedBinds = [
            'created_at' => [
                '2026-01-01',
                true,
            ],
            'created_at.1' => [
                '2026-01-31',
                true,
            ],
        ];

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
        $this->assertSame($expectedBinds, $builder->getBinds());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideWhereBetweenMethods(): iterable
    {
        return [
            'between'     => ['whereBetween', 'BETWEEN'],
            'not between' => ['whereNotBetween', 'NOT BETWEEN'],
        ];
    }

    #[DataProvider('provideOrWhereBetweenMethods')]
    public function testOrWhereBetweenMethods(string $method, string $sql): void
    {
        $builder = $this->db->table('jobs');

        $builder->where('active', 1)
            ->{$method}('created_at', ['2026-01-01', '2026-01-31']);

        $expectedSQL = 'SELECT * FROM "jobs" WHERE "active" = 1 OR "created_at" ' . $sql . " '2026-01-01' AND '2026-01-31'";

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideOrWhereBetweenMethods(): iterable
    {
        return [
            'or between'     => ['orWhereBetween', 'BETWEEN'],
            'or not between' => ['orWhereNotBetween', 'NOT BETWEEN'],
        ];
    }

    public function testWhereBetweenWithGroupedConditions(): void
    {
        $builder = $this->db->table('jobs');

        $builder->groupStart()
            ->whereBetween('created_at', ['2026-01-01', '2026-01-31'])
            ->orWhereNotBetween('updated_at', ['2026-02-01', '2026-02-28'])
            ->groupEnd()
            ->where('active', 1);

        $expectedSQL = 'SELECT * FROM "jobs" WHERE   ( "created_at" BETWEEN \'2026-01-01\' AND \'2026-01-31\' OR "updated_at" NOT BETWEEN \'2026-02-01\' AND \'2026-02-28\'  ) AND "active" = 1';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testWhereBetweenNoEscape(): void
    {
        $builder = $this->db->table('jobs');

        $builder->whereBetween('DATE(created_at)', ['20260101', '20260131'], escape: false);

        $expectedSQL   = 'SELECT * FROM "jobs" WHERE DATE(created_at) BETWEEN 20260101 AND 20260131';
        $expectedBinds = [
            'DATE(created_at)' => [
                '20260101',
                false,
            ],
            'DATE(created_at).1' => [
                '20260131',
                false,
            ],
        ];

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
        $this->assertSame($expectedBinds, $builder->getBinds());
    }

    public function testWhereBetweenWithAliasBeforeFrom(): void
    {
        $builder = $this->db->newQuery();

        $builder->whereBetween('u.created_at', ['2026-01-01', '2026-01-31'])
            ->from('users u');

        $expectedSQL = 'SELECT * FROM "users" "u" WHERE "u"."created_at" BETWEEN \'2026-01-01\' AND \'2026-01-31\'';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    /**
     * @param mixed $key
     */
    #[DataProvider('provideWhereInvalidKeyThrowInvalidArgumentException')]
    public function testWhereBetweenInvalidKeyThrowInvalidArgumentException($key): void
    {
        $this->expectException(InvalidArgumentException::class);

        $builder = $this->db->table('jobs');
        $builder->whereBetween($key, ['2026-01-01', '2026-01-31']);
    }

    /**
     * @param mixed $values
     */
    #[DataProvider('provideWhereBetweenInvalidValuesThrowInvalidArgumentException')]
    public function testWhereBetweenInvalidValuesThrowInvalidArgumentException($values): void
    {
        $this->expectException(InvalidArgumentException::class);

        $builder = $this->db->table('jobs');
        $builder->whereBetween('created_at', $values);
    }

    /**
     * @return iterable<string, array{mixed}>
     */
    public static function provideWhereBetweenInvalidValuesThrowInvalidArgumentException(): iterable
    {
        return [
            'null'         => [null],
            'not array'    => ['not array'],
            'empty array'  => [[]],
            'one value'    => [['2026-01-01']],
            'three values' => [
                ['2026-01-01', '2026-01-31', '2026-02-28'],
            ],
        ];
    }

    public function testWhereIn(): void
    {
        $builder = $this->db->table('jobs');

        $builder->whereIn('name', ['Politician', 'Accountant']);

        $expectedSQL   = 'SELECT * FROM "jobs" WHERE "name" IN (\'Politician\',\'Accountant\')';
        $expectedBinds = [
            'name' => [
                [
                    'Politician',
                    'Accountant',
                ],
                true,
            ],
        ];

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
        $this->assertSame($expectedBinds, $builder->getBinds());
    }

    public function testWhereInSubQuery(): void
    {
        $expectedSQL = 'SELECT * FROM "jobs" WHERE "id" IN (SELECT "job_id" FROM "users_jobs" WHERE "user_id" = 3)';

        // Closure
        $builder = $this->db->table('jobs');

        $builder->whereIn('id', static fn (BaseBuilder $builder) => $builder->select('job_id')->from('users_jobs')->where('user_id', 3));

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));

        // Builder
        $builder = $this->db->table('jobs');

        $subQuery = $this->db->table('users_jobs')
            ->select('job_id')
            ->where('user_id', 3);

        $builder->whereIn('id', $subQuery);

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    /**
     * @param mixed $key
     */
    #[DataProvider('provideWhereInvalidKeyThrowInvalidArgumentException')]
    public function testWhereInvalidKeyThrowInvalidArgumentException($key): void
    {
        $this->expectException('InvalidArgumentException');
        $builder = $this->db->table('jobs');

        $builder->whereIn($key, ['Politician', 'Accountant']);
    }

    public static function provideWhereInvalidKeyThrowInvalidArgumentException(): iterable
    {
        return [
            'null'         => [null],
            'empty string' => [''],
        ];
    }

    /**
     * @param mixed $values
     */
    #[DataProvider('provideWhereInEmptyValuesThrowInvalidArgumentException')]
    public function testWhereInEmptyValuesThrowInvalidArgumentException($values): void
    {
        $this->expectException('InvalidArgumentException');
        $builder = $this->db->table('jobs');

        $builder->whereIn('name', $values);
    }

    public static function provideWhereInEmptyValuesThrowInvalidArgumentException(): iterable
    {
        return [
            'null'                    => [null],
            'not array'               => ['not array'],
            'not instanceof \Closure' => [new stdClass()],
        ];
    }

    public function testWhereNotIn(): void
    {
        $builder = $this->db->table('jobs');

        $builder->whereNotIn('name', ['Politician', 'Accountant']);

        $expectedSQL   = 'SELECT * FROM "jobs" WHERE "name" NOT IN (\'Politician\',\'Accountant\')';
        $expectedBinds = [
            'name' => [
                [
                    'Politician',
                    'Accountant',
                ],
                true,
            ],
        ];

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
        $this->assertSame($expectedBinds, $builder->getBinds());
    }

    public function testWhereNotInSubQuery(): void
    {
        $expectedSQL = 'SELECT * FROM "jobs" WHERE "id" NOT IN (SELECT "job_id" FROM "users_jobs" WHERE "user_id" = 3)';

        // Closure
        $builder = $this->db->table('jobs');

        $builder->whereNotIn('id', static fn (BaseBuilder $builder) => $builder->select('job_id')->from('users_jobs')->where('user_id', 3));

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));

        // Builder
        $builder = $this->db->table('jobs');

        $subQuery = $this->db->table('users_jobs')
            ->select('job_id')
            ->where('user_id', 3);

        $builder->whereNotIn('id', $subQuery);

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testOrWhereIn(): void
    {
        $builder = $this->db->table('jobs');

        $builder->where('id', 2)->orWhereIn('name', ['Politician', 'Accountant']);

        $expectedSQL   = 'SELECT * FROM "jobs" WHERE "id" = 2 OR "name" IN (\'Politician\',\'Accountant\')';
        $expectedBinds = [
            'id' => [
                2,
                true,
            ],
            'name' => [
                [
                    'Politician',
                    'Accountant',
                ],
                true,
            ],
        ];

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
        $this->assertSame($expectedBinds, $builder->getBinds());
    }

    public function testOrWhereInSubQuery(): void
    {
        $expectedSQL = 'SELECT * FROM "jobs" WHERE "deleted_at" IS NULL OR "id" IN (SELECT "job_id" FROM "users_jobs" WHERE "user_id" = 3)';

        // Closure
        $builder = $this->db->table('jobs');

        $builder->where('deleted_at', null)->orWhereIn('id', static fn (BaseBuilder $builder) => $builder->select('job_id')->from('users_jobs')->where('user_id', 3));

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));

        // Builder
        $builder = $this->db->table('jobs');

        $subQuery = $this->db->table('users_jobs')
            ->select('job_id')
            ->where('user_id', 3);

        $builder->where('deleted_at', null)->orWhereIn('id', $subQuery);

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testOrWhereNotIn(): void
    {
        $builder = $this->db->table('jobs');

        $builder->where('id', 2)->orWhereNotIn('name', ['Politician', 'Accountant']);

        $expectedSQL   = 'SELECT * FROM "jobs" WHERE "id" = 2 OR "name" NOT IN (\'Politician\',\'Accountant\')';
        $expectedBinds = [
            'id' => [
                2,
                true,
            ],
            'name' => [
                [
                    'Politician',
                    'Accountant',
                ],
                true,
            ],
        ];

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
        $this->assertSame($expectedBinds, $builder->getBinds());
    }

    public function testOrWhereNotInSubQuery(): void
    {
        $expectedSQL = 'SELECT * FROM "jobs" WHERE "deleted_at" IS NULL OR "id" NOT IN (SELECT "job_id" FROM "users_jobs" WHERE "user_id" = 3)';

        // Closure
        $builder = $this->db->table('jobs');

        $builder->where('deleted_at', null)->orWhereNotIn('id', static fn (BaseBuilder $builder) => $builder->select('job_id')->from('users_jobs')->where('user_id', 3));

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));

        // Builder
        $builder = $this->db->table('jobs');

        $subQuery = $this->db->table('users_jobs')
            ->select('job_id')
            ->where('user_id', 3);

        $builder->where('deleted_at', null)->orWhereNotIn('id', $subQuery);

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/4443
     */
    public function testWhereWithLower(): void
    {
        $builder = $this->db->table('jobs');
        $builder->where('LOWER(jobs.name)', 'accountant');

        $expectedSQL = 'SELECT * FROM "jobs" WHERE LOWER(jobs.name) = \'accountant\'';
        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testWhereValueIsString(): void
    {
        $builder = $this->db->table('users');

        $builder->where('id', '1');

        $expectedSQL = <<<'SQL'
            SELECT * FROM "users" WHERE "id" = '1'
            SQL;
        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testWhereValueIsFloat(): void
    {
        $builder = $this->db->table('users');

        $builder->where('id', 1.234);

        $expectedSQL = <<<'SQL'
            SELECT * FROM "users" WHERE "id" = 1.234
            SQL;
        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    /**
     * The current behavior assumes MySQL.
     * Other databases may not work well, so we may want to change the behavior
     * to match the specifications of the database driver.
     */
    public function testWhereValueIsTrue(): void
    {
        $builder = $this->db->table('users');

        $builder->where('id', true);

        $expectedSQL = 'SELECT * FROM "users" WHERE "id" = 1';
        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    /**
     * The current behavior assumes MySQL.
     * Other databases may not work well, so we may want to change the behavior
     * to match the specifications of the database driver.
     */
    public function testWhereValueIsFalse(): void
    {
        $builder = $this->db->table('users');

        $builder->where('id', false);

        $expectedSQL = 'SELECT * FROM "users" WHERE "id" = 0';
        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    /**
     * Check if SQL injection is not possible when unexpected values are passed
     */
    public function testWhereValueIsArray(): void
    {
        $builder = $this->db->table('users');

        $builder->where('id', ['a', 'b']);

        // SQL syntax error
        $expectedSQL = <<<'SQL'
            SELECT * FROM "users" WHERE "id" = ('a','b')
            SQL;
        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    /**
     * Check if SQL injection is not possible when unexpected values are passed
     */
    public function testWhereValueIsArrayOfArray(): void
    {
        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('Array to string conversion');

        $builder = $this->db->table('users');

        $builder->where('id', [['a', 'b'], ['c', 'd']]);

        $builder->getCompiledSelect();
    }

    /**
     * Check if SQL injection is not possible when unexpected values are passed
     */
    public function testWhereValueIsArrayOfObject(): void
    {
        $this->expectException(Error::class);
        $this->expectExceptionMessage('Object of class stdClass could not be converted to string');

        $builder = $this->db->table('users');

        $builder->where('id', [(object) ['a' => 'b'], (object) ['c' => 'd']]);

        $builder->getCompiledSelect();
    }

    public function testWhereValueIsNull(): void
    {
        $builder = $this->db->table('users');

        $builder->where('id', null);

        $expectedSQL = 'SELECT * FROM "users" WHERE "id" IS NULL';
        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    /**
     * Check if SQL injection is not possible when unexpected values are passed
     */
    public function testWhereValueIsStdClass(): void
    {
        $this->expectException(Error::class);
        $this->expectExceptionMessage('Object of class stdClass could not be converted to string');

        $builder = $this->db->table('users');

        $builder->where('id', (object) ['a' => 'b']);

        $builder->getCompiledSelect();
    }

    /**
     * Check if SQL injection is not possible when unexpected values are passed
     */
    public function testWhereValueIsDateTime(): void
    {
        $this->expectException(Error::class);
        $this->expectExceptionMessage('Object of class DateTime could not be converted to string');

        $builder = $this->db->table('users');

        $builder->where('id', new DateTime('2022-02-19 12:00'));

        $builder->getCompiledSelect();
    }
}
