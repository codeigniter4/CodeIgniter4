<?php

namespace CodeIgniter\Database\Live;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @group DatabaseLive
 *
 * @internal
 */
final class WhereTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;

    protected $seed = 'Tests\Support\Database\Seeds\CITestSeeder';

    public function testWhereSimpleKeyValue()
    {
        $row = $this->db->table('job')->where('id', 1)->get()->getRow();

        $this->assertSame(1, (int) $row->id);
        $this->assertSame('Developer', $row->name);
    }

    public function testWhereCustomKeyValue()
    {
        $jobs = $this->db->table('job')->where('id !=', 1)->get()->getResult();

        $this->assertCount(3, $jobs);
    }

    public function testWhereArray()
    {
        $jobs = $this->db->table('job')->where([
            'id >'    => 2,
            'name !=' => 'Accountant',
        ])->get()->getResult();

        $this->assertCount(1, $jobs);

        $job = current($jobs);
        $this->assertSame('Musician', $job->name);
    }

    public function testWhereCustomString()
    {
        $jobs = $this->db->table('job')->where("id > 2 AND name != 'Accountant'")
            ->get()
            ->getResult();

        $this->assertCount(1, $jobs);

        $job = current($jobs);
        $this->assertSame('Musician', $job->name);
    }

    public function testOrWhere()
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

    public function testOrWhereSameColumn()
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

    public function testWhereIn()
    {
        $jobs = $this->db->table('job')
            ->whereIn('name', ['Politician', 'Accountant'])
            ->get()
            ->getResult();

        $this->assertCount(2, $jobs);
        $this->assertSame('Politician', $jobs[0]->name);
        $this->assertSame('Accountant', $jobs[1]->name);
    }

    /**
     * @group single
     */
    public function testWhereNotIn()
    {
        $jobs = $this->db->table('job')
            ->whereNotIn('name', ['Politician', 'Accountant'])
            ->get()
            ->getResult();

        $this->assertCount(2, $jobs);
        $this->assertSame('Developer', $jobs[0]->name);
        $this->assertSame('Musician', $jobs[1]->name);
    }

    public function testSubQuery()
    {
        $subQuery = $this->db->table('job')
            ->select('id')
            ->where('name', 'Developer')
            ->getCompiledSelect();

        $jobs = $this->db->table('job')
            ->where('id not in (' . $subQuery . ')', null, false)
            ->get()
            ->getResult();

        $this->assertCount(3, $jobs);
        $this->assertSame('Politician', $jobs[0]->name);
        $this->assertSame('Accountant', $jobs[1]->name);
        $this->assertSame('Musician', $jobs[2]->name);
    }

    public function testSubQueryAnotherType()
    {
        $subQuery = $this->db->table('job')
            ->select('id')
            ->where('name', 'Developer')
            ->getCompiledSelect();

        $jobs = $this->db->table('job')
            ->where('id = (' . $subQuery . ')', null, false)
            ->get()
            ->getResult();

        $this->assertCount(1, $jobs);
        $this->assertSame('Developer', $jobs[0]->name);
    }

    public function testWhereNullParam()
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

    public function testWhereIsNull()
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

    public function testWhereIsNotNull()
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
    public function testWhereWithLower()
    {
        $builder = $this->db->table('job');
        $builder->insert([
            'name'        => 'Brewmaster',
            'description' => null,
        ]);

        $job = $builder
            ->where(sprintf('LOWER(%s.name)', $this->db->prefixTable('job')), 'brewmaster')
            ->get()
            ->getResult();
        $this->assertCount(1, $job);
    }
}
