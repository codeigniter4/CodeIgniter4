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

/**
 * @internal
 *
 * @group Others
 */
final class LikeTest extends CIUnitTestCase
{
    protected $db;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = new MockConnection([]);
    }

    public function testSimpleLike(): void
    {
        $builder = new BaseBuilder('job', $this->db);

        $builder->like('name', 'veloper');

        $expectedSQL   = "SELECT * FROM \"job\" WHERE \"name\" LIKE '%veloper%' ESCAPE '!'";
        $expectedBinds = [
            'name' => [
                '%veloper%',
                true,
            ],
        ];

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
        $this->assertSame($expectedBinds, $builder->getBinds());
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/3970
     */
    public function testLikeWithRawSql(): void
    {
        $builder = new BaseBuilder('users', $this->db);

        $sql    = "concat(users.name, ' ', IF(users.surname IS NULL or users.surname = '', '', users.surname))";
        $rawSql = new RawSql($sql);
        $builder->like($rawSql, 'value', 'both');

        $expectedSQL   = "SELECT * FROM \"users\" WHERE  {$sql}  LIKE '%value%' ESCAPE '!' ";
        $expectedBinds = [
            $rawSql->getBindingKey() => [
                '%value%',
                true,
            ],
        ];

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
        $this->assertSame($expectedBinds, $builder->getBinds());
    }

    public function testLikeNoSide(): void
    {
        $builder = new BaseBuilder('job', $this->db);

        $builder->like('name', 'veloper', 'none');

        $expectedSQL   = "SELECT * FROM \"job\" WHERE \"name\" LIKE 'veloper' ESCAPE '!'";
        $expectedBinds = [
            'name' => [
                'veloper',
                true,
            ],
        ];

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
        $this->assertSame($expectedBinds, $builder->getBinds());
    }

    public function testLikeBeforeOnly(): void
    {
        $builder = new BaseBuilder('job', $this->db);

        $builder->like('name', 'veloper', 'before');

        $expectedSQL   = "SELECT * FROM \"job\" WHERE \"name\" LIKE '%veloper' ESCAPE '!'";
        $expectedBinds = [
            'name' => [
                '%veloper',
                true,
            ],
        ];

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
        $this->assertSame($expectedBinds, $builder->getBinds());
    }

    public function testLikeAfterOnly(): void
    {
        $builder = new BaseBuilder('job', $this->db);

        $builder->like('name', 'veloper', 'after');

        $expectedSQL   = "SELECT * FROM \"job\" WHERE \"name\" LIKE 'veloper%' ESCAPE '!'";
        $expectedBinds = [
            'name' => [
                'veloper%',
                true,
            ],
        ];

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
        $this->assertSame($expectedBinds, $builder->getBinds());
    }

    public function testOrLike(): void
    {
        $builder = new BaseBuilder('job', $this->db);

        $builder->like('name', 'veloper')->orLike('name', 'ian');

        $expectedSQL   = "SELECT * FROM \"job\" WHERE \"name\" LIKE '%veloper%' ESCAPE '!' OR  \"name\" LIKE '%ian%' ESCAPE '!'";
        $expectedBinds = [
            'name' => [
                '%veloper%',
                true,
            ],
            'name.1' => [
                '%ian%',
                true,
            ],
        ];

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
        $this->assertSame($expectedBinds, $builder->getBinds());
    }

    public function testNotLike(): void
    {
        $builder = new BaseBuilder('job', $this->db);

        $builder->notLike('name', 'veloper');

        $expectedSQL   = "SELECT * FROM \"job\" WHERE \"name\" NOT LIKE '%veloper%' ESCAPE '!'";
        $expectedBinds = [
            'name' => [
                '%veloper%',
                true,
            ],
        ];

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
        $this->assertSame($expectedBinds, $builder->getBinds());
    }

    public function testOrNotLike(): void
    {
        $builder = new BaseBuilder('job', $this->db);

        $builder->like('name', 'veloper')->orNotLike('name', 'ian');

        $expectedSQL   = "SELECT * FROM \"job\" WHERE \"name\" LIKE '%veloper%' ESCAPE '!' OR  \"name\" NOT LIKE '%ian%' ESCAPE '!'";
        $expectedBinds = [
            'name' => [
                '%veloper%',
                true,
            ],
            'name.1' => [
                '%ian%',
                true,
            ],
        ];

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
        $this->assertSame($expectedBinds, $builder->getBinds());
    }

    public function testCaseInsensitiveLike(): void
    {
        $builder = new BaseBuilder('job', $this->db);

        $builder->like('name', 'VELOPER', 'both', null, true);

        $expectedSQL   = "SELECT * FROM \"job\" WHERE LOWER(\"name\") LIKE '%veloper%' ESCAPE '!'";
        $expectedBinds = [
            'name' => [
                '%veloper%',
                true,
            ],
        ];

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
        $this->assertSame($expectedBinds, $builder->getBinds());
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/5775
     */
    public function testDBPrefixAndCoulmnWithTablename(): void
    {
        $this->db = new MockConnection(['DBPrefix' => 'db_']);
        $builder  = new BaseBuilder('test', $this->db);

        $builder->like('test.field', 'string');

        $expectedSQL = <<<'SQL'
            SELECT * FROM "db_test" WHERE "db_test"."field" LIKE '%string%' ESCAPE '!'
            SQL;
        $expectedBinds = [
            'test.field' => [
                '%string%',
                true,
            ],
        ];
        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
        $this->assertSame($expectedBinds, $builder->getBinds());
    }
}
