<?php

namespace CodeIgniter\Database\Builder;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockConnection;

/**
 * @internal
 */
final class LikeTest extends CIUnitTestCase
{
    protected $db;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = new MockConnection([]);
    }

    public function testSimpleLike()
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

    public function testLikeNoSide()
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

    public function testLikeBeforeOnly()
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

    public function testLikeAfterOnly()
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

    public function testOrLike()
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

    public function testNotLike()
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

    public function testOrNotLike()
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

    /**
     * @group single
     */
    public function testCaseInsensitiveLike()
    {
        $builder = new BaseBuilder('job', $this->db);

        $builder->like('name', 'VELOPER', 'both', null, true);

        $expectedSQL   = "SELECT * FROM \"job\" WHERE LOWER(name) LIKE '%veloper%' ESCAPE '!'";
        $expectedBinds = [
            'name' => [
                '%veloper%',
                true,
            ],
        ];

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
        $this->assertSame($expectedBinds, $builder->getBinds());
    }
}
