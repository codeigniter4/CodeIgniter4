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

    public function testGetFromReturnsFirstFrom()
    {
        $builder = $this->db->table('jobs');

        $result = $builder->getFrom();
        $this->assertSame('"jobs"', $result);
    }

    public function testGetFromCanReturnsSecondFrom()
    {
        $builder = $this->db->table('jobs');
        $builder->from('foo');

        $result = $builder->getFrom(0);
        $this->assertSame('"jobs"', $result);

        $result = $builder->getFrom(1);
        $this->assertSame('"foo"', $result);
    }
}
