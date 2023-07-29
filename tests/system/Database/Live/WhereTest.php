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
final class WhereTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;
    protected $seed    = CITestSeeder::class;

    public function testWhereSimpleKeyValue(): void
    {
        $row = $this->db->table('job')->where('id', 1)->get()->getRow();

        $this->assertSame(1, (int) $row->id);
        $this->assertSame('Developer', $row->name);
    }

    public function testWhereCustomKeyValue(): void
    {
        $jobs = $this->db->table('job')->where('id !=', 1)->get()->getResult();

        $this->assertCount(3, $jobs);
    }

    public function testWhereArray(): void
    {
        $jobs = $this->db->table('job')->where([
            'id >'    => 2,
            'name !=' => 'Accountant',
        ])->get()->getResult();

        $this->assertCount(1, $jobs);

        $job = current($jobs);
        $this->assertSame('Musician', $job->name);
    }

    public function testWhereCustomString(): void
    {
        $jobs = $this->db->table('job')->where("id > 2 AND name != 'Accountant'")
            ->get()
            ->getResult();

        $this->assertCount(1, $jobs);

        $job = current($jobs);
        $this->assertSame('Musician', $job->name);
    }

    public function testOrWhere(): void
    {
        $jobs = $this->db->table('job')
            ->where('name !=', 'Accountant')
            ->orWhere('id >', 3)
            ->get()
            ->getResult();

        $this->assertCount(3, $jobs);
        $this->assertSame('Developer', $jobs[0]->name);
        $this->assertSame('Politician', $jobs[1]->name);
        $this->assertSame('Musician', $jobs[2]->name);
    }

    public function testOrWhereSameColumn(): void
    {
        $jobs = $this->db->table('job')
            ->where('name', 'Developer')
            ->orWhere('name', 'Politician')
            ->get()
            ->getResult();

        $this->assertCount(2, $jobs);
        $this->assertSame('Developer', $jobs[0]->name);
        $this->assertSame('Politician', $jobs[1]->name);
    }

    public function testWhereIn(): void
    {
        $jobs = $this->db->table('job')
            ->whereIn('name', ['Politician', 'Accountant'])
            ->get()
            ->getResult();

        $this->assertCount(2, $jobs);
        $this->assertSame('Politician', $jobs[0]->name);
        $this->assertSame('Accountant', $jobs[1]->name);
    }

    public function testWhereNotIn(): void
    {
        $jobs = $this->db->table('job')
            ->whereNotIn('name', ['Politician', 'Accountant'])
            ->get()
            ->getResult();

        $this->assertCount(2, $jobs);
        $this->assertSame('Developer', $jobs[0]->name);
        $this->assertSame('Musician', $jobs[1]->name);
    }

    public function testSubQuery(): void
    {
        $subQuery = $this->db->table('job')
            ->select('id')
            ->where('name', 'Developer')
            ->getCompiledSelect();

        if ($this->db->DBDriver === 'OCI8') {
            $jobs = $this->db->table('job')
                ->where('"id" not in (' . $subQuery . ')', null, false)
                ->orderBy('id')
                ->get()
                ->getResult();
        } else {
            $jobs = $this->db->table('job')
                ->where('id not in (' . $subQuery . ')', null, false)
                ->get()
                ->getResult();
        }

        $this->assertCount(3, $jobs);
        $this->assertSame('Politician', $jobs[0]->name);
        $this->assertSame('Accountant', $jobs[1]->name);
        $this->assertSame('Musician', $jobs[2]->name);
    }

    public function testSubQueryAnotherType(): void
    {
        $subQuery = $this->db->table('job')
            ->select('id')
            ->where('name', 'Developer')
            ->getCompiledSelect();

        if ($this->db->DBDriver === 'OCI8') {
            $jobs = $this->db->table('job')
                ->where('"id" = (' . $subQuery . ')', null, false)
                ->get()
                ->getResult();
        } else {
            $jobs = $this->db->table('job')
                ->where('id = (' . $subQuery . ')', null, false)
                ->get()
                ->getResult();
        }

        $this->assertCount(1, $jobs);
        $this->assertSame('Developer', $jobs[0]->name);
    }

    public function testWhereNullParam(): void
    {
        $this->db->table('job')
            ->insert([
                'name'        => 'Brewmaster',
                'description' => null,
            ]);

        $jobs = $this->db->table('job')
            ->where('description', null)
            ->get()
            ->getResult();

        $this->assertCount(1, $jobs);
        $this->assertSame('Brewmaster', $jobs[0]->name);
    }

    public function testWhereIsNull(): void
    {
        $this->db->table('job')
            ->insert([
                'name'        => 'Brewmaster',
                'description' => null,
            ]);

        $jobs = $this->db->table('job')
            ->where('description IS NULL')
            ->get()
            ->getResult();

        $this->assertCount(1, $jobs);
        $this->assertSame('Brewmaster', $jobs[0]->name);
    }

    public function testWhereIsNotNull(): void
    {
        $this->db->table('job')
            ->insert([
                'name'        => 'Brewmaster',
                'description' => null,
            ]);

        $jobs = $this->db->table('job')
            ->where('description IS NOT NULL')
            ->get()
            ->getResult();

        $this->assertCount(4, $jobs);
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/4443
     */
    public function testWhereWithLower(): void
    {
        $builder = $this->db->table('job');
        $builder->insert([
            'name'        => 'Brewmaster',
            'description' => null,
        ]);

        $ANSISQLDriverNames = ['OCI8'];
        $lowerJobName       = sprintf('LOWER(%s.name)', $this->db->prefixTable('job'));

        if (in_array($this->db->DBDriver, $ANSISQLDriverNames, true)) {
            $lowerJobName = sprintf('LOWER("%s"."name")', $this->db->prefixTable('job'));
        }

        $job = $builder
            ->where($lowerJobName, 'brewmaster')
            ->get()
            ->getResult();
        $this->assertCount(1, $job);
    }
}
