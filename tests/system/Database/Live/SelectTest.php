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
final class SelectTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;
    protected $seed    = CITestSeeder::class;

    public function testSelectAllByDefault(): void
    {
        $row = $this->db->table('job')->get()->getRowArray();

        $this->assertArrayHasKey('id', $row);
        $this->assertArrayHasKey('name', $row);
        $this->assertArrayHasKey('description', $row);
    }

    public function testSelectSingleColumn(): void
    {
        $row = $this->db->table('job')->select('name')->get()->getRowArray();

        $this->assertArrayNotHasKey('id', $row);
        $this->assertArrayHasKey('name', $row);
        $this->assertArrayNotHasKey('description', $row);
    }

    public function testSelectMultipleColumns(): void
    {
        $row = $this->db->table('job')->select('name, description')->get()->getRowArray();

        $this->assertArrayNotHasKey('id', $row);
        $this->assertArrayHasKey('name', $row);
        $this->assertArrayHasKey('description', $row);
    }

    public function testSelectMax(): void
    {
        $result = $this->db->table('job')->selectMax('id')->get()->getRow();

        $this->assertSame(4, (int) $result->id);
    }

    public function testSelectMaxWithAlias(): void
    {
        $result = $this->db->table('job')->selectMax('id', 'xam')->get()->getRow();

        $this->assertSame(4, (int) $result->xam);
    }

    public function testSelectMin(): void
    {
        $result = $this->db->table('job')->selectMin('id')->get()->getRow();

        $this->assertSame(1, (int) $result->id);
    }

    public function testSelectMinWithAlias(): void
    {
        $result = $this->db->table('job')->selectMin('id', 'xam')->get()->getRow();

        $this->assertSame(1, (int) $result->xam);
    }

    public function testSelectAvg(): void
    {
        $result = $this->db->table('job')->selectAvg('id')->get()->getRow();

        $this->assertSame(2.5, (float) $result->id);
    }

    public function testSelectAvgWithAlias(): void
    {
        $result = $this->db->table('job')->selectAvg('id', 'xam')->get()->getRow();

        $this->assertSame(2.5, (float) $result->xam);
    }

    public function testSelectSum(): void
    {
        $result = $this->db->table('job')->selectSum('id')->get()->getRow();

        $this->assertSame(10, (int) $result->id);
    }

    public function testSelectSumWithAlias(): void
    {
        $result = $this->db->table('job')->selectSum('id', 'xam')->get()->getRow();

        $this->assertSame(10, (int) $result->xam);
    }

    public function testSelectCount(): void
    {
        $result = $this->db->table('job')->selectCount('id')->get()->getRow();

        $this->assertSame(4, (int) $result->id);
    }

    public function testSelectCountWithAlias(): void
    {
        $result = $this->db->table('job')->selectCount('id', 'xam')->get()->getRow();

        $this->assertSame(4, (int) $result->xam);
    }

    public function testSelectDistinctWorkTogether(): void
    {
        $users = $this->db->table('user')->select('country')->distinct()->get()->getResult();

        $this->assertCount(3, $users);
    }

    public function testSelectDistinctCanBeTurnedOff(): void
    {
        $users = $this->db->table('user')->select('country')->distinct(false)->get()->getResult();

        $this->assertCount(4, $users);
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/1226
     */
    public function testSelectWithMultipleWheresOnSameColumn(): void
    {
        $users = $this->db->table('user')
            ->where('id', 1)
            ->orWhereIn('id', [2, 3])
            ->get()
            ->getResultArray();

        $this->assertCount(3, $users);

        foreach ($users as $user) {
            $this->assertContains((int) $user['id'], [1, 2, 3]);
        }
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/1226
     */
    public function testSelectWithMultipleWheresOnSameColumnAgain(): void
    {
        $users = $this->db->table('user')
            ->whereIn('id', [1, 2])
            ->orWhere('id', 3)
            ->get()
            ->getResultArray();

        $this->assertCount(3, $users);

        foreach ($users as $user) {
            $this->assertContains((int) $user['id'], [1, 2, 3]);
        }
    }
}
