<?php

namespace CodeIgniter\Database\Builder;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockConnection;

/**
 * @internal
 */
final class OrderTest extends CIUnitTestCase
{
    protected $db;

    //--------------------------------------------------------------------

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = new MockConnection([]);
    }

    //--------------------------------------------------------------------

    public function testOrderAscending()
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->orderBy('name', 'asc');

        $expectedSQL = 'SELECT * FROM "user" ORDER BY "name" ASC';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    //--------------------------------------------------------------------

    public function testOrderDescending()
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->orderBy('name', 'desc');

        $expectedSQL = 'SELECT * FROM "user" ORDER BY "name" DESC';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    //--------------------------------------------------------------------

    public function testOrderRandom()
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->orderBy('name', 'random');

        $expectedSQL = 'SELECT * FROM "user" ORDER BY RAND()';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    //--------------------------------------------------------------------
}
