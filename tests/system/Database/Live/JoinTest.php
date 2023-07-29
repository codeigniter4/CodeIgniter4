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
final class JoinTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;
    protected $seed    = CITestSeeder::class;

    public function testSimpleJoin(): void
    {
        $row = $this->db->table('job')
            ->select('job.id as job_id, job.name as job_name, user.id as user_id, user.name as user_name')
            ->join('user', 'user.id = job.id')
            ->get()
            ->getRow();

        $this->assertSame(1, (int) $row->job_id);
        $this->assertSame(1, (int) $row->user_id);
        $this->assertSame('Derek Jones', $row->user_name);
        $this->assertSame('Developer', $row->job_name);
    }
}
