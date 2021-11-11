<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Database\Live;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @group DatabaseLive
 *
 * @internal
 */
final class EmptyTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;
    protected $seed    = 'Tests\Support\Database\Seeds\CITestSeeder';

    public function testEmpty()
    {
        $this->db->table('misc')->emptyTable();

        $this->assertSame(0, $this->db->table('misc')->countAll());
    }

    public function testTruncate()
    {
        $this->db->table('misc')->truncate();

        $this->assertSame(0, $this->db->table('misc')->countAll());
    }
}
