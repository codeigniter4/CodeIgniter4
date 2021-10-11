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
final class FromTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;
    protected $seed    = 'Tests\Support\Database\Seeds\CITestSeeder';

    public function testFromCanAddTables()
    {
        $result = $this->db->table('job')->from('misc')->get()->getResult();

        $this->assertCount(12, $result);
    }

    public function testFromCanOverride()
    {
        $result = $this->db->table('job')->from('misc', true)->get()->getResult();

        $this->assertCount(3, $result);
    }

    public function testFromWithWhere()
    {
        $result = $this->db->table('job')->from('user')->where('user.id', 1)->get()->getResult();

        $this->assertCount(4, $result);
    }
}
