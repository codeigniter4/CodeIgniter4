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
use CodeIgniter\Database\RawSql;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockConnection;
use DateTime;
use Error;
use ErrorException;
use stdClass;

/**
 * @internal
 */
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

    public function testSimpleWhere()
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

    public function testWhereNoEscape()
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

    public function testWhereCustomKeyOperator()
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

    public function testWhereAssociateArray()
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

    public function testWhereAssociateArrayKeyHasEqualValueIsNull()
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

    public function testWhereCustomString()
    {
        $builder = $this->db->table('jobs');

        $where = "id > 2 AND name != 'Accountant'";

        $expectedSQL   = "SELECT * FROM \"jobs\" WHERE \"id\" > 2 AND \"name\" != 'Accountant'";
        $expectedBinds = [];

        $builder->where($where);
        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
        $this->assertSame($expectedBinds, $builder->getBinds());
    }

    public function testWhereRawSql()
    {
        $builder = $this->db->table('jobs');

        $sql = "id > 2 AND name != 'Accountant'";
        $builder->where(new RawSql($sql));

        $expectedSQL   = "SELECT * FROM \"jobs\" WHERE id > 2 AND name != 'Accountant'";
        $expectedBinds = [];

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
        $this->assertSame($expectedBinds, $builder->getBinds());
    }

    public function testWhereValueSubQuery()
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

    public function testOrWhere()
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

    public function testOrWhereSameColumn()
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

    public function testWhereIn()
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

    public function testWhereInSubQuery()
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

    public function provideInvalidKeys()
    {
        return [
            'null'         => [null],
            'empty string' => [''],
        ];
    }

    /**
     * @dataProvider provideInvalidKeys
     *
     * @param mixed $key
     */
    public function testWhereInvalidKeyThrowInvalidArgumentException($key)
    {
        $this->expectException('InvalidArgumentException');
        $builder = $this->db->table('jobs');

        $builder->whereIn($key, ['Politician', 'Accountant']);
    }

    public function provideInvalidValues()
    {
        return [
            'null'                    => [null],
            'not array'               => ['not array'],
            'not instanceof \Closure' => [new stdClass()],
        ];
    }

    /**
     * @dataProvider provideInvalidValues
     *
     * @param mixed $values
     */
    public function testWhereInEmptyValuesThrowInvalidArgumentException($values)
    {
        $this->expectException('InvalidArgumentException');
        $builder = $this->db->table('jobs');

        $builder->whereIn('name', $values);
    }

    public function testWhereNotIn()
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

    public function testWhereNotInSubQuery()
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

    public function testOrWhereIn()
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

    public function testOrWhereInSubQuery()
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

    public function testOrWhereNotIn()
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

    public function testOrWhereNotInSubQuery()
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
    public function testWhereWithLower()
    {
        $builder = $this->db->table('jobs');
        $builder->where('LOWER(jobs.name)', 'accountant');

        $expectedSQL = 'SELECT * FROM "jobs" WHERE LOWER(jobs.name) = \'accountant\'';
        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testWhereValueIsString()
    {
        $builder = $this->db->table('users');

        $builder->where('id', '1');

        $expectedSQL = <<<'SQL'
            SELECT * FROM "users" WHERE "id" = '1'
            SQL;
        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testWhereValueIsFloat()
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
    public function testWhereValueIsTrue()
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
    public function testWhereValueIsFalse()
    {
        $builder = $this->db->table('users');

        $builder->where('id', false);

        $expectedSQL = 'SELECT * FROM "users" WHERE "id" = 0';
        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    /**
     * Check if SQL injection is not possible when unexpected values are passed
     */
    public function testWhereValueIsArray()
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
    public function testWhereValueIsArrayOfArray()
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
    public function testWhereValueIsArrayOfObject()
    {
        $this->expectException(Error::class);
        $this->expectExceptionMessage('Object of class stdClass could not be converted to string');

        $builder = $this->db->table('users');

        $builder->where('id', [(object) ['a' => 'b'], (object) ['c' => 'd']]);

        $builder->getCompiledSelect();
    }

    public function testWhereValueIsNull()
    {
        $builder = $this->db->table('users');

        $builder->where('id', null);

        $expectedSQL = 'SELECT * FROM "users" WHERE "id" IS NULL';
        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    /**
     * Check if SQL injection is not possible when unexpected values are passed
     */
    public function testWhereValueIsStdClass()
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
    public function testWhereValueIsDateTime()
    {
        $this->expectException(Error::class);
        $this->expectExceptionMessage('Object of class DateTime could not be converted to string');

        $builder = $this->db->table('users');

        $builder->where('id', new DateTime('2022-02-19 12:00'));

        $builder->getCompiledSelect();
    }
}
