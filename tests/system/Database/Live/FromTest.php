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
use Tests\Support\Database\Seeds\CITestSeeder;

/**
 * @group DatabaseLive
 *
 * @internal
 */
final class FromTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;
    protected $seed    = CITestSeeder::class;

    public function testFromCanAddTables(): void
    {
        $result = $this->db->table('job')->from('misc')->get()->getResult();

        $this->assertCount(12, $result);
    }

    public function testFromCanOverride(): void
    {
        $result = $this->db->table('job')->from('misc', true)->get()->getResult();

        $this->assertCount(3, $result);
    }

    public function testFromWithWhere(): void
    {
        $result = $this->db->table('job')->from('user')->where('user.id', 1)->get()->getResult();

        $this->assertCount(4, $result);
    }
}
