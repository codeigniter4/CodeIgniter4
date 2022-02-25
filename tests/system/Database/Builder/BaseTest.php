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

use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockConnection;

/**
 * @internal
 */
final class BaseTest extends CIUnitTestCase
{
    protected $db;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = new MockConnection([]);
    }

    public function testDbReturnsConnection()
    {
        $builder = $this->db->table('jobs');

        $result = $builder->db();

        $this->assertInstanceOf(MockConnection::class, $result);
    }

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

    public function testSubquerySameBaseBuilderObject()
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('The subquery cannot be the same object as the main query object.');

        $builder = $this->db->table('users');

        $builder->fromSubquery($builder, 'sub');
    }
}
