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
final class LimitTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;
    protected $seed    = CITestSeeder::class;

    public function testLimit(): void
    {
        $jobs = $this->db->table('job')
            ->limit(2)
            ->get()
            ->getResult();

        $this->assertCount(2, $jobs);
        $this->assertSame('Developer', $jobs[0]->name);
        $this->assertSame('Politician', $jobs[1]->name);
    }

    public function testLimitAndOffset(): void
    {
        $jobs = $this->db->table('job')
            ->limit(2, 2)
            ->get()
            ->getResult();

        $this->assertCount(2, $jobs);
        $this->assertSame('Accountant', $jobs[0]->name);
        $this->assertSame('Musician', $jobs[1]->name);
    }
}
