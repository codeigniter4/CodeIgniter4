<?php

namespace CodeIgniter\Database\Builder;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockConnection;

/**
 * @internal
 */
final class LimitTest extends CIUnitTestCase
{
    protected $db;

    //--------------------------------------------------------------------

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = new MockConnection([]);
    }

    //--------------------------------------------------------------------

    public function testLimitAlone()
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->limit(5);

        $expectedSQL = 'SELECT * FROM "user"  LIMIT 5';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    //--------------------------------------------------------------------

    public function testLimitAndOffset()
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->limit(5, 1);

        $expectedSQL = 'SELECT * FROM "user"  LIMIT 1, 5';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    //--------------------------------------------------------------------

    public function testLimitAndOffsetMethod()
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->limit(5)->offset(1);

        $expectedSQL = 'SELECT * FROM "user"  LIMIT 1, 5';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    //--------------------------------------------------------------------
}
