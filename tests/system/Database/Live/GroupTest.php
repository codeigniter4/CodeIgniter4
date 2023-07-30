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
final class GroupTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;
    protected $seed    = CITestSeeder::class;

    public function testGroupBy(): void
    {
        $result = $this->db->table('user')
            ->select('name')
            ->groupBy('name')
            ->get()
            ->getResult();

        $this->assertCount(4, $result);
    }

    public function testHavingBy(): void
    {
        $isANSISQL = in_array($this->db->DBDriver, ['OCI8'], true);

        if ($isANSISQL) {
            $result = $this->db->table('job')
                ->select('name')
                ->groupBy('name')
                ->having('SUM("id") >', 2)
                ->get()
                ->getResultArray();
        } else {
            $result = $this->db->table('job')
                ->select('name')
                ->groupBy('name')
                ->having('SUM(id) >', 2)
                ->get()
                ->getResultArray();
        }

        $this->assertCount(2, $result);
    }

    public function testOrHavingBy(): void
    {
        $isANSISQL = in_array($this->db->DBDriver, ['OCI8'], true);

        if ($isANSISQL) {
            $result = $this->db->table('user')
                ->select('id')
                ->groupBy('id')
                ->having('id >', 3)
                ->orHaving('SUM("id") >', 2)
                ->get()
                ->getResult();
        } else {
            $result = $this->db->table('user')
                ->select('id')
                ->groupBy('id')
                ->having('id >', 3)
                ->orHaving('SUM(id) >', 2)
                ->get()
                ->getResult();
        }

        $this->assertCount(2, $result);
    }

    public function testHavingIn(): void
    {
        $result = $this->db->table('job')
            ->select('name')
            ->groupBy('name')
            ->orderBy('name', 'asc')
            ->havingIn('name', ['Developer', 'Politician'])
            ->get()
            ->getResult();

        $this->assertCount(2, $result);
        $this->assertSame('Developer', $result[0]->name);
        $this->assertSame('Politician', $result[1]->name);
    }

    public function testorHavingIn(): void
    {
        $result = $this->db->table('job')
            ->select('name')
            ->groupBy('name')
            ->orderBy('name', 'asc')
            ->havingIn('name', ['Developer'])
            ->orHavingIn('name', ['Politician'])
            ->get()
            ->getResult();

        $this->assertCount(2, $result);
        $this->assertSame('Developer', $result[0]->name);
        $this->assertSame('Politician', $result[1]->name);
    }

    public function testHavingNotIn(): void
    {
        $result = $this->db->table('job')
            ->select('name')
            ->groupBy('name')
            ->orderBy('name', 'asc')
            ->havingNotIn('name', ['Developer', 'Politician'])
            ->get()
            ->getResult();

        $this->assertCount(2, $result);
        $this->assertSame('Accountant', $result[0]->name);
        $this->assertSame('Musician', $result[1]->name);
    }

    public function testOrHavingNotIn(): void
    {
        $isANSISQL = in_array($this->db->DBDriver, ['OCI8'], true);

        if ($isANSISQL) {
            $result = $this->db->table('job')
                ->select('name')
                ->groupBy('name')
                ->orderBy('name', 'asc')
                ->having('SUM("id") >', 2)
                ->orHavingNotIn('name', ['Developer', 'Politician'])
                ->get()
                ->getResult();
        } else {
            $result = $this->db->table('job')
                ->select('name')
                ->groupBy('name')
                ->orderBy('name', 'asc')
                ->having('SUM(id) >', 2)
                ->orHavingNotIn('name', ['Developer', 'Politician'])
                ->get()
                ->getResult();
        }

        $this->assertCount(2, $result);
        $this->assertSame('Accountant', $result[0]->name);
        $this->assertSame('Musician', $result[1]->name);
    }

    public function testHavingLike(): void
    {
        $result = $this->db->table('job')
            ->select('name')
            ->groupBy('name')
            ->havingLike('name', 'elo')
            ->get()
            ->getResult();

        $this->assertCount(1, $result);
        $this->assertSame('Developer', $result[0]->name);
    }

    public function testNotHavingLike(): void
    {
        $result = $this->db->table('job')
            ->select('name')
            ->groupBy('name')
            ->orderBy('name', 'asc')
            ->notHavingLike('name', 'ian')
            ->get()
            ->getResult();

        $this->assertCount(2, $result);
        $this->assertSame('Accountant', $result[0]->name);
        $this->assertSame('Developer', $result[1]->name);
    }

    public function testOrHavingLike(): void
    {
        $result = $this->db->table('job')
            ->select('name')
            ->groupBy('name')
            ->orderBy('name', 'asc')
            ->havingLike('name', 'elo')
            ->orHavingLike('name', 'cc')
            ->get()
            ->getResult();

        $this->assertCount(2, $result);
        $this->assertSame('Accountant', $result[0]->name);
        $this->assertSame('Developer', $result[1]->name);
    }

    public function testOrNotHavingLike(): void
    {
        $isANSISQL = in_array($this->db->DBDriver, ['OCI8'], true);

        if ($isANSISQL) {
            $result = $this->db->table('job')
                ->select('name')
                ->groupBy('name')
                ->orderBy('name', 'asc')
                ->having('SUM("id") >', 2)
                ->orNotHavingLike('name', 'ian')
                ->get()
                ->getResult();
        } else {
            $result = $this->db->table('job')
                ->select('name')
                ->groupBy('name')
                ->orderBy('name', 'asc')
                ->having('SUM(id) >', 2)
                ->orNotHavingLike('name', 'ian')
                ->get()
                ->getResult();
        }

        $this->assertCount(3, $result);
        $this->assertSame('Accountant', $result[0]->name);
        $this->assertSame('Developer', $result[1]->name);
        $this->assertSame('Musician', $result[2]->name);
    }

    public function testAndHavingGroupStart(): void
    {
        $isANSISQL = in_array($this->db->DBDriver, ['OCI8'], true);

        if ($isANSISQL) {
            $result = $this->db->table('job')
                ->select('name')
                ->groupBy('name')
                ->orderBy('name', 'asc')
                ->having('SUM("id") >', 2)
                ->havingGroupStart()
                ->having('SUM("id") <=', 4)
                ->havingLike('name', 'ant', 'before')
                ->havingGroupEnd()
                ->get()
                ->getResult();
        } else {
            $result = $this->db->table('job')
                ->select('name')
                ->groupBy('name')
                ->orderBy('name', 'asc')
                ->having('SUM(id) >', 2)
                ->havingGroupStart()
                ->having('SUM(id) <=', 4)
                ->havingLike('name', 'ant', 'before')
                ->havingGroupEnd()
                ->get()
                ->getResult();
        }

        $this->assertCount(1, $result);
        $this->assertSame('Accountant', $result[0]->name);
    }

    public function testOrHavingGroupStart(): void
    {
        $isANSISQL = in_array($this->db->DBDriver, ['OCI8'], true);

        if ($isANSISQL) {
            $result = $this->db->table('job')
                ->select('name')
                ->groupBy('name')
                ->orderBy('name', 'asc')
                ->having('SUM("id") >', 2)
                ->orHavingGroupStart()
                ->having('SUM("id") <=', 4)
                ->havingLike('name', 'ant', 'before')
                ->havingGroupEnd()
                ->get()
                ->getResult();
        } else {
            $result = $this->db->table('job')
                ->select('name')
                ->groupBy('name')
                ->orderBy('name', 'asc')
                ->having('SUM(id) >', 2)
                ->orHavingGroupStart()
                ->having('SUM(id) <=', 4)
                ->havingLike('name', 'ant', 'before')
                ->havingGroupEnd()
                ->get()
                ->getResult();
        }

        $this->assertCount(2, $result);
        $this->assertSame('Accountant', $result[0]->name);
        $this->assertSame('Musician', $result[1]->name);
    }

    public function testNotHavingGroupStart(): void
    {
        $isANSISQL = in_array($this->db->DBDriver, ['OCI8'], true);

        if ($isANSISQL) {
            $result = $this->db->table('job')
                ->select('name')
                ->groupBy('name')
                ->orderBy('name', 'asc')
                ->having('SUM("id") >', 2)
                ->notHavingGroupStart()
                ->having('SUM("id") <=', 4)
                ->havingLike('name', 'ant', 'before')
                ->havingGroupEnd()
                ->get()
                ->getResult();
        } else {
            $result = $this->db->table('job')
                ->select('name')
                ->groupBy('name')
                ->orderBy('name', 'asc')
                ->having('SUM(id) >', 2)
                ->notHavingGroupStart()
                ->having('SUM(id) <=', 4)
                ->havingLike('name', 'ant', 'before')
                ->havingGroupEnd()
                ->get()
                ->getResult();
        }

        $this->assertCount(1, $result);
        $this->assertSame('Musician', $result[0]->name);
    }

    public function testOrNotHavingGroupStart(): void
    {
        $isANSISQL = in_array($this->db->DBDriver, ['OCI8'], true);

        if ($isANSISQL) {
            $result = $this->db->table('job')
                ->select('name')
                ->groupBy('name')
                ->orderBy('name', 'asc')
                ->having('SUM("id") >', 2)
                ->orNotHavingGroupStart()
                ->having('SUM("id") <', 2)
                ->havingLike('name', 'o')
                ->havingGroupEnd()
                ->get()
                ->getResult();
        } else {
            $result = $this->db->table('job')
                ->select('name')
                ->groupBy('name')
                ->orderBy('name', 'asc')
                ->having('SUM(id) >', 2)
                ->orNotHavingGroupStart()
                ->having('SUM(id) <', 2)
                ->havingLike('name', 'o')
                ->havingGroupEnd()
                ->get()
                ->getResult();
        }

        $this->assertCount(3, $result);
        $this->assertSame('Accountant', $result[0]->name);
        $this->assertSame('Musician', $result[1]->name);
        $this->assertSame('Politician', $result[2]->name);
    }

    public function testAndGroups(): void
    {
        $result = $this->db->table('user')
            ->groupStart()
            ->where('id >=', 3)
            ->where('name !=', 'Chris Martin')
            ->groupEnd()
            ->where('country', 'US')
            ->get()
            ->getResult();

        $this->assertCount(1, $result);
        $this->assertSame('Richard A Causey', $result[0]->name);
    }

    public function testOrGroups(): void
    {
        $result = $this->db->table('user')
            ->where('country', 'Iran')
            ->orGroupStart()
            ->where('id >=', 3)
            ->where('name !=', 'Richard A Causey')
            ->groupEnd()
            ->get()
            ->getResult();

        $this->assertCount(2, $result);
        $this->assertSame('Ahmadinejad', $result[0]->name);
        $this->assertSame('Chris Martin', $result[1]->name);
    }

    public function testNotGroups(): void
    {
        $result = $this->db->table('user')
            ->where('country', 'US')
            ->notGroupStart()
            ->where('id >=', 3)
            ->where('name !=', 'Chris Martin')
            ->groupEnd()
            ->get()
            ->getResult();

        $this->assertCount(1, $result);
        $this->assertSame('Derek Jones', $result[0]->name);
    }

    public function testOrNotGroups(): void
    {
        $result = $this->db->table('user')
            ->where('country', 'US')
            ->orNotGroupStart()
            ->where('id >=', 2)
            ->where('country', 'Iran')
            ->groupEnd()
            ->get()
            ->getResult();

        $this->assertCount(3, $result);
        $this->assertSame('Derek Jones', $result[0]->name);
        $this->assertSame('Richard A Causey', $result[1]->name);
        $this->assertSame('Chris Martin', $result[2]->name);
    }

    public function testGroupByCount(): void
    {
        $result = $this->db->table('user')
            ->selectCount('id', 'count')
            ->groupBy('country')
            ->orderBy('country', 'desc')
            ->get()
            ->getResult();

        $this->assertSame(2, (int) $result[0]->count);
    }
}
