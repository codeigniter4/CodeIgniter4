<?php

namespace CodeIgniter\Database\Builder;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockConnection;

/**
 * @internal
 */
final class BaseTest extends CIUnitTestCase
{
    protected $db;

    //--------------------------------------------------------------------

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = new MockConnection([]);
    }

    //--------------------------------------------------------------------

    public function testDbReturnsConnection()
    {
        $builder = $this->db->table('jobs');

        $result = $builder->db();

        $this->assertInstanceOf(MockConnection::class, $result);
    }

    //--------------------------------------------------------------------

    public function testGetTableReturnsTable()
    {
        $builder = $this->db->table('jobs');

        $result = $builder->getTable();
        $this->assertSame('jobs', $result);
    }

    public function testGetTableIgnoresFrom()
    {
        $builder = $this->db->table('jobs');

        $builder->from('foo');
        $result = $builder->getTable();
        $this->assertSame('jobs', $result);
    }
}
